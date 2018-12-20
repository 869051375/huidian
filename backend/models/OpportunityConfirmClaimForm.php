<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\OpportunityPublic;
use Yii;
use yii\base\Model;

class OpportunityConfirmClaimForm extends Model
{
    public $opportunity_id;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['opportunity_id'], 'integer'],
            [['opportunity_id'], 'required'],
            ['opportunity_id', 'validateOpportunityId'],
        ];
    }

    public function validateOpportunityId()
    {
        $dataStr = date('Y-m-d', time());
        $startTime = strtotime($dataStr);
        $endTime = strtotime($dataStr) + 86400;
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunity = CrmOpportunity::findOne($this->opportunity_id);
        if(null == $this->opportunity)
        {
            $this->addError('opportunity_id', '商机不存在');
            return ;
        }
        /** @var OpportunityPublic $opportunityPublic */
        $opportunityPublic = $this->opportunity->opportunityPublic;
        if(null == $opportunityPublic)
        {
            $this->addError('opportunity_id', '商机公海不存在！');
            return ;
        }

        if($administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('opportunity_id', '您不是业务员，无权提取此商机！');
        }

        if(null == $administrator->department || !$administrator->isBelongCompany())
        {
            $this->addError('opportunity_id', '您无权操作！');
            return ;
        }

        if($administrator->department_id != $opportunityPublic->department_id && $administrator->department->parent_id != $opportunityPublic->department_id)
        {
            $this->addError('opportunity_id', '此商机您无权提取！');
        }

        if($this->opportunity->is_receive && $this->opportunity->administrator_id > 0)
        {
            $this->addError('opportunity_id', '该商机已经被提取，不能进行该操作');
        }

        //当前商机对应的商机公海保护的数量
        if($opportunityPublic->extract_number_limit > 0)
        {
            $department = $opportunityPublic->department;
            $ids = [];
            if(null != $department)
            {
                $children = $department->children;
                if(null != $children)
                {
                    foreach ($children as $child)
                    {
                        $ids[] = $child->id;
                    }
                }
            }
            //判断当前商机对应的商机公海是否有最大提取限制（24小时之内）,今天已经提取到商机的数量，利用提取时间大于0，并且部门属于商机公海部门或者是商机公海对应部门的下属部门
            $count = CrmOpportunity::find()
                ->where(['administrator_id' => $administrator->id])
                ->andWhere(['>=', 'extract_time', $startTime])
                ->andWhere(['<=', 'extract_time', $endTime])
                ->andWhere(['>', 'extract_time', 0])
                ->andWhere(['or', ['department_id' => $opportunityPublic->department_id],['in', 'department_id',$ids]])
                ->count();
            if($count >= $opportunityPublic->extract_number_limit)
            {
                $this->addError('opportunity_id', '对不起，今日提取商机数量以达到上限，请耐心等待明日再次提取哦！');
            }
        }
    }

    public function confirmClaim()
    {
        if(!$this->validate())
        {
            return false;
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunity->is_receive = 1;
        $this->opportunity->administrator_id = $administrator->id;
        $this->opportunity->administrator_name = $administrator->name;
        $this->opportunity->company_id = $administrator->company_id;
        $this->opportunity->department_id = $administrator->department_id;
        $this->opportunity->extract_time  = time();
        /** @var CrmCustomer $customer */
        $customer = $this->opportunity->customer;
        // 判断当前商机的客户是否在客户公海,当商机和客户同在公海时，某人提取商机，则客户从公海消除，且该业务员成为该客户负责人
        $t = Yii::$app->db->beginTransaction();
        try
        {
            if(null != $customer && $customer->isPublic())
            {
                $customer->customer_public_id = 0;
                $customer->is_receive = 1;
                $customer->administrator_id = $administrator->id;
                $customer->company_id = $administrator->company_id;
                $customer->department_id = $administrator->department_id;
                $customer->save(false);

                //新增合伙人
                CrmCustomerCombine::addTeam($administrator, $customer);
            }
            //提取公海商机时需要操作记录
            CrmCustomerLog::add('公海商机提取成功', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_OPPORTUNITY);
            //当客户不在公海，客户下的某个商机在公海，对这个客户的公海商机提取，需要写一条客户操作记录。如果客户在公海，提取商机时同时提取公海客户，并且分别写一条操作记录
            CrmCustomerLog::add($this->opportunity->opportunityPublic->name.'公海商机"'.$this->opportunity->id.'"提取成功', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
            $this->opportunity->opportunity_public_id = 0;
            $this->opportunity->save(false);
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
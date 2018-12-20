<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityProduct;
use common\models\MessageRemind;
use common\models\OpportunityAssignDepartment;
use common\models\Product;
use common\utils\BC;
use Yii;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/12
 * Time: 下午4:39
 */

/**
 * Class OpportunityForm
 * @package backend\models
 *
 * @property Company $company
 * @property Administrator $administrator
 * @property Administrator $admin
 */
class OpportunityForm extends Model
{
    public $update_id;
    public $name;
    public $customer_id;
    public $customer_name;
    public $administrator_id;
    public $progress;
    public $predict_deal_time;
    public $business_subject_id;
    public $remark;

    public $company_id;

    /**
     * @var OpportunityProductForm[]
     */
    public $products = [];

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var BusinessSubject
     */
    public $businessSubject;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    public function rules()
    {
        return [
            [['name', 'customer_id', 'progress'], 'required'],
            ['name', 'string', 'max' => 30, 'tooLong' => '只能包含至多30个字符！'],
            [['remark'], 'string', 'max' => 80],
            [['name', 'remark'], 'filter', 'filter' => 'trim'],
            [['name'],'match','pattern'=>'/^[(\x{4E00}-\x{9FA5})a-zA-Z0-9\-\+\)\()]*$/u','message'=>'只允许输入文字、英文字母（不区分大小写）、数字、+、-、括号，且不允许夹带空格符号'],
            [['business_subject_id'], 'integer'],
            [['customer_id'], 'validateCustomerId'],
            [['business_subject_id'], 'validateBusinessSubjectId'],
            [['predict_deal_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['progress'], 'in', 'range' => [20,40,60,80]],
            [['business_subject_id'], 'default', 'value' => '0'],
            [['update_id'], 'validateUpdateId'],
            [['company_id', 'administrator_id'], 'required', 'on' => ['insert']],
            [['company_id', 'administrator_id'], 'integer'],

        ];
    }

    public function validateCustomerId()
    {
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customer)
        {
            $this->addError('customer_id', '客户不存在');
        }
        else
        {
            if($this->customer->customerPublic)
            {
                $this->addError('customer_id', '该客户已存在于客户公海“'. $this->customer->customerPublic->name.'”中，无法创建商机，请前往提取');
            }
        }
    }

    public function validateBusinessSubjectId()
    {
        if($this->business_subject_id > 0)
        {
            $this->businessSubject = BusinessSubject::findOne($this->business_subject_id);
            if(null == $this->businessSubject || $this->businessSubject->customer_id != $this->customer_id)
            {
                $this->addError('business_subject_id', '找不到业务主体信息');
            }
        }
    }

    public function validateUpdateId()
    {
        if(null == $this->opportunity || $this->administrator_id != $this->administrator->id || !$this->opportunity->isStatusNotDeal())
        {
            $this->addError('name', '您不能修改该商机');
        }
    }

    public function attributeLabels()
    {
        return [
            'customer_name' => '客户名称',
            'name' => '商机名称',
            'progress' => '商机状态',
            'predict_deal_time' => '预计成交时间',
            'business_subject_id' => '关联业务主体',
            'remark' => '商机备注',
            'company_id' => '指派公司',
            'administrator_id' => '商机负责人',
        ];
    }

    /**
     * @return null|CrmDepartment
     */
    private function prepare()
    {
        if(!$this->validate())
        {
            return null;
        }
        if(!is_array($this->products) || empty($this->products))
        {
            $this->addError('products', '请选择商品');
            return null;
        }

        $department_id = 0;
        $departmentIds = [];
        /** @var Product $product */
        foreach($this->products as $product)
        {
//            if(!$product->validate())
//            {
//                $error = $product->getFirstErrors();
//                $this->addError('products', reset($error));
//                return null;
//            }

//            if($department_id != 0 && $department_id != $product->product->department_id)
//            {
//                $this->addError('products', '不能同时添加多个不同商机分配部门的商品');
//                return null;
//            }

//            $opportunityAssignDepartment = OpportunityAssignDepartment::find()->where(['product_id' => $product->product_id])->one();
            $opportunityAssignDepartments = OpportunityAssignDepartment::find()->where(['product_id' => $product->product_id])->all();
            if(null == $opportunityAssignDepartments)
            {
                $this->addError('products', '尚未设置商机分配部门');
                return null;
            }
            else
            {
                $departmentId = [];
                foreach ($opportunityAssignDepartments as $opportunityAssignDepartment)
                {
                    if($department_id != 0 && $department_id != $opportunityAssignDepartment->department_id)
                    {
                        $this->addError('products', '不能同时添加多个不同商机分配部门的商品');
                        return null;
                    }
                    $departmentId[] = $opportunityAssignDepartment->department_id;
                }
            }
            $q = CrmOpportunityProduct::find()->alias('p')->joinWith(['opportunity o'])
                ->andWhere(['o.customer_id' => $this->customer_id, 'p.product_id' => $product->product_id])
                ->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]]);
            if(!empty($this->business_subject_id))
            {
                $q->andWhere(['o.business_subject_id' => $this->business_subject_id]);
            }
            else
            {
                $q->andWhere(['o.business_subject_id' => '0']);
            }
            if(null != $this->opportunity && !$this->opportunity->isNewRecord)
            {
                $q->andWhere(['!=', 'o.id', $this->opportunity->id]);
            }
            $opportunityCount = $q->count();
            if($opportunityCount > 0)
            {
                $this->addError('products', '该客户存在 "'.$product->product_name.'" 的未成交商机');
                return null;
            }
//            else
//            {
//                if($this->customer->user)
//                {
//                    /** @var Order $order */
//                    $order = Order::find()
//                        ->andWhere([
//                            'product_id' => $product->product_id,
//                            'status' => Order::STATUS_PENDING_PAY,
//                            'user_id' => $this->customer->user->id,
//                        ])->one();
//                    if($order->virtualOrder->isPendingPayment())
//                    {
//                        $this->addError('products', '该客户存在 "'.$product->product->name.'" 的未付款订单');
//                        return null;
//                    }
//                }
//            }

//            $department_id = $product->product->department_id;
            /** @var OpportunityAssignDepartment $opportunityAssignDepartment */
//            $department_id = $opportunityAssignDepartment->department_id;//同公司的相同部门的商品
            $departmentIds[] = $departmentId;
        }
//        $department = CrmDepartment::findOne($department_id);


        //校验商品是否有共同的商机分配部门
        foreach ($departmentIds as $i => $ids)
        {
            if($i > 0 )
            {
                $commonDepartmentIds = array_intersect($departmentIds[0],$departmentIds[$i]);
                if(empty($commonDepartmentIds))
                {
                    $this->addError('products', '不能同时添加多个不同商机分配部门的商品，请分别创建。');
                    return null;
                }
                else
                {
                    $commonCompanyIds = [];
                    foreach ($commonDepartmentIds as $commonDepartmentId)
                    {
                        $department = CrmDepartment::findOne($commonDepartmentId);
                        if(null == $department)
                        {
                            $this->addError('products', '操作有误。');
                            return null;
                        }
                        else
                        {
                            $commonCompanyIds[] = $department->company_id;
                        }
                    }

                    /** @var \common\models\Administrator $administrator */
                    $administrator = Yii::$app->user->identity;
                    $company_id = 0;
                    if($administrator->isBelongCompany() && $administrator->company_id)
                    {
                        $company_id = $administrator->company_id;
                    }
                    if(!in_array($company_id, $commonCompanyIds))
                    {
                        $this->addError('products', '不能同时添加多个不同商机分配部门的商品，请分别创建。');
                        return null;
                    }
                }
            }
        }

        /** @var array $departmentId */
        $departments = CrmDepartment::find()->where(['in','id',$departmentId])->andWhere(['status' => CrmDepartment::STATUS_ACTIVE])->all();
        if(null == $departments)
        {
            $this->addError('products', '找不到商机分配部门信息');
            return null;
        }
        return $departments;
    }

    public function update()
    {
        $department = $this->prepare();
        if(null == $department)
        {
            return false;
        }

        /** @var CrmOpportunityProduct[] $deleteList */
        $deleteList = [];
        foreach($this->opportunity->opportunityProducts as $opportunityProduct)
        {
            $has = false;
            foreach($this->products as $opportunityProductForm)
            {
                if($opportunityProduct->id == $opportunityProductForm->update_id)
                {
                    $has = true;
                    $opportunityProductForm->updateModel = $opportunityProduct;
                }
            }
            if(!$has)
            {
                $deleteList[] = $opportunityProduct;
            }
        }

        $this->opportunity->name = $this->name;
        $this->opportunity->progress = $this->progress;
        $this->opportunity->predict_deal_time = strtotime($this->predict_deal_time);
        $this->opportunity->business_subject_id = $this->business_subject_id;
        $this->opportunity->remark = $this->remark;
        $this->opportunity->updated_at = time();
        $this->opportunity->updater_id = $this->administrator->id;
        $this->opportunity->updater_name = $this->administrator->name;
        $this->opportunity->save(false);

        foreach($this->products as $product)
        {
            $product->opportunity = $this->opportunity;
            $product->save();
        }

        foreach($deleteList as $opportunityProduct)
        {
            $opportunityProduct->delete();
        }
        $this->opportunity->updateTotalAmount();
        CrmCustomerLog::add('编辑商机', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->opportunity;
    }

    public function save()
    {
//        $department = $this->prepare();
//        if(null == $department)
//        {
//            return false;
//        }
        $departments = $this->prepare();
        $departmentId = [];
        $leaderId = [];
        $assignAdministratorId = [];
        $companyId = [];
        if(null == $departments)
        {
            return false;
        }
        else
        {
            /** @var CrmDepartment $department */
            foreach ($departments as $department)
            {
                $departmentId[] = $department->id;
                $leaderId[] = $department->leader_id;
                $assignAdministratorId[] = $department->assign_administrator_id;
                $companyId[] =$department->company_id;
            }
        }
        $model = new CrmOpportunity();
//        if($this->administrator->department && $this->administrator->department_id != $department->id && $department->id != $this->administrator->department->parent_id)
        if($this->administrator->department && !in_array($this->administrator->department_id, $departmentId) && !in_array($this->administrator->department->parent_id, $departmentId))
        {
//            if(!$department->leader_id && !$department->assign_administrator_id)
//            {
//                $this->addError('products', '该商品类别找不到对应的负责部门的负责人');
//                return false;
//            }
            if(empty($leaderId) && empty($assignAdministratorId))
            {
                $this->addError('products', '该商品类别找不到对应的负责部门的负责人');
                return false;
            }
            else
            {
                // 分配给别人的商机
                // 优先分配给部门设置的默认分配人员
                $departments = CrmDepartment::find()->select('id')->where(['company_id' => $this->administrator->company_id])->all();
                $companyDepartmentIds = [];
                foreach ($departments as $department)
                {
                    $companyDepartmentIds[] = $department->id;
                }
                $departmentId = array_intersect($companyDepartmentIds, $departmentId);
                $department = CrmDepartment::findOne(array_pop($departmentId));
                if(null == $department)
                {
                    $this->addError('products', '该商品类别找不到对应的负责部门的负责人');
                    return false;
                }

                /** @var CrmDepartment $firstDepartment */
                if($department->assign_administrator_id)
                {
                    $toAdministrator = Administrator::findOne($department->assign_administrator_id);
                }
                else
                {
                    if($department->leader_id)
                    {
                        $toAdministrator = Administrator::findOne($department->leader_id);
                    }
                    else
                    {
                        $this->addError('products', '该商品类别找不到对应的负责部门的负责人');
                        return false;
                    }
                }

                $model->send_administrator_id = $this->administrator->id;
                $model->administrator_id = $toAdministrator->id;
                $model->administrator_name = $toAdministrator->name;
                $model->department_id = $toAdministrator->department_id;
                $model->company_id = $toAdministrator->company_id;
                $model->is_receive = 0;
            }
        }
        else
        {
            if(empty($this->administrator->department_id))
            {
                $this->addError('products', '您的信息中尚未设置所属部门，请先设置所属部门');
                return false;
            }
            else
            {
                // 自己的商机
                $model->administrator_id = $this->administrator->id;
                $model->administrator_name = $this->administrator->name;
                $model->department_id = $this->administrator->department_id;
                $model->company_id = $this->administrator->company_id;
                $model->is_receive = 1;
            }
        }

//        if($model->is_receive)
//        {
//            CrmCustomerCombine::addTeam($this->administrator, $this->customer);
//        }

        $model->remark = $this->remark;
        $model->name = $this->name;
        $model->progress = $this->progress;
        $model->business_subject_id = $this->business_subject_id;
        $model->predict_deal_time = strtotime($this->predict_deal_time);
        $model->status = CrmOpportunity::STATUS_NOT_DEAL;
        $model->customer_id = $this->customer->id;
        $model->customer_name = $this->customer->name;
        $model->user_id = $this->customer->user_id;

        $model->creator_name = $this->administrator->name;
        $model->creator_id = $this->administrator->id;
        $model->created_at = time();

        $model->save(false);

        foreach($this->products as $product)
        {
            $product->opportunity = $model;
            $product->save();
        }
        $model->updateTotalAmount();
        CrmCustomerLog::add('创建商机', $model->customer_id, $model->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);

        if(!$model->is_receive)
        {
            //消息提醒
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '你有一条待确认商机：'. $model->name .'，请点击前往查看！';
            $popup_message = '您有一个待确认商机：'. $model->name .'，请及时查看哦！';
            $type = MessageRemind::TYPE_COMMON;
            $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_NEED_CONFIRM;
            $receive_id = $model->administrator_id;
            $customer_id = $model->customer_id;
            $opportunity_id = $model->id;
            $sign = 'g-'.$receive_id.'-'.$opportunity_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, $opportunity_id, $administrator);
            }
        }
        return $model;
    }

    /**
     * @param $model CrmOpportunity
     */
    public function setUpdateModel($model)
    {
        $this->opportunity = $model;
        $this->update_id = $model->id;
        $this->name = $model->name;
        $this->customer_id = $model->customer_id;
        $this->customer_name = $model->customer_name;
        $this->administrator_id = $model->administrator_id;
        $this->progress = $model->progress;
        $this->predict_deal_time = $model->predict_deal_time > 0 ? date('Y-m-d', $model->predict_deal_time) : '';
        if($model->businessSubject)
        {
            $this->businessSubject = $model->businessSubject;
            $this->business_subject_id = $model->business_subject_id;
        }
        $this->remark = $model->remark;

        foreach($model->opportunityProducts as $opportunityProduct)
        {
            $p = new OpportunityProductForm();
            $p->update_id = $opportunityProduct->id;
            $p->updateModel = $opportunityProduct;
            $p->product_id = $opportunityProduct->product_id;
            $p->product_price_id = $opportunityProduct->productPrice ? $opportunityProduct->productPrice->id : 0;
            $p->price = $opportunityProduct->price;
            $p->category_name = $opportunityProduct->category_name;
            $p->product_name = $opportunityProduct->product_name;
            $p->original_price = $opportunityProduct->getOriginalPrice();
            $p->product = $opportunityProduct->product;
            $p->productPrice = $opportunityProduct->productPrice;
            $p->opportunity = $model;
            $p->qty = $opportunityProduct->qty;
            $p->subtotal_price = BC::mul($opportunityProduct->price ,$opportunityProduct->qty);
            $this->products[] = $p;
        }
    }

    public function commonSave()
    {
        $department = $this->prepare();
        if(null == $department)
        {
            return false;
        }

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $model = new CrmOpportunity();
        if($this->customer->customerPublic)
        {
            $this->addError('customer_id', '该客户已存在于客户公海“'. $this->customer->customerPublic->name.'”中，无法创建商机，请前往提取');
            return false;
        }
        if($this->administrator->id != $administrator->id)
        {
            if(empty($this->administrator->department_id))
            {
                $this->addError('products', '商机负责人尚未设置所属部门，请先设置所属部门');
                return false;
            }
            else
            {
                //替别人创建的商机
                $model->send_administrator_id = $administrator->id;
                $model->administrator_id = $this->administrator->id;
                $model->administrator_name = $this->administrator->name;
                $model->department_id = $this->administrator->department_id;
                $model->company_id = $this->administrator->company_id;
                $model->is_receive = 0;
            }
        }
        else
        {
            if(empty($administrator->department_id))
            {
                $this->addError('products', '商机负责人尚未设置所属部门，请先设置所属部门');
                return false;
            }
            else
            {
                // 自己的商机
                $model->administrator_id = $administrator->id;
                $model->administrator_name = $administrator->name;
                $model->department_id = $administrator->department_id;
                $model->company_id = $administrator->company_id;
                $model->is_receive = 1;
            }
        }

        if($model->is_receive)
        {
            CrmCustomerCombine::addTeam($administrator, $this->customer);
        }
        $model->remark = $this->remark;
        $model->name = $this->name;
        $model->progress = $this->progress;
        $model->business_subject_id = $this->business_subject_id ? $this->business_subject_id : 0;
        $model->predict_deal_time = strtotime($this->predict_deal_time);
        $model->status = CrmOpportunity::STATUS_NOT_DEAL;
        $model->customer_id = $this->customer->id;
        $model->customer_name = $this->customer->name;
        $model->user_id = $this->customer->user_id;
        $model->creator_name = $administrator->name;
        $model->creator_id = $administrator->id;
        $model->created_at = time();
        $model->save(false);

        foreach($this->products as $product)
        {
            $product->opportunity = $model;
            $product->save();
        }
        $model->updateTotalAmount();
        CrmCustomerLog::add('创建商机', $model->customer_id, $model->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);

        if(!$model->is_receive)
        {
            //消息提醒
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '你有一条待确认商机：'. $model->name .'，请点击前往查看！';
            $popup_message = '您有一个待确认商机：'. $model->name .'，请及时查看哦！';
            $type = MessageRemind::TYPE_COMMON;
            $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_NEED_CONFIRM;
            $receive_id = $model->administrator_id;
            $customer_id = $model->customer_id;
            $opportunity_id = $model->id;
            $sign = 'g-'.$receive_id.'-'.$opportunity_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, $opportunity_id, $administrator);
            }
        }
        return $model;
    }


    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()
            ->where(['id' => $this->administrator_id])->one();
    }

    public function getAdmin()
    {
        return Administrator::find()
            ->where(['id' => $this->administrator_id])->one();
    }
}
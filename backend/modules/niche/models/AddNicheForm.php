<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmClue;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\Niche;
use common\models\NicheProduct;
use common\models\NichePublicDepartment;
use common\utils\BC;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;


/**
 * 用于新增商机
 * @SWG\Definition(required={"name", "customer_id", "predict_deal_time", "source_id", "channel_id", "next_follow_time"}, @SWG\Xml(name="AddNicheForm"))
 */
class AddNicheForm extends NicheForm
{
    /**
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/CreateNicheProductForm"))
     * @var CreateNicheProductForm[]
     */
    public $products;

    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['products'],'validateProducts'],
            [['products'],'validateCrossProductLine'],
            [['products'],'validatePower'],
            [['products'],'validatePowerProduct'],
            [['predict_deal_time'],'validatePredictDealTime'],
            [['next_follow_time'],'validateNextFollowTime'],
            [['products'],'required','message'=>'请选择商机商品！']
        ]);
    }

    public function load($data, $formName = '')
    {
        $this->products = [];
        if(isset($data['products']) && $data['products'] != '')
        {
            $products = $data['products'];
            foreach ($products as $product)
            {
                $p = new CreateNicheProductForm();
                $p->load($product);
                $this->products[] = $p;
            }
            unset($data['products']);
        }
        return parent::load($data, $formName);
    }

    public function validatePowerProduct()
    {
        foreach ($this->products as $product_id)
        {
            $query = new Query();
            $one = $query->from(['ni'=>Niche::tableName()])
                ->leftJoin(['np'=>NicheProduct::tableName()],'np.niche_id = ni.id')
                ->where(['np.product_id'=>$product_id->product_id])
                ->andWhere(['ni.customer_id'=>$this->customer_id])
                ->andWhere(['in','progress',[Niche::PROGRESS_10,Niche::PROGRESS_30,Niche::PROGRESS_60,Niche::PROGRESS_80]])
                ->one();
            if (!empty($one))
            {
                return $this->addError('products', '对不起，当前客户下已存在同样的商机，请检查后再保存！');
            }
        }
        return true;
    }

    public function validatePower()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        /** @var NichePublicDepartment $niche_public */
        $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
        if (!empty($niche_public))
        {
            if ($niche_public->nichePublic->is_own == 1)
            {
                //包含
                $niche_count = Niche::find()->where(['administrator_id' => $administrator->id])->andWhere("niche_public_id = 0 or niche_public_id is null ")->count();
                if ((int)$niche_count >= $niche_public->nichePublic->have_max_sum)
                {
                    return $this->addError('id', '对不起，当前用户拥有商机已达上限');
                }
            }
//            else
//            {
//                //不包含 新增的时候 不包含不需要验证
//                $niche_count = Niche::find()->where(['administrator_id' => $administrator->id])->andWhere(['or',['is_distribution'=>1],['is_extract'=>1],['is_transfer'=>1],['is_cross'=>1]])->andWhere("niche_public_id = 0 or niche_public_id is null ")->count();
//            }
        }
        return true;
    }

    public function validateProducts()
    {
        if(empty($this->products))
        {
            $this->addError('products', '请选择商机商品！');
        }
        else
        {
            foreach($this->products as $nicheProductForm)
            {
                if(!$nicheProductForm->validate())
                {
                    $errors = $nicheProductForm->getFirstErrors();
                    $this->addError('products', current($errors));
                }
            }
        }
    }

    public function validateCrossProductLine()
    {
        if(empty($this->products))
        {
            $this->addError('products', '请选择商机商品！');
        }
        else
        {
            /** @var CreateNicheProductForm  $nicheProductForm */
            foreach($this->products as $nicheProductForm)
            {
               if (isset($nicheProductForm->opportunityAssignDepartmentOne)){
                   $department_arr[] = $nicheProductForm->opportunityAssignDepartmentOne->department_id;
               }
            }

            if (isset($department_arr))
            {
                $department_arr = array_unique($department_arr);
                if (count($department_arr) > 1)
                {
                    $this->addError('products', '跨产品线商品不能在同一个商机内保存。');
                }
            }
            else
            {
                return $this->addError('niche_public_id', '该商品分配部门没有设置商机公海。');
            }
        }
        return true;
    }

    public function validatePredictDealTime()
    {
        if (strtotime($this->predict_deal_time) < strtotime(date('Y-m-d',time()))){
            $this->addError('predict_deal_time', '预计成交时间不能小于当前创建时间。');
        }
    }

    public function validateNextFollowTime()
    {
        if (strtotime($this->next_follow_time) < time()){
            $this->addError('next_follow_time', '下次跟进时间不能小于当前创建时间。');
        }
    }





    /**
     * @param $administrator Administrator
     * @return \common\models\Niche|null
     */
    public function save($administrator)
    {
        if(!$this->hasErrors())
        {
            /** @var Niche $niche */
            $niche = new Niche();
            $niche->load($this->attributes, '');

            //如果是跨产品线的商机的话
            /** @var CreateNicheProductForm $nicheProductForm */
            foreach($this->products as $nicheProductForm)
            {
                if (isset($nicheProductForm->opportunityAssignDepartment)){
                    if (!in_array($administrator->department_id,$nicheProductForm->opportunityAssignDepartment)){
                        /** @var NichePublicDepartment $niche_public_id */
                        $niche_public_id = NichePublicDepartment::find()->where(['department_id'=>$nicheProductForm->opportunityAssignDepartmentOne->department_id])->one();
                        if (empty($niche_public_id))
                        {
                            return $this->addError('products', '该商品分配部门没有设置商机公海。');
                        }
                        else
                        {
                            $niche->niche_public_id = $niche_public_id->niche_public_id;
                            $niche->is_cross = 1;
                            $niche->move_public_time= time();
                            $niche->recovery_at= time();
                            $this->addError('niche_public_id', '您创建的商机属于跨产品线商机，此商机已经被转移至“'.$niche_public_id->nichePublic->name.'”商机公海中，请及时关注最新动态。');
                        }
                    }
                }
                else
                {
                    return $this->addError('products', '该商品分配部门没有设置商机公海。');
                }

            }

            $niche->next_follow_time = strtotime($this->next_follow_time);
            $niche->predict_deal_time = strtotime($this->predict_deal_time);
            $niche->administrator_id = $niche->niche_public_id ? 0 :$administrator->id;
            $niche->administrator_name = $niche->niche_public_id ? '' : $administrator->name;
            $niche->creator_id = $administrator->id;
            $niche->creator_name = $administrator->name;
            $niche->progress = Niche::PROGRESS_10;
            $niche->status = Niche::STATUS_NOT_DEAL;
            $niche->source_name = isset($niche->source->name) ? $niche->source->name : '';
            $niche->business_subject_id = isset($niche->businessSubject->id) ? $niche->businessSubject->id : 0;
            $niche->is_new = 1;
            $niche->channel_name = isset($niche->channel->name) ? $niche->channel->name : '';
            $niche->company_id = isset($administrator->company_id) ? $administrator->company_id : 0;
            $niche->department_id = isset($administrator->department_id) ? $administrator->department_id : 0;
            $niche->customer_id = $niche->customer_id ? $niche->customer_id : 0;
            $niche->user_id = $niche->customer->user_id ? $niche->customer->user_id : 0;
            $niche->contacts_id = $niche->contacts_id ? $niche->contacts_id : 0;
            $totalAmount = 0;
            foreach($this->products as $product)
            {
                $totalAmount += BC::mul($product->qty, $product->price);
            }
            $niche->total_amount = $totalAmount;

            $niche->save(false);
            foreach($this->products as $product)
            {
                $product->save($niche);
            }

            /** @var CrmCustomer $customer_one */
            $customer_one = CrmCustomer::find()->where(['id'=>$niche->customer_id])->one();
            /** @var CrmContacts $contract */
            $contract = CrmContacts::find()->where(['customer_id'=>$niche->customer_id])->one();
            //统计埋点
            $data = new CustomerExchangeList();
            if (($customer_one->created_at+10*60) > time())
            {
                /** @var CrmClue $clue_one */
                $clue_one = CrmClue::find()->where(['business_subject_id'=>$customer_one->businessSubject->id])->one();

                if (!empty($clue_one))
                {
                    $data->clueToNiche(['id'=>$clue_one->id,'from'=>'','administrator_id'=>$administrator->id,'province_id'=> isset($contract->province_id) ? $contract->province_id : 0,'city_id'=> isset($contract->city_id) ? $contract->city_id : 0,'district_id' => isset($contract->district_id) ? $contract->district_id : 0,'source_id'=>isset($niche->source_id) ? $niche->source_id : 0,'channel_id'=>isset($niche->channel_id) ? $niche->channel_id : 0]);
                }
            }

            $data->niche(['id'=>$niche->id,'from'=>'','administrator_id'=>$administrator->id,'province_id'=> isset($contract->province_id) ? $contract->province_id : 0,'city_id'=> isset($contract->city_id) ? $contract->city_id : 0,'district_id' => isset($contract->district_id) ? $contract->district_id : 0,'source_id'=>isset($niche->source_id) ? $niche->source_id : 0,'channel_id'=>isset($niche->channel_id) ? $niche->channel_id : 0,'amount'=>$niche->total_amount]);
            $nicheFunnel = new NicheFunnel();
            $nicheFunnel->add($niche->id,$niche->progress);
            //统计埋点结束

            //添加操作记录
            NicheOperationRecord::create($niche->id,'新增商机','商机创建成功');
            return $niche;
        }
        return null;
    }
}

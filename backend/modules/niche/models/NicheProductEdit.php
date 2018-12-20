<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NicheProduct;
use common\models\NichePublicDepartment;
use common\models\NicheTeam;
use Yii;
use yii\base\Model;
use yii\db\Query;


/**
 * 商机商品编辑
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheProductEdit"))
 */
class NicheProductEdit extends Model
{


    /** @var Administrator $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $niche_id;

    /**
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/CreateNicheProductForm"))
     * @var CreateNicheProductForm[]
     */
    public $products;

    public function load($data, $formName = '')
    {
        $this->products = [];
        if(isset($data['products']) && $data['products'] != '')
        {
            $products = $data['products'];
            foreach ($products as $product)
            {
                $p = new CreateNicheProductForm();
                $p->load($product,'');
                $this->products[] = $p;
            }
            unset($data['products']);
        }
        return parent::load($data, $formName);
    }


    public function rules()
    {
        return [
            [['niche_id','products'], 'required'],
            [['niche_id'], 'integer'],
            [['niche_id'], 'validateNicheId'],
            [['products'],'validateProducts'],
            [['products'],'validateCrossProductLine'],
            [['products'],'validatePower'],
        ];
    }

    //编辑商品的时候暂时不走这个验证
    public function validatePowerProduct()
    {
        /** @var Niche $niche_one */
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        foreach ($this->products as $product_id)
        {
            $query = new Query();
            $one = $query->from(['ni'=>Niche::tableName()])
                ->leftJoin(['np'=>NicheProduct::tableName()],'np.niche_id = ni.id')
                ->where(['np.product_id'=>$product_id->product_id])
                ->andWhere('ni.id <> '.$this->niche_id)
                ->andWhere(['ni.customer_id'=>$niche_one->customer_id])
                ->andWhere(['in','progress',[Niche::PROGRESS_10,Niche::PROGRESS_30,Niche::PROGRESS_60,Niche::PROGRESS_80]])
                ->all();
            if (!empty($one))
            {
                return $this->addError('products', '对不起，当前客户下已存在同样的商机，请检查后再保存！');
            }
        }
        return true;
    }

    public function validateNicheId()
    {
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        if (empty($niche_one))
        {
            return $this->addError('niche_id','商机ID不存在');
        }
        return true;
    }

    public function validatePower()
    {
        $this->currentAdministrator->id;
        //是负责人可以修改
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->one();

        //如果是协作成员并且有权限也可以修改
        $nicheTeam_one = NicheTeam::find()->where(['niche_id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->andWhere(['is_update'=>1])->one();

        if (empty($niche_one) && empty($nicheTeam_one))
        {
            $this->addError('products', '暂无修改权限!');
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

            if (isset($department_arr)){
                $department_arr = array_unique($department_arr);
                if (count($department_arr) > 1){
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

    public function save()
    {

        if(!$this->hasErrors())
        {
            /** @var Niche $niche */
            $niche = Niche::find()->where(['id'=>$this->niche_id])->one();
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();

            try{
                NicheProduct::deleteAll(['niche_id'=>$this->niche_id]);
                $total_amount = (float)0;
                foreach ($this->products as $product){
                    $total_amount += (float)$product->amount;
                    $product->save($niche);
                }
                //更新商品价格
                $niche->total_amount = $total_amount;
                $niche->save(false);
                //添加操作记录
                NicheOperationRecord::create($this->niche_id,'编辑商机','编辑了商机商品明细');
                $transaction ->commit();
                $res = true;
            }catch (\Exception $e){
                $transaction -> rollBack();
                $res = false;
            }
            return $res;
        }
        return null;
    }


}

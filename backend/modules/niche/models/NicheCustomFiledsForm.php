<?php

namespace backend\modules\niche\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use backend\modules\niche\models\NicheCustomFileds;


/**
 * 商机客户信息
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCustomFiledsForm"))
 */
class NicheCustomFiledsForm extends Model
{

    /**
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/NicheCustomFileds"))
     * @var NicheCustomFileds[]
     */
    public $fileds;

    /**
     * 类型 (0:商机列表，1：公海列表 2：默认商机列表 3：默认公海列表)
     * @SWG\Property(example = 2)
     * @var integer
     */
    public $type;


    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['fileds'],'validateFileds'],
            [['type'],'required','message'=>'所属类型！']
        ]);
    }

    public function validateFileds()
    {
        if($this->type == NicheCustomFileds::NICHE_FILEDS_LIST_DEFAULT || $this->type == NicheCustomFileds::NICHE_PUBLIC_FILEDS_LIST_DEFAULT){
            $this->addError('type','不允许设置默认列表');
        }
        if($this->type == NicheCustomFileds::NICHE_FILEDS_LIST){
            $filds = array_column(NicheCustomFileds::$fileds_default,'fileds');
        }else{
            $array = array_merge(NicheCustomFileds::$fileds_default,NicheCustomFileds::$fileds_default_public);
            $filds = array_column($array,'fileds');
        }
        $fild = array_column($this->fileds,'fileds');
        for($i=0;$i<count($fild);$i++){
            if(!in_array($fild[$i],$filds)){
                $this->addError('fileds','提交的字段有误');
            }
        }
        return true;
    }

    public function add($administrator)
    {
        $model = \common\models\NicheCustomFileds::find()->where(['administrator_id'=>$administrator->id])->andWhere(['type'=>$this->type])->one();
        if($model){
            $model->updated_at = time();
        }else{
            $model = new \common\models\NicheCustomFileds();
            $model->created_at = time();
        }
        $model->administrator_id = $administrator->id;
        $model->fileds = json_encode($this->fileds);
        $model->type = $this->type;
        return $model->save(false);
    }



}
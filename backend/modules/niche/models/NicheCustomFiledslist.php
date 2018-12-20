<?php

namespace backend\modules\niche\models;

use yii\base\Model;
use common\models\NicheCustomFileds;

/**
 * 商机客户信息
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCustomFiledslist"))
 */
class NicheCustomFiledslist extends Model
{

    /**
     * 类型 (0:商机列表，1：公海列表 2：默认商机列表 3：默认公海列表)
     * @SWG\Property(example = 2)
     * @var integer
     */
    public $type;

    public function rules()
    {
        return [
            [["type"], 'required'],
        ];
    }


    public function getList($administrator)
    {
        if(in_array($this->type,[\backend\modules\niche\models\NicheCustomFileds::NICHE_FILEDS_LIST,\backend\modules\niche\models\NicheCustomFileds::NICHE_PUBLIC_FILEDS_LIST])){
            $fileds = NicheCustomFileds::find()->select('fileds')
                        ->where(['administrator_id'=>$administrator->id])
                        ->andWhere(['type'=>$this->type])
                        ->asArray()
                        ->one();
            if(!empty($fileds)){
                return json_decode($fileds['fileds'],true);
            }
        }
        if($this->type == \backend\modules\niche\models\NicheCustomFileds::NICHE_FILEDS_LIST_DEFAULT || $this->type == \backend\modules\niche\models\NicheCustomFileds::NICHE_FILEDS_LIST){
            $list = \backend\modules\niche\models\NicheCustomFileds::$fileds_default;
            $model = NicheCustomFileds::find()->where(['administrator_id'=>$administrator->id])->andWhere(['type'=>\backend\modules\niche\models\NicheCustomFileds::NICHE_FILEDS_LIST])->one();
            if($model){
                $model->updated_at = time();
            }else{
                $model = new NicheCustomFileds();
                $model->created_at = time();
            }
            $model->administrator_id = $administrator->id;
            $model->fileds = json_encode($list);
            $model->type = \backend\modules\niche\models\NicheCustomFileds::NICHE_FILEDS_LIST;
            $model->save(false);
            return $list;
        }else{
            $list = array_merge(\backend\modules\niche\models\NicheCustomFileds::$fileds_default,\backend\modules\niche\models\NicheCustomFileds::$fileds_default_public);
            $model = NicheCustomFileds::find()->where(['administrator_id'=>$administrator->id])->andWhere(['type'=>\backend\modules\niche\models\NicheCustomFileds::$fileds_default])->one();
            if($model){
                $model->updated_at = time();
            }else{
                $model = new NicheCustomFileds();
                $model->created_at = time();
            }
            $model->administrator_id = $administrator->id;
            $model->fileds = json_encode($list);
            $model->type = \backend\modules\niche\models\NicheCustomFileds::$fileds_default;
            $model->save(false);
            return $list;
        }

    }
}
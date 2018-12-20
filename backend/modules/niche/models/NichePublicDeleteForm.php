<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;


/**
 * 删除商机公海
 * @SWG\Definition(required={"id"}, @SWG\Xml(name="NichePublicDeleteForm"))
 */
class NichePublicDeleteForm extends Model
{
    /**
     * 商机公海id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    public function rules()
    {
        return [
            [['id'],'required'],
            [['id'],'validateNichePublicId'],
        ];

    }

    public function validateNichePublicId()
    {
        $model = new NichePublic();
        $data = $model::find()->where(['id'=>$this->id])->one();
        if(empty($data)){
            $this->addError('id',"暂无数据");
        }
        $niche = new Niche();
        $niches = $niche::findOne(['niche_public_id'=>$this->id]);
        if($niches){
            $this->addError('id',"对不起，当前商机公海下有商机数据，不允许被删除！");
        }
        return true;
    }
    /**
     * @return bool
     */
    public function remove()
    {
        $model = new NichePublic();
        $model::deleteAll(['id'=>$this->id]);
        $models = new NichePublicDepartment();
        return $models::deleteAll(['niche_public_id'=>$this->id]);
    }

}
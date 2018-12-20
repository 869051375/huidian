<?php

namespace backend\modules\niche\models;

use common\models\NicheRecord;
use yii\base\Model;


/**
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NicheRecordList"))
 */
class NicheRecordList extends Model
{

    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;


    public function rules()
    {
        return [
            [['niche_id'],'required'],
            [['niche_id'], 'integer'],
        ];
    }

    public function getNicheRecord()
    {
        return NicheRecord::find()->where(['niche_id'=>$this->niche_id])->orderBy(['id'=>SORT_DESC])->all();
    }

}
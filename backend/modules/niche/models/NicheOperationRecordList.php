<?php

namespace backend\modules\niche\models;

use common\models\NicheOperationRecord;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NicheOperationRecordList"))
 */
class NicheOperationRecordList extends Model
{

    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 每页多少条
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page_num;

    /**
     * 页码
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page;

    public function rules()
    {
        return [
            [['niche_id','page_num','page'],'required'],
            [['niche_id'], 'integer'],
        ];
    }

    public function getNicheOperationRecord()
    {
        $query = NicheOperationRecord::find()->where(['niche_id'=>$this->niche_id])->orderBy(['created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->page_num,
                'page' => $this->page-1,
            ]
        ]);
        return $dataProvider;
    }

}
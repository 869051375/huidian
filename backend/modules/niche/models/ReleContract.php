<?php

namespace backend\modules\niche\models;

use common\models\Contract;
use common\models\Niche;
use common\models\NicheContract;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 *
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="ReleContract"))
 */
class ReleContract extends Model
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
            [['niche_id','page_num','page'], 'required'],
            [['niche_id'], 'integer'],
            [['niche_id'], 'validateNicheId'],
        ];
    }

    public function validateNicheId()
    {
        $niche = Niche::find()->where(['id' => $this->niche_id])->one();
        if (empty($niche)) {
            $this->addError('id', '暂无数据');
        }

        return true;
    }

    public function getList()
    {
        $nichcontact = NicheContract::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        $contract_ids = array_column($nichcontact,'contract_id');
        $contract = Contract::find()->where(['in','id',$contract_ids]);
        $dataProvider = new ActiveDataProvider([
            'query' => $contract,
            'pagination' => [
                'pageSize' => $this->page_num,
                'page' => $this->page-1,
            ]
        ]);
        return $dataProvider;
    }
}
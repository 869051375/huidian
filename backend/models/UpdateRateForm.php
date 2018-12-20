<?php
namespace backend\models;
use common\models\PersonMonthProfit;
use Yii;
use yii\base\Model;

class UpdateRateForm extends Model
{
    public $rate;
    public $person_month_profit_id;

    /**
     * @var PersonMonthProfit
     */
    public $personMonthProfit;

    public function rules()
    {
        return [
            ['person_month_profit_id', 'required'],
            [['person_month_profit_id'], 'integer'],
            [['rate'], 'required'],
            [['rate'], 'number'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
            ['person_month_profit_id', 'validatePersonMonthProfitId'],
        ];
    }

    public function validatePersonMonthProfitId()
    {
        $this->personMonthProfit = PersonMonthProfit::findOne($this->person_month_profit_id);
        if(null == $this->personMonthProfit)
        {
            $this->addError('person_month_profit_id','找不到指定的预计利润数据');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rate' => '个人提成最新比例',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;

        $t = Yii::$app->db->beginTransaction();
        try
        {
            $this->personMonthProfit->reward_proportion = $this->rate;
            $t->commit();
            $this->personMonthProfit->save(false);
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}

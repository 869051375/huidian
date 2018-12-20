<?php
namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\ExpectedProfitSettlementDetail;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderRecord;
use common\models\PerformanceRecord;
use common\models\PerformanceStatistics;
use common\models\Receipt;
use common\utils\BC;
use Yii;
use yii\base\Model;

class BillsPerformanceCorrectForm extends Model
{
    public $administrator_id;
    public $sn;
    public $correct_price;
    public $rate;
    public $title;
    public $content;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Administrator
     */
    public $administrator;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['correct_price','title'], 'trim'],
            [['sn','title','correct_price','rate','administrator_id'], 'required'],
            [['administrator_id'], 'integer'],
            ['administrator_id', 'validateAdministratorId'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
            ['correct_price', 'validateCorrectPrice'],
            [['sn'], 'validateSn'],
            [['title'], 'string', 'max' => 6],
            [['content'], 'string', 'max' => 30],
        ];
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::find()
            ->where(['id' => $this->administrator_id,'type' => Administrator::TYPE_SALESMAN])
            ->limit(1)->one();
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','找不到指定的人员');
        }
    }

    public function validateSn()
    {
        $this->order = Order::find()->where(['sn' => $this->sn])->limit(1)->one();
        if(null == $this->order)
        {
            $this->addError('sn', '找不到订单。');
        }
        else
        {
            $calculatePerformance = $this->order->getCalculatePerformance();
            if(floatval($this->correct_price) == 0)
            {
                $this->addError('correct_price', '请输入更正金额。');
            }
            if($calculatePerformance == 0)
            {
                $this->addError('correct_price', '当前订单无需更正金额。');
            }
            if(abs($this->correct_price) > $calculatePerformance)
            {
                $this->addError('correct_price', '更正金额只能输入负数，其绝对值要小于订单的已计算和已更正金额。');
            }
        }
    }

    public function validateCorrectPrice()
    {
        $start = strpos($this->correct_price,'.',0);
        $count = strlen($this->correct_price);
        if($this->correct_price > 0)
        {
            $this->addError('correct_price','输入格式必须为负数值，最多只支持到小数点后两位数，如-0.02元。');
        }
        else
        {
            $y = BC::sub($count,$start+1,0);
            if($start && $y  > 2)
            {
                $this->addError('correct_price','输入格式必须为负数值，最多只支持到小数点后两位数，如-0.02元。');
            }
        }
    }

    private function getYearMonth()
    {
        $year = 0;
        $month = 0;
        $record = MonthProfitRecord::getLastRecord();
        if($record == null)
        {
            $year = date('Y');
            $month = date('m');
        }
        else
        {
            if($record->isFinish() && ($record->isPerformanceReady() ||  $record->isPerformanceDoing()))
            {
                $year = $record->year;
                $month = $record->month;
            }
            elseif ($record->isFinish() && $record->isPerformanceFinish())
            {
                $year = $record->getNextMonth()['year'];
                $month = $record->getNextMonth()['month'];
            }
        }
        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    public function save()
    {
        if(!$this->validate())return false;
        $data = [];
        $time = time();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;

        $data[] = [
            'virtual_order_id' => $this->order->virtualOrder->id,
            'order_id' => $this->order->id,
            'administrator_id' => $this->administrator->id,
            'administrator_name' => $this->administrator->name,
            'department_id' => $this->administrator->department->id,
            'department_name' => $this->administrator->department->name,
            'year' => $this->getYearMonth()['year'],
            'month' => $this->getYearMonth()['month'],
            'type' => PerformanceStatistics::TYPE_CORRECT,
            'algorithm_type' => PerformanceStatistics::ALGORITHM_GENERAL,
            'title' => $this->title,
            'remark' => $this->content,
            'calculated_performance' =>  $this->correct_price,//已计算业绩金额,
            'performance_reward' => round(BC::div(BC::mul($this->correct_price,$this->rate,5),100,5),2),//业绩提成
            'reward_proportion' => $this->rate,
            'creator_id' => $admin->id,
            'creator_name' => $admin->name,
            'created_at' => $time,
        ];
        $t = Yii::$app->db->beginTransaction();
        try
        {
            PerformanceRecord::createRecord($this->order->virtualOrder->id,$this->order->id,0,0,0,0,$this->correct_price);
            \Yii::$app->db->createCommand()->batchInsert(PerformanceStatistics::tableName(), [
                'virtual_order_id',
                'order_id',
                'administrator_id',
                'administrator_name',
                'department_id',
                'department_name',
                'year',
                'month',
                'type',
                'algorithm_type',
                'title',
                'remark',
                'calculated_performance',
                'performance_reward',
                'reward_proportion',
                'creator_id',
                'creator_name',
                'created_at',
            ], $data)->execute();
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    public function attributeLabels()
    {
        return [
            'sn' => '订单号',
            'correct_price' => '更正实际利润金额',
            'title' => '金额名称',
            'content' => '更正备注',
            'rate' => '计算提点',
        ];
    }
}
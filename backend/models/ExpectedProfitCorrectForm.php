<?php
namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\ExpectedProfitSettlementDetail;
use common\models\Order;
use common\models\OrderRecord;
use common\models\Receipt;
use common\utils\BC;
use Yii;
use yii\base\Model;

class ExpectedProfitCorrectForm extends Model
{
    public $order_id;
    public $correct_price;
    public $title;
    public $content;

    /**
     * @var Order
     */
    public $order;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['correct_price','title'], 'trim'],
            [['order_id','title','correct_price'], 'required'],
            ['correct_price', 'compare', 'compareValue' => 1, 'operator' => '<', 'message'=>'输入格式必须为负数值，最多只支持到小数点后两位数，如-0.02元。'],
            [['order_id'], 'validateOrderId'],
            [['correct_price'], 'validateCorrectPrice'],
            [['title'], 'string', 'max' => 6],
            [['content'], 'string', 'max' => 30],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id', '找不到订单。');
        }
        else
        {
            $expectedProfit = $this->order->getExpectedProfit();
            if($this->correct_price == 0)
            {
                $this->addError('correct_price', '请输入更正金额。');
            }
            if($expectedProfit == 0)
            {
                $this->addError('correct_price', '当前订单无需更正金额。');
            }
            if(abs($this->correct_price) > $expectedProfit)
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

    public function save()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);
        $data = [];
        $rate = 100;//负责人的百分比
        $profit = 0;
        if($this->order->orderTeams)
        {
            foreach($this->order->orderTeams as $orderTeam)
            {
                $administrator = Administrator::findOne($orderTeam->administrator_id);
                $expected_profit = round(BC::div(BC::mul($this->correct_price, $orderTeam->divide_rate, 5), 100, 5),2);//以计算的业绩
                $profit += $expected_profit;
                $rate = BC::sub($rate, $orderTeam->divide_rate, 2);
                $data[] = [
                    'year' => $year,
                    'month' => $month,
                    'order_id' => $this->order->id,
                    'virtual_order_id' => $this->order->virtual_order_id,
                    'sn' => $this->order->sn,
                    'v_sn' => $this->order->virtualOrder->sn,
                    'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
                    'company_id' => $administrator->company_id,
                    'company_name' => $administrator->company->name,
                    'title' => $this->title,
                    'remark' => $this->content,
                    'administrator_id' => $administrator->id,
                    'administrator_name' => $administrator->name,
                    'department_id' => $administrator->department_id,
                    'department_name' => $administrator->department->name,
                    'department_path' => $administrator->department->path,
                    'expected_profit' => $expected_profit,
                    'creator_id' => $admin->id,
                    'creator_name' => $admin->name,
                    'created_at' => $time,
                ];
            }
        }
        if($rate < 100)
        {
            //主要负责人分共享人分成后的剩余的全部预计利润
            $this->correct_price = BC::sub($this->correct_price, $profit);
        }

        $data[] = [
            'year' => $year,
            'month' => $month,
            'order_id' => $this->order->id,
            'virtual_order_id' => $this->order->virtual_order_id,
            'sn' => $this->order->sn,
            'v_sn' => $this->order->virtualOrder->sn,
            'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
            'company_id' => $this->order->salesman->company_id,
            'company_name' => $this->order->salesman->company->name,
            'title' => $this->title,
            'remark' => $this->content,
            'administrator_id' => $this->order->salesman_aid,
            'administrator_name' => $this->order->salesman->name,
            'department_id' => $this->order->salesman->department_id,
            'department_name' => $this->order->salesman->department->name,
            'department_path' => $this->order->salesman->department->path,
            'expected_profit' => $this->correct_price,
            'creator_id' => $admin->id,
            'creator_name' => $admin->name,
            'created_at' => $time,
        ];
        $t = Yii::$app->db->beginTransaction();
        try
        {
            \Yii::$app->db->createCommand()->batchInsert(ExpectedProfitSettlementDetail::tableName(), [
                'year',
                'month',
                'order_id',
                'virtual_order_id',
                'sn',
                'v_sn',
                'type',
                'company_id',
                'company_name',
                'title',
                'remark',
                'administrator_id',
                'administrator_name',
                'department_id',
                'department_name',
                'department_path',
                'expected_profit',
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
            'correct_price' => '更正金额',
            'title' => '金额名称',
            'content' => '更正备注',
        ];
    }
}
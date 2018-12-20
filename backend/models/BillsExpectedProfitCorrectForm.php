<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\ExpectedProfitSettlementDetail;
use common\models\Order;
use common\utils\BC;
use Yii;
use yii\base\Model;

class BillsExpectedProfitCorrectForm extends Model
{
    public $administrator_id;
    public $department_id;
    public $sn;
    public $correct_price;
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

    /**
     * @var CrmDepartment
     */
    public $department;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['correct_price','title'], 'trim'],
            [['sn','title','correct_price'], 'required'],
            [['administrator_id'], 'required','on' => 'admin'],
            [['department_id'], 'required','on' => 'department'],
            [['administrator_id','department_id'], 'integer'],
            [['administrator_id'], 'validateAdministratorId','on' => 'admin'],
            [['department_id'], 'validateDepartmentId','on' => 'department'],
            ['correct_price', 'compare', 'compareValue' => 1, 'operator' => '<', 'message'=>'输入格式必须为负数值，最多只支持到小数点后两位数，如-0.02元。'],
            [['sn'], 'validateSn'],
            [['correct_price'], 'validateCorrectPrice'],
            [['title'], 'string', 'max' => 6],
            [['content'], 'string', 'max' => 30],
        ];
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::find()->where(['id' => $this->administrator_id,'type' => Administrator::TYPE_SALESMAN])
            ->limit(1)->one();
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','找不到指定的人员');
        }
    }

    public function validateDepartmentId()
    {
        $this->department = CrmDepartment::find()->where(['id' => $this->department_id])->limit(1)->one();
        if(null == $this->department)
        {
            $this->addError('administrator_id','找不到指定的部门');
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
        if($this->administrator_id)
        {
            $this->setScenario('admin');
        }
        elseif($this->department_id)
        {
            $this->setScenario('department');
        }
        if(!$this->validate()) return false;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);
        $data = [];

        $data[] = [
            'year' => $year,
            'month' => $month,
            'order_id' => $this->order->id,
            'virtual_order_id' => $this->order->virtual_order_id,
            'sn' => $this->order->sn,
            'v_sn' => $this->order->virtualOrder->sn,
            'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
            'company_id' => $this->administrator ? $this->administrator->company_id : ($this->department ? $this->department->company->id : 0),
            'company_name' => $this->administrator ? $this->administrator->company->name : ($this->department ? $this->department->company->name : ''),
            'title' => $this->title,
            'remark' => $this->content,
            'administrator_id' => $this->administrator ? $this->administrator->id : 0,
            'administrator_name' => $this->administrator ? $this->administrator->name : '',
            'department_id' => $this->administrator ? $this->administrator->department->id :  ($this->department ? $this->department->id : 0),
            'department_name' => $this->administrator ? $this->administrator->department->name :  ($this->department ? $this->department->name : ''),
            'department_path' => $this->administrator ? $this->administrator->department->path :  ($this->department ? $this->department->path : ''),
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
            'sn' => '订单号',
        ];
    }
}
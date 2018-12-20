<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderTeam;
use Yii;
use yii\base\Model;

class ChangeOrderTeamRate extends Model
{
    public $order_id;
    public $rate;//分成比例
    public $team;//多业务员id


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
            [['order_id','team','rate'], 'required'],
            ['order_id', 'validateOrderId'],
            ['team', 'validateTeam'],
            ['rate', 'validateRate'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('salesman_id', '订单不存在。');
            return ;
        }
    }

    public function validateTeam()
    {
        if(isset($this->rate))
        {
            foreach($this->rate as $item)
            {
                if($item == null)
                {
                    $this->addError('team', '对不起,业绩分配比例不能为空');
                }
            }
        }
        if(empty($this->team) || count($this->rate) != count($this->team) )
        {
            $this->addError('team', '对不起,业绩分配比例不能为空');
        }
    }

    public function validateRate()
    {
        $total_rate = 0;
        if(is_array($this->rate))
        {
            foreach($this->rate as $rates)
            {
                if(preg_match('/^[0-9]+(.[0-9]{1,2})?$/',$rates))
                {
                    $total_rate += $rates;
                }
                else
                {
                    $this->addError('rate', '对不起,业绩分配比例只能为数字');
                }
            }
        }

        if($total_rate > 100)
        {
            $this->addError('rate', '对不起,业绩分配比例不能大于100%');
        }
        elseif ($total_rate < 0)
        {
            $this->addError('rate', '对不起,业绩分配比例不能小于0%');
        }
    }


    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'administrator_id' => '业务人员',
            'team' => '分成业务人员',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->team as $key => $item)
            {
                $orderTeam = OrderTeam::findOne($item);
                $orderTeam->divide_rate = $this->rate[$key];
                $orderTeam->save(false);
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
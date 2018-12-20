<?php
namespace common\models;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 17:54
 *
 * 接口模型
 *
 * 商标节点信息
 *
 */

class TrademarkFlowInfo
{
    public $flow_id;
    public $flow_item;
    public $flow_time;
    public function load($data)
    {
        $this->flow_id = $data['FlowId'];
        $this->flow_item = $data['FlowItem'];
        $this->flow_time = $data['FlowDate'];
    }
}
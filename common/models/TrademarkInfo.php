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
 * 商标列表信息
 *
 */

class TrademarkInfo
{
    public $id;
    public $reg_no;
    public $int_cls;
    public $int_cls_name;
    public $name;
    public $application_date;
    public $applicant_cn;
    public $applicant_en;
    public $agent;
    public $status;
    public $flow_status;
    public $flow_status_desc;
    public $has_image;
    public $image;

    public function load($data)
    {
        $trademarkCategory = new TrademarkCategory();
        $this->id = $data['Id'];
        $this->reg_no = $data['RegNo'];
        $this->int_cls = $data['IntCls'];
        $this->int_cls_name = $trademarkCategory->findTrademarkCategory($data['IntCls']);
        $this->name = $data['Name'];
        $this->application_date = $data['AppDate'];
        $this->applicant_cn = $data['ApplicantCn'];
        $this->applicant_en = $data['ApplicantEn'];
        $this->agent = $data['Agent'];
        $this->status = $data['Status'];
        $this->flow_status = $data['FlowStatus'];
        $this->flow_status_desc = $data['FlowStatusDesc'];
        $this->has_image = $data['HasImage'];
        $this->image = $data['ImageUrl'];
    }


}
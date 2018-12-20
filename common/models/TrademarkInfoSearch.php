<?php
namespace common\models;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 17:54
 * 更改接口返回数据格式
 * 接口的使用方式
 *
 * 获取商标详细信息
 */
use yii\helpers\Json;

    class TrademarkInfoSearch
    {
        /**
         * @var int
         */
        public $result_status; //状态码

        /**
         * @var string
         */
        public $message; //返回信息


        /**
         * @var array
         */
        public $result = []; // 返回数据

        /**
         * @var array
         */
        public $flow_info = []; // 流程节点信息

        /**
         * @var array
         */
        public $list_group_items = []; // 注册商标分组

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

        public $address_cn;
        public $address_en;
        public $announcement_issue;
        public $announcement_date;
        public $applicant_a;
        public $applicant_b;
        public $color;
        public $regIssue;
        public $reg_date;
        public $later_stage_date;
        public $international_date;
        public $effective_date;
        public $valid_period;


        /**
         * @param $jsonString
         */
        public function loadData($jsonString)
        {
            $trademarkCategory = new TrademarkCategory();
            $data = Json::decode($jsonString);
            $this->result_status = $data['Status'];
            $this->message = $data['Message'];
            if($data['Status'] == 200)
            {
                $this->list_group_items = $data['Result']['ListGroupItems'];
                $this->id = $data['Result']['Id'];
                $this->reg_no = $data['Result']['RegNo'];
                $this->int_cls = $data['Result']['IntCls'];
                $this->int_cls_name = $trademarkCategory->findTrademarkCategory($data['Result']['IntCls']);
                $this->name = $data['Result']['Name'];
                $this->application_date = $data['Result']['AppDate'];
                $this->applicant_cn = $data['Result']['ApplicantCn'];
                $this->applicant_en = $data['Result']['ApplicantEn'];
                $this->agent = $data['Result']['Agent'];
                $this->status = $data['Result']['Status'];
                $this->flow_status = $data['Result']['FlowStatus'];
                $this->flow_status_desc = $data['Result']['FlowStatusDesc'];
                $this->has_image = $data['Result']['HasImage'];
                $this->image = $data['Result']['ImageUrl'];

                $this->address_cn = $data['Result']['AddressCn'];
                $this->address_en = $data['Result']['AddressEn'];
                $this->announcement_issue = $data['Result']['AnnouncementIssue'];
                $this->announcement_date = $data['Result']['AnnouncementDate'];
                $this->applicant_a = $data['Result']['Applicant1'];
                $this->applicant_b = $data['Result']['Applicant2'];
                $this->color = $data['Result']['Color'];
                $this->regIssue = $data['Result']['RegIssue'];
                $this->reg_date = $data['Result']['RegDate'];
                $this->later_stage_date = $data['Result']['HouQiZhiDingDate'];
                $this->international_date = $data['Result']['GuoJiZhuCeDate'];
                $this->effective_date = $data['Result']['YouXianQuanDate'];
                $this->valid_period = $data['Result']['ValidPeriod'];
                foreach($data['Result']['FlowItems'] as $item)
                {
                    $flowInfo = new TrademarkFlowInfo();
                    $flowInfo->load($item);
                    $this->flow_info[] = $flowInfo;
                }
            }
        }
    }


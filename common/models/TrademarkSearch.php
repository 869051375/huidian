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
 * 商标列表
 *
 */
use yii\helpers\Json;

    class TrademarkSearch
    {
        /**
         * @var int
         */
        public $status; //状态码

        /**
         * @var string
         */
        public $message; //返回信息
        /**
         * @var int
         */
        public $page_size; //显示条数

        /**
         * @var int
         */
        public $page_index; //当前页

        /**
         * @var int
         */
        public $total_records; //总记录数
        /**
         * @var array
         */
        public $trademark_category = [] ; // 商标类别

        /**
         * @var array
         */
        public $result = []; // 返回数据

        /**
         * @param $jsonString
         */
        public function loadData($jsonString)
        {
            $data = Json::decode($jsonString);

            $this->status = $data['Status'];
            $this->message = $data['Message'];
            if($data['Status'] == 200)
            {
                $this->page_size = $data['Paging']['PageSize'];
                $this->page_index = $data['Paging']['PageIndex'];
                $this->total_records = $data['Paging']['TotalRecords'];
                foreach($data['GroupItems'] as $groupItem)
                {
                    if($groupItem['Key'] == 'intcls')
                    {
                        foreach($groupItem['Items'] as $item)
                        {
                            $trademarkCategory = new IntClsTrademark();
                            $trademarkCategory->load($item);
                            $this->trademark_category[] = $trademarkCategory;
                        }
                    }
                }
                foreach($data['Result'] as $result)
                {
                    $trademarkInfo = new TrademarkInfo();
                    $trademarkInfo->load($result);
                    $this->result[] = $trademarkInfo;
                }
            }
        }
    }

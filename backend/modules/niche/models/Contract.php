<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 合同列表
 * @SWG\Definition(required={}, @SWG\Xml(name="Contract"))
 */
class Contract extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $id;

    /**
     * 合同名称
     * @SWG\Property(example = "敬业合同")
     * @var string
     */
    public $name;

    /**
     * 合同类型编号
     * @SWG\Property(example = "DH13141213")
     * @var string
     */
    public $serial_number;


    /**
     * 合同编号
     * @SWG\Property(example = "DH1314121343")
     * @var string
     */
    public $contract_no;

    /**
     * 创建人姓名
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $creator_name;


    /**
     * 创建时间
     * @SWG\Property(example = "1541404757")
     * @var integer
     */
    public $created_at;


    /**
     * 合同状态 （0：未签约 1：已签约）
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $status;

}
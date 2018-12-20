<?php

namespace backend\modules\niche\models;


/**
 * 商机公海详细信息加密
 * @SWG\Definition(required={}, @SWG\Xml(name="PublicNicheDetail"))
 */
class PublicNicheDetail extends NicheList
{

    /**
     * 客户名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $name;


    /**
     * 手机号码
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $phone;

    /**
     * 联系座机
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $tel;

    /**
     * 微信
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $wechat;

    /**
     * QQ
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $qq;

    /**
     * 来点电话
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $caller;

    /**
     * 邮箱
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $email;

    /**
     * 生日
     * @SWG\Property(example = "1993-07-04")
     * @var string
     */
    public $birthday;

    /**
     * 来源
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $source;

    /**
     * 来源渠道ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $channel_id;


    /**
     * 部门
     * @SWG\Property(example = "市场部")
     * @var string
     */
    public $department;

    /**
     * 职位
     * @SWG\Property(example = "总经理")
     * @var string
     */
    public $position;

    /**
     * 籍贯
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $native_place;

    /**
     * 兴趣爱好
     * @SWG\Property(example = "打篮球")
     * @var string
     */
    public $customer_hobby;

    /**
     * 详细地址
     * @SWG\Property(example = "朝外大街264号")
     * @var string
     */
    public $street;

    /**
     * 备注
     * @SWG\Property(example = "我是备注")
     * @var string
     */
    public $remark;

}
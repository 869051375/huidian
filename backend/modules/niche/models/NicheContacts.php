<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机联系人信息
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheContacts"))
 */
class NicheContacts extends Model
{

    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;


    /**
     * 客户表关联id
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $customer_id;

    /**
     * 姓名
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    /**
     * 男或女：默认0男，1女
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $gender;

    /**
     * 客户生日（1949-10-01）
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $birthday;

    /**
     * 客户爱好
     * @SWG\Property(example = "我是爱好")
     * @var string
     */
    public $customer_hobby;

    /**
     * 客户来源,0其他方式1百度,2.360,3市场活动,4地推,5客户介绍,6合作渠道,7老客户复购,8其他搜索引擎,9.400电话,10TQ线索,11阿里云服务市场,12.自己推广'
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $source;

    /**
     * 来源渠道ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $channel_id;

    /**
     * 电话号码
     * @SWG\Property(example = "1")
     * @var string
     */
    public $tel;

    /**
     * 来电号码
     * @SWG\Property(example = "")
     * @var string
     */
    public $caller;

    /**
     * 手机号
     * @SWG\Property(example = "18101378821")
     * @var string
     */
    public $phone;

    /**
     * 邮箱
     * @SWG\Property(example = "123456@163.com")
     * @var string
     */
    public $email;

    /**
     * QQ
     * @SWG\Property(example = "86901111")
     * @var string
     */
    public $qq;

    /**
     * 微信
     * @SWG\Property(example = "juejinWX001")
     * @var string
     */
    public $wechat;

    /**
     * 省份id,唯一不能自增
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $province_id;

    /**
     * 省份名字
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $province_name;

    /**
     * 城市id,唯一不能自增
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $city_id;

    /**
     * 城市名字
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $city_name;

    /**
     * 区域id,唯一不能自增
     * @SWG\Property(example = "1")
     * @var string
     */
    public $district_id;

    /**
     * 地区名字
     * @SWG\Property(example = "朝阳区")
     * @var string
     */
    public $district_name;

    /**
     * 详细地址
     * @SWG\Property(example = "东三环京粮大厦16层")
     * @var string
     */
    public $street;

    /**
     * 籍贯
     * @SWG\Property(example = "山西省太原市")
     * @var string
     */
    public $native_place;

    /**
     * 备注
     * @SWG\Property(example = "这是一个备注")
     * @var string
     */
    public $remark;

    /**
     * 职位
     * @SWG\Property(example = "这是一个职位")
     * @var string
     */
    public $position;

    /**
     * 部门
     * @SWG\Property(example = "这是一个部门")
     * @var string
     */
    public $department;



}

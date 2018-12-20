<?php
/**
 * Created by PhpStorm.
 * User: imxiangli
 * Date: 2017/2/19
 * Time: 下午10:17
 */

namespace backend\models;

use common\models\Property;
use yii\base\Model;

class SettingForm extends Model
{
    public $default_user_avatar; // 客户默认头像
    public $default_customer_service_avatar; // 客服默认头像
    public $default_supervisor_avatar; // 嘟嘟妹默认头像
    public $logo; // logo
    public $bottom_logo; // 底部logo

    public $default_customer_principal; // 默认新客户负责人（administrator_id）

    public $order_file_sms_id; // 后台上传订单文件给客户发送信息的短信模板id
    public $order_file_sms_preview; // 后台上传订单文件给客户发送信息的短信预览

    public $send_invoice_sms_id; // 发票寄送通知短信模板id
    public $send_invoice_sms_preview; // 发票寄送通知短信预览

    public $assign_clerk_sms_id; // 客服派单给服务人员发送通知短信模板id
    public $assign_clerk_sms_preview; // 客服派单给服务人员发送信息的短信预览
    public $re_assign_clerk_sms_id; // 客服修改服务人员发送通知短信模板id
    public $start_service_sms_id; // 开始服务时发送短信通知客户模板id
    public $start_service_sms_preview; // 开始服务时给客户发送信息的短信预览
    public $send_renewal_remind_sms_id; // 续费提醒时发送短信通知客户模板id
    public $send_renewal_remind_sms_preview; // 续费提醒时给客户发送信息的短信预览
    public $refund_record_sms_id; // 财务人员退款成功后点击【确认退款】时发送短信通知客户模板id
    public $assign_customer_service_sms_id; // 体验商品申请时发送短信通知客服模板id

    public $register_sms_id; // 用户注册短信验证码短信模板id
    public $reset_password_sms_id; // 找回密码短信验证码短信模板id

    public $global_js_code_pc; // 全局js代码（PC）
    public $global_js_code_m; // 全局js代码（移动）

    public $default_product_guarantee; // 默认商品服务保障
    public $product_about_us; // 商品详情页关于掘金企服
    public $default_product_guarantee_m; // 默认商品服务保障（移动端）
    public $product_about_us_m; // 商品详情页关于掘金企服（移动端）
    public $company_intro; // 公司简介

    public $mobile_domain; // 移动版域名
    public $pc_domain; // 电脑版域名

    public $take_coupon_mobile_domain; // 优惠券领取移动版域名
    public $take_coupon_pc_domain; // 优惠券领取电脑版域名

    public $tel_400; // 400电话
    public $tel_line; // 固定电话
    public $icp; // ICP备案

    public $order_pay_timeout;//下单后支付时间限制（小时）
    public $unpaid_caveat_time;//下单后未支付飘红时间

    public $share_link_image; // 默认分享链接图片

    public $nav_more_links; // 导航链接更多服务设置

    private $_attributeLabels;

    public $live_url; // pc端直播url
    public $live_url_m; // 移动端直播url

    public $search_field; // 搜索字段名
    public $other_refund_time; // 支付宝退款限制时间（天）
    public $wx_refund_time; // 微信退款限制时间（天）

    public $profit_rule; // 业绩提成规则
    public $pay_rate; // 虚拟订单已付款占应付款比例（）以上可计算，默认30
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['default_user_avatar', 'default_customer_service_avatar', 'default_supervisor_avatar', 'logo','bottom_logo','share_link_image'], 'string'],
            [['order_file_sms_id', 'order_file_sms_preview', 'send_invoice_sms_id', 'send_invoice_sms_preview', 'start_service_sms_preview', 'send_renewal_remind_sms_preview', 'assign_clerk_sms_preview'], 'string'],
            [['assign_clerk_sms_id', 're_assign_clerk_sms_id', 'start_service_sms_id', 'send_renewal_remind_sms_id', 'refund_record_sms_id', 'assign_customer_service_sms_id',], 'string'],
            [['default_product_guarantee', 'default_product_guarantee_m','product_about_us', 'product_about_us_m', 'tel_400', 'icp', 'tel_line', 'company_intro','search_field'], 'string'],
            [['reset_password_sms_id', 'register_sms_id'], 'string'],
            [['global_js_code_m', 'global_js_code_pc'], 'string'],
            [['mobile_domain', 'pc_domain'], 'string'],
            [['take_coupon_mobile_domain', 'take_coupon_pc_domain','live_url','live_url_m'], 'string'],
            [['order_pay_timeout','other_refund_time','wx_refund_time'],'integer'],
            [['default_customer_principal','unpaid_caveat_time','profit_rule'],'integer'],
            ['pay_rate','number'],
            [['nav_more_links'], 'string'],
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        /** @var Property[] $properties */
        $properties = Property::find()
                    ->where(['not in', 'key', ['home_seo_title', 'home_seo_keywords', 'home_seo_description', 'head_meta', 'stats_code']])
                    ->all();
        foreach ($properties as $property)
        {
            $key = $property->key;
            if(!property_exists($this, $key)) continue; // 跳过不存在的属性
            $this->$key = $property->getValue();
            $this->_attributeLabels[$key] = $property->desc;
        }
    }

    public function attributeLabels()
    {
        if(empty($this->_attributeLabels['default_user_avatar']))
        {
            $this->_attributeLabels['default_user_avatar'] = '客户默认头像';
        }
        if(empty($this->_attributeLabels['default_customer_service_avatar']))
        {
            $this->_attributeLabels['default_customer_service_avatar'] = '客服默认头像';
        }
        if(empty($this->_attributeLabels['default_supervisor_avatar']))
        {
            $this->_attributeLabels['default_supervisor_avatar'] = '嘟嘟妹默认头像';
        }
        if(empty($this->_attributeLabels['default_customer_principal']))
        {
            $this->_attributeLabels['default_customer_principal'] = '新注册客户分配负责人';
        }
        if(empty($this->_attributeLabels['logo']))
        {
            $this->_attributeLabels['logo'] = 'Logo';
        }
        if(empty($this->_attributeLabels['bottom_logo']))
        {
            $this->_attributeLabels['bottom_logo'] = '底部Logo';
        }
        if(empty($this->_attributeLabels['order_file_sms_id']))
        {
            $this->_attributeLabels['order_file_sms_id'] = '后台上传订单文件给客户发送信息的短信模板id';
        }
        if(empty($this->_attributeLabels['order_file_sms_preview']))
        {
            $this->_attributeLabels['order_file_sms_preview'] = '后台上传订单文件给客户发送信息的短信预览';
        }
        if(empty($this->_attributeLabels['global_js_code_pc']))
        {
            $this->_attributeLabels['global_js_code_pc'] = '全局js代码（PC）';
        }
        if(empty($this->_attributeLabels['global_js_code_m']))
        {
            $this->_attributeLabels['global_js_code_m'] = '全局js代码（移动）';
        }
        if(empty($this->_attributeLabels['register_sms_id']))
        {
            $this->_attributeLabels['register_sms_id'] = '用户注册短信验证码短信模板id';
        }
        if(empty($this->_attributeLabels['reset_password_sms_id']))
        {
            $this->_attributeLabels['reset_password_sms_id'] = '找回密码短信验证码短信模板id';
        }
        if(empty($this->_attributeLabels['send_invoice_sms_id']))
        {
            $this->_attributeLabels['send_invoice_sms_id'] = '发票寄送通知短信模板id';
        }
        if(empty($this->_attributeLabels['send_invoice_sms_preview']))
        {
            $this->_attributeLabels['send_invoice_sms_preview'] = '发票寄送通知短信预览';
        }
        if(empty($this->_attributeLabels['assign_clerk_sms_id']))
        {
            $this->_attributeLabels['assign_clerk_sms_id'] = '派单给服务人员发送通知短信模板id';
        }
        if(empty($this->_attributeLabels['assign_clerk_sms_preview']))
        {
            $this->_attributeLabels['assign_clerk_sms_preview'] = '客服派单给服务人员发送信息的短信预览';
        }
        if(empty($this->_attributeLabels['re_assign_clerk_sms_id']))
        {
            $this->_attributeLabels['re_assign_clerk_sms_id'] = '修改服务人员发送通知短信模板id';
        }
        if(empty($this->_attributeLabels['start_service_sms_id']))
        {
            $this->_attributeLabels['start_service_sms_id'] = '开始服务时发送短信通知客户模板id';
        }
        if(empty($this->_attributeLabels['start_service_sms_preview']))
        {
            $this->_attributeLabels['start_service_sms_preview'] = '开始服务时给客户发送信息的短信预览';
        }
        if(empty($this->_attributeLabels['send_renewal_remind_sms_id']))
        {
            $this->_attributeLabels['send_renewal_remind_sms_id'] = '续费提醒发送短信通知客户模板id';
        }
        if(empty($this->_attributeLabels['send_renewal_remind_sms_preview']))
        {
            $this->_attributeLabels['send_renewal_remind_sms_preview'] = '续费提醒时给客户发送信息的短信预览';
        }
        if(empty($this->_attributeLabels['refund_record_sms_id']))
        {
            $this->_attributeLabels['refund_record_sms_id'] = '财务人员退款成功后点击【确认退款】时发送短信通知客户模板id';
        }
        if(empty($this->_attributeLabels['assign_customer_service_sms_id']))
        {
            $this->_attributeLabels['assign_customer_service_sms_id'] = '体验商品申请时发送短信通知客服模板id';
        }
        if(empty($this->_attributeLabels['default_product_guarantee']))
        {
            $this->_attributeLabels['default_product_guarantee'] = '默认商品服务保障';
        }
        if(empty($this->_attributeLabels['default_product_guarantee_m']))
        {
            $this->_attributeLabels['default_product_guarantee_m'] = '默认商品服务保障（移动端）';
        }
        if(empty($this->_attributeLabels['product_about_us']))
        {
            $this->_attributeLabels['product_about_us'] = '商品详情页关于掘金企服';
        }
        if(empty($this->_attributeLabels['product_about_us_m']))
        {
            $this->_attributeLabels['product_about_us_m'] = '商品详情页关于掘金企服（移动端）';
        }
        if(empty($this->_attributeLabels['tel_400']))
        {
            $this->_attributeLabels['tel_400'] = '400电话';
        }
        if(empty($this->_attributeLabels['tel_line']))
        {
            $this->_attributeLabels['tel_line'] = '固定电话';
        }
        if(empty($this->_attributeLabels['icp']))
        {
            $this->_attributeLabels['icp'] = 'ICP备案号';
        }
        if(empty($this->_attributeLabels['order_pay_timeout']))
        {
            $this->_attributeLabels['order_pay_timeout'] = '下单后支付时间限制（小时）';
        }
        if(empty($this->_attributeLabels['unpaid_caveat_time']))
        {
            $this->_attributeLabels['unpaid_caveat_time'] = '订单认领报警时间（分钟）';
        }
        if(empty($this->_attributeLabels['pc_domain']))
        {
            $this->_attributeLabels['pc_domain'] = '电脑版域名（带http://）';
        }
        if(empty($this->_attributeLabels['mobile_domain']))
        {
            $this->_attributeLabels['mobile_domain'] = '移动版域名（带http://）';
        }
        if(empty($this->_attributeLabels['take_coupon_mobile_domain']))
        {
            $this->_attributeLabels['take_coupon_mobile_domain'] = '优惠券领取移动版域名（带http://）';
        }
        if(empty($this->_attributeLabels['take_coupon_pc_domain']))
        {
            $this->_attributeLabels['take_coupon_pc_domain'] = '优惠券领取电脑版域名（带http://）';
        }
        if(empty($this->_attributeLabels['share_link_image']))
        {
            $this->_attributeLabels['share_link_image'] = '默认分享链接图片';
        }
        if(empty($this->_attributeLabels['company_intro']))
        {
            $this->_attributeLabels['company_intro'] = '公司简介';
        }
        if(empty($this->_attributeLabels['nav_more_links']))
        {
            $this->_attributeLabels['nav_more_links'] = '导航更多服务设置';
        }
        if(empty($this->_attributeLabels['live_url']))
        {
            $this->_attributeLabels['live_url'] = '直播地址（PC端）';
        }
        if(empty($this->_attributeLabels['live_url_m']))
        {
            $this->_attributeLabels['live_url_m'] = '直播地址（移动端）';
        }
        if(empty($this->_attributeLabels['search_field']))
        {
            $this->_attributeLabels['search_field'] = '搜索字段名';
        }
        if(empty($this->_attributeLabels['other_refund_time']))
        {
            $this->_attributeLabels['other_refund_time'] = '支付宝退款限制时间（天）';
        }
        if(empty($this->_attributeLabels['wx_refund_time']))
        {
            $this->_attributeLabels['wx_refund_time'] = '微信退款限制时间（天）';
        }
        if(empty($this->_attributeLabels['profit_rule']))
        {
            $this->_attributeLabels['profit_rule'] = '业绩提成规则';
        }
        if(empty($this->_attributeLabels['pay_rate']))
        {
            $this->_attributeLabels['pay_rate'] = '虚拟订单已付款占应付款比例（）%以上可计算';
        }
        return $this->_attributeLabels;
    }

    public function save()
    {
        foreach ($this->attributes as $attribute => $value)
        {
            Property::set($attribute, trim($value));
        }
        return true;
    }
}
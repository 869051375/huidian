<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * 商机列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheList"))
 */
class NicheList extends Model
{

    /**
     * 自增id
     * @SWG\Property(example = "")
     * @var integer
     */
    public $id;

    /**
     * 商机名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $name;

    /**
     * 所属业务员ID（负责人）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $administrator_id;

    /**
     * 联系人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $contacts_id;

    /**
     * 所属业务员名字（负责人）
     * @SWG\Property(example = "")
     * @var string
     */
    public $administrator_name;

    /**
     * 下次跟进时间   2018-10-23 15:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $next_follow_time;

    /**
     * 最后跟进人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $last_record_creator_id;

    /**
     * 最后跟进人名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $last_record_creator_name;

    /**
     * 最后跟进时间开始时间   2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $last_record_start;

    /**
     * 最后跟进时间结束时间   2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $last_record_end;

    /**
     * 创建人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $creator_id;

    /**
     * 创建人名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $creator_name;

    /**
     * 创建时间开始时间 2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $created_at_start;

    /**
     * 创建时间结束时间 2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $created_at_end;

    /**
     * 商机进度(默认10,30,60,80,100,0),100（赢单）,0（输单）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $progress;

    /**
     * 商机状态（默认0未成交，1申请中，2已成交，3已失败）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $status;

    /**
     * 标签ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $label_id;

    /**
     * 标签名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $label_name;

    /**
     * 客户ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $customer_id;

    /**
     * 商机来源ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $source_id;

    /**
     * 商机来源名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $source_name;

    /**
     * 商机渠道ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $channel_id;

    /**
     * 商机渠道名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $channel_name;

    /**
     * 商机总金额，保留小数点2位
     * @SWG\Property(example = "")
     * @var string
     */
    public $total_amount;

    /**
     * 分配人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $distribution_id;

    /**
     * 分配人名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $distribution_name;

    /**
     * 分配时间开始时间 2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $distribution_at_start;

    /**
     * 分配时间结束时间 2018-10-01 00:00:00
     * @SWG\Property(example = "")
     * @var integer
     */
    public $distribution_at_end;

    /**
     * 阶段更新时间   2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $stage_update_at;

    /**
     * 更新人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $update_id;

    /**
     * 更新人name
     * @SWG\Property(example = "")
     * @var integer
     */
    public $update_name;

    /**
     * 是否是分配默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_distribution;

    /**
     * 是否是提取默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_extract;

    /**
     * 是否是转移默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_transfer;

    /**
     * 是否是跨默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_cross;

    /**
     * 是否是新默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_new;

    /**
     * 是否是受保护默认（0不是，1是）
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_protect;

    /**
     * 赢单原因
     * @SWG\Property(example = "")
     * @var string
     */
    public $win_reason;

    /**
     * 赢单的阶段
     * @SWG\Property(example = "")
     * @var integer
     */
    public $win_progress;

    /**
     * 赢单的描述
     * @SWG\Property(example = "")
     * @var string
     */
    public $win_describe;

    /**
     * 输单的原因
     * @SWG\Property(example = "")
     * @var string
     */
    public $lose_reason;

    /**
     * 输单的阶段
     * @SWG\Property(example = "")
     * @var integer
     */
    public $lose_progress;

    /**
     * 输单的描述
     * @SWG\Property(example = "")
     * @var string
     */
    public $lose_describe;

    /**
     * 商机备注
     * @SWG\Property(example = "")
     * @var string
     */
    public $remark;

    /**
     * 所属公司
     * @SWG\Property(example = "")
     * @var integer
     */
    public $company_id;

    /**
     * 所属部门
     * @SWG\Property(example = "")
     * @var integer
     */
    public $department_id;

    /**
     * 商机提取成功时间 2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $extract_time;

    /**
     * 商机移入公海时间
     * @SWG\Property(example = "")
     * @var integer
     */
    public $move_public_time;

    /**
     * 转出人ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $send_administrator_id;

    /**
     * 转出时间戳    2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $send_time;

    /**
     * 预计成交时间   2018-10-23
     * @SWG\Property(example = "")
     * @var integer
     */
    public $predict_deal_time;

    /**
     * 实际成交时间   2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $deal_time;

    /**
     * 作废时间 2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $invalid_time;

    /**
     * 作废原因
     * @SWG\Property(example = "")
     * @var string
     */
    public $invalid_reason;

    /**
     * 注册用户ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $user_id;

    /**
     * 最后修改时间
     * @SWG\Property(example = "")
     * @var integer
     */
    public $updated_at;

    /**
     * 商品ID
     * @SWG\Property(example = "")
     * @var integer
     */
    public $product_id;

    /**
     * 商机金额 1 为 >= 2 为<=
     * @SWG\Property(example = "")
     * @var integer
     */
    public $compare;

    /**
     * 自定义筛选key
     * @SWG\Property(example = "")
     * @var integer
     */
    public $niche_key;

    /**
     * 自定义筛选val
     * @SWG\Property(example = "")
     * @var integer
     */
    public $niche_val;

    /**
     * 今天跟进的商机查询    入参 1
     * @SWG\Property(example = "")
     * @var integer
     */
    public $follow_today;

    /**
     * 近三天跟进的商机查询    入参 1
     * @SWG\Property(example = "")
     * @var integer
     */
    public $follow_three;


    /**
     * 近三个月跟进的商机查询    入参 1
     * @SWG\Property(example = "")
     * @var integer
     */
    public $follow_month;

    /**
     *  $scene  1：我负责的跟进中商机
    //          2：我参与的跟进中商机
    //          3: 我分享的跟进中商机
    //          4：下属负责的跟进中商机
    //          5：下属参与的跟进中商机
    //          6：下属分享的跟进中商机
    //          7：全部跟进中商机

    //          11: 我负责的已成交商机
    //          12: 我参与的已成交商机
    //          13:我分享的已成交商机
    //          14:下属负责的已成交商机
    //          15:下属参与的已成交商机
    //          16:下属分享的已成交商机
    //          17:全部的已成交商机

    //          21: 我负责的已输单商机
    //          22: 我参与的已输单商机
    //          23:我分享的已输单商机
    //          24:下属负责的已输单商机
    //          25:下属参与的已输单商机
    //          26:下属分享的已输单商机
    //          27:全部的已输单商机
     * @SWG\Property(example = "")
     * @var integer
     */
    public $scene;

    /**
     * 回收时间 2018-10-23 15:58:25
     * @SWG\Property(example = "")
     * @var integer
     */
    public $recovery_at;

    /**
     * 标签颜色
     * @SWG\Property(example = "")
     * @var string
     */
    public $label_color;

    /**
     * 客户名称
     * @SWG\Property(example = "")
     * @var string
     */
    public $customer_name;

    /**
     * 多少条
     * @SWG\Property(example = "")
     * @var integer
     */
    public $page_num;

    /**
     * 页数
     * @SWG\Property(example = "")
     * @var integer
     */
    public $page;

    /**
     * 商品信息
     * @SWG\Property(example = "")
     * @var string
     */
    public $product;

    /**
     * 客户编号
     * @SWG\Property(example = "")
     * @var integer
     */
    public $customer_number;

    /**
     * 即将被回收    如需查询入参 1
     * @SWG\Property(example = "")
     * @var integer
     */
    public $soon_recovery;

    /**
     * 是否有权限 （详情-阶段更新，编辑） 1为有权限 0为没有
     * @SWG\Property(example = "")
     * @var integer
     */
    public $is_power;

    /**
     * 一级类目查询
     * @SWG\Property(example = "")
     * @var integer
     */
    public $top_category_id;

    /**
     * 二级类目查询
     * @SWG\Property(example = "")
     * @var integer
     */
    public $category_id;

    /**
     * 是否显示放弃按钮 1显示 0 不显示
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $display_give_up;




}

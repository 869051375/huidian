<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 商机客户信息
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCustomFileds"))
 */
class NicheCustomFileds extends Model
{

    /**
     * 字段名称
     * @SWG\Property(example = "负责人")
     * @var string
     */
    public $fileds_name;

    /**
     * 字段
     * @SWG\Property(example = "name")
     * @var string
     */
    public $fileds;

    /**
     * 排序
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $sort;


    /**
     * 状态 （0：隐藏 1：显示）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $status;

    /**
     * 可否修改 （0：不可以 1：可以）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_update;


    const NICHE_FILEDS_LIST = 0;
    const NICHE_PUBLIC_FILEDS_LIST = 1;
    const NICHE_FILEDS_LIST_DEFAULT = 2;
    const NICHE_PUBLIC_FILEDS_LIST_DEFAULT = 3;


    public static $fileds_default = array(
        ['fileds'=>'name','fileds_name'=>'商机名称','sort'=>'1','status'=>1,'is_update'=>0],
        ['fileds'=>'customer_name','fileds_name'=>'客户名称','sort'=>'2','status'=>1,'is_update'=>1],
        ['fileds'=>'total_amount','fileds_name'=>'商机金额','sort'=>'3','status'=>1,'is_update'=>1],
        ['fileds'=>'predict_deal_time','fileds_name'=>'预计成交时间','sort'=>'4','status'=>1,'is_update'=>1],
        ['fileds'=>'progress','fileds_name'=>'商机阶段','sort'=>'5','status'=>1,'is_update'=>1],
        ['fileds'=>'win_rate','fileds_name'=>'赢率','sort'=>'6','status'=>1,'is_update'=>1],
        ['fileds'=>'next_follow_time','fileds_name'=>'下次跟进时间','sort'=>'7','status'=>1,'is_update'=>1],
        ['fileds'=>'id','fileds_name'=>'商机ID','sort'=>'8','status'=>1,'is_update'=>1],
        ['fileds'=>'administrator_name','fileds_name'=>'负责人','sort'=>'9','status'=>1,'is_update'=>1],
        ['fileds'=>'last_record_creator_name','fileds_name'=>'最后跟进人','sort'=>'10','status'=>1,'is_update'=>1],
        ['fileds'=>'last_record','fileds_name'=>'最后跟进时间','sort'=>'11','status'=>1,'is_update'=>1],
        ['fileds'=>'creator_name','fileds_name'=>'创建人','sort'=>'12','status'=>1,'is_update'=>1],
        ['fileds'=>'created_at','fileds_name'=>'创建时间','sort'=>'13','status'=>1,'is_update'=>1],
        ['fileds'=>'product','fileds_name'=>'商品信息','sort'=>'14','status'=>1,'is_update'=>1],
        ['fileds'=>'label_name','fileds_name'=>'标签','sort'=>'15','status'=>1,'is_update'=>1],
        ['fileds'=>'customer_id','fileds_name'=>'客户编号','sort'=>'16','status'=>1,'is_update'=>1],
        ['fileds'=>'source_name','fileds_name'=>'来源','sort'=>'17','status'=>1,'is_update'=>1],
        ['fileds'=>'channel_name','fileds_name'=>'来源渠道','sort'=>'18','status'=>1,'is_update'=>1],
        ['fileds'=>'distribution_name','fileds_name'=>'分配人员','sort'=>'19','status'=>1,'is_update'=>1],
        ['fileds'=>'distribution_at','fileds_name'=>'分配时间','sort'=>'20','status'=>1,'is_update'=>1],
        ['fileds'=>'customer_created_at','fileds_name'=>'客户创建时间','sort'=>'21','status'=>1,'is_update'=>1],
        ['fileds'=>'stage_update_at','fileds_name'=>'阶段更新时间','sort'=>'22','status'=>1,'is_update'=>1],
    );

    public static $fileds_default_public = array(
        ['fileds'=>'public_name','fileds_name'=>'所属公海','sort'=>'23','status'=>1,'is_update'=>1],
        ['fileds'=>'recovery_at','fileds_name'=>'回收时间','sort'=>'24','status'=>1,'is_update'=>1],
    );
}
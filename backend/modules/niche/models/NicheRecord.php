<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheRecord"))
 */
class NicheRecord extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 20)
     * @var integer
     */
    public $id;


    /**
     * 商机表id
     * @SWG\Property(example =1)
     * @var integer
     */
    public $niche_id;

    /**
     * 跟进记录内容
     * @SWG\Property(example = "跟进成功")
     * @var string
     */
    public $content;

    /**
     * 下次跟进时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $next_follow_time;

    /**
     * 跟进人姓名
     * @SWG\Property(example = "杨彦江")
     * @var string
     */
    public $creator_name;


    /**
     * 跟进人ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $creator_id;

    /**
     * 跟进时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $created_at;


    /**
     * 跟进方式ID
     * @SWG\Property(example = "10")
     * @var integer
     */
    public $follow_mode_id;

    /**
     * 跟方式名称
     * @SWG\Property(example = "普通跟进")
     * @var string
     */
    public $follow_mode_name;


    /**
     * 开始时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $start_at;

    /**
     * 结束时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $end_at;

}
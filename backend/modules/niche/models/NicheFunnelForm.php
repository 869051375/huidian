<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 商机商品
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheFunnelForm"))
 */
class NicheFunnelForm extends Model
{

    /**
     * 数量
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $number;

    /**
     * 总金额
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $total_number;

    /**
     * 商机阶段（10:目标识别 30：需求确定 60：谈判审核 80：合同确认 100：赢单  0：输单）
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $progress;
}
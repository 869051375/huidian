<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use Yii;


/**
 * 更新商机
 * @SWG\Definition(required={"id", "name", "customer_id", "predict_deal_time", "source_id", "channel_id", "next_follow_time"}, @SWG\Xml(name="UpdateNicheForm"))
 */
class UpdateNicheForm extends Niche
{
    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    public function save()
    {

    }
}

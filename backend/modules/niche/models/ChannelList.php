<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 渠道来源列表
 * @SWG\Definition(required={}, @SWG\Xml(name="ChannelList"))
 */
class ChannelList extends Model
{

    /**
     * 渠道来源ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 渠道来源名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    public function select()
    {
        return \common\models\Channel::find()->select('id,name')->where(['status'=>1])->orderBy('sort asc')->asArray()->all();
    }
}

<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机创建人列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCreateList"))
 */
class NicheCreateList extends Model
{

    /**
     * 创建人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $creator_id;


    /**
     * 创建人名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $creator_name;

    public function select()
    {
        return \common\models\Niche::find()->distinct()->select('creator_id,creator_name')->asArray()->all();
    }
}

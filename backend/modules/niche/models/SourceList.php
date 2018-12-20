<?php

namespace backend\modules\niche\models;

use common\models\Source;
use Yii;
use yii\base\Model;


/**
 * 来源列表
 * @SWG\Definition(required={}, @SWG\Xml(name="SourceList"))
 */
class SourceList extends Model
{

    /**
     * 来源ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 来源名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    public function select()
    {
        return Source::find()->select('id,name')->where(['status'=>1])->orderBy('sort asc')->asArray()->all();
    }
}

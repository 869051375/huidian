<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机最后跟进人
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheLastRecordList"))
 */
class NicheLastRecordList extends Model
{

    /**
     * 跟进人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_record_creator_id;


    /**
     * 跟进人名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $last_record_creator_name;

    public function select()
    {
        return \common\models\Niche::find()->distinct()->select('last_record_creator_id,last_record_creator_name')->where("last_record_creator_name != '' ")->asArray()->all();
    }
}

<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机分配人列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheDistributionList"))
 */
class NicheDistributionList extends Model
{

    /**
     * 分配人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $distribution_id;


    /**
     * 分配人名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $distribution_name;

    public function select()
    {
        return \common\models\Niche::find()->distinct()->select('distribution_id,distribution_name')->where("distribution_name != '' ")->asArray()->all();
    }
}

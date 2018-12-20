<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机创建人列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheAdministratorList"))
 */
class NicheAdministratorList extends Model
{

    /**
     * 负责人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $administrator_id;


    /**
     * 负责人名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $administrator_name;



    public function select()
    {
        return \common\models\Niche::find()->distinct()->select('administrator_id,administrator_name')->asArray()->all();
    }
}

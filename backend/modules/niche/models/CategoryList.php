<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 类目列表
 * @SWG\Definition(required={}, @SWG\Xml(name="CategoryList"))
 */
class CategoryList extends Model
{

    /**
     * 类目ID （非必填查询一级类目默认传0，二级类目传一级类目ID）
     * @SWG\Property(example = 0)
     * @var integer
     */
    public $id;


    /**
     * 类目名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    public function rules()
    {
        return [
            [['id'],'integer'],
            [['name'],'string'],
        ];

    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function select()
    {
        if ($this->id > 0)
        {
            return \common\models\ProductCategory::find()->distinct()->select('id,name')->where(['parent_id'=>$this->id])->asArray()->all();
        }
        else
        {
            return \common\models\ProductCategory::find()->distinct()->select('id,name')->where(['parent_id'=>0])->asArray()->all();
        }
    }
}

<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 类目联动商品列表
 * @SWG\Definition(required={}, @SWG\Xml(name="ProductCategoryList"))
 */
class ProductCategoryList extends Model
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
        return \common\models\Product::find()->distinct()->select('id,alias as name')->where(['status'=>1])->andWhere(['category_id'=>$this->id])->asArray()->all();
    }
}

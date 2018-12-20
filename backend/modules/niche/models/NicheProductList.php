<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublicDepartment;
use common\models\OpportunityAssignDepartment;
use common\models\Product;
use Yii;
use yii\base\Model;
use yii\db\Query;


/**
 * 商机商品列表接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheProductList"))
 */
class NicheProductList extends Model
{

    /**
     * 商品ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $id;

    /**
     * 一级分类
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $top_category_id;

    /**
     * 二级分类
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $category_id;

    /**
     * 商品原价
     * @SWG\Property(example = "1")
     * @var float
     */
    public $original_price;

    /**
     * 商品销售价格
     * @SWG\Property(example = "1")
     * @var float
     */
    public $price;

    /**
     * 商品别名
     * @SWG\Property(example = "商品别名")
     * @var string
     */
    public $name;

    /** @var $currentAdministrator */
    public $currentAdministrator;

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }


    public function select()
    {
        return Product::find()->select('product.alias as name,product.id,product.top_category_id,product.category_id,product.price,product.original_price')->leftJoin(['dep'=>OpportunityAssignDepartment::tableName()],'dep.product_id = product.id')->where(['dep.company_id'=>$this->currentAdministrator->company_id])->all();
    }
}

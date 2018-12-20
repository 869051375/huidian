<?php
namespace backend\models;


use common\models\Clerk;
use common\models\ClerkItems;
use common\models\ProductCategory;
use common\models\Province;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ClerkItemsForm
 * @property Province $province
 */
class ClerkItemsForm extends Model
{
    public $clerk_id;
    public $top_category_id;
    public $category_id;
    public $product_ids;

    /**
     * @var Clerk
     */
    public $clerk;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['clerk_id', 'required'],
            ['clerk_id', 'validateClerkId'],
            [['category_id','top_category_id'], 'integer'],
            [['product_ids'], 'validateProductIds'],
            [['product_ids'], 'required'],
        ];
    }

    public function validateClerkId()
    {
        $this->clerk = Clerk::findOne($this->clerk_id);
        if(null == $this->clerk)
        {
            $this->addError('clerk_id', '服务人员不存在。');
            return ;
        }
    }

    public function validateProductIds()
    {
//        $moedel = ClerkItems::find()->where([
//            'check_id'=>$this->clerk_id,
//            'category_id'=>$this->category_id,
//            'top_category_id'=>$this->top_category_id])
//            ->one();
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clerk_id' => '服务人员ID',
            'top_category_id' => '',
            'category_id' => '服务项目',
            'product_ids' => '商品',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $clerkItem = $this->clerk->getClerkItemByCategoryId($this->category_id);
        if(null == $clerkItem)
        {
            $clerkItem = new ClerkItems();
            $clerkItem->clerk_id = $this->clerk_id;
            $clerkItem->category_id = $this->category_id;
            $clerkItem->top_category_id = $this->top_category_id;
        }
        $ids = $clerkItem->getProductIds();
        $clerkItem->setProductIds(ArrayHelper::merge($ids, $this->product_ids));
        return $clerkItem->save(false);
    }
}
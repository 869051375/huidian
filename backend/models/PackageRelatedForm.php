<?php
namespace backend\models;
use common\models\PackageRelated;
use common\models\Product;
use yii\base\Model;

/**
 * Class PackageRelatedForm
 * @package backend\models
 *
 */
class PackageRelatedForm extends Model
{
    public $package_related_id;
    public $package_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_id', 'package_related_id'], 'required'],
            ['package_related_id', 'validatePackageId'],
            ['package_related_id', 'validatePackageRelatedId'],
        ];
    }

    public function validatePackageRelatedId()
    {
        $model = Product::findOne($this->package_id);
        if(null != $model)
        {
            if($model->isPayAfterService())
            {
                $this->addError('package_related_id', '先服务后付费商品不允许添加关联套餐商品！');
            }
        }
        if($this->package_related_id == $this->package_id)
        {
            $this->addError('package_related_id', '不能关联自己!');
        }
    }

    public function validatePackageId()
    {
        $r_id = $this->package_related_id;
        $p_id = $this->package_id;
        $data = PackageRelated::find()
            ->andWhere(['=','package_related_id',$r_id])
            ->andWhere(['=','package_id',$p_id])
            ->All();
        if(!empty($data))
        {
            $this->addError('package_related_id', '您已经添加过了!');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'package_related_id' => '关联套餐',
        ];
    }

    /**
     * @return PackageRelated
     */
    private function createPackageRelated()
    {
        $model = new PackageRelated();
        $model->package_id = $this->package_related_id;
        $model->package_related_id = $this->package_id;
        $model->save(false);
        return $model;
    }

    /**
     * @return PackageRelated
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $this->createPackageRelated();
        $product_related = new PackageRelated();
        $product_related->package_id = $this->package_id;
        $product_related->package_related_id = $this->package_related_id;
        return $product_related->save() ? $product_related : null;
    }


}

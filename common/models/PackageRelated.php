<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%package_related}}".
 *
 * @property integer $package_id
 * @property integer $package_related_id
 *
 * @property Product $packageProduct
 */
class PackageRelated extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%package_related}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_id', 'package_related_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'package_id' => 'Package ID',
            'package_related_id' => 'Package Related ID',
        ];
    }

    public function getPackageProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'package_related_id']);
    }
}

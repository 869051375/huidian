<?php
namespace backend\models;

use common\models\ProductCategory;

/**
 * Class PackageProductsForm
 * @package backend\models
 *
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Flow $flow
 */
class ClassificationProductsForm extends ProductCategory
{


   public function rules()
   {
   	return [
            [['keywords','description','title'], 'required'],
            [['keywords','title'], 'string', 'max' => 80 ,'message'=>'至多包含80个字符'],
            [['description'], 'string', 'max' => 200 ,'message'=>'至多包含80个字符'],
            [['content'], 'string'],
        ];
   }
       /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'keywords' => '关键词',
            'title' => '标题',
            'description' => '描述',
            'content' => '页面底部描述',

        ];
    }






}

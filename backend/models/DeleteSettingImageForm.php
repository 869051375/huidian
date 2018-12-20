<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/19
 * Time: 上午11:36
 */

namespace backend\models;

use common\models\Property;
use imxiangli\image\storage\ImageStorageInterface;
use yii\base\Model;

class DeleteSettingImageForm extends Model
{
    public $key;
    public $file;

    /**
     * @var Property
     */
    public $property;

    public function rules()
    {
        return [
            [['key', 'file'], 'required'],
            [['key'], 'validateKey'],
        ];
    }

    public function validateKey()
    {
        $this->property = Property::find()->where(['key' => $this->key])->one();
        if(null == $this->property)
        {
            $this->addError('key', '找不到指定的数据');
        }
    }

    public function delete()
    {
        if(!$this->validate()) return false;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $imageStorage->delete($this->file);
        Property::set($this->key, null);
        return true;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/19
 * Time: 上午11:36
 */

namespace backend\models;

use common\models\Trademark;
use imxiangli\image\storage\ImageStorageInterface;
use yii\base\Model;

class DeleteTrademarkImageForm extends Model
{
    public $trademark_id;

    /**
     * @var Trademark
     */
    public $trademark;

    public function rules()
    {
        return [
            [['trademark_id'], 'required'],
            [['trademark_id'], 'validateImageId'],
        ];
    }

    public function validateImageId()
    {
        $this->trademark = Trademark::findOne($this->trademark_id);
        if(null === $this->trademark)
        {
            $this->addError('trademark_id', '找不到商标图片');
        }
    }

    public function delete()
    {
        if(!$this->validate()) return false;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $imageStorage->delete($this->trademark->image);
        $this->trademark->image = '';
        $this->trademark->save(false);
        return true;
    }
}
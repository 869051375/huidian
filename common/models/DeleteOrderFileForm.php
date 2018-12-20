<?php

namespace common\models;

use imxiangli\image\storage\OSS;
use Yii;
use yii\base\Model;

class DeleteOrderFileForm extends Model
{
    public $file_id;
    public $key;

    /**
     * @var OrderFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file_id','key'], 'required'],
            ['key', 'string'],
            [['file_id'], 'validateImageId'],
        ];
    }

    public function validateImageId()
    {
        $this->file = OrderFile::findOne($this->file_id);
        if(null === $this->file)
        {
            $this->addError('file_id', '找不到订单文件');
        }
    }

    public function delete()
    {
        if(!$this->validate()) return false;

        $files = $this->file->getFiles();
        /** @var OSS $oss */
        $oss = Yii::$app->get('oss');
        foreach($files as $file)
        {
            if($file['key'] == $this->key)
            {
                $oss->delete($this->key);
                $this->file->removeFile($this->key);
                $this->file->save(false);
                if(count($this->file->getFiles()) <= 0)
                {
                    $this->file->delete();
                }
                return true;
            }
        }
        return false;
    }
}
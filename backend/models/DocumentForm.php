<?php
namespace backend\models;

use common\models\Document;
use yii\base\Model;
/**
 * Class DocumentForm
 * @package backend\models
 *
 */
class DocumentForm extends Model
{
    /**
     * @var Document
     */
    public $title;
    public $sort = 0;
    public $content;
    public $document_category_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['document_category_id', 'sort'], 'integer'],
            [['sort'], 'integer'],
            [['sort'], 'safe'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'document_category_id' => 'Document Category ID',
            'title' => '文档标题',
            'content' => '文档正文',
            'sort' => '排序值',
        ];
    }

    /**
     * @return Document
     */
    public function save()
    {
        $document = new Document();
        $document->load($this->attributes, '');
        if(!$document->save(false))
        {
            return null;
        }
        return $document;
    }

    /**
     * @param Document $document
     * @return null
     */
    public function update($document)
    {
        if(!$this->validate()) return false;
        $document->load($this->attributes, '');
        if($document->update(false)) return true;
        return false;
    }
}

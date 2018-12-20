<?php

namespace backend\modules\niche\models;

use common\models\Tag;
use yii\base\Model;


/**
 * 用于更新商机标签
 *  @SWG\Definition(required={"name", "color"}, @SWG\Xml(name="NicheLabelAddForm"))
 */
class NicheLabelAddForm extends Model
{

    /**
     * 标签类型 (1:属于商机类型标签)
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;

    /**
     * 标签名称
     * @SWG\Property(example = "重要商机")
     * @var string
     */
    public $name;

    /**
     * 标签颜色
     * @SWG\Property(example = "f00000")
     * @var string
     */
    public $color;


    public function rules()
    {
        return [
            [['type','name','color'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['color'],'string', 'max'=> 6],
        ];
    }

    public function save($administrator)
    {
        $tag = new Tag();
        $tag->company_id = $administrator->company_id ;
        $tag->type = NicheLabel::NICHE_TYPE;
        $tag->name = $this->name;
        $tag->color = $this->color;
        return $tag->save(false);
    }
}
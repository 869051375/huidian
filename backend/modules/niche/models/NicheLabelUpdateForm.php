<?php

namespace backend\modules\niche\models;

use common\models\Tag;
use Yii;
use yii\base\Model;


/**
 * 用于更新商机的商机商品
 *  @SWG\Definition(required={"id", "name", "color"}, @SWG\Xml(name="NicheLabelUpdateForm"))
 */
class NicheLabelUpdateForm extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

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
            [['id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['color'],'string', 'max'=> 6],
            ['id', 'validateNicheLabel'],
        ];
    }

    public function validateNicheLabel()
    {
        $model = new Tag();
        $niche = $model::find()->where(['id'=>$this->id])->one();
        if(empty($niche)){
            $this->addError('id','修改成功');
        }
        $user = \Yii::$app->user->identity;
        $niche = $model::find()->where(['id'=>$this->id])->andWhere(['company_id'=>$user['company_id']])->one();
        if(empty($niche)){
            $this->addError('id','您没有权限操作本条标签');
        }
        return true;
    }

    public function save()
    {
        $tag = Tag::findOne($this->id);
        if(empty($tag)){
            return true;
        }
        $tag->name = $this->name;
        $tag->color = $this->color;
        return $tag->save(false);
    }
}
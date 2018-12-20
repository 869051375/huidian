<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use common\models\Tag;
use yii\base\Model;


/**
 *  @SWG\Definition(required={"id", "niche_id"}, @SWG\Xml(name="NicheLabelChangeForm"))
 */
class NicheLabelChangeForm extends Model
{
    /**
     * 标签自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 商机id
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $niche_id;


    /**
     * 商机标签名称
     * @SWG\Property(example = "重要商机")
     * @var integer
     */
    public $name;


    public function rules()
    {
        return [
            [['id', 'niche_id','name'], 'required'],
            [['id'], 'integer'],
            [['name'],'string','max'=>25],
            ['id', 'validateNicheLabel'],
        ];
    }

    public function validateNicheLabel()
    {
        $label =Tag::find()->where(['id' => $this->id])->one();
        if (empty($label)) {
            $this->addError('id', '此标签不存在');
        }
        return true;
    }
    /*
     * 修改商机标签
     * */
    public function save()
    {
        $niche_id = explode(',',$this->niche_id);
        for ($i =0;$i<count($niche_id);$i++){
            $niche = Niche::findOne($niche_id[$i]);
            $niche->label_id = $this->id;
            $niche->label_name = $this->name;
            $niche->updated_at = time();
            $niche->save(false);
        }
       return true;
    }
}

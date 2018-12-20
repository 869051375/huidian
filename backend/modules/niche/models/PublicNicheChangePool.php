<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use common\models\NicheOperationRecord;
use common\models\NichePublic;
use yii\base\Model;
use Yii;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="PublicNicheChangePool"))
 */
class PublicNicheChangePool extends Model
{

    /**
     * 商机ID
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $niche_id;

    /**
     * 公海ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $public_id;


    public function rules()
    {
        return [
            [['niche_id','public_id'], 'required'],
            [['public_id'], 'integer'],
            [['niche_id'], 'string'],
        ];
    }

    public function changePool($administrator)
    {
        $niche_id = explode(',',$this->niche_id);
        for($i=0;$i<count($niche_id);$i++) {
            /** @var Niche $niche */
            $niche = Niche::find()->where(['id' => $niche_id[$i]])->one();
            $niche_public = NichePublic::find()->where(['id' => $this->public_id])->one();
            $niche->niche_public_id = $this->public_id;
            $niche->update_id = $administrator->id;
            $niche->update_name = $administrator->name;
            $niche->move_public_time = time();
            $niche->updated_at = time();
            $niche->save(false);
            $model = new NicheOperationRecord();
            $model->niche_id = $this->niche_id;
            $model->content = "更换分组到" . $niche_public->name;
            $model->item = "更换分组";
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
            $model->created_at = time();
            $model->save(false);
        }
        return true;
    }
}
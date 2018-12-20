<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use common\models\NicheOperationRecord;
use yii\base\Model;
use Yii;

/**
 *
 * @SWG\Definition(required={"niche_id", "content", "follow_mode_id", "follow_mode_name"}, @SWG\Xml(name="CreateNicheRecordForm"))
 */
class CreateNicheRecordForm extends Model
{
    /**
     * 商机表id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 跟进记录内容
     * @SWG\Property(example = "跟进成功")
     * @var string
     */
    public $content;

    /**
     * 下次跟进时间
     * @SWG\Property(example = "2018-09-12")
     * @var string
     */
    public $next_follow_time;


    /**
     * 跟进方式ID
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $follow_mode_id;

    /**
     * 跟方式名称
     * @SWG\Property(example = "普通跟进")
     * @var string
     */
    public $follow_mode_name;


    /**
     * 开始时间
     * @SWG\Property(example = "2018-09-12")
     * @var string
     */
    public $start_at;

    /**
     * 结束时间
     * @SWG\Property(example = "2018-09-12")
     * @var string
     */
    public $end_at;



    public function rules()
    {
        return [
            [["niche_id", "content",  "follow_mode_id", "follow_mode_name"], 'required'],
            [['niche_id','follow_mode_id'], 'integer'],
            [['content',"next_follow_time",'start_at','end_at'], 'string'],
            [['follow_mode_name'], 'string', 'max' => 25],
            ['niche_id', 'validateNiche'],
        ];
    }

    public function validateNiche()
    {

        $model = new \common\models\Niche();
        $niche = $model::find()->where(['id'=>$this->niche_id])->one();
        if(empty($niche)){
            $this->addError('niche_id','这条商机不存在');
        }
        return true;
    }

    public function save($administrator)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try{
            $nicheRecord = new \common\models\NicheRecord();
            $nicheRecord->niche_id = $this->niche_id;
            $nicheRecord->content = $this->content;
            $nicheRecord->next_follow_time = $this->next_follow_time ? strtotime($this->next_follow_time) : "";
            $nicheRecord->creator_id = $administrator->id ? $administrator->id:"0";
            $nicheRecord->creator_name = $administrator->name ? $administrator->name : "0";
            $nicheRecord->created_at = time();
            $nicheRecord->follow_mode_id = $this->follow_mode_id;
            $nicheRecord->follow_mode_name = $this->follow_mode_name;
            $nicheRecord->start_at = !empty($this->start_at) ? strtotime($this->start_at) :0;
            $nicheRecord->end_at = !empty($this->end_at) ? strtotime($this->end_at) :0;
            $nicheRecord->save(false);
            $niche = Niche::findOne(['id'=>$this->niche_id]);
            $niche->last_record_creator_id = $administrator->id;
            $niche->last_record_creator_name = $administrator->name;
            $niche->next_follow_time = $this->next_follow_time ? strtotime($this->next_follow_time) :"";
            $niche->is_new = 0;
            $niche->last_record = time();
            $niche->update_id = $administrator->id;
            $niche->update_name = $administrator->name;
            $niche->updated_at = time();
            $niche->save(false);
            $nicheOperationRecord = new NicheOperationRecord();
            $nicheOperationRecord->niche_id = $this->niche_id;
            $nicheOperationRecord->content = "跟进了商机";
            $nicheOperationRecord->item = '跟进商机';
            $nicheOperationRecord->creator_id = $administrator->id ? $administrator->id:"0";
            $nicheOperationRecord->creator_name = $administrator->name ? $administrator->name : "0";
            $nicheOperationRecord->created_at = time();
            $nicheOperationRecord->save(false);
            $transaction -> commit();
            $res = true;
        }catch (\Exception $e){
            echo $e->getMessage();die;
            $transaction ->rollBack();
            $res = false;
        }
        return $res;
    }
}

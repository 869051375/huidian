<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmContacts;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use Yii;
use yii\base\Model;


/**
 * 商机赢单接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheWin"))
 */
class NicheWin extends Model
{

    /**
     * 赢单原因
     * @SWG\Property(example = "价格原因")
     * @var integer
     */
    public $win_reason;

    /**
     * 赢单描述
     * @SWG\Property(example = "这是赢单的描述")
     * @var integer
     */
    public $win_describe;

    /**
     * 是否更新预计成交时间为当前时间  1为是。0为否
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_change;


    /** @var $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $niche_id;



    public function rules()
    {
        return [
            [['niche_id','win_reason','win_describe'], 'required'],
            [['niche_id','is_change'], 'integer'],
            [['win_reason','win_describe'], 'string'],
            [['niche_id'], 'validateNicheId'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateNicheId()
    {
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        if (empty($niche_one))
        {
            return $this->addError('niche_ids','商机ID不存在');
        }
        return true;
    }

//    public function validateProgress()
//    {
//        if (!in_array($this->progress,[20,40,60,80]))
//        {
//            return $this->addError('progress','商机阶段更新错误');
//        }
//        return true;
//    }



    public function save()
    {

        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try{

            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
            $niche_one->win_progress = $niche_one->progress;
            $niche_one->win_describe = $this->win_describe;
            $niche_one->win_reason = $this->win_reason;
            $niche_one->progress = Niche::PROGRESS_100;
            $niche_one->stage_update_at = time();
            $niche_one->stage_update_id = $this->currentAdministrator->id;
            $niche_one->stage_update_name = $this->currentAdministrator->name;
            $niche_one->last_record = time();
            $niche_one->is_new = 0;
            if ($this->is_change == 1)
            {
                $niche_one->predict_deal_time = time();
            }
            $niche_one->status = Niche::STATUS_DEAL;
            $niche_one->update_id = $this->currentAdministrator->id;
            $niche_one->update_name = $this->currentAdministrator->name;
            $niche_one->updated_at = time();
            $niche_one->is_protect = 0;
            //统计埋点
            $data = new CustomerExchangeList();
            /** @var CrmContacts $contract */
            $contract = CrmContacts::find()->where(['customer_id'=>$niche_one->customer_id])->one();
            $data->win(['id'=>$niche_one->id,'administrator_id'=>$niche_one->administrator_id,'province_id'=> isset($contract->province_id) ? $contract->province_id : 0,'city_id'=> isset($contract->city_id) ? $contract->city_id : 0,'district_id' => isset($contract->district_id) ? $contract->district_id : 0,'source_id'=>isset($niche_one->source_id) ? $niche_one->source_id : 0,'channel_id'=>isset($niche_one->channel_id) ? $niche_one->channel_id : 0,'amount'=>$niche_one->total_amount]);

            //跟进记录
            $niche_record = new NicheRecord();
            $niche_record->niche_id = $niche_one->id;
            $niche_record->content = '商机阶段更新为：赢单，赢单原因为：'.$this->win_reason;
            $niche_record->creator_id = $this->currentAdministrator->id;
            $niche_record->creator_name = $this->currentAdministrator->name;
            $niche_record->created_at = time();
            $niche_record->follow_mode_id = 0;
            $niche_record->follow_mode_name = '其他';
            $niche_record->save(false);

            //添加操作记录
            NicheOperationRecord::create($this->niche_id,'商机赢单','商机阶段更新为：赢单，赢单原因为：'.$this->win_reason);
            //商机漏洞
            $model = new NicheFunnel();
            $model->add($this->niche_id,100);
            $res = $niche_one->save(false);
            $transaction ->commit();
        }catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;
        }
        return $res;

    }
}

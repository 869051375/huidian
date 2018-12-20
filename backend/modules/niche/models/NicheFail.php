<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmContacts;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use Yii;
use yii\base\Model;
use yii\db\Query;


/**
 * 商机输单接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheFail"))
 */
class NicheFail extends Model
{

    /**
     * 输单原因
     * @SWG\Property(example = "价格原因")
     * @var integer
     */
    public $lose_reason;

    /**
     * 输单描述
     * @SWG\Property(example = "这是赢单的描述")
     * @var integer
     */
    public $lose_describe;

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
            [['niche_id','lose_reason','lose_describe'], 'required'],
            [['niche_id'], 'integer'],
            [['lose_reason','lose_describe'], 'string'],
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


    public function save()
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var NichePublicDepartment $niche_public */
            $query = new Query();
            $niche_public = $query->from(['np' => NichePublic::tableName()])->select('np.id as niche_public_id')
                ->leftJoin(['npd' => NichePublicDepartment::tableName()], 'np.id = npd.niche_public_id')
                ->where(['np.status' => 1])
                ->andWhere(['npd.department_id' => $this->currentAdministrator->department_id])
                ->one();
            //如果当前用户没有公海的话
            if (empty($niche_public)) {
                /** @var Niche $niche_one */
                $niche_one = Niche::find()->where(['id' => $this->niche_id])->one();
                $niche_one->lose_progress = $niche_one->progress;
                $niche_one->lose_describe = $this->lose_describe;
                $niche_one->lose_reason = $this->lose_reason;
                $niche_one->progress = Niche::PROGRESS_0;
                $niche_one->status = Niche::STATUS_FAIL;
                $niche_one->stage_update_at = time();
                $niche_one->stage_update_id = $this->currentAdministrator->id;
                $niche_one->stage_update_name = $this->currentAdministrator->name;
                $niche_one->last_record = time();
                $niche_one->is_protect = 0;
                $niche_one->is_new = 0;
                $niche_one->update_id = $this->currentAdministrator->id;
                $niche_one->update_name = $this->currentAdministrator->name;
                $niche_one->updated_at = time();

                //跟进记录
                $niche_record = new NicheRecord();
                $niche_record->niche_id = $niche_one->id;
                $niche_record->content = '商机阶段更新为：输单，输单原因为：' . $this->lose_reason;
                $niche_record->creator_id = $this->currentAdministrator->id;
                $niche_record->creator_name = $this->currentAdministrator->name;
                $niche_record->created_at = time();
                $niche_record->follow_mode_id = 0;
                $niche_record->follow_mode_name = '其他';
                $niche_record->save(false);
                
                //统计埋点
                $data = new CustomerExchangeList();
                /** @var CrmContacts $contract */
                $contract = CrmContacts::find()->where(['customer_id'=>$niche_one->customer_id])->one();
                $data->lose(['id'=>$this->niche_id,'administrator_id' => $niche_one->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount]);

                //添加操作记录
                NicheOperationRecord::create($this->niche_id, '商机输单', '商机阶段更新为：输单，输单原因为：' . $this->lose_reason);
                //商机漏斗
                $model = new NicheFunnel();
                $model->add($this->niche_id,0);
                $niche_one->save(false);
                $res = 1;
            } //当前用户有公海
            else
            {
                /** @var Niche $niche_one */
                $niche_one = Niche::find()->where(['id' => $this->niche_id])->one();
                $niche_one->lose_progress = $niche_one->progress;
                $niche_one->lose_describe = $this->lose_describe;
                $niche_one->lose_reason = $this->lose_reason;
                $niche_one->progress = Niche::PROGRESS_0;
                $niche_one->status = Niche::STATUS_FAIL;
                $niche_one->stage_update_at = time();
                $niche_one->stage_update_id = $this->currentAdministrator->id;
                $niche_one->stage_update_name = $this->currentAdministrator->name;
                $niche_one->last_record = time();
                $niche_one->is_protect = 0;
                $niche_one->is_new = 0;
                $niche_one->niche_public_id = $niche_public['niche_public_id'];
                $niche_one->update_id = $this->currentAdministrator->id;
                $niche_one->update_name = $this->currentAdministrator->name;
                $niche_one->updated_at = time();
                $niche_one->move_public_time = time();
                $niche_one->recovery_at = time();
                $niche_one->administrator_id = 0;
                $niche_one->administrator_name = '';

                //统计埋点
                $data = new CustomerExchangeList();
                /** @var CrmContacts $contract */
                $contract = CrmContacts::find()->where(['customer_id'=>$niche_one->customer_id])->one();
                $data->lose(['id'=>$niche_one->id,'administrator_id' => $niche_one->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount]);

                //跟进记录
                $niche_record = new NicheRecord();
                $niche_record->niche_id = $niche_one->id;
                $niche_record->content = '商机阶段更新为：输单，输单原因为：' . $this->lose_reason;
                $niche_record->creator_id = $this->currentAdministrator->id;
                $niche_record->creator_name = $this->currentAdministrator->name;
                $niche_record->created_at = time();
                $niche_record->follow_mode_id = 0;
                $niche_record->follow_mode_name = '其他';
                $niche_record->save(false);

                //添加操作记录
                NicheOperationRecord::create($this->niche_id, '商机输单', '商机阶段更新为：输单，输单原因为：' . $this->lose_reason);
                $niche_one->save(false);
                $res = 2;
                //商机漏洞
                $model = new NicheFunnel();
                $model->add($this->niche_id,0);
            }

            $transaction ->commit();
        }catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;
        }
        return $res;

    }
}

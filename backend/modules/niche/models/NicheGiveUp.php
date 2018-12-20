<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmContacts;
use common\models\Niche;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use common\models\NicheTeam;
use Yii;
use yii\base\Model;


/**
 * 放弃商机
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheGiveUp"))
 */
class NicheGiveUp extends Model
{

    /**
     * 放弃理由
     * @SWG\Property(example = "放弃理由")
     * @var string
     */
    public $reason;

    /** @var $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1,2,3")
     * @var integer
     */
    public $niche_ids;



    public function rules()
    {
        return [
            [['niche_ids'], 'required','message'=>'请至少选择1条商机！'],
            [['niche_ids'], 'string'],
            [['reason'], 'string','tooLong'=>5],
            [['niche_ids'], 'validateNicheIds'],
            [['reason'], 'validateReason'],
            [['niche_ids'], 'validateDepartment'],
            [['niche_ids'], 'validateIsProtect'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateReason()
    {
        if (mb_strlen($this->reason) >= 500)
        {
            return $this->addError('reason','放弃原因，输入长度不能超过500字');
        }
        return true;
    }

    public function validateNicheIds()
    {
        $ids = explode(',', $this->niche_ids);
        //负责人是我的
        $niche_count = \common\models\Niche::find()->where(['in','id',$ids])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->count();
        //协作人是我的
        $team_count = \common\models\NicheTeam::find()->where(['in','niche_id',$ids])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->count();

        if (((int)$niche_count+(int)$team_count) < count($ids)){
            return $this->addError('niche_ids','您不是团队成员，不能放弃所选商机');
        }
        return true;
    }
    
    //检验商机是否被保护
    public function validateIsProtect()
    {
        $ids = explode(',', $this->niche_ids);
        /** @var Niche $niche */
        $niche = Niche::find()->where(['in','id',$ids])->all();
        foreach ($niche as $item)
        {
            if ($item->is_protect == 1)
            {
                return $this->addError('niche_ids','对不起，该商机（ID：'.$item->id.'）已被保护，请取消保护之后再放弃');
            }
        }
        return true;
    }


    /** 验证是否开启公司部门 */
    public function validateDepartment()
    {
        if (isset($this->currentAdministrator->department_id) && $this->currentAdministrator->department_id >0)
        {
            if (!NichePublicDepartment::find()->where(['department_id'=>$this->currentAdministrator->department_id])->one())
            {
                return $this->addError('niche_ids','对不起，当前用户所在部门没有商机公海，不能放弃商机。');
            }
        }else{
            return $this->addError('niche_ids','对不起，当前用户所在部门没有商机公海，不能放弃商机。');
        }
        return true;
    }




    public function save()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $administrator->department_id = isset($administrator->department_id) ? $administrator->department_id : 0;

        /** @var NichePublicDepartment $niche_public */
        $niche_public = NichePublicDepartment::find()->where(['department_id' => $administrator->department_id])->one();

        $ids = explode(',', $this->niche_ids);
        $count = 0;
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($ids as $niche_id)
            {
                /** @var \common\models\Niche $niche_one */
                $niche_one = $this->getOne($niche_id);
                //如果负责人是自己

                if ($niche_one)
                {
                    //查看是否有协助人
                    if ($team = $this->getTeam($niche_id))
                    {
                        $niche_one->administrator_id = $team->administrator_id;
                        $niche_one->administrator_name = $team->administrator_name;
                        $niche_one->update_id = $this->currentAdministrator->id;
                        $niche_one->update_name = $this->currentAdministrator->name;
                        $niche_one->updated_at = time();
                        $niche_one->last_record = time();
                        $niche_one->is_new = 0;
                        $niche_one->save(false);

                        //把协作人移除
                        $team->delete();
                        ++$count;

                        //跟进记录
                        $niche_record = new NicheRecord();
                        $niche_record->niche_id = $niche_one->id;
                        $niche_record->content = '放弃商机成功，当前负责人为：' . $team->administrator_name . '，放弃原因为：' . $this->reason;
                        $niche_record->creator_id = $this->currentAdministrator->id;
                        $niche_record->creator_name = $this->currentAdministrator->name;
                        $niche_record->created_at = time();
                        $niche_record->follow_mode_id = 0;
                        $niche_record->follow_mode_name = '其他';
                        $niche_record->save(false);

                        //添加操作记录
                        NicheOperationRecord::create($niche_id, '放弃商机', '放弃商机成功，当前负责人为：' . $team->administrator_name . '，放弃原因为：' . $this->reason);
                    }
                    else
                    {
                        $niche_one->administrator_id = 0;
                        $niche_one->administrator_name = '';
                        $niche_one->niche_public_id = $niche_public->niche_public_id;
                        $niche_one->is_give_up = 1;
                        $niche_one->is_extract = 0;
                        $niche_one->is_distribution = 0;
                        $niche_one->is_transfer = 0;
                        $niche_one->is_new = 0;
                        $niche_one->update_id = $this->currentAdministrator->id;
                        $niche_one->update_name = $this->currentAdministrator->name;
                        $niche_one->updated_at = time();
                        $niche_one->last_record = time();
                        $niche_one->move_public_time = time();
                        $niche_one->recovery_at= time();
                        $niche_one->save(false);
                        ++$count;
                        //添加操作记录
                        NicheOperationRecord::create($niche_id, '放弃商机', '放弃商机到' . $niche_public->nichePublic->name . '，放弃原因为：' . $this->reason);
                    }

                    //跟进记录
                    $niche_record = new NicheRecord();
                    $niche_record->niche_id = $niche_one->id;
                    $niche_record->content = '放弃商机成功，当前负责人为：' . $niche_one->administrator_name . '，放弃原因为：' . $this->reason;
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
                    $data->niche(['id' => $niche_one->id, 'from' => '', 'administrator_id' => $this->currentAdministrator->id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount],'giveup');
                    $model = new NicheFunnel();
                    $model->del($niche_one->id,$this->currentAdministrator->id);
                }
                else
                {
                    $niche_team = NicheTeam::find()->where(['niche_id' => $niche_id])->andWhere(['administrator_id' => $this->currentAdministrator->id])->one();
                    if (!empty($niche_team))
                    {
                        $niche_team->delete();
                        NicheOperationRecord::create($niche_id, '放弃商机', '放弃商机成功，当前负责人为：' . $niche_one->administrator_name . '，放弃原因为：' . $this->reason);
                    }
                }
            }
            $transaction ->commit();
        }
        catch (\Exception $e)
        {
            $transaction -> rollBack();
            $count = false;
        }
        return $count;


    }

    public function getOne($id)
    {
        return \common\models\Niche::find()->where(['id'=>$id])->one();
    }

    public function getTeam($id)
    {
        return \common\models\NicheTeam::find()->where(['niche_id'=>$id])->orderBy('sort asc')->one();
    }
}

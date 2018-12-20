<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use \backend\modules\niche\models\NicheOperationRecord;
use common\models\CrmContacts;
use common\models\Niche;
use common\models\NichePublicDepartment;
use common\models\NicheTeam;
use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="TransferNicheForm"))
 */
class TransferNicheForm extends Model
{
    /**
     * 商机id列表（多个逗号分隔）
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $niche_ids;

    /**
     * 成员id（系统用户id）
     * @SWG\Property(example = 1)
     * @var string
     */
    public $administrator_id;

    /**
     * 是否保留【负责人】为商机团队成员 1是，0不是
     * @SWG\Property(example = 1)
     * @var string
     */
    public $is_retain;
    /** @var $administrator_name */
    public $administrator_name;
    /** @var $power */
    public $power;

    /**
     * @var Administrator
     */
    public $currentAdministrator;

    public function rules()
    {
        return [
            [['niche_ids', 'administrator_id'], 'required'],
            [['niche_ids'], 'validateNicheIds'],
            [['is_retain','power'], 'integer'],
            [['administrator_name'], 'string'],
            [['administrator_id'], 'validateAdministratorId'],
            [['niche_ids'], 'validatePower'],
            [['niche_ids'], 'validatePowerMax'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validatePowerMax()
    {
        $ids = explode(',', $this->niche_ids);
        /** @var Administrator $administrator */
        $administrator = Administrator::find()->where(['id'=>$this->administrator_id])->one();
        /** @var NichePublicDepartment $niche_public */
        $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
        if (!empty($niche_public))
        {
            if ($niche_public->nichePublic->is_own == 1)
            {
                //包含
                $niche_count = Niche::find()->where(['administrator_id' => $administrator->id])->andWhere("niche_public_id = 0 or niche_public_id is null ")->count();
            }
            else
            {
                //不包含
                $niche_count = Niche::find()->where(['administrator_id' => $administrator->id])->andWhere(['or',['is_distribution'=>1],['is_extract'=>1],['is_transfer'=>1],['is_cross'=>1]])->andWhere("niche_public_id = 0 or niche_public_id is null ")->count();
            }

            if (((int)$niche_count+count($ids)) > $niche_public->nichePublic->have_max_sum) {
                return $this->addError('id', '对不起，当前用户拥有商机已达上限');
            }
        }
        return true;
    }

    public function validatePower()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $ids = explode(',', $this->niche_ids);
        $niche_count = \common\models\Niche::find()->where(['in','id',$ids])->andWhere(['administrator_id'=>$administrator->id])->count();

        //当前的商机不全部是自己负责
        if ((int)$niche_count != count($ids)){
            //获取到这组数据里面不属于我负责的商机 查看是否是我下属负责的商机
            $niche_administrator_id = \common\models\Niche::find()->distinct()->select('administrator_id')->where(['in','id',$ids])->andWhere(['<>','administrator_id',$administrator->id])->asArray()->all();
            //获取到下级部门的所有人
            $subordinate = $administrator->getTreeAdministratorId();
            //获取到这组数据在我的下级 负责的商机有多少个
           if ($subordinate)
           {
               if (count($niche_administrator_id) != count(array_intersect($subordinate,array_column($niche_administrator_id,'administrator_id')))){
                   return $this->addError('niche_ids','暂无此权限');
               }
           }
           else
           {
               return $this->addError('niche_ids','暂无此权限');
           }
        }
        return true;
    }

    public function validateNicheIds()
    {
        $ids = explode(',', $this->niche_ids);
        $niche_count = \common\models\Niche::find()->where(['in','id',$ids])->count();
        if ((int)$niche_count != count($ids)){
            return $this->addError('niche_ids','商机ID不存在');
        }
        return true;
    }

    public function validateAdministratorId()
    {
        /** @var Administrator $administrator */
        $administrator = Administrator::find()->where(['id'=>$this->administrator_id])->one();
        if (empty($administrator)){
            return $this->addError('administrator_id','负责人不存在');
        }
        $this->administrator_name = $administrator->name;
        return true;
    }

    public function save()
    {
        $count = 0;
        $ids = explode(',', $this->niche_ids);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            foreach ($ids as $niche_id) {
                // todo 需要确认，是否需要移除作为合作人的关系   跟产品沟通之后不需要清除商机原团队成员
//            \common\models\NicheTeam::deleteAll(['niche_id' => $niche_id, 'administrator_id' => $this->administrator_id]);
                //如果勾选了保留负责人为自己团队成员则把原负责人设为我的团队成员
                /** @var \common\models\Niche $niche_one */
                $niche_one = \common\models\Niche::find()->where(['id' => $niche_id])->one();

                if ($this->is_retain == 1) {
                    if (!empty($niche_one)) {

                        $sort = (int)\common\models\NicheTeam::find()
                            ->select('sort')
                            ->where(['niche_id' => $niche_id])
                            ->orderBy(['sort' => SORT_DESC])
                            ->scalar();

                        $team = new \common\models\NicheTeam();
                        $team->niche_id = $niche_id;
                        $team->administrator_id = $niche_one->administrator_id;
                        $team->administrator_name = $niche_one->administrator_name;
                        $team->is_update = 0;
                        $team->sort = ++$sort;
                        $team->create_at = time();
                        $team->save(false);

                    }
                }
                NicheTeam::deleteAll(['administrator_id' => $this->administrator_id, 'niche_id' => $niche_id]);
                $count += Niche::updateAll(['administrator_id' => $this->administrator_id, 'administrator_name' => $this->administrator_name, 'is_transfer' => 1, 'is_extract' => 0, 'is_distribution' => 0, 'is_give_up' => 0, 'send_administrator_id' => $niche_one->administrator_id, 'send_time' => time()], ['id' => $niche_id]);
                //添加操作记录
                NicheOperationRecord::create($niche_id, '转移商机', '转移商机新负责人为' . $this->administrator_name);

            }
            $transaction ->commit();
            //统计埋点
            foreach ($ids as $niche_id) {
                $data = new CustomerExchangeList();
                $niche_one = Niche::find()->where(['id' => $niche_id])->one();
                /** @var CrmContacts $contract */
                $contract = CrmContacts::find()->where(['customer_id' => $niche_one->customer_id])->one();
                $data->niche(['id' => $niche_one->id, 'from' =>$this->currentAdministrator->id , 'administrator_id' => $this->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount], 'change');
                $model = new NicheFunnel();
                //商机漏斗
                $model->del($niche_id);
                $model->add($niche_id, $niche_one->progress);
            }
        }catch (\Exception $e)
        {
            $transaction -> rollBack();
            $count = false;
        }
        return $count;
    }
}

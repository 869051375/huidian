<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmContacts;
use common\models\Niche;
use common\models\NicheTeam;
use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="ShareNicheForm"))
 */
class ShareNicheForm extends Model
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
     * @var Administrator
     */
    public $currentAdministrator;

    /** @var $administrator_name */
    public $administrator_name;

    public function rules()
    {
        return [
            [['niche_ids', 'administrator_id'], 'required'],
            [['niche_ids'], 'validateNicheIds'],
            [['administrator_id'], 'validateAdministratorId'],
            [['administrator_id'], 'validateTeam'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateTeam()
    {
        $ids = explode(',', $this->niche_ids);
        foreach ($ids as $niche_id)
        {
            $niche_team = NicheTeam::find()->where(['niche_id'=>$niche_id])->andWhere(['administrator_id'=>$this->administrator_id])->one();

            $niche_one = Niche::find()->where(['administrator_id'=>$this->administrator_id])->andWhere(['id'=>$niche_id])->one();
            if (!empty($niche_team) || !empty($niche_one))
            {
                return $this->addError('niche_ids','当前商机下已存在相同协作人，请勿重复添加！');
            }
        }
        return true;
    }

    public function validateNicheIds()
    {
        $ids = explode(',', $this->niche_ids);
        $niche_count = \common\models\Niche::find()->where(['in','id',$ids])->count();
        if ($niche_count != count($ids)){
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
                $sort = (int)\common\models\NicheTeam::find()
                    ->select('sort')
                    ->where(['niche_id' => $niche_id])
                    ->orderBy(['sort' => SORT_DESC])
                    ->scalar();

                $model = new \common\models\NicheTeam();
                $model->niche_id = $niche_id;
                $model->administrator_id = $this->administrator_id;
                $model->administrator_name = $this->administrator_name; // todo 补全信息和其他冗余字段信息
                $model->is_update = 0;
                $model->sort = ++$sort;

                //埋点
                $models = new NicheFunnel();
                /** @var Niche $niche */
                $niche = Niche::find()->where(['id' => $niche_id])->one();
                $models->add($niche_id, $niche->progress,$this->administrator_id);
                $census = new CustomerExchangeList();
                /** @var CrmContacts $contract */
                $contract = CrmContacts::find()->where(['customer_id' => $niche->customer_id])->one();
                $census->niche(['id' => $niche->id, 'from' => '', 'administrator_id' => $this->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche->source_id) ? $niche->source_id : 0, 'channel_id' => isset($niche->channel_id) ? $niche->channel_id : 0, 'amount' => $niche->total_amount]);

                //添加操作记录
                NicheOperationRecord::create($niche_id, '协作商机', '新增商机团队成员为' . $this->administrator_name);

                // todo 注意，数据库表中 create_at 命名错误，不统一，应该为 created_at，需要调整
                $count += $model->save(false) ? 1 : 0;
            }
            $transaction ->commit();
        }catch (\Exception $e)
        {
            $transaction -> rollBack();
            $count = false;
        }
        return $count;
    }
}

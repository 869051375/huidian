<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use Yii;
use yii\base\Model;


/**
 * 商机阶段变更接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheTransform"))
 */
class NicheTransform extends Model
{

    /**
     * 商机阶段(20，40，60，80)
     * @SWG\Property(example = "30")
     * @var integer
     */
    public $progress;


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
            [['niche_id','progress'], 'required'],
            [['progress','niche_id'], 'integer'],
            [['niche_id'], 'validateNicheId'],
            [['progress'], 'validateProgress'],
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

    public function validateProgress()
    {
        if (!in_array($this->progress,[10,30,60,80]))
        {
            return $this->addError('progress','商机阶段更新错误');
        }
        return true;
    }



    public function save()
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id' => $this->niche_id])->one();
            $niche_one->progress = $this->progress;
            $niche_one->stage_update_at = time();
            $niche_one->stage_update_id = $this->currentAdministrator->id;
            $niche_one->stage_update_name = $this->currentAdministrator->name;
            $niche_one->status = Niche::STATUS_NOT_DEAL;
            $niche_one->win_reason = '';
            $niche_one->win_progress = 0;
            $niche_one->win_describe = '';
            $niche_one->lose_reason = '';
            $niche_one->lose_progress = 0;
            $niche_one->lose_describe = '';


            if ($this->progress == Niche::PROGRESS_10) {
                $msg = '目标识别';
            } elseif ($this->progress == Niche::PROGRESS_30) {
                $msg = '需求确定';
            } elseif ($this->progress == Niche::PROGRESS_60) {
                $msg = '谈判审核';
            } elseif ($this->progress == Niche::PROGRESS_80) {
                $msg = '合同确认';
            }

            //埋点
            $models = new NicheFunnel();
            $models->add($this->niche_id, $this->progress);

            //添加操作记录
            NicheOperationRecord::create($this->niche_id, '商机阶段更新', '商机阶段更新为：' . $msg);

            $res = $niche_one->save(false);
            $transaction ->commit();
        } catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;
        }
        return $res;
    }
}

<?php

namespace backend\modules\niche\models;

use common\models\Channel;
use common\models\Niche;
use common\models\NicheTeam;
use common\models\Source;
use Yii;
use yii\base\Model;


/**
 * 商机基本信息编辑
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheUpdateBase"))
 */
class NicheUpdateBase extends Model
{


    /** @var $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $niche_id;


    /**
     * 商机名称
     * @SWG\Property(example = "新的商机名字")
     * @var string
     */
    public $name;

    /**
     * 商机来源ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $source_id;
    public $source_name;

    /**
     * 商机渠道ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $channel_id;
    public $channel_name;
    public $update_id;
    public $update_name;

    /**
     * 商机备注
     * @SWG\Property(example = "这是一个新的备注")
     * @var string
     */
    public $remark;

    /**
     * 预计成交时间   2018-10-23
     * @SWG\Property(example = "2018-10-23")
     * @var integer
     */
    public $predict_deal_time;
    public $updated_at;

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function rules()
    {
        return [
            [['niche_id'], 'required'],
            [['updated_at','update_id','channel_id','source_id','niche_id'], 'integer'],
            [['predict_deal_time','remark','update_name','channel_name','source_name','name'], 'string'],
            [['niche_id'], 'validateNicheId'],
            [['channel_id'], 'validateChannelId'],
            [['source_id'], 'validateSourceId'],
            [['source_id'], 'validatePower'],
        ];
    }

    public function validatePower()
    {
        $this->currentAdministrator->id;
        //是负责人可以修改
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->one();

        //如果是协作成员并且有权限也可以修改
        $nicheTeam_one = NicheTeam::find()->where(['niche_id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->currentAdministrator->id])->andWhere(['is_update'=>1])->one();

        if (empty($niche_one) && empty($nicheTeam_one))
        {
            $this->addError('products', '暂无修改权限!');
        }
        return true;
    }

    public function validateNicheId()
    {
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        if (empty($niche_one))
        {
            return $this->addError('niche_id','商机ID不存在');
        }
        return true;
    }

    public function validateChannelId()
    {
        /** @var Channel $channel */
        $channel = Channel::find()->where(['id'=>$this->channel_id])->andWhere(['status'=>1])->one();
        if (empty($channel)){
            return $this->addError('channel_id','渠道ID不存在');
        }
        $this->channel_name = $channel->name;
    }

    public function validateSourceId()
    {
        /** @var Source $source */
        $source = Source::find()->where(['id'=>$this->source_id])->andWhere(['status'=>1])->one();
        if (empty($source)){
            return $this->addError('source_id','渠道来源ID不存在');
        }
        $this->source_name = $source->name;
    }


    public function save()
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id' => $this->niche_id])->one();
            $niche_one->name = $this->name;
            $niche_one->channel_id = $this->channel_id;
            $niche_one->channel_name = $this->channel_name;
            $niche_one->source_id = $this->source_id;
            $niche_one->source_name = $this->source_name;
            $niche_one->remark = $this->remark;
            $niche_one->predict_deal_time = strtotime($this->predict_deal_time);
            $niche_one->update_id = $this->currentAdministrator->id;
            $niche_one->update_name = $this->currentAdministrator->name;
            $niche_one->updated_at = time();

            //埋点
            $customer_model = new CustomerExchangeList();
            $customer_model->updateNiche($this->niche_id);

            //添加操作记录
            NicheOperationRecord::create($this->niche_id, '编辑商机', '编辑了商机基本信息');
            $res = $niche_one->save(false);
            $transaction ->commit();
        }catch (\Exception $e)
        {
            $transaction -> rollBack();
            $res = false;
        }
        return $res;
    }


}

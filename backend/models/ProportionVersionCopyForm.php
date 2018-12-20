<?php
namespace backend\models;

use common\models\Administrator;
use common\models\RewardProportionRule;
use common\models\RewardProportionVersion;
use Yii;
use yii\base\Model;

class ProportionVersionCopyForm extends Model
{
    public $version_id;

    /**
     * @var  RewardProportionVersion
     */
    public $version;

    public function rules()
    {
        return [
            [['version_id'], 'integer'],
            [['version_id'], 'validateVersionId'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'reward_proportion_id' => ' ',
            'effective_month' => '生效时间',
        ];
    }

    public function validateVersionId()
    {
        $this->version = RewardProportionVersion::findOne($this->version_id);
        if(null == $this->version)
        {
            $this->addError('version_id','版本不存在！');
        }
        elseif (empty($this->version->proportionRule))
        {
            $this->addError('version_id','请先添加提成规则！');
        }
        elseif (empty($this->version->effective_month))
        {
            $this->addError('version_id','请先将此版本生效！');
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function copy()
    {
        /** @var Administrator  $administrator */
        $administrator = Yii::$app->user->identity;
        $rewardProportionVersion = new RewardProportionVersion();
        $rewardProportionVersion->reward_proportion_id = $this->version->reward_proportion_id;
        $this->version->updated_at = time();
        $this->version->updater_id = $administrator->id;
        $this->version->updater_name = $administrator->name;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $rewardProportionVersion->save(false);
            $this->copyRule($rewardProportionVersion);
            $this->version->save(false);
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    /***
     * @param RewardProportionVersion $ProportionVersion
     */
    private function copyRule($ProportionVersion)
    {
        /** @var RewardProportionRule[] $rules */
        $rules = RewardProportionRule::find()->where(['reward_proportion_version_id' => $this->version->id])->all();
        foreach($rules as $rule)
        {
            $ruleModel = new RewardProportionRule();
            $ruleModel->reward_proportion_version_id = $ProportionVersion->id;
            $ruleModel->expected_total_profit = $rule->expected_total_profit;
            $ruleModel->reward_proportion = $rule->reward_proportion;
            $ruleModel->created_at = time();
            $ruleModel->save(false);
        }
    }

}

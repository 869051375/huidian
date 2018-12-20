<?php
namespace backend\models;

use common\models\Administrator;
use common\models\RewardProportionVersion;
use Yii;
use yii\base\Model;

class RewardProportionVersionForm extends Model
{
    public $effective_month;
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
            [['effective_month'], 'string', 'max' => 10],
            [['effective_month'], 'date', 'format' => 'yyyy-MM'],
            [['effective_month'], 'validateMonth'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'version_id' => ' ',
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
    }

    public function validateEffective()
    {
        $this->version = RewardProportionVersion::findOne($this->version_id);
        if(null == $this->version)
        {
            $this->addError('version_id','版本不存在！');
        }
        elseif (empty($this->version->effective_month))
        {
            $this->addError('version_id','请先将此版本生效！');
        }
    }

    public function validateMonth()
    {
        $model = RewardProportionVersion::find()->where(['effective_month' => $this->effective_month,'reward_proportion_id' => $this->version->reward_proportion_id])->one();
        if($this->effective_month < date('Y-m'))
        {
            $this->addError('effective_month','时间不能小于当前时间！');
        }
        else if(!empty($model))
        {
            $this->addError('effective_month','不能存在相同的生效时间！');
        }
    }

    /**
     * @return bool
     */
    public function effective()
    {
        if(!$this->validate())return false;
        /** @var Administrator  $administrator */
        $administrator = Yii::$app->user->identity;
        $this->version->effective_month = $this->effective_month;
        $this->version->updated_at = time();
        $this->version->updater_id = $administrator->id;
        $this->version->updater_name = $administrator->name;
        $reward = $this->version->save(false);
        if($reward)
        {
            return true;
        }
        return false;
    }

}

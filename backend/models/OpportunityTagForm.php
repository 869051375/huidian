<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmOpportunity;
use common\models\OpportunityTag;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class OpportunityTagForm extends Model
{
    public $opportunity_id;
    public $tag_id;
    public $company_id;
    public $ids;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var CrmOpportunity[]
     */
    public $opportunities = [];

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['tag_id'], 'required', 'message' => '请选择标签', 'on' => ['add']],
            [['tag_id', 'opportunity_id', 'company_id'], 'integer'],
            ['ids', 'each', 'rule' => ['integer']],
            ['ids', 'validateOpportunityIds', 'on' => ['add']],
            ['ids', 'validateCancelIds', 'on' => ['cancel']],
        ];
    }

    public function validateOpportunityIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunities = CrmOpportunity::find()->where(['in', 'id', $this->ids])->all();
        if(empty($this->tag_id))
        {
            $this->addError('tag_id', '请选择标签！');
        }
        if(empty($this->opportunities))
        {
            $this->addError('ids', '请选择商机！');
        }
        foreach($this->opportunities as $opportunity)
        {
            if($opportunity->administrator_id != $administrator->id)
            {
                $this->addError('ids', '标签应用失败，您不是商机负责人！');
            }
        }
    }

    public function validateCancelIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunities = CrmOpportunity::find()->where(['in', 'id', $this->ids])->all();
        if(empty($this->opportunities))
        {
            $this->addError('ids', '请选择商机！');
        }
        foreach($this->opportunities as $opportunity)
        {
            if($opportunity->administrator_id != $administrator->id)
            {
                $this->addError('ids', '标签取消失败，您不是商机负责人！');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'tag_id' => '标签',
        ];
    }

    /**
     * 应用标签
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->ids as $opportunity_id)
            {
                $opportunityTag = OpportunityTag::find()->where(['opportunity_id' => $opportunity_id])->one();
                if($opportunityTag)
                {
                    $opportunityTag->tag_id = $this->tag_id;
                    $opportunityTag->save(false);
                }
                else
                {
                    $opportunityTag = new OpportunityTag();
                    $opportunityTag->tag_id = $this->tag_id;
                    $opportunityTag->opportunity_id = $opportunity_id;
                    $opportunityTag->save(false);
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollback();
            throw $e;
        }
    }

    public function cancel()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->ids as $opportunity_id)
            {
                $opportunityTag = OpportunityTag::find()->where(['opportunity_id' => $opportunity_id])->one();
                if($opportunityTag)
                {
                    $opportunityTag->delete();
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollback();
            throw $e;
        }
    }
}
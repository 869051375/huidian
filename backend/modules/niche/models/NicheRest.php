<?php
namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;


/**
 * 商机阶段重置
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheRest"))
 */
class NicheRest extends Model
{

    /**
     * 重置原因
     * @SWG\Property(example = "重置原因")
     * @var integer
     */
    public $reason;

    /**
     * 重置描述
     * @SWG\Property(example = "这是重置的描述")
     * @var integer
     */
    public $describe;


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
            [['niche_id'], 'required','message'=>'请至少选择1条商机！'],
            [['niche_id'], 'integer'],
            [['reason','describe'], 'string'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }


    public function save()
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id' => $this->niche_id])->one();
            $niche_one->progress = Niche::PROGRESS_10;
            $niche_one->stage_update_at = time();
            $niche_one->status = Niche::STATUS_NOT_DEAL;;
            $niche_one->win_reason = '';
            $niche_one->win_progress = 0;
            $niche_one->win_describe = '';
            $niche_one->lose_reason = '';
            $niche_one->lose_progress = 0;
            $niche_one->lose_describe = '';
            //添加操作记录
            NicheOperationRecord::create($this->niche_id, '商机激活', '商机阶段更新为：目标识别，激活原因为：' . $this->reason.'；具体原因为：'.$this->describe);
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

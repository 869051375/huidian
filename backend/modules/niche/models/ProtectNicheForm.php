<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/9
 * Time: 10:10
 */

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NichePublicDepartment;
use common\utils\BC;
use yii\base\Model;
use Yii;


/**
 * @SWG\Definition(required={"type"}, @SWG\Xml(name="ProtectNicheForm"))
 */
class ProtectNicheForm extends Model
{

    /**
     * 商机ID
     * @SWG\Property(example = "1,2,3")
     * @var integer
     */
    public $niche_ids;

    /** @var $currentAdministrator */
    public $currentAdministrator;

    /** @var $is_protect */
    public $is_protect;



    public function rules()
    {
        return [
            [['niche_ids'], 'required','message'=>'请至少选择1条商机！'],
            [['niche_ids'], 'string'],
            [['niche_ids'], 'validateNicheIds'],
            [['niche_ids'], 'validateProtectMax'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateProtectMax()
    {
        if ($this->is_protect == 1){
            $ids = explode(',', $this->niche_ids);

            $count = 0;
            $niche_array = array();
            foreach ($ids as $niche_id)
            {
                /** @var Niche $niche_one */
                $niche_one = Niche::find()->where(['id'=>$niche_id])->one();
                $niche_public = NichePublicDepartment::find()->where(['department_id'=>$niche_one->administrator->department_id])->one();
                if (!empty($niche_public))
                {
                    $count += 1;

                    if (isset($niche_array[$niche_one->administrator->id])){
                        $niche_array[$niche_one->administrator->id] += 1;
                    }
                    else
                    {
                        $niche_array[$niche_one->administrator->id] = 1;
                    }
                }
            }
            if ($count != (int)count($ids))
            {
                if ($this->is_protect == 0)
                {
                    return $this->addError('niche_ids','对不起，当前所选商机没有公海，取消保护失败。');
                }
                else
                {
                    return $this->addError('niche_ids','对不起，当前所选商机没有公海');
                }

            }
            foreach ($niche_array as $k=>$v)
            {
                $niche_count = Niche::find()->where(['administrator_id'=>$k])->andWhere(['is_protect'=>1])->count();
                /** @var Administrator $administrator */
                $administrator = Administrator::find()->where(['id'=>$k])->one();
                /** @var NichePublicDepartment $niche_public */
                $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();

                if ((int)$niche_public->nichePublic->protect_max_sum < (int)BC::add($niche_count,$v,0))
                {
                    return $this->addError('niche_ids','对不起，当前用户的保护商机数量已达上限！');
                }
            }
        }
        return true;
    }


    public function validateNicheIds()
    {
        $ids = explode(',', $this->niche_ids);
        $niche_count = \common\models\Niche::find()->where(['in','id',$ids])->andWhere(['in','progress',[10,30,60,80]])->count();
        if ((int)$niche_count != count($ids)){
            return $this->addError('niche_ids','商机ID不存在');
        }
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
                /** @var \common\models\Niche $niche_one */
                $count += \common\models\Niche::updateAll(['is_protect' => $this->is_protect, 'updated_at' => time(), 'update_id' => $this->currentAdministrator->id, 'update_name' => $this->currentAdministrator->name], ['id' => $niche_id]);
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
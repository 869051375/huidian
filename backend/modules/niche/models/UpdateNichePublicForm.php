<?php

namespace backend\modules\niche\models;

use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;



/**
 *
 * @SWG\Definition(required={"name", "department_id", "distribution_move_time", "personal_move_time", "extract_max_sum", "protect_max_su", "have_max_sum", "is_own"}, @SWG\Xml(name="UpdateNichePublicForm"))
 */
class UpdateNichePublicForm extends Model
{
    /**
     * 公海id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 公海名称
     * @SWG\Property(example = "公海")
     * @var string
     */
    public $name;


    /**
     * 所属部门ID
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $department_id;


    /**
     * 他人分配的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $distribution_move_time;


    /**
     * 个人的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $personal_move_time;

    /**
     * 个人24小时内从公海中提取的商机最大限制数量为
     * @SWG\Property(example = 5)
     * @var integer
     */
    public $extract_max_sum;

    /**
     * 个人可以保护的最大商机数量为
     * @SWG\Property(example = 3)
     * @var integer
     */
    public $protect_max_sum;

    /**
     * 个人拥有最大商机数量为
     * @SWG\Property(example = 8)
     * @var integer
     */
    public $have_max_sum;


    /**
     * 个人拥有的商机数是否包含自己创建的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_own;



    public function rules()
    {
        return [
            [['id','name','department_id','distribution_move_time','personal_move_time','extract_max_sum','protect_max_sum','have_max_sum','is_own'], 'required'],
            [['id','distribution_move_time','personal_move_time','extract_max_sum','protect_max_sum','have_max_sum','is_own'], 'integer'],
            [['name'], 'string', 'max' => 50],
            ['id', 'validateNichePublic'],
        ];
    }

    public function validateNichePublic()
    {
        $model = new NichePublic();
        $niche = $model::find()->where(['id'=>$this->id])->one();
        if(empty($niche)){
            $this->addError('id','此公海不存在');
        }
        return true;
    }


    public function save($administrator)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try{
            $nichePublic = new \common\models\NichePublic();
            $nichepublic = $nichePublic::findOne($this->id);
            $nichepublic->name = $this->name;
            $nichepublic->company_id = $administrator->company_id ? $administrator->company_id : "0";
            $nichepublic->distribution_move_time = $this->distribution_move_time;
            $nichepublic->personal_move_time = $this->personal_move_time;
            $nichepublic->extract_max_sum = $this->extract_max_sum;
            $nichepublic->protect_max_sum = $this->protect_max_sum;
            $nichepublic->have_max_sum = $this->have_max_sum;
            $nichepublic->is_own = $this->is_own;
            $nichepublic->updated_at = time();
            $nichepublic->save(false);
            $nichePublicDepartment = new NichePublicDepartment();
            $nichePublicDepartment::deleteAll(['niche_public_id'=>$this->id]);
            $department_ids = explode(',',$this->department_id);
            $ids = array_unique($department_ids);
            for($i = 0;$i<count($ids);$i++){
                $nichePublicDepartments = new NichePublicDepartment();
                $nichePublicDepartments->niche_public_id = $this->id;
                $nichePublicDepartments->department_id = $ids[$i];
                $nichePublicDepartments->save(false);
            }
            $transaction->commit();
            $res = true;
        }catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;
        }
        return $res;
    }

}
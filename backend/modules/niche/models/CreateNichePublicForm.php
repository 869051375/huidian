<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;

/**
 *
 * @SWG\Definition(required={"name", "department_id", "distribution_move_time", "personal_move_time", "extract_max_sum", "protect_max_su", "have_max_sum", "is_own"}, @SWG\Xml(name="CreateNichePublicForm"))
 */
class CreateNichePublicForm extends Model
{
    /**
     * 公海名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $name;


    /**
     * 所属部门id
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
     * @SWG\Property(example = 15)
     * @var integer
     */
    public $extract_max_sum;

    /**
     * 个人可以保护的最大商机数量为
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $protect_max_sum;

    /**
     * 个人拥有最大商机数量为
     * @SWG\Property(example = 10)
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
            [['name','department_id','distribution_move_time','personal_move_time','extract_max_sum','protect_max_sum','have_max_sum','is_own'], 'required'],
            [['distribution_move_time','personal_move_time','extract_max_sum','protect_max_sum','have_max_sum','is_own'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['department_id'], 'string', 'max' => 30],
        ];
    }


    public function save($administrator)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try{
            $nichePublic = new \common\models\NichePublic();
            $nichePublic->name = $this->name;
            $nichePublic->company_id = $administrator->company_id ? $administrator->company_id : "0";
            $nichePublic->creator_id = $administrator->id;
            $nichePublic->creator_name = $administrator->name;
            $nichePublic->distribution_move_time = $this->distribution_move_time;
            $nichePublic->personal_move_time = $this->personal_move_time;
            $nichePublic->extract_max_sum = $this->extract_max_sum;
            $nichePublic->protect_max_sum = $this->protect_max_sum;
            $nichePublic->have_max_sum = $this->have_max_sum;
            $nichePublic->is_own = $this->is_own;
            $nichePublic->create_at = time();
            $nichePublic->save(false);
            $nichePublic_id =Yii::$app->db->getLastInsertID();
            $department_ids = explode(',',$this->department_id);
            for($i = 0;$i<count($department_ids);$i++){
                $nichePublicDepartment = new \common\models\NichePublicDepartment();
                $nichePublicDepartment->niche_public_id = $nichePublic_id;
                $nichePublicDepartment->department_id = $department_ids[$i];
                $nichePublicDepartment->save(false);
            }
            $transaction ->commit();
            $res = true;
        }catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;
        }
        return $res;
    }



}
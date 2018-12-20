<?php

namespace backend\modules\niche\models;

use common\models\Company;
use common\models\CrmDepartment;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NichePublic"))
 */
class NichePublic extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 公海名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $name;

    /**
     * 所属公司id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $company_id;

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


    /**
     * 状态
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $status;


    /**
     * 创建时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $create_at;


    /**
     * 修改时间
     * @SWG\Property(example = "2018-10-30 10:20:30")
     * @var integer
     */
    public $updated_at;



    public function organize($administrator)
    {
        $array = [];
        $company = $administrator->company;
        if (isset($company))
        {
            $item = [
                'id' => $company->id,
                'label' => $company->name,
            ];
            $data = CrmDepartment::find()->select('id,name as label,parent_id')->where(['company_id'=>$company->id])->asArray()->all();
            $item['children'] = $this->genTree($data,0);
            $array[] = $item;
        }else
        {
            $company = Company::find()->select('id,name')->all();
            foreach ($company as $k=> $v){
                $item = [
                    'id' => $v->id,
                    'label' => $v->name,
                ];
                $data = CrmDepartment::find()->select('id,name as label,parent_id')->where(['company_id'=>$v->id])->asArray()->all();
                $item['children'] = $this->genTree($data,0);
                $array[] = $item;
            }
        }
        return $array;
    }

    function genTree($a,$pid){
        $tree = array();
        $department = NichePublicDepartment::find()->all();
        $department_ids = array_column($department,'department_id');
        foreach($a as $v){
            if(in_array($v['id'],$department_ids)){
                $v['disabled'] = true;
            }else{
                $v['disabled'] = false;
            }
            if($v['parent_id'] == $pid){
                $v['children'] = $this->genTree($a,$v['id']);
                if($v['children'] == null){
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

}
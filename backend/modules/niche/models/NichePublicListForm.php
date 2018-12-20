<?php

namespace backend\modules\niche\models;

use common\models\Company;
use common\models\CrmDepartment;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NichePublicListForm"))
 */
class NichePublicListForm extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 类型
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;


    /**
     * 公海名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $name;

    /**
     * 个人24小时内从商机大公海中提取的商机最大限制数量为
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $big_public_extract_max_sum;

    /**
     * 商机公海中的商机（）工作日不进行提取，将自动回收至商机大公海
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $big_public_not_extract;

    /**
     * 创建人id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $creator_id;


    /**
     * 创建人名称
     * @SWG\Property(example = "大海")
     * @var string
     */
    public $creator_name;

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
     * 个人拥有最大商机数量为
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $have_max_sum;


    /**
     * 状态
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $status;

    /**
     * 关联部门
     * @SWG\Property(example = "部门1，部门2，部门3")
     * @var string
     */
    public $department;

    /**
     * 关联部门
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $department_id;




}
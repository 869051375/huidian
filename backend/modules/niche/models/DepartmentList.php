<?php

namespace backend\modules\niche\models;

use backend\fixtures\Administrator;
use common\models\Company;
use common\models\CrmDepartment;
use Yii;
use yii\base\Model;


/**
 * 所属部门列表
 * @SWG\Definition(required={}, @SWG\Xml(name="DepartmentList"))
 */
class DepartmentList extends Model
{

    /**
     * 所属部门ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 所属公司名称
     * @SWG\Property(example = "公司1")
     * @var string
     */
    public $name;

    public function rules()
    {
        return [
            [['id'],'integer'],
        ];

    }

    public function select()
    {
        return CrmDepartment::find()->select('id,name')->where(['company_id'=>$this->id])->asArray()->all();
    }
}

<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use yii\base\Model;


/**
 * 所属公司列表
 * @SWG\Definition(required={}, @SWG\Xml(name="PersonList"))
 */
class PersonList extends Model
{

    /**
     * 员工ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 员工名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;


    public function getList($id)
    {
        return Administrator::find()->select('id,name')->where(['company_id'=>$id])->andWhere(['status'=>1])->andWhere(['is_belong_company'=>1])->andWhere(['is_dimission'=>0])->all();
    }
}
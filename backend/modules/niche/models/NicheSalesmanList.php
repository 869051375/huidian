<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use Yii;
use yii\base\Model;


/**
 * 商机创建人列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheSalesmanList"))
 */
class NicheSalesmanList extends Model
{

    /**
     * 业务员ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 业务员名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;


    public function select()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $department_id = $administrator->getTreeDepartmentId(true);
        return Administrator::find()->distinct()->select('id,name')->where(['in','department_id',$department_id])->andWhere(['status'=>1])->all();
    }
}

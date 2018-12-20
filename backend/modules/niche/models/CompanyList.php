<?php

namespace backend\modules\niche\models;

use backend\fixtures\Administrator;
use common\models\Company;
use Yii;
use yii\base\Model;


/**
 * 所属公司列表
 * @SWG\Definition(required={}, @SWG\Xml(name="CompanyList"))
 */
class CompanyList extends Model
{

    /**
     * 所属公司ID
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

    public function select($type)
    {
        if($type == 1){
            return Company::find()->select('id,name')->asArray()->all();
        }
        /** @var \common\models\Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        if (isset($administrator->company_id) && $administrator->company_id > 0){
            $company[] = Company::find()->select('id,name')->where(['id'=>$administrator->company_id])->asArray()->one();
            return $company;
        }else{
            return Company::find()->select('id,name')->asArray()->all();
        }
    }
}

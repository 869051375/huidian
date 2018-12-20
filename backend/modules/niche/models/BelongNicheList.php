<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;
use yii\db\Query;


/**
 * 所属客户列表
 * @SWG\Definition(required={}, @SWG\Xml(name="BelongNicheList"))
 */
class BelongNicheList extends Model
{

    /**
     * 客户ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 客户名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    /** @var $currentAdministrator */
    public $currentAdministrator;


    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function select()
    {
        $query = new Query();
        $query->distinct('id,name')->select("a.id,(case when bs.company_name is null then '[无名称]' else bs.company_name end) as name");
        return $query->from(['a'=>CrmCustomer::tableName()])
            ->leftJoin(['b'=>CrmCustomerCombine::tableName()],'a.id = b.customer_id')
            ->leftJoin(['bs'=>BusinessSubject::tableName()],'bs.customer_id = a.id')
            ->where(['a.administrator_id'=>$this->currentAdministrator->id])
            ->orWhere(['b.administrator_id'=>$this->currentAdministrator->id])
            ->all();
    }
}

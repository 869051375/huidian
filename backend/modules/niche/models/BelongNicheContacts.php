<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;
use yii\db\Query;


/**
 * 所属客户联动查询联系人
 * @SWG\Definition(required={}, @SWG\Xml(name="BelongNicheContacts"))
 */
class BelongNicheContacts extends Model
{

    /**
     * 联系人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 联系人名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;

    /**
     * 客户来源ID
     * @SWG\Property(example = "1")
     * @var string
     */
    public $source;

    /**
     * 来源渠道ID
     * @SWG\Property(example = "2")
     * @var string
     */
    public $channel_id;

    /** @var $currentAdministrator */
    public $currentAdministrator;



    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function select($id)
    {
        //获取客户联系人
        return CrmContacts::find()->select('id,name,source,channel_id')->where(['customer_id'=>$id])->one();
    }
}

<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheForm"))
 */
class NicheForm extends Model
{
    /**
     * 商机名称
     * @SWG\Property(example = "测试商机")
     * @var string
     */
    public $name;

    /**
     * 下次跟进时间
     * @SWG\Property(example = "2018-10-23 16:00:00")
     * @var integer
     */
    public $next_follow_time;

    /**
     * 客户ID
     * @SWG\Property(example = 102)
     * @var integer
     */
    public $customer_id;

    /**
     * 商机来源ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $source_id;

    public $source_name = '';

    /**
     * 商机渠道ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $channel_id;

    /**
     * 联系人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $contacts_id;

    public $channel_name = '';

    /**
     * 商机备注
     * @SWG\Property(example = "这是备注")
     * @var string
     */
    public $remark = '';

    /**
     * 预计成交时间
     * @SWG\Property(example = "2018-10-24")
     * @var integer
     */
    public $predict_deal_time;

    public function rules()
    {
        return [
            [['name', 'customer_id', 'predict_deal_time', 'source_id', 'channel_id', 'next_follow_time','contacts_id'], 'required'],
            [['name'], 'string', 'max' => 50,'tooLong'=>'商机名称不能为空，且输入长度不能超过50个文字。'],
            [['remark'], 'string', 'max' => 500],
            [['next_follow_time'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            [['predict_deal_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['customer_id'], 'validateCustomerId'],
            [['source_id'], 'validateSourceId'],
            [['channel_id'], 'validateChannelId'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }


    public function validateCustomerId()
    {

    }

    public function validateSourceId()
    {

    }

    public function validateChannelId()
    {

    }
}

<?php

namespace common\models;

use common\utils\BC;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%receipt}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $virtual_order_id
 * @property integer $pay_record_id
 * @property string $virtual_sn
 * @property string $payment_amount
 * @property integer $pay_method
 * @property string $pay_images
 * @property string $remark
 * @property integer $status
 * @property integer $pay_account
 * @property integer $receipt_company
 * @property integer $financial_code
 * @property string $audit_note
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $receipt_date
 * @property integer $is_separate_money
 * @property integer $created_at
 * @property integer $confirm_user_id
 * @property string $confirm_user_name
 * @property integer $confirm_at
 *
 * @property VirtualOrder $virtualOrder
 * @property Company $company
 */
class Receipt extends ActiveRecord
{
    public $password; // 仅用于表单确认回款
    public $receipt_id;

    const STATUS_NO = 0;//审核中
    const STATUS_YES = 1;//审核通过
    const STATUS_FAILED = 2;//审核失败

    const SEPARATE_MONEY_ACTIVE = 1;  //自动分配回款
    const SEPARATE_MONEY_DISABLED = 0;//不自动分配回款

    public $is_send_sms;

    /**
     * @var VirtualOrder
     */
    private $_vo;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%receipt}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => false,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['virtual_order_id', 'company_id','payment_amount','pay_images', 'receipt_company','receipt_date', 'pay_method','remark'], 'required'],
            [['virtual_order_id'], 'validateVirtualOrder'],
            [['payment_amount'], 'number', 'min' => '0'],
            [['payment_amount'], 'validatePaymentAmount','on' => ['review', 'receipt_create']],
            [['receipt_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['pay_method'], 'in', 'range' => [PayRecord::TYPE_ALI_PAY, PayRecord::TYPE_LINE_MONEY,
                PayRecord::TYPE_PRIVATE_TRANSFER, PayRecord::TYPE_PUBLIC_TRANSFER, PayRecord::TYPE_WX_PAY,
                PayRecord::TYPE_PRIVATE_ALIYUN,PayRecord::TYPE_PRIVATE_TENCENT,PayRecord::TYPE_OTHER_COLLECTION
            ]],
            [['remark'], 'string', 'max' => 500],
            [['audit_note'], 'string', 'max' => 100],
            [['pay_account'], 'string', 'max' => 50],
            [['financial_code'], 'string', 'max' => 6],
            [['pay_account'], 'match', 'pattern'=>'/^\w+$/i', 'message'=>'付款账户格式不正确！'],
            [['pay_images'], 'string'],
            [['pay_images'], 'validatePayImages'],
            // [['receipt_date'], 'validateReceiptDate'],
            [['status'], 'in', 'range' => [Receipt::STATUS_NO, Receipt::STATUS_YES, Receipt::STATUS_FAILED]],
            [['receipt_id','company_id','is_send_sms','invoice'], 'integer'],
            [['is_separate_money'], 'boolean'],

            [['virtual_order_id'], 'validateReceipt', 'on' => 'receipt_create'],
        ];
    }

    public function validateReceiptDate()
    {
        if(strtotime($this->receipt_date) < strtotime(date('Y-m-d', $this->_vo->created_at)))
        {
            $this->addError('receipt_date', '日期不得早于提交订单的日期');
        }
    }

    public function validateVirtualOrder()
    {
        $this->_vo = VirtualOrder::findOne($this->virtual_order_id);
        if(null == $this->_vo)
        {
            $this->addError('virtual_order_id', '找不到订单信息');
        }
        if(!$this->_vo->isPendingPayment() && !$this->_vo->isUnpaid())
        {
            $this->addError('virtual_order_id', '该订单非待付款状态，不能进行该操作。');
        }
        $this->virtual_sn = $this->_vo->sn;
        $adjustOrder = AdjustOrderPrice::find()->where(['virtual_order_id' => $this->virtual_order_id,'status' => AdjustOrderPrice::STATUS_PENDING])->limit(1)->one();
        if($adjustOrder)
        {
            $this->addError('virtual_order_id', '订单价格审核中,暂时不能新建回款。');
        }
    }

    public function validatePaymentAmount()
    {
        if($this->_vo)
        {
            /** @var VirtualOrder $vo */
            $vo = $this->_vo;
            $maxPendingPayAmount = $vo->getPendingPayAmount();
            /** @var Receipt[] $receipts */
            $query = Receipt::find()->where(['virtual_order_id' => $vo->id, 'status' => Receipt::STATUS_NO]);
            if($this->receipt_id)
            {
                $query->andWhere(['!=', 'id', $this->receipt_id]);
            }
            $receipts = $query->all();
            if(!empty($receipts))
            {
                $totalAmount = 0;
                /** @var Receipt $receipt */
                foreach ($receipts as $receipt)
                {
                    $totalAmount = BC::add($totalAmount, $receipt->payment_amount);
                }
                $paymentAmount = BC::sub($maxPendingPayAmount, $totalAmount);
                if($paymentAmount < $this->payment_amount)
                {
                    $this->addError('payment_amount', '已提交过新建回款还未确认，目前回款金额最多为'.$paymentAmount.'元');
                }
            }
            else
            {
                if($maxPendingPayAmount < $this->payment_amount)
                {
                    $this->addError('payment_amount', '回款金额不可超过'.$maxPendingPayAmount.'元');
                }
            }
        }
    }

    public function validatePayImages()
    {
        $this->pay_images = trim($this->pay_images, ';');
    }

    public function validateReceipt()
    {
        if(!empty($this->_vo) && !empty($this->_vo->receipt))
        {
            foreach ($this->_vo->receipt as $receipt)
            {
                if($receipt->status == Receipt::STATUS_NO)
                {
                    $this->addError('virtual_order_id', '该订单有一笔正在审核中的回款，需等待公司财务审核后才可继续回款。');
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_order_id' => '虚拟订单id',
            'virtual_sn' => '虚拟订单号17位',
            'payment_amount' => '回款金额',
            'pay_method' => '回款方式',
            'pay_images' => '回款图片',
            'remark' => '回款备注',
            'status' => '是否回款确认，0否、1是',
            'creator_id' => '创建人id',
            'creator_name' => '创建人姓名',
            'receipt_date' => '收款日期',
            'is_separate_money' => '自动分配回款',
            'created_at' => '创建时间戳',
            'confirm_user_id' => '确认人id',
            'confirm_user_name' => '确认人姓名',
            'confirm_at' => '确认时间戳',
            'password' => '审核密码',
            'is_send_sms' => '给财务发送回款审批手机短信',
            'pay_account' => '打款账户',
            'receipt_company' => '收款公司',
            'financial_code' => '财务明细编号',
            'audit_note' => '审核备注',
            'invoice' => '是否开票',
        ];
    }
    
    public function getVirtualOrder()
    {
        return self::hasOne(VirtualOrder::className(), ['id' => 'virtual_order_id']);
    }

    public function getPayMethodName()
    {
        $names = PayRecord::getPayMethod();
        return isset($names[$this->pay_method]) ? $names[$this->pay_method] : '未知';
    }

    public function getCompany()
    {
        return self::hasOne(Company::className(), ['id' => 'company_id']);
    }

        public function getContract()
    {
        return self::hasOne(Contract::className(), ['virtual_order_id' => 'virtual_order_id']);
    }

    public function getFiles()
    {
        $images = explode(';', $this->pay_images);
        return $images;
    }

    public function getImage()
    {
        if($this->pay_images)
        {
            if(strpos(';',$this->pay_images))
            {
                $pay_images = explode(';', $this->pay_images);
                return $pay_images;
            }
            else
            {
                return [$this->pay_images];
            }
        }
        return null;
    }

    public function getImageUrl($key)
    {
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($key, ['width' => null, 'height' => null, 'mode' => 1]);
    }
}

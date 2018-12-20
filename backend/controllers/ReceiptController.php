<?php

namespace backend\controllers;

use backend\models\ConfirmPayForm;
use common\actions\UploadImageAction;
use common\jobs\SendSmsJob;
use common\models\Administrator;
use common\models\ContractRecord;
use common\models\MessageRemind;
use common\models\Order;
use common\models\OrderRecord;
use common\models\Receipt;
use common\models\UploadImageForm;
use common\models\VirtualOrder;
use common\utils\BC;
use imxiangli\image\storage\ImageStorageInterface;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\log\Logger;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ReceiptController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['create', 'validation', 'delete-image', 'review', 'order-info', 'info', 'payment-amount'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'validation', 'delete-image', 'upload', 'info', 'payment-amount'],
                        'allow' => true,
                        'roles' => ['receipt/create'],
                    ],
                    [
                        'actions' => ['create', 'validation', 'delete-image', 'upload', 'info', 'payment-amount'],
                        'allow' => true,
                        'roles' => ['virtual-order-action/receipt'],
                    ],
                    [
                        'actions' => ['list', 'review', 'validation', 'delete-image', 'upload', 'order-info', 'info'],
                        'allow' => true,
                        'roles' => ['receipt/review'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadImageAction::className(),
                'modelClass' => UploadImageForm::className(),
                'keyTemplate' => 'receipt/{date:Ymd}-{time}.{ext}',
                'mode' => '1',
                'thumbnailWidth' => 80,
                'thumbnailHeight' => 80,
            ],
        ];
    }

    public function actionList()
    {
        $query = Receipt::find()->alias('r');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->andWhere(['r.status' => '0']);
       //关联合同
        $query->joinWith(['contract c']);
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        if($administrator->isCompany())
        {
            $query->andWhere(['r.company_id' => $administrator->company_id]);
        }
        $query->andWhere(['not in', 'vo.status', [VirtualOrder::STATUS_BREAK_PAYMENT]]);
        $query->orderBy(['r.created_at' => SORT_DESC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    public function actionOrderInfo($id)
    {
        /**@var $model Order **/
        $model = $this->findOrderModel($id);
        $data['product_name'] = $model->product_name;
        $data['area'] = $model->getArea();
        $data['company_name'] = $model->company_name;
        $data['salesman_name'] = empty($model->salesman_name) ? '待分配': $model->salesman_name;
        $data['status'] = $model->getStatus();
        return ['status' => 200,'order' => $data];
    }

    private function findOrderModel($id)
    {
        $model = Order::find()->where(['id'=>$id])->one();
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的订单!');
        }
        return $model;
    }

    public function actionDeleteImage()
    {
        $key = Yii::$app->request->post('key');
        if(false === stripos($key, 'receipt/'))
        {
            return ['status' => 400, 'message' => '您要删除的图片不存在'];
        }
//        /** @var ImageStorageInterface $imageStorage */
        // 先不做真实删除，因为删除后不保存，会显示一个裂开的图片
       $imageStorage = \Yii::$app->get('imageStorage');
       $imageStorage->delete($key); 
       
        return ['status' => 200];
    }

    public function actionCreate()
    {
        $model = new Receipt();
        $model->setScenario('receipt_create');
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
            $model->receipt_date = strtotime($model->receipt_date);
            $model->status = Receipt::STATUS_NO;
            $model->save(false);

            $pn = $model->getPayMethodName();
            foreach($model->virtualOrder->orders as $order)
            {
                if ($model->invoice == 1){
                    $invoice = '是';
                } else{
                    $invoice = '否';
                }

                $images = null;
                if($model->getImage())
                {
                    $images .= '回款图片：';
                    foreach ($model->getImage() as $item)
                    {
                        $images .= "<a href=".$model->getImageUrl($item).">{$item}</a>";
                    }
                }


                $receipt_date = Yii::$app->formatter->asDate($model->receipt_date);
                OrderRecord::create($order->id, '订单提交回款', "回款金额：{$model->payment_amount}元；收款日期：{$receipt_date}；收款公司：{$model->receipt_company}；回款方式：{$pn}，打款账户：{$model->pay_account}；是否开票：{$invoice}；回款备注：{$model->remark}；{$images}", $administrator, 0, 1);
            }

            if($model->virtualOrder->opportunities)
            {
                foreach($model->virtualOrder->opportunities as $opportunity)
                {
                    $images = null;
                    if($model->getImage())
                    {
                        $images .= '回款图片：';
                        foreach ($model->getImage() as $item)
                        {
                            $images .= "<a href=".$model->getImageUrl($item).">{$item}</a>";
                        }
                    }
                    if($opportunity->contract)
                    {
                        contractRecord::CreateRecord($opportunity->contract->id,"{$administrator->name}新建了回款审核，明细为：回款金额：{$model->payment_amount}元；回款日期：{$receipt_date}；回款方式：{$model->getPayMethodName()}，打款账户为：{$model->pay_account}; 收款公司：{$model->receipt_company};回款备注：{$model->remark};{$images}", $administrator);
                    }
                }
            }

            //后台消息提醒
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $financial_email = '';
            $financial_id = '';
            if($model->company && $model->company->administratorByFinancial)
            {
                //1.判断是否有公司。2.判断公司下是否设置了财务人员并且是财务人员启用和开启了公司部门功能
                $financial_email = $model->company->administratorByFinancial->email;
                $financial_id = $model->company->financial_id;
            }
            if($financial_email && $financial_id)
            {
                $message = '回款审核提醒-虚拟订单号：'.$model->virtual_sn ;
                $popup_message = '您有一条新订单需新建回款审核，请查看！';
                $type = MessageRemind::TYPE_EMAILS;
                $type_url = MessageRemind::TYPE_URL_RECEIPT;
                $receive_id = $financial_id;
                $email = $financial_email;
                $sign = 'l-'.$receive_id.'-'.$model->virtual_order_id.'-'.$model->id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, 0, 0, $administrator, $email);
                }
            }
            if($financial_id && $financial = $model->company->administratorByFinancial)
            {
                $phone = $financial->phone;
                //新建回款审核
                try {
                    // 给财务发送回款审批手机短信（加入短信队列）
                    /** @var Queue $queue */
                    $queue = \Yii::$app->get('queue', false);
                    if($queue && $phone)
                    {
                        // 业务员：{1}，订单号：{2}
                        $queue->pushOn(new SendSmsJob(),[
                            'phone' => $phone,
                            'sms_id' => '258442',//订单新建回款审核 模板id：258442
                            'data' => [$administrator->name, $model->virtual_sn]
                        ], 'sms');
                    }
                }catch (\Exception $e){
                    Yii::getLogger()->log($e, Logger::LEVEL_INFO);
                }
            }
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionInfo($id)
    {
        $result=[];
        //业务主体
        $result['BusinessSubject']= Receipt::find()->asArray()->select('b.company_name,c.serial_number,b.region')->from('receipt e')->leftJoin('contract c','c.virtual_order_id=e.virtual_order_id')->leftJoin('crm_opportunity o','o.id=c.opportunity_id')->leftJoin('business_subject b','b.id=o.business_subject_id')->where(['=','e.id',$id])->all();
        //下单方式
        $res=Receipt::find()->asArray()->select('o.is_proxy,o.creator_name')->from('receipt e')->leftJoin('order o','o.virtual_order_id=e.virtual_order_id')->where(['=','e.id',$id])->one();
        if($res['is_proxy']==1){
            $result['way']= $res['creator_name']."后台新增";
        }else{
            $result['way']= '客户自主下单';
        }
        //虚拟订单号
        $virtualResuly=Receipt::find()->asArray()->select('v.sn,e.creator_name,e.created_at,e.invoice,v.total_amount,e.payment_amount,v.payment_amount as vPaymentAmount,e.remark')->from('receipt e')->leftJoin('virtual_order v','v.id=e.virtual_order_id')->where(['=','e.id',$id])->one();
        $result['virtualNumber']=$virtualResuly['sn'];
        $result['applyPeople']=$virtualResuly['creator_name'];
        $result['createdAt']=date('Y-m-d H:i:s',$virtualResuly['created_at']);
        $result['invoice']=$virtualResuly['invoice'];
        $result['remark']=$virtualResuly['remark'];
        $result['total_amount']=$virtualResuly['total_amount'];                                                  //應付
        $result['vPaymentAmount']=$virtualResuly['vPaymentAmount'];                                                 //已付
        $result['remaining_amount']=$virtualResuly['total_amount']-$virtualResuly['vPaymentAmount'];                                                  //待付   應付-已付
        $result['payment_amount']=$virtualResuly['payment_amount'];                                                 //回款金額
        $model = $this->findModel($id);
        $model->receipt_date = date('Y-m-d', $model->receipt_date);
        $pay_images = explode(';', $model->pay_images);
        $images = [];
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        foreach($pay_images as $image)
        {
            $images[] = [
                'key' => $image,
                'url' => $imageStorage->getImageUrl($image),
                'thumbnailUrl' => $imageStorage->getImageUrl($image, ['width' => 80, 'height' => 80, 'mode' => 1]),
            ];
        }
        $model->pay_images = $images;
        $modelData = $this->receiptDate($model->virtual_order_id);
        return ['status' => 200, 'model' => $model->attributes, 'models' => $modelData, 'result'=>$result];
    }

    public function actionPaymentAmount($virtual_order_id)
    {
        $result=[];
        $receipts = Receipt::find()->where(['virtual_order_id' => $virtual_order_id, 'status' => Receipt::STATUS_NO])->all();

        //业务主体
        $result['BusinessSubject']= Receipt::find()->asArray()->select('b.company_name,c.serial_number,b.region')->from('contract c')->leftJoin('crm_opportunity o','o.id=c.opportunity_id')->leftJoin('business_subject b','b.id=o.business_subject_id')->where(['=','c.virtual_order_id',$virtual_order_id])->all();
        //下单方式
        $res=Receipt::find()->asArray()->select('is_proxy,creator_name')->from('order o')->where(['=','o.virtual_order_id',$virtual_order_id])->one();
        if($res['is_proxy']==1){
            $result['way']= $res['creator_name']."后台新增";
        }else{
            $result['way']= '客户自主下单';
        }
        //虚拟订单号
        $virtualResuly=Receipt::find()->asArray()->select('sn')->from('virtual_order v')->where(['=','v.id',$virtual_order_id])->one();
        $result['virtualNumber']=$virtualResuly['sn'];
        $virtualOrder = $this->findVirtualOrderModel($virtual_order_id);
        $maxPendingPayAmount = $virtualOrder->getPendingPayAmount();
        $totalAmount = 0;
        if(!empty($receipts))
        {
            /** @var Receipt $receipt */
            foreach ($receipts as $receipt)
            {
                $totalAmount = BC::add($totalAmount, $receipt->payment_amount);
                $result['auditPeople']=$receipt->confirm_user_name;
                $result['applyPeople']=$receipt->creator_name;
            }
            $paymentAmount = BC::sub($maxPendingPayAmount, $totalAmount);
        }
        else
        {
            $paymentAmount = $maxPendingPayAmount;
        }
        $modelData = $this->receiptDate($virtual_order_id);
        return [
            'status' => 200,
            'payment_amount' => $this->serializeData($paymentAmount),
            'new_payment_amount' => $this->serializeData($totalAmount),
            'result' => $this->serializeData($result),
            'models' => $modelData
        ];
    }

    /**
     * @param $virtual_order_id
     * @return array
     */
    private function receiptDate($virtual_order_id)
    {
        $allReceipts = Receipt::find()->where(['virtual_order_id' => $virtual_order_id])->orderBy(['created_at' => SORT_ASC])->all();
        $modelData = [];
        if(!empty($allReceipts))
        {
            /** @var Receipt $receiptRecord */
            foreach($allReceipts as $receiptRecord)
            {
                $pay_images = explode(';', $receiptRecord->pay_images);
                $images = '';
                /** @var ImageStorageInterface $imageStorage */
                $imageStorage = \Yii::$app->get('imageStorage');
                foreach($pay_images as $image)
                {
                    if(!empty($image))
                    {
                        $images .= "<div class='thumbnail pull-left'><img src=".$imageStorage->getImageUrl($image, ['width' => 80, 'height' => 80, 'mode' => 1])." big-src=".$imageStorage->getImageUrl($image)."></div>";
                    }
                }
                $receiptRecord->pay_images = $images;
                $modelData[] = $this->serialize($receiptRecord);
            }
        }
        return $modelData;
    }
    /**
     * @param Receipt $model
     * @return array
     */
    private function serialize($model)
    {
        return [
            'id' => $model->id,
            'payment_amount' => $model->payment_amount.'元',
            'pay_images' => $model->pay_images,
            'created_at' => Yii::$app->formatter->asDatetime($model->created_at, 'yyyy-MM-dd HH:mm'),
            'creator_name' => $model->creator_name,
        ];
    }

    private function findModel($id)
    {
        /** @var Receipt $model */
        $model = Receipt::find()->where(['id'=>$id])->one();
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的回款信息!');
        }
        return $model;
    }

    private function findVirtualOrderModel($id)
    {
        /** @var VirtualOrder $model */
        $model = VirtualOrder::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的订单信息!');
        }
        return $model;
    }

    public function actionReview()
    {
        $receipt_id = Yii::$app->request->post('receipt_id');
        $password = Yii::$app->request->post('password');
        $model = $this->findModel($receipt_id);
        $model->receipt_id = $receipt_id;
        $receipt_date = Yii::$app->request->post('Receipt')['receipt_date'];
        $remark = Yii::$app->request->post('Receipt')['audit_note'];

        $model->load(Yii::$app->request->post());

        $form = new ConfirmPayForm();
        $form->receipt_id = $receipt_id;
        $form->virtual_order_id = $model->virtual_order_id;
        $form->pay_method = $model->pay_method; //付款方式
        $form->confirm_payment_amount = $model->payment_amount;//确认付款金额
        $form->password = $password;
        $form->is_separate_money = $model->is_separate_money;//自动分配回款
        $form->audit_note = $remark;

        if($model->status == 1)
        {
            $form->setScenario('review');
            if(!$form->save())
            {
                $errors = $form->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        elseif ($model->status == 2)
        {
            if(!$form->receiptAuditFailedSave())
            {
                $errors = $form->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $model->confirm_user_id = $administrator->id;
        $model->confirm_user_name = $administrator->name;
        $model->confirm_at = time();
        $model->receipt_date = strtotime($receipt_date);
        $model->save(false);
        return ['status' => 200];
    }

    public function actionValidation()
    {
        $model = new Receipt();
        $model->receipt_id = Yii::$app->request->post('receipt_id');
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}

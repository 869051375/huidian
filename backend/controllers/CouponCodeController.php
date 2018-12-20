<?php
namespace backend\controllers;


use backend\models\CouponCodeForm;
use common\models\Coupon;
use common\models\CouponCode;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CouponCodeController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'status',
                    'validation',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['info', 'code-list'],
                        'allow' => true,
                        'roles' => ['coupon/info'],
                    ],
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['coupon/create'],
                    ],
                    [
                        'actions' => ['update-info'],
                        'allow' => true,
                        'roles' => ['coupon/update'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['coupon-code/export'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new CouponCodeForm();
        $model->setScenario('insert');
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $coupon = $model->save();
            if($coupon)
            {
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['update-info', 'id' => $coupon->id]);
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
    }

    public function actionUpdateInfo($id)
    {
        $coupon = $this->findModel($id);
        $model = new CouponCodeForm();
        $model->setScenario('update');
        $model->setAttributes($coupon->attributes);
        $model->coupon_id = $coupon->id;
        if($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $model->update($coupon);
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['/coupon-list/code-list', 'id' => $coupon->id]);
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->render('/coupon/update', [
            'coupon' => $coupon,
            'model' => $model,
        ]);
    }

    public function actionValidation()
    {
        $data = Yii::$app->request->post('CouponCodeForm');
        $model = new CouponCodeForm();
        if(!empty($data['id']))
        {
            $model = $this->findModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        return $this->render('/coupon/info', [
            'model' => $model,
        ]);
    }

    //随机码列表
    public function actionCodeList($id)
    {
        $model = $this->findModel($id);
        $query = CouponCode::find()->where(['coupon_id' => $model->id]);
        $query->select(['random_code', 'coupon_id', 'user_id', 'status']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('code-list', [
            'provider' => $provider,
        ]);
    }

    public function actionExport($id)
    {
        $url = Yii::$app->request->getReferrer();
        $export_code = Yii::$app->cache->get('export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $model = $this->findModel($id);
        $query = CouponCode::find()->where(['coupon_id' => $model->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $totalCount = $dataProvider->totalCount;
        $count = $dataProvider->count;
        if(empty($count))
        {
            Yii::$app->session->setFlash('error', '没有获取到任何随机码记录！');
            return $this->redirect($url);
        }
        $batch = ceil($totalCount / $count);
        $csv = Writer::createFromString('');
        $header = ['优惠码','使用状态','客户姓名'];
        $csv->insertOne($header);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var CouponCode[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $row)
            {
                $csv->insertOne([
                    "\t".$row->random_code,
                    "\t".$row->getStatusName(),
                    empty($row->user)?'': "\t".$row->user->name
                ]);
            }
        }
        $filename = date('YmdHis').rand(0,9999).rand(0,9999).'_随机码记录.csv';
        Yii::$app->cache->set('export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }

    /**
     * @param int $id
     * @return Coupon
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Coupon::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的优惠券!');
        }
        return $model;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
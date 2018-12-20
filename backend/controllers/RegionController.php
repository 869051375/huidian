<?php

namespace backend\controllers;

use common\models\City;
use common\models\District;
use common\models\Province;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: imxiangli
 * Date: 2017/2/8
 * Time: 下午3:00
 */
class RegionController extends BaseController
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
                'only' => ['delete', 'sort', 'update', 'detail', 'validation', 'ajax-provinces', 'ajax-cities', 'ajax-districts'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','export'],
                        'allow' => true,
                        'roles' => ['region/list'],
                    ],
                    [
                        'actions' => ['create','validation'],
                        'allow' => true,
                        'roles' => ['region/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['region/delete'],
                    ],
                    [
                        'actions' => ['update', 'sort', 'detail','validation'],
                        'allow' => true,
                        'roles' => ['region/update'],
                    ],
                    [
                        'actions' => ['ajax-provinces', 'ajax-cities', 'ajax-districts'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($pid = 0, $cid = 0)
    {
        /** @var Province[] $models */
        $models = Province::find()->orderBy(['sort' => SORT_ASC])->all();
        return $this->render('list', ['provinces' => $models, 'pid' => $pid, 'cid' => $cid]);
    }

    // 创建一个省份／城市／地区
    public function actionCreate($data)
    {
        $model = $this->getNewModel($data);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
            return $this->redirectToList($model);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirectToList($model);
    }

    public function actionValidation($data)
    {
        $model = $this->getNewModel($data);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    /**
     * @param $type
     * @return ActiveRecord
     * @throws NotAcceptableHttpException
     */
    private function getNewModel($type)
    {
        $model = null;
        if ($type == 'province') {
            $model = new Province();
        } else if ($type == 'city') {
            $model = new City();
        } else if ($type == 'district') {
            $model = new District();
        }
        if (null == $model) {
            throw new NotAcceptableHttpException('您的操作有误!');
        }
        return $model;
    }

    public function actionDetail($data, $id)
    {
        $model = $this->findModel($data, $id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
    }

    // 更新分类
    public function actionUpdate($data, $id)
    {
        $model = $this->findModel($data, $id);

        if ($data == 'province'){
            $city = City::find()->where(['province_id'=>$id])->all();
            foreach ($city as $k=>$v){
                $v->province_name = Yii::$app->request->post()['Province']['name'];
                $v->save(false);
            }
            $district = District::find()->where([$data.'_id'=>$id])->all();
            foreach ($district as $k=>$v){
                $v->province_name  = Yii::$app->request->post()['Province']['name'];
                $v->save(false);
            }
        }
        if ($data == 'city'){
            $district = District::find()->where([$data.'_id'=>$id])->all();
            foreach ($district as $k=>$v){
                $v->city_name  = Yii::$app->request->post()['City']['name'];
                $v->save(false);
            }
        }
        

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        }
        else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirectToList($model);
    }

    // 分类排序
    public function actionSort($data)
    {
        // post: source_id, target_id
        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findModel($data, $source_id);
        $target = $this->findModel($data, $target_id);

        // 交换两个分类的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    // 删除分类
    public function actionDelete($data)
    {
        // post: id
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($data, $id);
        $model->delete();
        return ['status' => 200];
    }

    private function redirectToList($model)
    {
        if ($model instanceof Province) {
            return $this->redirect(['list', 'pid' => $model->id]);
        } else if ($model instanceof City) {
            return $this->redirect(['list', 'pid' => $model->province_id, 'cid' => $model->id]);
        } else if ($model instanceof District) {
            return $this->redirect(['list', 'pid' => $model->province_id, 'cid' => $model->city_id]);
        }
        return $this->redirect(['list']);
    }

    public function actionAjaxProvinces($keyword = null)
    {
        $query = Province::find()->select(['id', 'name']);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $query->orderBy(['sort' => SORT_ASC]);
        return ['status' => 200, 'provinces' => $this->serializeData($query->all())];
    }

    public function actionAjaxCities($province_id, $keyword = null)
    {
        $query = City::find()->select(['id', 'name'])->where(['province_id' => $province_id]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $query->orderBy(['sort' => SORT_ASC]);
        return ['status' => 200, 'cities' => $this->serializeData($query->all())];
    }

    public function actionAjaxDistricts($city_id, $keyword = null)
    {
        $query = District::find()->select(['id', 'name'])->where(['city_id' => $city_id]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $query->orderBy(['sort' => SORT_ASC]);
        return ['status' => 200, 'districts' => $this->serializeData($query->all())];
    }

    // 加载一个分类，当找不到时抛出异常
    private function findModel($type, $id)
    {
        $model = null;
        if ($type == 'province') {
            $model = Province::findOne($id);
        } else if ($type == 'city') {
            $model = City::findOne($id);
        } else if ($type == 'district') {
            $model = District::findOne($id);
        }
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的分类!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    public function actionExport()
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
        $query = District::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $count = $dataProvider->totalCount;
        if(empty($count))
        {
            Yii::$app->session->setFlash('error', '没有获取到地区数据！');
            return $this->redirect($url);
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['省份','省份ID','城市','城市ID','区县','区县ID'];
        $csv->insertOne($header);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var District[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as  $district)
            {
                $csv->insertOne([
                    "\t" . $district->province_name,
                    "\t" . $district->province_id,
                    "\t" . $district->city_name,
                    "\t" . $district->city_id,
                    "\t" . $district->name,
                    "\t" . $district->id,
                ]);
            }
        }
        $filename = date('YmdHis').'_服务地区.csv';

        Yii::$app->cache->set('export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }
}
<?php

namespace backend\controllers;

use common\actions\UploadImageAction;
use common\models\UploadImageForm;
use Yii;
use common\models\Banner;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * BannerController implements the CRUD actions for Banner model.
 */

/**
 * Created by PhpStorm.
 * User: xinqiangWang
 * Date: 2017/2/20
 * Time: 14:28
 */
class BannerController extends BaseController
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'sort', 'validation', 'detail'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['banner/list'],
                    ],
                    [
                        'actions' => ['create', 'upload', 'validation'],
                        'allow' => true,
                        'roles' => ['banner/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['banner/delete'],
                    ],
                    [
                        'actions' => ['update', 'upload', 'validation', 'detail', 'sort'],
                        'allow' => true,
                        'roles' => ['banner/update'],
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
                'keyTemplate' => 'banner/{date:Ymd}-{time}.{ext}',
                'thumbnailWidth' => 200,
                'thumbnailHeight' => 100,
            ],
        ];
    }

    public function actionList($target = '1')
    {
        $query = Banner::find();
        if($target == '1')
        {
            $query->where(['target' => 1]);
        }
        else if($target == '2')
        {
            $query->where(['target' => 2]);
        }
        //$query->select(['id', 'title', 'image', 'sort', 'url']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['sort' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
            'target' => $target,
        ]);
    }

    // 新增焦点图
    public function actionCreate()
    {
        $model = new Banner();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
            return $this->redirect(['list', 'target' => $model->target]);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['list', 'target' => $model->target]);
    }

    public function actionValidation()
    {
        $model = new Banner();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $bannerData = $this->serializeData($model);
        $bannerData['imageUrl'] = $model->getImageUrl();
        return ['status' => 200, 'model' => $bannerData];
    }

    // 更新焦点图
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list', 'target' => $model->target]);
    }

    // 焦点图排序
    public function actionSort()
    {
        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findModel($source_id);
        $target = $this->findModel($target_id);

        // 交换两个焦点图的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    // 删除焦点图
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    // 加载一个焦点图，当找不到时抛出异常
    private function findModel($id)
    {
        $model = Banner::findOne($id);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的焦点图!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
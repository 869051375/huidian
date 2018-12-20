<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/3
 * Time: 上午9:14
 */

namespace backend\controllers;

use backend\models\DeleteProductImageForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ProductImageController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'delete',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['product-introduce/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionDelete()
    {
        $model = new DeleteProductImageForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->delete())
            {
                return ['status' => 200];
            }
        }
        if($model->hasErrors())
        {
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return ['status' => 400, 'message' => '您的请求有误。'];
    }
}
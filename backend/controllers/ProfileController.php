<?php

namespace backend\controllers;

use backend\models\PasswordForm;
use common\models\Administrator;
use Yii;
use yii\web\NotFoundHttpException;

class ProfileController extends BaseController
{
    public function actionInfo()
    {
        $model = $this->findModel();
        if($model->load(Yii::$app->request->post()))
        {
            $model->updatePersonnel();
            $model->save(false);
        }
        return $this->render('info',['model'=>$model]);
    }

    public function actionPassword()
    {
        $model = new PasswordForm();
        if($model->load(Yii::$app->request->post())&&$model->validate())
        {
            $model->update();
            $model = new PasswordForm();
            Yii::$app->session->setFlash('success', '修改成功!');
            return $this->render('password',['model'=>$model]);
        }
        return $this->render('password',['model'=>$model]);
    }

    private function findModel()
    {
        $model = Administrator::findOne(Yii::$app->user->id);
        if(null==$model)
        {
            throw new NotFoundHttpException('找不到指定管理员！');
        }
        return $model;
    }
}

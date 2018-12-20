<?php
namespace backend\controllers;
use Yii;


class SwaggerController extends BaseController
{
    public function actions()
    {
        return [
            //The document preview addesss:http://api.yourhost.com/site/doc
            'doc' => [
                'class' => 'light\swagger\SwaggerAction',
                'restUrl' => \yii\helpers\Url::to(['/swagger/api'], true),
            ],
            //The resultUrl action.
            'api' => [
                'class' => 'light\swagger\SwaggerApiAction',
                //The scan directories, you should use real path there.
                'scanDir' => [
                    Yii::getAlias('@backend/swagger'),
                    Yii::getAlias('@backend/controllers'),
                    Yii::getAlias('@backend/modules/niche/controllers'),
                    Yii::getAlias('@backend/modules/niche/models')
                ],
                //The security key
                //'api_key' => '123456',
            ],
        ];
    }
}
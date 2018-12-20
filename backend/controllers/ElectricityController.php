<?php
namespace backend\controllers;

use common\models\CrmDataSynchronization;
use Yii;
use yii\filters\AccessControl;
use backend\models\CrmSynchronizationSeaForm;



class ElectricityController extends ApiController
{
  
    private static $modelObj;//model实例

    public function init()
    {
      self::$modelObj = new CrmSynchronizationSeaForm;
    }

    //过滤器
    public function behaviors()
    {
      return [
      	'access' => [
      			//允许访问的方法
                'class' => AccessControl::className(),
                'rules' => [
                  	[
                  	  'actions' => ['data-synchronous'],//方法
                        'allow' => true,//允许访问
                        /**
                        *@ 代表登陆有这个权限
                        *electricity/date-synchronous 必须在角色中添加权限
                        */ 
                        'roles' => ['@'],
                  	],
                  	[
                  	  'actions' => ['create'],//方法
                        'allow' => true,//允许访问
                        'roles' => ['@'],
                  	],
                ],
      	   ],
      ];   
    }  	

    	//显示主页
    public function actionDataSynchronous()
    {
       return $this->render('index',['model'=>self::$modelObj]);
    }

    //入库
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        /** @var CrmDataSynchronization $model */
        $model = new CrmDataSynchronization();
        $model->load($post['CrmSynchronizationSeaForm'],'');
        $model->add();
        return $this->render('index',['model'=>self::$modelObj]);
    }






}

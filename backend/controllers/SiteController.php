<?php
namespace backend\controllers;

use backend\actions\CKEditorAction;
use backend\models\LoginForm;
use common\actions\UploadImageAction;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\UploadImageForm;
use Yii;
use yii\filters\AccessControl;
use yii\redis\Connection;
use yii\web\ErrorAction;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'upload', 'test', 'ly', 'upload-desc-image'],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function($rule, $action)
                        {
                            return $this->goHome();
                        }
                    ],
                ],
            ],
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'logout' => ['post'],
//                ],
//            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'upload' => [
                'class' => UploadImageAction::className(),
                'modelClass' => UploadImageForm::className(),
                'thumbnailWidth' => 500,
                'thumbnailHeight' => 300,
            ],
            'upload-desc-image' => [
                'class' => CKEditorAction::class,
                'path' => 'images',
            ]
        ];
    }

    /**
     * 文件上传测试页面
     *
     * @return string
     */
    public function actionTest()
    {
        return $this->render('index');
    }

    /**
     * 后台首页
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['crm/index']);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'login';
        if (!Yii::$app->user->isGuest)
        {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login())
        {
            //登录成功获取session
            $session = Yii::$app->session;
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            //登录成功写进redis
            $redis = Yii::$app->redis;
            $redis->set('user-login-id-'.$administrator->id, $session->id);
            //新增后台操作日志
            if(!Yii::$app->session->get('rootGoBack'))
            {
                AdministratorLog::logLoginSuccess($model->username);
            }
            $administrator = Administrator::findByUsername($model->username);
            if(Yii::$app->getUser()->getReturnUrl(null) == Yii::$app->getHomeUrl()
                && $administrator->type == Administrator::TYPE_SALESMAN)
            {
                return $this->redirect(['crm/index']);
            }
            return $this->goBack();
        }
        else
        {
            //新增后台操作日志
            if($model->username)
            {
                $username = Administrator::find()
                    ->where('username = :username', [':username' => $model->username])
                    ->limit(1)->one();
                if($username)
                {
                    AdministratorLog::logLoginFail($model->username);
                }
            }
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLy()
    {
       return $this->render('ly');
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if(Yii::$app->user->logout())
        {
            //新增后台退出操作日志
            AdministratorLog::logLogout($admin);
        }
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $backendNavItemsKey = 'backend-nav-'.$admin->id;
        if($redis->get('system'.$admin->id))
        {
            $redis->del('system'.$admin->id);
        }
        Yii::$app->cache->delete($backendNavItemsKey);
        Yii::$app->session->remove('rootGoBack');
        return $this->goHome();
    }

}

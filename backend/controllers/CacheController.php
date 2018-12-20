<?php
namespace backend\controllers;


use Yii;
use yii\filters\AccessControl;
use yii\redis\Cache;

class CacheController extends BaseController
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
                        'actions' => ['index', 'flush-cache', 'clear-assets','flush-crm-cache'],
                        'allow' => true,
                        'roles' => ['cache/flush'],
                    ]
                ],
            ],
        ];
    }
    

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionFlushCache()
    {
        Yii::$app->cache->flush();
        Yii::$app->getSession()->setFlash('success', '缓存已被刷新');
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionFlushCrmCache()
    {
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $countRedisCache->flush();
        Yii::$app->getSession()->setFlash('success', 'CRM数据缓存刷新');
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionClearAssets()
    {
        $this->deleteAssets(Yii::getAlias('@frontend/web/assets'));
        $this->deleteAssets(Yii::getAlias('@mobile/web/assets'));
        Yii::$app->getSession()->setFlash('success', '样式缓存资源已被清理');
        return $this->redirect(Yii::$app->request->referrer);
    }

    private function deleteAssets($path)
    {
        foreach(glob($path . DIRECTORY_SEPARATOR . '*') as $asset){
            if(is_link($asset)){
                unlink($asset);
            } elseif(is_dir($asset)){
                $this->deleteDir($asset);
            } else {
                unlink($asset);
            }
        }
    }

    private function deleteDir($directory)
    {
        $iterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        return rmdir($directory);
    }
}
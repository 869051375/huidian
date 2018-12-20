<?php
namespace common\actions;

use yii\base\Action;
use Yii;
use yii\log\Logger;
use yii\redis\Connection;

class RedirectStatisticsAction extends Action
{
	public function run($key, $uri)
	{
        try
        {
            $cache = Yii::$app->cache;
            $sid = Yii::$app->session->getId();
            $uKey = $sid.'-redirect-statistics-'.$key;
            if(!$cache->get($uKey))
            {
                $uv_key = static::getUvKey($key);
                $cache->set($uKey, true, 86400);
                $this->increase($uv_key);
            }
            $pv_key = static::getPvKey($key);
            $this->increase($pv_key);
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log('redirect-statistics'.$key.'-统计出现错误', Logger::LEVEL_ERROR);
        }

        return $this->controller->redirect($uri);
	}

    private function increase($key, $num = 1)
    {
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        if($redis->get($key))
        {
            $redis->incrby($key, $num);
        }
        else
        {
            $redis->set($key, $num);
        }
    }

    public static function getPvKey($key, $date = null)
    {
        $pv_key = ($date ? $date : date('Y-m-d')).'-redirect-statistics-pv-'.$key;
        return $pv_key;
    }

    public static function getUvKey($key, $date = null)
    {
        $pv_key = ($date ? $date : date('Y-m-d')).'-redirect-statistics-uv-'.$key;
        return $pv_key;
    }

    public static function getPv($key, $date = null)
    {
        $pv_key = static::getPvKey($key, $date);
        try
        {
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            return intval($redis->get($pv_key));
        }
        catch (\Exception $e)
        {
        }
        return 0;
    }

    public static function getUv($key, $date = null)
    {
        $uv_key = static::getUvKey($key, $date);
        try
        {
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            return intval($redis->get($uv_key));
        }
        catch (\Exception $e)
        {
        }
        return 0;
    }

    public static function clearPv($key, $date)
    {
        $pv = 0;
        try
        {
            $pv = static::getPv($key, $date);
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $redis->del(static::getPvKey($key, $date));
        }
        catch (\Exception $e)
        {
        }
        return $pv;
    }

    public static function clearUv($key, $date)
    {
        $uv = 0;
        try
        {
            $uv = static::getUv($key, $date);
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $redis->del(static::getUvKey($key, $date));
        }
        catch (\Exception $e)
        {
        }
        return $uv;
    }
}
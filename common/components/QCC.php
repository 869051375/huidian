<?php
namespace common\components;

use common\models\CheckName;
use common\models\TrademarkInfoSearch;
use common\models\TrademarkSearch;
use Yii;
use yii\base\Component;
use yii\httpclient\Client;

class QCC extends Component
{
    public $key;
    public $type = 'json';

    /**
     * 企业关键字模糊查询
     * @param string $keyword
     * @return mixed|null|string
     */
    public function apiECISimpleSearch($keyword = null)
    {
        $hashKeyword = md5($keyword);
        $cacheData = Yii::$app->cache->get($hashKeyword);
        if(!empty($cacheData))
        {
            return $cacheData;
        }
        $client = new Client();
        $response = $client->get('http://i.yjapi.com/ECISimple/Search',
            ['key' => $this->key, 'dtype' => $this->type, 'keyword' => $keyword])->send();
        if($response->getIsOk())
        {
            $jsonString = $response->getContent();
            $searchData = new CheckName();
            /** @var CheckName[] $searchData */
            $searchData->loadData($jsonString);
            Yii::$app->cache->set($hashKeyword, $searchData, 24*60*60);
            return $searchData;
        }
        else
        {
            return null;
        }
    }

    /**
     * ECISimpleGetDetails
     * 根据ID获取照面信息
     * @param string $id
     */
    public function apiECISimpleGetDetails($id = null)
    {

    }

    /**
     * @param null $id
     * @return TrademarkSearch|mixed|null
     * 根据ID获取照面信息
     */
    public function apiECISimpleGetDetailsByName($id = null)
    {
        if(null == $id)
        {
            return null;
        }
        $hashKeyword = md5($id);
        $cacheData = \Yii::$app->cache->get($hashKeyword);
        if(!empty($cacheData))
        {
            return $cacheData;
        }

        $client = new Client();
        $response = $client->get('http://i.yjapi.com/ECISimple/GetDetails',
            ['keyno' => $id,'key' => $this->key, 'dtype' => $this->type])->send();
        if($response->getIsOk())
        {
            $jsonString = $response->getContent();
            \Yii::$app->cache->set($hashKeyword,$jsonString,24*60*60);
            return $jsonString;
        }
        else
        {
            return null;
        }
    }

    /**
     * 商标关键字模糊查询
     * @param string $keyword
     * @param int $page
     * @return mixed|null|string
     */
    public function apiTrademarkSearch($keyword = null ,$page = 1)
    {
        if(null == $keyword)
        {
            return null;
        }
        $hashKeyword = md5($keyword.'category_id-0page-'.$page);
        $cacheData = \Yii::$app->cache->get($hashKeyword);
        if(!empty($cacheData))
        {
            return $cacheData;
        }

        $client = new Client();
        $response = $client->get('http://i.yjapi.com/tm/Search',
            ['key' => $this->key, 'dtype' => $this->type, 'keyword' => $keyword , 'pageIndex' => $page])->send();
        if($response->getIsOk())
        {
            $jsonString = $response->getContent();
            $trademarkSearch = new TrademarkSearch();
            $trademarkSearch->loadData($jsonString);
            \Yii::$app->cache->set($hashKeyword,$trademarkSearch,24*60*60);
            return $trademarkSearch;
        }
        else
        {
            return null;
        }
    }

    /**
     * 商标分类id + 关键词
     * @param string $keyword
     * @param int $category_id
     * @param int $page
     * @return TrademarkSearch|mixed|null
     */
    public function apiIntClsSearch($keyword = null , $category_id = 0 ,$page = 1)
    {
        if(null == $keyword || null == $category_id)
        {
            return null;
        }
        $hashKeyword = md5($keyword.'category_id-'.$category_id.'page-'.$page);
        $cacheData = \Yii::$app->cache->get($hashKeyword);
        if(!empty($cacheData))
        {
            return $cacheData;
        }

        $client = new Client();
        $response = $client->get('http://i.yjapi.com/tm/Search',
            ['key' => $this->key, 'dtype' => $this->type, 'keyword' => $keyword , 'intCls'=>$category_id, 'pageIndex' => $page])->send();
        if($response->getIsOk())
        {
            $jsonString = $response->getContent();
            $trademarkSearch = new TrademarkSearch();
            $trademarkSearch->loadData($jsonString);
            \Yii::$app->cache->set($hashKeyword,$trademarkSearch,24*60*60);
            return $trademarkSearch;
        }
        else
        {
            return null;
        }
    }

    /**
     * 商标详细信息
     * @param integer $id
     * @return mixed|null|string
     */
    public function apiTrademarkInfoSearch($id = 0)
    {
        if(null == $id)
        {
            return null;
        }
        $hashId = md5($id);
        $cacheData = \Yii::$app->cache->get($hashId);
        if(!empty($cacheData))
        {
            return $cacheData;
        }

        $client = new Client();
        $response = $client->get('http://i.yjapi.com/tm/GetDetails',
            ['key' => $this->key, 'dtype' => $this->type, 'id' => $id])->send();
        if($response->getIsOk())
        {
            $jsonString = $response->getContent();
            $trademarkSearch = new TrademarkInfoSearch();
            $trademarkSearch->loadData($jsonString);
            \Yii::$app->cache->set($hashId,$trademarkSearch,24*60*60);
            return $trademarkSearch;
        }
        else
        {
            return null;
        }
    }

}

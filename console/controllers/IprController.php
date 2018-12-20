<?php

namespace console\controllers;

use common\models\TrademarkCategory;
use common\models\TrademarkCategoryGroup;
use common\models\TrademarkCategoryGroupItem;
use Yii;
use yii\console\Controller;
use yii\httpclient\Client;

class IprController extends Controller
{
    public function actionIndex($start)
    {
        $url = "http://ipr.zbj.com/sort/:id/";
        $i = $start;
        for(; $i < 46; $i++)
        {
            $tc = TrademarkCategory::findOne($i);
            $page = $this->doGet(str_replace(':id', $i, $url));
            if($page)
            {
                $m = []; // full_name
                preg_match("/\<h1\>(.*?)\<\/h1\>/is", $page, $m);
                //echo ($m[1]);
                if($m[1])
                {
                    $tc->full_name = trim($m[1]);
                }

                $m = []; // intro
                preg_match("/id\=\"advanced-search\"\>查询能否注册该类别\<\/div\>\s*\<\/div\>\s*\<p\>(.*?)\<\/p\>/is", $page, $m);
                //echo ($m[1]);
                if(isset($m[1]))
                {
                    $tc->intro = trim($m[1]);
                }

                $m = []; // annotate
                preg_match("/\<div\ class\=\"category\-des\"\>(.*?)\<\/div\>/is", $page, $m);
                //echo ($m[1]);
                if(isset($m[1]))
                {
                    $tc->annotate = trim(str_replace('<p class="category-notes">【注释】</p>', '', $m[1]));
                }

                $m = []; // remark
                preg_match("/\<div\ class=\"group-remarks\"\>\s*\<span\>备注\<\/span\>\s*\<p\>(.*?)\<\/p\>/is", $page, $m);
                //echo ($m[1]);
                if(isset($m[1]))
                {
                    $tc->remark = trim($m[1]);
                }

                $tc->save(false);

                // 获取链接li区域
                $m = []; // annotate
                // <div class="group-con">
                preg_match("/\<div\ class=\"group-con\"\>\s*\<ul\>(.*?)\<\/ul\>/is", $page, $m);
                //echo ($m[1]);

                if(isset($m[1]))
                {
                    $links = $m[1];
                    $m = [];
                    preg_match_all("/\<a\ href\=\"(.*?)\">(.*?)\<span\>(.*?)\<\/span\>\<\/a\>/is", $links, $m);
                    //var_export($m);
                    // $m[1][] // urls
                    // $m[2][] // code // 【0101】 -
                    // $m[3][] // name
                    //intro
                    $urls = $m;
                    foreach($urls[1] as $k => $groupUrl)
                    {
                        $is_new = false;
                        $code = str_replace(['【', '】 -'], '', $urls[2][$k]);
                        $group = TrademarkCategoryGroup::find()->where(['code' => $code])->one();
                        if(null == $group)
                        {
                            $group = new TrademarkCategoryGroup();
                            $is_new = true;
                        }
                        $group->name = $urls[3][$k];
                        $group->code = str_replace(['【', '】 -'], '', $urls[2][$k]);
                        $group->category_id = $i;
                        $group->save(false);
                        $groupPage = $this->doGet($groupUrl);
                        // id 二级分类id，序号，群组内容
                        $m = [];
                        preg_match("/\<div\ class=\"group\-des\"\>\s*\<div\ class=\"group\-title\"\>(.*?)\<\/div\>(.*?)\<\/div\>/is", $groupPage, $m);
                        if(isset($m[2]))
                        {
                            $items = $m[2];
                            $m = [];
                            preg_match_all("/\<strong\>(.*?)\<\/strong\>\s*\<p\>(.*?)\<\/p\>/is", $items, $m);
                            foreach($m[1] as $j => $code)
                            {
                                $item = null;
                                if(!$is_new)
                                {
                                    $item = TrademarkCategoryGroupItem::find()->where(['group_id' => $group->id, 'code' => trim($code)])->one();
                                }
                                if(null == $item)
                                {
                                    $item = new TrademarkCategoryGroupItem();
                                }
                                $item->code = trim($code);
                                $item->items = trim($m[2][$j]);
                                $item->group_id = $group->id;
                                $item->save(false);
                                echo '111';
                            }
                        }
                        echo '222';
                        sleep(rand(1, 2));
                    }
                }
            }
            echo '333';
            sleep(rand(3, 5));
        }
    }

    private function doGet($url)
    {
        echo 'get:'.$url;
        $client = new Client();
        $request = $client->get($url);
        $request->setOptions(['userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36']);
        $response = $request->send();
        if($response->getIsOk())
        {
            echo 'get:OK';
            return $response->getContent();
        }
        echo 'get:NotOK';
        return '';
    }

    public function console($text)
    {
        echo $text;
    }
}

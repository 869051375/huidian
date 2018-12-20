<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/8/24
 * Time: 上午11:43
 */

namespace console\record;


use common\actions\RedirectStatisticsAction;
use common\models\Banner;
use yii\db\Expression;

class BannerClickRecord
{
    public function record($date)
    {
        /** @var Banner[] $all */
        $all = Banner::find()->all();
        foreach($all as $banner)
        {
            $pv = RedirectStatisticsAction::clearPv($banner->id, $date);
            $uv = RedirectStatisticsAction::clearUv($banner->id, $date);

            Banner::updateAll(['pv' => new Expression('pv+:pv')], 'id=:id', [':pv' => $pv, ':id' => $banner->id]);
            Banner::updateAll(['uv' => new Expression('uv+:uv')], 'id=:id', [':uv' => $uv, ':id' => $banner->id]);
        }
    }
}
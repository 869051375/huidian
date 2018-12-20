<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use yii\base\Model;


/**
 * 商机列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheOperationRecord"))
 */
class NicheOperationRecord extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $id;

    /**
     * 商机表id
     * @SWG\Property(example = 5)
     * @var integer
     */
    public $niche_id;

    /**
     * 操作记录内容
     * @SWG\Property(example = "修改商机排序")
     * @var string
     */
    public $content;


    /**
     * 操作项
     * @SWG\Property(example = "修改商机排序")
     * @var string
     */
    public $item;

    /**
     * 操作人id
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $creator_id;

    /**
     * 操作人名字
     * @SWG\Property(example = "爽妹子")
     * @var string
     */
    public $creator_name;


    /**
     * 操作时间
     * @SWG\Property(example = "2018-10-23 15:58:25")
     * @var integer
     */
    public $created_at;


    //创建商机操作记录
    public static function create($niche_id,$item,$content)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new \common\models\NicheOperationRecord();
        $model->niche_id = $niche_id;
        $model->content = $content;
        $model->item = $item;
        $model->creator_id = $administrator->id;
        $model->creator_name = $administrator->name;
        $model->created_at = time();
        return $model->save(false);
    }
}
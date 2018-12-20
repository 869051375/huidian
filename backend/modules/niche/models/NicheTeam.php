<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheTeam"))
 */
class NicheTeam extends Model
{

    /**
     * 自增id
     * @SWG\Property()
     * @var integer
     */
    public $id;

    /**
     * 商机表ID
     * @SWG\Property()
     * @var integer
     */
    public $niche_id;

    /**
     * 负责人ID
     * @SWG\Property()
     * @var integer
     */
    public $administrator_id;

    /**
     * 负责人名称
     * @SWG\Property()
     * @var string
     */
    public $administrator_name;

    /**
     * 是否有修改权限
     * @SWG\Property()
     * @var integer
     */
    public $is_update;

    /**
     * 排序
     * @SWG\Property()
     * @var integer
     */
    public $sort;

    /**
     * 创建时间
     * @SWG\Property()
     * @var integer
     */
    public $create_at;

    /**
     * 最后修改时间
     * @SWG\Property()
     * @var integer
     */
    public $updated_at;

}

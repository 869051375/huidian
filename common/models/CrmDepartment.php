<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "{{%crm_department}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $level
 * @property string $title
 * @property string $is_show_nav
 * @property string $image
 * @property string $icon_image
 * @property string $banner_image
 * @property string $banner_url
 * @property string $keywords
 * @property string $description
 * @property string $customer_service_link
 * @property integer $status
 * @property integer $reward_proportion_id
 * @property integer $leader_id
 * @property integer $assign_administrator_id
 * @property integer $company_id
 * @property string $path
 * @property integer $created_at
 * @property integer $updated_at
 *
 *
 * @property CrmDepartment[] $children
 * @property Administrator $leader
 * @property Administrator $assignAdministrator
 * @property Administrator[] $departmentManagers
 * @property CrmDepartment $parent
 * @property RewardProportion $rewardProportion
 * @property Company $company
 * @property CrmOpportunity[] $crmOpportunities
 * @property OpportunityPublic $opportunityPublic
 * @property CluePublicId $clue_public_id
 * @property CluePublic $cluePublic
 * @property CrmDepartment $getTreeDepartmentId
 */
class CrmDepartment extends ActiveRecord
{
    public $department;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const LEVEL_ONE = 1;
    const LEVEL_TWO = 2;
    const LEVEL_THREE = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_department}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @return Query
     */
    public static function findEnabled()
    {
        return parent::find()->andWhere(['status' => CrmDepartment::STATUS_ACTIVE]);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'level', 'leader_id', 'created_at', 'updated_at', 'assign_administrator_id', 'company_id', 'reward_proportion_id','clue_public_id'], 'integer'],
            [['path'], 'string', 'max' => 32],

            [['name'], 'filter', 'filter' => 'trim'],
            [['name','reward_proportion_id'], 'required'],
            [['name'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'leader_id' => '负责人',
            'clue_public_id' => '关联公海ID',
            'assign_administrator_id' => '商机默认分配到',
            'company_id' => 'Company Id',
            'reward_proportion_id' => '关联提成方案',
            'name' => '部门名称',
            'code' => '部门编号',
            'path' => 'Path',
            'id' => 'ID',
            'parent_id' => '',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getChildren()
    {
        return static::hasMany(static::className(), ['parent_id' => 'id'])->andWhere(['status' => CrmDepartment::STATUS_ACTIVE])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getParent()
    {
        return static::hasOne(static::className(), ['id' => 'parent_id']);
    }

    public function getLeader()
    {
        return static::hasOne(Administrator::className(), ['id' => 'leader_id']);
    }


    public function getCluePublic()
    {
        return self::hasOne(CluePublic::className(), ['id' => 'clue_public_id']);
    }

    public function getAssignAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'assign_administrator_id']);
    }

    public function getRewardProportion()
    {
        return static::hasOne(RewardProportion::className(), ['id' => 'reward_proportion_id']);
    }

    public function getDepartmentManagers()
    {
        return self::hasMany(Administrator::className(), ['department_id' => 'id'])->andWhere(['is_department_manager' => '1']);
    }

    public function getCompany()
    {
        return static::hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getCrmOpportunities()
    {
        return $this->hasMany(CrmOpportunity::className(), ['department_id' => 'id']);
    }

    public function getOpportunityPublic()
    {
        return OpportunityPublic::find()->where(['department_id' => $this->id])->orWhere(['department_id' => $this->parent_id])->one();
    }

    public function getDepartment()
    {
        $this->department = CrmDepartment::find()->where(['in','id' => $this->id])->createCommand()->sql;
    }

    //根据组织树获取department_id   include 为 true 包括自己的部门 false 不包括自己的部门 默认不包括
    public function getTreeDepartmentId($include = false){
        if ($this->id == 0){
            return false;
        }
        $data = CrmDepartment::find()->asArray()->all();
        $department_id = $this->getTree($data,$this->id);
        $department = $this->recur('id',$department_id);
        if ($include){
            array_push($department,$this->id);
        }
        return $department;
    }

    //获取到组织树里面的ID   不包括自己部门
    private function recur($key, $array){
        $data = [];
        array_walk_recursive($array, function ($v, $k) use ($key, &$data) {
            if ($k == $key) {
                array_push($data, $v);
            }
        });
        return $data;
    }

    function getTree($array, $pid =0, $level = 0){

        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parent_id'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                $this->getTree($array, $value['id'], $level+1);

            }
        }
        return $list;
    }



    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if(empty($this->leader_id))
            {
                $this->leader_id = 0;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function beforeDelete()
    {
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert)
        {
            if($this->parent_id > 0)
            {
                $level = CrmDepartment::find()->where('id=:id', [':id' => $this->parent_id])
                    ->select('level')->limit(1)->scalar();
                $this->level = $level + 1;

                $department = CrmDepartment::findOne($this->parent_id);
                if(null != $department)
                {
                    $this->path = $department->path . '-' . $this->id;
                }
                else
                {
                    $this->path = $this->id;
                }
            }
            else
            {
                $this->path = $this->id;
            }
            $this->save(false);
        }
    }

    public function canDisable()
    {
        // 如果有下级部门，则不允许删除
        if($this->getChildren()->count() > 0)
        {
            return false;
        }
        if(0 < Administrator::find()->where(['department_id' => $this->id])->count())
        {
            return false;
        }
        return true;
    }

    /**
     * @return CrmDepartment
     */
    public function getTopDepartment()
    {
        $arr = explode('-', $this->path);
        if(isset($arr[0]))
        {
            if($arr[0] == $this->path)
            {
                return $this;
            }
            return static::findOne($arr[0]);
        }
        return $this;
    }


//    /**
//     * @param int $limit
//     * @return CrmDepartment[]
//     */
//    public static function getList($limit = 4)
//    {
//        return self::find()
//            ->where(['parent_id'=>0])
//            ->orderBy(['created_at' =>SORT_DESC])
//            ->limit($limit)
//            ->all();
//    }
//
//    /**
//     * @param int $limit
//     * @return CrmDepartment[]
//     */
//    public static function getNavList($limit = 0)
//    {
//        $result = static::getDb()->cache(function ($db) use ($limit) {
//            $query = self::find()->where(['is_show_nav' => '1', 'parent_id' => '0'])->orderBy(['created_at' =>SORT_DESC]);
//            if($limit > 0) $query->limit($limit);
//            return $query->all();
//        });
//        return $result;
//    }
//
//    public static function getTopCategory($top_category_id)
//    {
//        $result = static::getDb()->cache(function ($db) use ($top_category_id) {
//            return self::find()->where(['id'=>$top_category_id])->one();
//        });
//        return $result;
//    }
//
//    /**
//     * @param $top_category_id
//     * @param int $limit
//     * @return CrmDepartment[]
//     */
//    public static function getCategoryList($top_category_id,$limit=3)
//    {
//        $result = static::getDb()->cache(function ($db) use ($top_category_id, $limit) {
//            return self::find()
//                ->where(['parent_id'=>$top_category_id])
//                ->orderBy(['created_at' =>SORT_DESC])
//                ->limit($limit)
//                ->all();
//        });
//        return $result;
//
//    }
}

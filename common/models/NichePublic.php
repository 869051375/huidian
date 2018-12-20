<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_public".
 *
 * @property int $id 自增id
 * @property int $type 类型
 * @property string $name 商机公海名称
 * @property int $company_id 所属公司
 * @property int $creator_id 创建人ID
 * @property string $creator_name 创建人名称
 * @property int $distribution_move_time 他人分配的商机（）工作日不添加跟进记录，将自动回收至商机公海。
 * @property int $big_public_extract_max_sum 个人24小时内从商机大公海中提取的商机最大限制数量为。
 * @property int $big_public_not_extract 商机公海中的商机（）工作日不进行提取，将自动回收至商机大公海。
 * @property int $personal_move_time 个人的商机（）工作日不添加跟进记录，将自动回收至商机公海。
 * @property int $extract_max_sum 个人24小时内从公海中提取的商机最大限制数量为
 * @property int $protect_max_sum 个人可以保护的最大商机数量为
 * @property int $have_max_sum 个人拥有的商机数量最大为
 * @property int $is_own 个人拥有的商机数是否包含自己创建的商机
 * @property int $status 1启动，0禁用，默认为1
 * @property int $create_at 创建时间
 * @property int $updated_at 最后修改时间
 */
class NichePublic extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_public';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'type', 'big_public_extract_max_sum', 'big_public_not_extract', 'distribution_move_time', 'personal_move_time', 'extract_max_sum', 'protect_max_sum', 'have_max_sum', 'is_own', 'status', 'create_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'company_id' => 'Company ID',
            'big_public_extract_max_sum' => 'Big Public Extract Max Sum',
            'big_public_not_extract'=>'Big Public Protect Time',
            'distribution_move_time' => 'Distribution Move Time',
            'personal_move_time' => 'Personal Move Time',
            'extract_max_sum' => 'Extract Max Sum',
            'protect_max_sum' => 'Protect Max Sum',
            'have_max_sum' => 'Have Max Sum',
            'is_own' => 'Is Own',
            'status' => 'Status',
            'create_at' => 'Create At',
            'updated_at' => 'Updated At',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        if(!empty($this->create_at)){
            $fields['create_at'] = function() {
                return Yii::$app->formatter->asDatetime($this->create_at);
            };
        }
        if(!empty($this->updated_at)){
            $fields['updated_at'] = function() {
                return Yii::$app->formatter->asDatetime($this->updated_at);
            };
        }
        $fields['department'] = function() {
            if($this->type == 1){
                return "全部公司";
            }else{
                $department_id = NichePublicDepartment::find()->where(['niche_public_id'=>$this->id])->asArray()->all();
                $department_ids = array_column($department_id,'department_id');
                $departments = CrmDepartment::find()->select('name')->where(['in','id',$department_ids])->asArray()->all();
                $department_name = array_column($departments,'name');
                return implode(',',$department_name);
            }
        };
        $fields['department_id'] = function() {
            if($this->type == 1){
                return "";
            }else {
                $department_id = NichePublicDepartment::find()->where(['niche_public_id' => $this->id])->asArray()->all();
                return array_column($department_id, 'department_id');
            }
        };
        $fields['creator_name'] = function() {
            if($this->type == 1){
                return "【管理员】";
            }else{
                /** @var Administrator $admin */
                $admin = Administrator::find()->where(['id'=>$this->creator_id])->one();
                $department = '';
                $department_name = '';
                if(isset($admin->department_id) && $admin->department_id == 0){
                    $department = "【管理员】";
                }elseif(isset($admin->department_id)){
                    $department_id = Administrator::getParentDepartmentId($admin->department_id);
                    for ($i=0;$i<count($department_id);$i++){
                        $departments = CrmDepartment::find()->where(['id'=>$department_id[$i]])->asArray()->one();
                        $department_name .= $departments['name'].'/';
                    }
                    $department = "【".substr($department_name,0,strlen($department_name)-1)."】";
                    if(isset($admin->title) && $admin->title!= ''){
                        $department = $department."【".$admin->title."】";
                    }else{
                        $department.="【-】";
                    }
                }
                return $this->creator_name.$department;
            }
        };
        return $fields;
    }
}

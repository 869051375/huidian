<?php
namespace backend\models;

use common\models\CrmClue;
use yii\base\Model;

class ClueForm extends Model
{
    public $submit_type;
    public $name;
    public $gander;
    public $source_id;
    public $channel_id;
    public $company_name;
    public $mobile;
    public $wechat;
    public $qq;
    public $call;
    public $tel;
    public $email;
    public $birthday;
    public $department;
    public $position;
    public $native_place;
    public $province_id;
    public $province_name;
    public $city_id;
    public $city_name;
    public $district_id;
    public $district_name;
    public $address;
    public $interest;
    public $remark;

    public $status;
    public $is_new;
    public $follow_status;
    public $administrator_id;
    public $administrator_name;
    public $company_id;
    public $department_id;
    public $creator_id;
    public $creator_name;
    public $label_id;
    public $invalid_time;
    public $business_subject_id;
    public $last_record;
    public $last_record_creator_id;
    public $next_follow_time;
    public $clue_public_id;
    public $extract_time;
    public $move_public_time;
    public $updater_id;
    public $created_at;
    public $updated_at;
    public $last_record_creator_name;
    public $updater_name;
    public $is_repeat;
    public $user_id;
    public $is_abandon;
    public $transfer_at;
    public $recovery_at;


    /**
     * @var
     */
    public $orders;

    public function rules()
    {
        return [
            [[ 'source_id','is_repeat','recovery_at','transfer_at','gander', 'channel_id', 'province_id', 'city_id', 'district_id', 'label_id', 'status', 'is_new', 'follow_status', 'invalid_time', 'administrator_id', 'company_id', 'department_id', 'business_subject_id', 'last_record', 'last_record_creator_id', 'next_follow_time', 'clue_public_id', 'extract_time', 'move_public_time', 'creator_id', 'updater_id', 'created_at', 'updated_at','birthday'], 'integer'],
            [[ 'administrator_name', 'last_record_creator_name', 'creator_name', 'updater_name'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 20, 'tooLong'=>'请输入正确的姓名'],
            [['company_name'], 'string', 'max' => 100, 'tooLong'=>'请输入正确的公司名称'],
//            [['wechat'], 'string', 'max' => 25, 'tooLong'=>'请输入正确的微信'],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{5,25})+$/', 'message' => '请输入正确的微信'],
            [['mobile'], 'string', 'max' => 11,'tooLong'=>'请输入正确的手机号码'],
            [['tel'], 'string', 'max' => 15,'tooLong'=>'请输入正确的联系座机'],
            [['position'], 'string', 'max' => 50 ,'tooLong'=>'内容超过最长长度限制'],
            [['qq'], 'string', 'max' => 25,'tooLong'=>'请输入正确的QQ'],
            [['department'], 'string', 'max' => 50,'tooLong'=>'内容超过最长长度限制'],
            [['interest'], 'string', 'max' => 100,'tooLong'=>'内容超过最长长度限制'],
            [['native_place'], 'string', 'max' => 50,'tooLong'=>'内容超过最长长度限制'],
            [['address'], 'string', 'max' => 200,'tooLong'=>'请输入正确的联系地址'],
            [['remark'], 'string', 'max' => 200,'tooLong'=>'内容超过最大长度限制'],
            [['email'], 'string', 'max' => 100,'tooLong'=>'请输入正确的联系邮箱'],
            [['call'], 'string', 'max' => 20,'tooLong'=>'请输入正确的来电电话'],
            [[ 'province_name', 'city_name', 'district_name'], 'string', 'max' => 10],
            [[ 'mobile'], 'match', 'pattern' => '/^1\d{10}$/','message'=>'请输入正确的手机号码'],
            [[ 'qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/','message'=>'请输入正确的QQ'],
            [[ 'tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/','message'=>'请输入正确的联系座机'],
            [[ 'email'], 'match', 'pattern' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/','message'=>'请输入正确的联系邮箱'],
            [['name', 'submit_type','source_id','channel_id'], 'required','on'=>'add'],//必填
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name'=>'名称',
            'company_name' => 'Company Name',
            'source_id' => 'Source ID',
            'channel_id' => 'Channel ID',
            'mobile' => 'Mobile',
            'gander' => 'Gander',
            'tel' => 'Tel',
            'email' => 'Email',
            'qq' => 'Qq',
            'wechat' => 'Wechat',
            'call' => 'Call',
            'birthday' => 'Birthday',
            'department' => 'Department',
            'position' => 'Position',
            'native_place' => 'Native Place',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'address' => 'Address',
            'interest' => 'Interest',
            'remark' => 'Remark',
            'label_id' => 'Label ID',
            'status' => 'Status',
            'is_new' => 'Is New',
            'follow_status' => 'Follow Status',
            'invalid_time' => 'Invalid Time',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'business_subject_id' => 'Business Subject ID',
            'last_record' => 'Last Record',
            'last_record_creator_id' => 'Last Record Creator ID',
            'last_record_creator_name' => 'Last Record Creator Name',
            'next_follow_time' => 'Next Follow Time',
            'clue_public_id' => 'Clue Public ID',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created_at',
            'updated_at' => 'Updated At',
        ];
    }

    public function Add()
    {
        if (!$this->validate()) return false;

        $obj = new CrmClue();

        $obj->company_name = $this->company_name ? $this->company_name : '';
        $obj->name = $this->name ? $this->name : '';
        $obj->source_id = $this->source_id ? $this->source_id : 0;
        $obj->user_id = $this->user_id ? $this->user_id : '';
        $obj->channel_id = $this->channel_id ? $this->channel_id : 0;
        $obj->mobile = $this->mobile ? $this->mobile : '';
        $obj->gander = $this->gander ? $this->gander : 0;
        $obj->tel = $this->tel ? $this->tel : '';
        $obj->email = $this->email ? $this->email : '';
        $obj->qq = $this->qq ? $this->qq : '';
        $obj->wechat = $this->wechat ? $this->wechat : '';
        $obj->call = $this->call ? $this->call : '';
        $obj->birthday = $this->birthday ? $this->birthday : '';
        $obj->department = $this->department ? $this->department : '';
        $obj->position = $this->position ? $this->position : '';
        $obj->native_place = $this->native_place ? $this->native_place : '';
        $obj->province_id = $this->province_id ? $this->province_id : '';
        $obj->province_name = $this->province_name ? $this->province_name : '';
        $obj->city_id = $this->city_id ? $this->city_id : '';
        $obj->city_name = $this->city_name ? $this->city_name : '';
        $obj->district_id = $this->district_id ? $this->district_id : '';
        $obj->district_name = $this->district_name ? $this->district_name : '';
        $obj->address = $this->address ? $this->address : '';
        $obj->interest = $this->interest ? $this->interest : '';
        $obj->remark = $this->remark ? $this->remark : '';
        $obj->label_id = $this->label_id ? $this->label_id : '';
        $obj->status = $this->status ? $this->status : 0;
        $obj->is_new = $this->is_new ? $this->is_new : 0;
        $obj->is_repeat = $this->is_repeat ? $this->is_repeat : 0;
        $obj->is_abandon = $this->is_abandon ? $this->is_abandon : 0;
        $obj->follow_status = $this->follow_status ? $this->follow_status : 0;
        $obj->invalid_time = $this->invalid_time ? $this->invalid_time : '';
        $obj->administrator_id = $this->administrator_id ? $this->administrator_id : '';
        $obj->administrator_name = $this->administrator_name ? $this->administrator_name : '';
        $obj->company_id = $this->company_id ? $this->company_id : '';
        $obj->department_id = $this->department_id ? $this->department_id : '';
        $obj->business_subject_id = $this->business_subject_id ? $this->business_subject_id : '';
        $obj->last_record = $this->last_record ? $this->last_record : '';
        $obj->last_record_creator_id = $this->last_record_creator_id ? $this->last_record_creator_id : '';
        $obj->last_record_creator_name = $this->last_record_creator_name ? $this->last_record_creator_name : '';
        $obj->next_follow_time = $this->next_follow_time ? $this->next_follow_time : '';
        $obj->clue_public_id = $this->clue_public_id ? $this->clue_public_id : 0;
        $obj->extract_time = $this->extract_time ? $this->extract_time : '';
        $obj->move_public_time = $this->move_public_time ? $this->move_public_time : '';
        $obj->creator_id = $this->creator_id ? $this->creator_id : '';
        $obj->transfer_at = $this->transfer_at ? $this->transfer_at : '';
        $obj->creator_name = $this->creator_name ? $this->creator_name : '';
        $obj->updater_id = $this->updater_id ? $this->updater_id : '';
        $obj->updater_name = $this->updater_name ? $this->updater_name : '';
        $obj->created_at = $this->created_at ? $this->created_at : '';
        $obj->updated_at = $this->updated_at ? $this->updated_at : '';
        $obj->recovery_at = $this->recovery_at ? $this->recovery_at : '';

//        foreach ($obj->attributes as $k=>$v){
//            if (empty($v)){
//                $obj->$k = 0;
//            }
//        }
        $obj->save(false);
        return $obj;
    }

}

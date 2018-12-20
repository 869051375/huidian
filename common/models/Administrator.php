<?php

namespace common\models;

use common\validators\TelPhoneValidator;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%administrator}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $latter
 * @property integer $is_root
 * @property integer $is_department_manager
 * @property integer $phone
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $call_center
 * @property integer $type
 * @property integer $status
 * @property integer $is_belong_company
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $is_dimission
 * @property string $title
 * @property string $image
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Clerk $clerk
 * @property CustomerService $customerService
 * @property Salesman $salesman
 * @property Supervisor $supervisor
 * @property CrmDepartment $department
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 * @property CrmCustomerCombine[] $customerCombines
 * @property CrmCustomer[] $customers
 * @property AdministratorLog $administratorLog
 * @property CrmDepartment $leader
 * @property CrmDepartment $assignAdministrator
 *
 * @property CrmOpportunity[] $crmOpportunities
 * @property CrmCustomer[] $crmCustomeres
 *
 * @property CustomerPublic $customerPublic
 * @property cluePublicId $clue_public_id
 * @property CallCenterAssignCompany $callCenterAssignCompany
 */
class Administrator extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const DIMISSION_ACTIVE = 1;
    const DIMISSION_DISABLED = 0;

    const TYPE_ADMIN = 1; // 管理员
    const TYPE_CUSTOMER_SERVICE = 2; // 客服
    const TYPE_SUPERVISOR = 3; // 嘟嘟妹
    const TYPE_CLERK = 4;      // 服务人员
    const TYPE_SALESMAN = 5;   // 业务员

    //是否启用公司与部门
    const BELONG_COMPANY_ACTIVE = 1;//是
    const BELONG_COMPANY_DISABLED = 0;//否

    //是否所在部门领导/助理
    const DEPARTMENT_MANAGER_ACTIVE = 1;//是
    const DEPARTMENT_MANAGER_DISABLED = 0;//否

    public $password;

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

    public function getTypeName()
    {
        $typeList = static::getTypes();
        if($this->type)
        {
            return $typeList[$this->type];
        }
        return null;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_ADMIN => '管理员',
            self::TYPE_CUSTOMER_SERVICE => '客服',
            self::TYPE_SUPERVISOR => '嘟嘟妹',
            self::TYPE_CLERK => '服务人员',
            self::TYPE_SALESMAN => '业务员',
        ];
    }

    public function updatePersonnel()
    {
        if ($this->type === self::TYPE_CUSTOMER_SERVICE) {
            /** @var CustomerService $model */
            $model = CustomerService::find()->where(['administrator_id'=>$this->id])->one();
            $model->name = $this->name;
            $model->phone = $this->phone;
            $model->email = $this->email;
            $model->save(false);
        } else if ($this->type === self::TYPE_SUPERVISOR) {
            /** @var Supervisor $model */
            $model = Supervisor::find()->where(['administrator_id'=>$this->id])->one();
            $model->name = $this->name;
            $model->phone = $this->phone;
            $model->email = $this->email;
            $model->save(false);

        } else if ($this->type === self::TYPE_CLERK) {
            /** @var Clerk $model */
            $model = Clerk::find()->where(['administrator_id'=>$this->id])->one();
            $model->name = $this->name;
            $model->phone = $this->phone;
            $model->email = $this->email;
            $model->save(false);

        }else if ($this->type === self::TYPE_SALESMAN) {
            /** @var Salesman $model */
            $model = Salesman::find()->where(['administrator_id'=>$this->id])->one();
            $model->name = $this->name;
            $model->phone = $this->phone;
            $model->email = $this->email;
            $model->save(false);
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%administrator}}';
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isRoot()
    {
        return $this->is_root == 1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'name', 'email', 'phone', 'image','call_center'], 'filter', 'filter' => 'trim'],
            [['username', 'name','latter', 'email', 'phone', 'status'], 'required'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 10],
            [['latter'],'match', 'pattern'=>'/^[a-zA-Z]+$/i', 'message'=>'姓名全拼只能是英文字符！'],
            [['latter'], 'string', 'max' => 30],
            [['call_center'], 'string', 'max' => 6],

            [['phone'], 'unique'],
            [['phone'], TelPhoneValidator::className(), 'phoneOnly' => true, 'message' => '请输入11位手机号！'],

            [['email'], 'unique'],
            ['email', 'email'],
            ['is_department_manager', 'boolean'],

            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
            [['password'], 'required', 'on' => 'create'],
            [['password'], 'string', 'min' => 6, 'max' => 18],

            [['department_id', 'is_belong_company', 'company_id'], 'integer'],
            [['department_id', 'is_belong_company'], 'default', 'value' => '0'],
            [['title'], 'string', 'max' => 6],
            [['image'], 'string', 'max' => 64],

            [['name'], 'validateNameUpdate', 'on' => 'update_personnel'],
            [['name'], 'validateNameCreate', 'on' => 'create'],

            [['image'], 'validateImage', 'skipOnEmpty' => false, 'skipOnError' => false],

            [['is_belong_company'], 'validateIsBelongCompany', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function validateNameCreate()
    {
        if(!empty($this->name))
        {
            if($this->type == Administrator::TYPE_CUSTOMER_SERVICE)
            {
                $customerService = CustomerService::find()->where(['name'=>$this->name])->one();
                if(null != $customerService)
                {
                    $this->addError('name', '客服名字已存在！');
                }
            }
            elseif($this->type == Administrator::TYPE_SUPERVISOR)
            {
                $supervisor = Supervisor::find()->where(['name'=>$this->name])->one();
                if(null != $supervisor)
                {
                    $this->addError('name', '嘟嘟妹名字已存在！');
                }
            }
        }
    }

    public function validateNameUpdate()
    {
        if(!empty($this->name))
        {
            if($this->type == Administrator::TYPE_CUSTOMER_SERVICE)
            {
                /** @var CustomerService $customerService */
                $customerService = CustomerService::find()->where(['name'=>$this->name])->one();
                if(null != $customerService && $customerService->administrator_id != $this->id)
                {
                    $this->addError('name', '客服名字已存在！');
                }
            }
            elseif($this->type == Administrator::TYPE_SUPERVISOR)
            {
                /** @var Supervisor $supervisor */
                $supervisor = Supervisor::find()->where(['name'=>$this->name])->one();
                if(null != $supervisor && $supervisor->administrator_id != $this->id)
                {
                    $this->addError('name', '嘟嘟妹名字已存在！');
                }
            }
        }
    }

    public function validateImage()
    {
        if(empty($this->image))
        {
            if($this->type == Administrator::TYPE_CUSTOMER_SERVICE || $this->type == Administrator::TYPE_SUPERVISOR)
            {
                $this->addError('image', '头像不能为空。');
            }
        }
    }

    public function validateIsBelongCompany()
    {
        if(empty($this->department_id) || empty($this->company_id))
        {
            if($this->is_belong_company == static::BELONG_COMPANY_ACTIVE)
            {
                $this->addError('department_id', "所属公司／部门不能为空");
            }
            if($this->type == Administrator::TYPE_SALESMAN)
            {
                $this->addError('department_id', "业务员必须选择所属公司和部门");
            }
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '登录账号',
            'name' => '姓名',
            'latter' => '姓名全拼',
            'password' => '密码',
            'is_root' => 'Is Root',
            'phone' => '手机号码',
            'is_department_manager' => '所在部门领导/助理',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => '邮箱',
            'call_center' => 'Callcenter工号',
            'type' => '账号类型',
            'status' => '启用',
            'department_id' => '所属公司/部门',
            'company_id' => '所属公司',
            'is_belong_company' => '启用公司与部门',
            'image' => '头像',
            'title' => '职位',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getClerk()
    {
        return $this->hasOne(Clerk::className(), ['administrator_id' => 'id']);
    }

    public function getCustomerService()
    {
        return $this->hasOne(CustomerService::className(), ['administrator_id' => 'id']);
    }

    public function getSalesman()
    {
        return $this->hasOne(Salesman::className(), ['administrator_id' => 'id']);
    }

    public function getSupervisor()
    {
        return $this->hasOne(Supervisor::className(), ['administrator_id' => 'id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(CrmDepartment::className(), ['id' => 'department_id']);
    }

    public function getLeader()
    {
        return $this->hasOne(CrmDepartment::className(), ['leader_id' => 'id']);
    }
    public function getAssignAdministrator()
    {
        return $this->hasOne(CrmDepartment::className(), ['assign_administrator_id' => 'id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getCustomerCombines()
    {
        return $this->hasMany(CrmCustomerCombine::className(), ['administrator_id' => 'id']);
    }

    public function getCustomers()
    {
        return $this->hasMany(CrmCustomer::className(), ['administrator_id' => 'id']);
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id, 'company_id' => $this->company_id])->one();
    }

    public function getAdministratorLog()
    {
        return $this->hasOne(AdministratorLog::className(), ['administrator_id' => 'id'])->where(['type' => AdministratorLog::TYPE_LOGIN_SUCCESS])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getCrmOpportunities()
    {
        return $this->hasMany(CrmOpportunity::className(), ['administrator_id' => 'id']);
    }

    public function getCrmCustomeres()
    {
        return $this->hasMany(CrmCustomer::className(), ['administrator_id' => 'id']);
    }

    public function getCustomerPublic()
    {
        return $this->hasOne(CustomerPublic::className(), ['company_id' => 'company_id']);
    }

    public function getCallCenterAssignCompany()
    {
        return $this->hasOne(CallCenterAssignCompany::className(), ['company_id' => 'company_id']);
    }

    /**
     * 服务人员
     * @param int $administratorId
     * @param string $status
     * @throws \Exception
     */
    public function saveClerk($administratorId, $status)
    {
        if($status === 'insert') {
            $clerk = new Clerk();
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $clerk->creator_id = $user->id;
            $clerk->creator_name = $user->name;
            $clerk->name = $this->name;
            $clerk->phone = $this->phone;
            $clerk->email = $this->email;
            $clerk->status = $this->status;
            $clerk->administrator_id = $administratorId;
            $clerk->company_id = $clerk->administrator ? $clerk->administrator->company_id : 0;
            $clerk->save(false);
        }
        elseif($status === 'update')
        {
            /** @var Clerk $clerk */
            $clerk = Clerk::find()->where('administrator_id=:id', [':id' => $administratorId])->limit('1')->one();
            try
            {
                $clerk->name = $this->name;
                $clerk->phone = $this->phone;
                $clerk->email = $this->email;
                $clerk->status = $this->status;
                $clerk->company_id = $clerk->administrator ? $clerk->administrator->company_id : 0;
                $clerk->save(false);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }

    /**
     * 业务人员
     * @param int $administratorId
     * @param string $status
     * @throws \Exception
     */
    public function saveSalesman($administratorId, $status)
    {
        ini_set( 'display_errors', 'off' );
        if($status === 'insert')
        {
            $salesman = new Salesman();
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $salesman->creator_id = $user->id;
            $salesman->creator_name = $user->name;
            $salesman->name = $this->name;
            $salesman->phone = $this->phone;
            $salesman->email = $this->email;
            $salesman->status = $this->status;
            $salesman->administrator_id = $administratorId;
            $salesman->company_id = $salesman->administrator ? $salesman->administrator->company_id : 0;
            $salesman->save(false);
        }
        elseif($status === 'update')
        {
            /** @var Salesman $salesman */
            $salesman = Salesman::find()->where('administrator_id=:id', [':id' => $administratorId])->one();
            try
            {
                //$salesman->name = $this->name;
                $salesman->phone = isset($this->phone) ? $this->phone : '';
                $salesman->email = isset($this->email) ? $this->email : '';
                $salesman->status = $this->status;
                $salesman->company_id = $salesman->administrator ? $salesman->administrator->company_id : 0;
                //更新客户合伙人表对应的公司和部门
                $this->updateCustomerCombine($salesman->administrator);
                $salesman->save(false);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }

    /**
     * 督导（嘟嘟妹）
     * @param int $administratorId
     * @param string $status
     * @throws \Exception
     */
    public function saveSupervisor($administratorId, $status)
    {
        if($status === 'insert') {

            $supervisor = new Supervisor();
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $supervisor->creator_id = $user->id;
            $supervisor->creator_name = $user->name;
            $supervisor->name = $this->name;
            $supervisor->phone = $this->phone;
            $supervisor->email = $this->email;
            $supervisor->status = $this->status;
            $supervisor->administrator_id = $administratorId;
            $supervisor->save(false);

        }
        elseif($status === 'update')
        {
            /** @var Supervisor $supervisor */
            $supervisor = Supervisor::find()->where('administrator_id=:id', [':id' => $administratorId])->limit('1')->one();
            try
            {
                $supervisor->name = $this->name;
                $supervisor->phone = $this->phone;
                $supervisor->email = $this->email;
                $supervisor->status = $this->status;
                $supervisor->save(false);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }

    /**
     * 客服
     * @param int $administratorId
     * @param string $status
     * @throws \Exception
     */
    public function saveCustomerService($administratorId, $status)
    {
        if($status === 'insert')
        {
            $customerService = new CustomerService();
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $customerService->creator_id = $user->id;
            $customerService->creator_name = $user->name;
            $customerService->name = $this->name;
            $customerService->phone = $this->phone;
            $customerService->email = $this->email;
            $customerService->status = $this->status;
            $customerService->administrator_id = $administratorId;
            $customerService->company_id = $customerService->administrator ? $customerService->administrator->company_id : 0;
            $customerService->save(false);
        }
        elseif ($status === 'update')
        {
            /** @var CustomerService $customerService */
            $customerService = CustomerService::find()->where('administrator_id=:id', [':id' => $administratorId])->limit('1')->one();
            try
            {
                $customerService->name = $this->name;
                $customerService->phone = $this->phone;
                $customerService->email = $this->email;
                $customerService->status = $this->status;
                $customerService->company_id = $customerService->administrator ? $customerService->administrator->company_id : 0;
                $customerService->save(false);
            }
            catch (\Exception $e)
            {
                throw $e;
            }
        }
    }

    /**
     * @param Administrator $model
     */
    public function saveStatus($model)
    {
        if($model->type == Administrator::TYPE_CUSTOMER_SERVICE)
        {
            $statusModel = $model->customerService;
        }
        elseif ($model->type == Administrator::TYPE_SUPERVISOR)
        {
            $statusModel = $model->supervisor;
        }
        elseif ($model->type == Administrator::TYPE_CLERK)
        {
            $statusModel = $model->clerk;
        }
        elseif ($model->type == Administrator::TYPE_SALESMAN)
        {
            $statusModel = $model->salesman;
        }
        /** @var CustomerService|Supervisor|Clerk|Salesman $statusModel */
        $statusModel->status = $model->status;
        $statusModel->save(false);
    }

    public function isLeader()
    {
        return $this->department && $this->department->leader_id == $this->id;
    }

    public function isDepartmentManager()
    {
        return $this->department && $this->is_department_manager;
    }

    /**
     * @param $department CrmDepartment
     * @return boolean
     */
    public function isParentDepartment($department)
    {
        if(false !== stripos($department->path, $this->department->path))
        {
            return true;
        }
        return false;
    }

    public function isBelongCompany()
    {
        return $this->is_belong_company == self::BELONG_COMPANY_ACTIVE;
    }

    public function isCompany()
    {
        return $this->isBelongCompany() && $this->company_id ? true : false;
    }

    public function getImageUrl($width=100, $height=100)
    {
        $image = $this->image;
        if(empty($image)) {
            return '/images/default.png';
        }
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 0]);
    }

    /**
     * @param Administrator $administrator
     */
    private function updateCustomerCombine($administrator)
    {
        if(null != $administrator)
        {
            if($administrator->customerCombines)
            {
                foreach ($administrator->customerCombines as $customerCombine)
                {
                    $customerCombine->company_id = $administrator->company_id;
                    $customerCombine->department_id = $administrator->department_id;
                    $customerCombine->save(false);
                }
            }
            $this->updateCustomer($administrator);
        }
    }

    /**
     * @param Administrator $administrator
     */
    private function updateCustomer($administrator)
    {
        if($administrator->customers)
        {
            /** @var CrmCustomer $customer */
            foreach ($administrator->customers as $customer)
            {
                $customer->company_id = $administrator->company_id;
                $customer->department_id = $administrator->department_id;
                $customer->save(false);
            }
        }
    }

    //根据组织树获取department_id   include 为 true 包括自己的部门 false 不包括自己的部门 默认不包括
    public function getTreeDepartmentId($include = false){
        if ($this->department_id == 0){
            return false;
        }
        $data = CrmDepartment::find()->asArray()->all();
        $department_id = $this->getTree($data,$this->department_id);
        $department = $this->recur('id',$department_id);
        if ($include){
            array_push($department,$this->department_id);
        }
        return $department;
    }

    //根据组织树获取administrator_id   include 为 true 包括自己 false 不包括自己 默认不包括  include_department 为true的时候获取包括本部门的其他人 false的时候不包括本部门的其他人
    public function getTreeAdministratorId($include = false , $include_department = false)
    {
        if ($this->department_id == 0){
            return false;
        }
        if ($include_department)
        {
            $department_id = $this->getTreeDepartmentId(true);
        }
        else {
            $department_id = $this->getTreeDepartmentId(false);
        }
        $query = Administrator::find()->select('id')->where(['in','department_id',$department_id])->asArray();
        if (!$include)
        {
            $query->andWhere(['!=','id',$this->id]);
        }
        $administrator_id = $query->all();
        if (!empty($administrator_id))
        {
            $administrator_arr = array();
            foreach ($administrator_id as $item){
                array_push($administrator_arr,$item['id']);
            }
            return $administrator_arr;
        }
        return false;
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
    //获取部门的所有父类的ID，默认返回包含部门自身ID，传false时不返回
    public static function getParentDepartmentId($department_id,$include_department = true)
    {
        $res = self::getParent($department_id);
        if($include_department){
            $res = $res.$department_id;
        }else{
            $res = substr($res,0,strlen($res)-1);
        }
        $data = explode(',',$res);
        return $data;

    }

    public static function getParent($department_id)
    {
        $department_ids = '';
        $department = CrmDepartment::find()->where(['id'=>$department_id])->asArray()->one();
        if($department['parent_id'] != 0){
            $department_ids .= $department['parent_id'];
            $data = self::getParent($department['parent_id']);
            if(isset($data)){
                $department_ids .= ','.$data;
            }
        }
        return $department_ids;
    }

}

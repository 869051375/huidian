<?php
namespace backend\controllers;


use backend\models\ClueSearch;
use backend\modules\niche\models\ClueCustomList;
use backend\modules\niche\models\CustomerExchangeList;
use common\models\Administrator;
use backend\models\ClueForm;
use common\models\ClueOperationRecord;
use common\models\CluePublic;
use common\models\CrmClue;
use common\models\CrmCustomerApi;
use common\models\CrmDepartment;
use yii\filters\AccessControl;
use Yii;


class ClueController extends ApiController
{


    private $post;
    /** @var  CrmClue $obj*/
    private $obj;

    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','list','follow-status','custom-filter','clue-add-tag','clue-remove-tag','add','transfer','abandon','discarded','remove','salesman-list','details','edit','follow-status-update','department','repeat-list','replace','extract','distribution','details-encrypt','become-customer','create-list','get-partner','custom-list','custom-change','get-permissions-all'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        $this->post = Yii::$app->request->post();
        /** @var CrmClue obj */
        $this->obj = new CrmClue();
    }

    /**
     * @return array
     * 线索管理列表
     */
    public function actionList()
    {

//        $this->post['clue_type'] = 'my_clue';                     //我的线索
//        $this->post['clue_type'] = 'my_change';                     //我的已转换的线索
//        $this->post['clue_type'] = 'my_change_subordinate';         //我的下属已转换的线索
//        $this->post['clue_type'] = 'change_whole';                  //我的所有转换数据
//        $this->post['clue_type'] = 'my_subordinate';              //我的下属
//        $this->post['clue_type'] = 'whole';                       //所有数据

//        $this->post['page'] = 1;        //页数
//        $this->post['page_num'] = 2;    //偏移量
//        $this->post['soon_recovery'] = 1;    //即将被回收
//        $this->post['is_new'] = 1;       //查询新线索
//        $this->post['status'] = 1;          //查询转移的线索
//        $this->post['status'] = 2;          //查询分配的线索
//        $this->post['status'] = 3;          //查询提取的线索
//        $this->post['_start'] ='2018-10-21 00:00:00';     //转换时间
//        $this->post['transfer_at_end'] ='2018-10-23 00:00:00';     //转换时间
////        近三天维护的线索查询
//        $this->post['nearly_three_days'] = 1;
//        $this->post['label_id'] = 1;                            //标签（标签给我ID就好了）
//        $this->post['id'] = 1;                                  //
//        $this->post['administrator_id'] = 0;                  //按照负责人查询
//        $this->post['department_id'] = 2;                   //按照所属部门查询
//        $this->post['company_id'] = 2;                   //按照所属部门查询
//        $this->post['creator_id'] = 1;                      //按照创建人查询
//        $this->post['follow_status'] = 0;                   //按照跟进状态查询
//        $this->post['created_at'] = ['',''];  //按照创建时间
//        $this->post['updated_at'] = [1533887018,1533887019];  //按照最后修改时间

        //自定义筛选
//        $this->post['custom_key'] = 'wechat';               //自定义筛选key
//        $this->post['custom_val'] = '12';                 //自定义筛选val


//        $this->post['name'] = '张三';                                   //按照名字查询
//        $this->post['company_name'] = '掘金企服有限公司';                 //按照公司名称查询
//        $this->post['mobile'] = '18101367722';                          //按照手机号查询
//        $this->post['tel'] = '101-2533211';                           //按照电话查询
//        $this->post['email'] = '191029293@163.com';                   //按照邮箱查询
//        $this->post['qq'] = '8906172632';                               //按照QQ查询
//        $this->post['wechat'] = 'asdfasdf123';                               //按照微信查询
//        $this->post['call'] = '1012533211';                               //按照来电电话查询
//        $this->post['source_id'] = 1;                               //按照线索来源查询
//        $this->post['channel_id'] = 2;                               //按照线索渠道来源查询


        if (!isset($this->post['clue_type'])){
            return $this->response(self::FAIL,'缺少参数clue_type');
        }

        if (!in_array($this->post['clue_type'],CrmClue::CLUE_TYPE)){
            return $this->response(self::FAIL,'非法参数clue_type');
        }


        if (!isset($this->post['page']) || $this->post['page'] <= 1){
            $this->post['page'] = 1;
        }

        if (!empty($this->post['custom_key'])&& !empty($this->post['custom_val'])){

            $custom_key = '';
            foreach (CrmClue::CUSTOM_FILTER as $k=>$v){
                if ($v['key'] == $this->post['custom_key']){
                    $custom_key = $this->post['custom_key'];
                }
            }

            if (empty($custom_key)){
                return $this->response(self::FAIL,'非法参数custom_key');
            }

            //拼接自定义筛选字段
            $this->post[$custom_key] = $this->post['custom_val'];

        }


        if (isset($this->post['administrator_id']) && $this->post['administrator_id'] == 0){
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->post['administrator_id'] = $user->id;
            //如果是查询我的线索公海的话
            if ($this->post['clue_type'] == CrmClue::CLUE_TYPE_MY_CLUE || $this->post['clue_type'] == CrmClue::CLUE_TYPE_CHANGE)
            {
                $this->post['administrator_id'] = $user->id;
            }
            //如果要查询我的下属administrator_id 为我的下属的所有id
            if ($this->post['clue_type'] == CrmClue::CLUE_TYPE_MY_SUBORDINATE || $this->post['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_SUBORDINATE)
            {
                if (!$user->isBelongCompany() && !$user->isCompany()){
                    $this->post['administrator_id'] = 'administrator_id_no';
                }
                else
                {
                    $this->post['administrator_id'] = $user->getTreeAdministratorId(false,true);
                }
            }
            //如果要查询全部administrator_id为我的下属的所有ID + 我的ID
            if ($this->post['clue_type'] == CrmClue::CLUE_TYPE_WHOLE || $this->post['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_WHOLE)
            {
                if (!$user->isBelongCompany() && !$user->isCompany()){
                    $this->post['administrator_id'] = 'administrator_id';
                }
                else{
                    $this->post['administrator_id'] = $user->getTreeAdministratorId(true,true);
                }
            }
        }

        if (!$this->post['administrator_id'])
        {
            $this->post['administrator_id'] = -1;
        }
        $this->post['page'] = isset($this->post['page']) ? $this->post['page'] : 1;      //页数如果没有传值 默认为0
        $this->post['page_num'] = isset($this->post['page_num']) ? $this->post['page_num'] : 20;  //偏移量如果没有传值 默认为20
        $this->post['limit'] = ($this->post['page']-1) * $this->post['page_num']; //偏移量


//        if (!isset($post_arr['clue_type']) || !isset((CrmClue::CLUE_TYPE)[$post_arr['clue_type']]))
//        {
//            return $this->response('100','clue_type编码错误');
//        }
//        if (!isset($post_arr['administrator_id']) || $post_arr['administrator_id'] < 0)
//        {
//            return $this->resPonse('100','','administrator_id参数错误');
//        }

        $obj = new ClueSearch();
        $obj->load($this->post,'');
        $data = $obj->getList ($this->post);
        if (!empty($data)){
            $data_arr = array();
            /**
             * @var  $k
             * @var CrmClue $v
             */
            foreach ($data as $k=>$v){
                $attributes = $v->attributes;
                $attributes['label_name'] = isset($v->tag->name) ? $v->tag->name : '';
                $attributes['label_color'] = isset($v->tag->color) ? $v->tag->color : '';
                $attributes['channel_name'] = isset($v->channel->name) ? $v->channel->name : '';
                $attributes['source_name'] = isset($v->source->name) ? $v->source->name : '';
                $attributes['department_name'] = isset($v->departments->name) ? $v->departments->name : '';
                $attributes['next_follow_time'] = isset($v->recordDesc->next_follow_time) ? $v->recordDesc->next_follow_time : '--';
                $data_arr[$k] = $attributes;
            }
            $data_arr_new['data'] = $data_arr;
            $data_arr_new['count'] = (int)$obj->getList ($this->post,1);
            $data_arr_new['page_num'] = $this->post['page_num'];
            $data_arr_new['page'] = $this->post['page'];
        }else{
            $data_arr_new['data'] = null;
            $data_arr_new['count'] = (int)$obj->getList ($this->post,1);
            $data_arr_new['page_num'] = $this->post['page_num'];
            $data_arr_new['page'] = $this->post['page'];
        }
        $error = $this->obj->getFirstErrors();

        if (empty($error))
        {
            return $this->response(self::SUCCESS,'查询成功',isset($data_arr_new) ? $data_arr_new : null);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    /**
     * @return array
     * 新增销售线索
     */
    public function actionAdd(){
//        $this->post['submit_type'] = 'me';                  //me为我的线索提交  public为公海提交
//        $this->post['name'] = '张三张三张三张三';
//        $this->post['gander'] = 1;                          //性别1男2女
//        $this->post['source_id'] = 1;                       //线索来源
//        $this->post['channel_id'] = 2;                      //线索渠道来源
//        $this->post['company_name'] = '掘金企服有限公司掘金企服有限公司';      //公司名称
//        $this->post['mobile'] = '18101367722';                //手机号
//        $this->post['wechat'] = 'asdfasdf123';               //微信号
//        $this->post['qq'] = '8906172632';                   //QQ
//        $this->post['call'] = '1012533211';                //来电电话
//        $this->post['tel'] = '101-2533211';                 //联系座机
//        $this->post['email'] = '191029293@163.com';         //邮箱
//        $this->post['birthday'] = '20180809';               //生日
//        $this->post['department'] = '产品研发部';             //部门
//        $this->post['position'] = 'PHP研发经理';                 //职位
//        $this->post['native_place'] = '北京市朝阳区东三环中路精粮大厦16层'; //籍贯
//        $this->post['province_id'] = 25;                    //省份ID
//        $this->post['province_name'] = '山西省';             //省份
//        $this->post['city_id'] = 38;                        //城市ID
//        $this->post['city_name'] = '吕梁市';                 //城市
//        $this->post['district_id'] = 66;                    //区县ID
//        $this->post['district_name'] = '文水县';             //区县
//        $this->post['address'] = '北张乡北张村308号---@@';              //详细地址
//        $this->post['interest'] = '北京市朝阳区东三环中路精粮大厦16层北京市朝阳区东三环中路精粮大厦16层';                //兴趣爱好
//        $this->post['remark'] = '这是个备注';                //线索备注
//        $this->post['clue_public_id'] = 2;                  //线索公海ID

        if (!isset($this->post['submit_type'])){
            return $this->response(400,'缺少参数submit_type');
        }
        if ($this->post['submit_type'] == 'public'){
            if (!isset($this->post['clue_public_id'])){
                return $this->response(400,'缺少参数clue_public_id');
            }
            $this->post['recovery_at'] = time();
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $this->post['status'] = $this->post['submit_type'] == CrmClue::SUBMIT_TYPE_ME ? 0 : 5;               //线索状态
        $this->post['is_new'] = 1;                          //是否为新线索
        $this->post['follow_status'] = 0;                   //线索跟进状态
        $this->post['administrator_id'] = $user->id;        //所属业务员员ID
        $this->post['administrator_name'] = $user->name;    //所属业务员名字
        $this->post['company_id'] = $user->company_id;      //所属公司ID
        $this->post['department_id'] = $user->department_id;//所属部门ID
        $this->post['creator_id'] =$user->id ;              //创建人ID
        $this->post['creator_name'] = $user->name;          //创建人名字
        $this->post['updater_id'] = $user->id;              //最后修改人ID
        $this->post['updater_name'] = $user->name;          //最后修改人名字
        $this->post['updated_at'] = time();                 //最后修改时间
        //看是否有重复
        $clue = $this->obj->is_repeat($this->post);
        if (!empty($clue)){
            $this->post['is_repeat'] = 1;
        }else{
            $this->post['is_repeat'] = 0;
        }
        
        if ($this->post['submit_type'] != 'public'){
            //开启了公司和部门的才进行验证
            if ($user->isBelongCompany() && $user->isCompany()){
                //查看是否可以新增线索
                /** @var CrmClue $this->obj */
                $this->obj->checkAddClue($user,true);
            }
        }

        $error = $this->obj->getFirstErrors();
        if (!empty($error)){
            return $this->response(self::FAIL,reset($error));
        }
        
        $obj = new ClueForm();
        $obj->setScenario('add');
        $obj->load($this->post,'');
        $data = $obj->Add();

        if ($data)
        {
            //添加操作记录
            $operation = new ClueOperationRecord();
            $operation->create($data['id'],$operation::CREATE_CLUE,'创建了销售线索',$user->id,$user->name);

            //统计埋点
            $niche = new CustomerExchangeList();
            $niche->clue(['id'=>$data->id,'from'=>'','administrator_id'=>$user->id,'province_id'=> (isset($this->post['province_id']) && $this->post['province_id'] != '') ? $this->post['province_id'] : 0,'city_id'=> (isset($this->post['city_id']) && $this->post['city_id']!='') ? $this->post['city_id'] : 0,'district_id' => (isset($this->post['district_id']) && $this->post['district_id'] != '') ? $this->post['district_id'] : 0,'source_id'=>isset($this->post['source_id']) ? $this->post['source_id'] : 0,'channel_id'=>isset($this->post['channel_id']) ? $this->post['channel_id'] : 0]);

            return $this->response(self::SUCCESS,'销售线索新增成功',$obj);
        }
        else
        {
            return $this->response(self::FAIL,$obj->getFirstErrors());
        }

    }

    /**
     * @return array
     * 线索详情接口
     */
    public function actionDetails()
    {
//        $this->post['clue_id'] = 1;
        /** @var CrmClue $data */
        $data = $this->obj->details($this->post);

        $attributes = $data->attributes;
        $attributes['channel_name'] = isset($data->channel->name) ? $data->channel->name : '';
        $attributes['source_name'] = isset($data->source->name) ? $data->source->name : '';
        $attributes['user_name'] = isset($data->user->name) ? $data->user->name : '';
        $attributes['user_phone'] = isset($data->user->phone) ? $data->user->phone : '';
        $attributes['user_email'] = isset($data->user->email) ? $data->user->email : '';
        $attributes['user_address'] = isset($data->user->address) ? $data->user->address : '';
        $attributes['label_name'] = isset($data->tag->name) ? $data->tag->name : '';
        $attributes['label_color'] = isset($data->tag->color) ? $data->tag->color : '';
        $attributes['department_name'] = isset($data->departments->name) ? $data->departments->name : '--部门';
        $attributes['city_id'] = (string)$attributes['city_id'];
        $attributes['district_id'] = (string)$attributes['district_id'];
        $attributes['province_id'] = (string)$attributes['province_id'];

        if ($attributes)
        {
            return $this->response(self::SUCCESS,'查询成功',$attributes);
        }
        else
        {
            $error = $this->obj->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }

    }

    /**
     * @return array
     * 线索详情接口 加密
     */
    public function actionDetailsEncrypt()
    {
//        $this->post['clue_id'] = 1;

        /** @var CrmClue $data */
        $data = $this->obj->details($this->post);
        $data['name'] = mb_substr($data['name'],0,1).$this->getXing((mb_strlen($data['name'])-1));
        if (mb_strlen($data['company_name'])>5){
            $data['company_name'] = mb_substr($data['company_name'],0,2).$this->getXing((mb_strlen($data['company_name'])-4)).mb_substr($data['company_name'],(mb_strlen($data['company_name'])-2),2);
        }
        else
        {
            $data['company_name'] = mb_substr($data['company_name'],0,1).$this->getXing((mb_strlen($data['company_name'])-1));
        }

        $data['department'] = $this->getXing((mb_strlen($data['department'])-1)).mb_substr($data['department'],(mb_strlen($data['department'])-1),1);

        $data['position'] = $this->getXing((mb_strlen($data['position'])));
        $data['address'] = $this->getXing((mb_strlen($data['address'])));
        $data['native_place'] = mb_substr($data['native_place'],0,2).$this->getXing((mb_strlen($data['native_place'])-2));
        $data['interest'] = mb_substr($data['interest'],0,2).$this->getXing((mb_strlen($data['interest'])-2));
        $data['remark'] = mb_substr($data['remark'],0,2).$this->getXing((mb_strlen($data['remark'])-2));
        $data['mobile'] = mb_substr($data['mobile'],0,3).$this->getXing((mb_strlen($data['mobile'])-7)).substr($data['mobile'],7,4);
        $data['wechat'] = mb_substr($data['wechat'],0,1).$this->getXing((mb_strlen($data['wechat'])-1));
        $data['qq'] = mb_substr($data['qq'],0,1).$this->getXing((mb_strlen($data['qq'])-1));
        $data['call'] = mb_substr($data['call'],0,1).$this->getXing((mb_strlen($data['call'])-1));
        $data['tel'] = mb_substr($data['tel'],0,1).$this->getXing((mb_strlen($data['tel'])-1));
        $data['email'] = mb_substr($data['email'],0,1).$this->getXing((mb_strlen($data['email'])-1));

        $attributes = $data->attributes;
        $attributes['user_name'] = isset($data->user->name) ? mb_substr($data->user->name,0,1).$this->getXing((mb_strlen($data->user->name)-1)) : '';
        $attributes['user_phone'] = isset($data->user->phone) ? mb_substr($data->user->phone,0,2).$this->getXing((mb_strlen($data->user->phone)-2)) : '';
        $attributes['user_email'] = isset($data->user->email) ? $data['user']['email'] = mb_substr($data->user->email,0,1).$this->getXing((strlen($data->user->email)-1)) : '';
        $attributes['user_address'] = isset($data->user->address) ? mb_substr($data->user->address,0,1).$this->getXing((mb_strlen($data->user->address)-1)) : '';

        $attributes['channel_name'] = isset($data->channel->name) ? $data->channel->name : '';
        $attributes['source_name'] = isset($data->source->name) ? $data->source->name : '';
        $attributes['label_name'] = isset($data->tag->name) ? $data->tag->name : '';
        $attributes['label_color'] = isset($data->tag->color) ? $data->tag->color : '';
        $attributes['department_name'] = isset($data->departments->name) ? $data->departments->name : '--部门';
        $attributes['city_id'] = (string)$attributes['city_id'];
        $attributes['district_id'] = (string)$attributes['district_id'];
        $attributes['province_id'] = (string)$attributes['province_id'];
        if ($attributes)
        {
            return $this->response(self::SUCCESS,'查询成功',$attributes);
        }
        else
        {
            $error = $this->obj->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }

    }

    function getXing($num){
        $str = '';
        for ($i=0;$i<$num;$i++)
        {
            $str.='*';
        }
        return $str;
    }


    /**
     * @return array
     * 线索编辑接口
     */
    public function actionEdit()
    {
//        $this->post['clue_id'] = 14;                       //要编辑的线索ID
////
//        $this->post['name'] = '张三';
//        $this->post['gander'] = 1;                          //性别1男2女
//        $this->post['source_id'] = 1;                       //线索来源
//        $this->post['channel_id'] = 2;                      //线索渠道来源
//        $this->post['company_name'] = '掘金企服有限公司';      //公司名称
//        $this->post['mobile'] = '18101367722';                //手机号
//        $this->post['wechat'] = 'asdfasdf123';               //微信号
//        $this->post['qq'] = '8906172632';                   //QQ
//        $this->post['call'] = '1012533211';                //来电电话
//        $this->post['tel'] = '101-2533211';                 //联系座机
//        $this->post['email'] = '191029293@163.com';         //邮箱
//        $this->post['birthday'] = '20181125';               //生日
//        $this->post['department'] = '产品研发部';             //部门
//        $this->post['position'] = 'PHP研发经理';                 //职位
//        $this->post['native_place'] = '北京市朝阳区东三环中路精粮大厦16层'; //籍贯
//        $this->post['province_id'] = 25;                    //省份ID
//        $this->post['province_name'] = '山西省';             //省份
//        $this->post['city_id'] = 38;                        //城市ID
//        $this->post['city_name'] = '吕梁市';                 //城市
//        $this->post['district_id'] = 66;                    //区县ID
//        $this->post['district_name'] = '文水县';             //区县
//        $this->post['address'] = '北张乡北张村308号';              //详细地址
//        $this->post['interest'] = '兴趣爱好';                //兴趣爱好
//        $this->post['remark'] = '这是个备注';                //线索备注

        if (!isset($this->post['clue_id'])){
            return $this->response(400,'缺少参数clue_id');
        }

        //看是否有重复
        $clue_repeat = $this->obj->is_repeat($this->post);
        if (count($clue_repeat) > 1){
            $this->post['is_repeat'] = 1;
        }else{
            $this->post['is_repeat'] = 0;
        }

        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $this->post['updater_id'] = $user->id;
        $this->post['updater_name'] = $user->name;
        $this->post['updated_at'] = time();                 //最后修改时间
        $clue = $this->findModel($this->post['clue_id']);
//        $clue->setScenario('edit');
//        unset($this->post['clue_id']);

        $clue->load($this->post,'');
        $data = $clue->save();

        if ($data)
        {
            //添加操作记录
            $operation = new ClueOperationRecord();
            $operation->create($this->post['clue_id'],$operation::EDIT_CLUE,'编辑了线索资料',$user->id,$user->name);

            //埋点
            $customer_model = new CustomerExchangeList();
            $customer_model->updateClue($this->post['clue_id']);

            return $this->response(200,'编辑成功');
        }
        else
        {
            $error = $clue->getFirstErrors();
            return $this->response(400,reset($error));
        }

    }


    /**
     * @return array
     * 线索跟进状态修改接口
     */
    public function actionFollowStatusUpdate()
    {
//        $this->post['clue_id'] = 12;         //线索ID
//        $this->post['follow_status'] = 1;   //跟进状态
        $query = CrmClue::findOne($this->post['clue_id']);
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;

        $query->updater_id = $user->id;              //最后修改人ID
        $query->updater_name = $user->name;          //最后修改人名字
        $query->updated_at = time();                 //最后修改时间

        $query->load($this->post,'');
        $data = $query->save(false);

        if ($data)
        {
            if ($this->post['follow_status'] == CrmClue::FOLLOW_STATUS_CONTACT)
            {
                $follow_status = '已联系';
            }
            else if($this->post['follow_status'] == CrmClue::FOLLOW_STATUS_CLOSE)
            {
                $follow_status = '已关闭';
            }
            else if ($this->post['follow_status'] == CrmClue::FOLLOW_STATUS_UNRELATED)
            {
                $follow_status = '未处理';
            }

            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            //添加操作记录
            $operation = new ClueOperationRecord();
            $operation->create($this->post['clue_id'],$operation::UPDATE_CLUE_TYPE,'编辑线索跟进状态为：'.$follow_status,$user->id,$user->name);

            return $this->response(self::SUCCESS,'保存成功');
        }
        else
        {
            $error = $query->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }
    }

    private function findModel($id)
    {
        $obj = new CrmClue();
        $model = $obj::findOne($id);
        if (null == $model) {
            $model->addError('id','找不到指定的线索');
        }
        return $model;
    }

    /**
     * @return array
     * 转移销售线索给其他负责人
     */
    public function actionTransfer()
    {

//        $this->post['clue_id'] = [14,15];
//        $this->post['administrator_id'] = 78;

        if (!isset($this->post['clue_id']) || !isset($this->post['administrator_id']))
        {
            return $this->response(self::FAIL,'缺少参数clue_id & administrator_id');
        }
        $administrator = Administrator::findOne($this->post['administrator_id']);

        if (empty($administrator))
        {
            return $this->response(self::FAIL,'负责人不存在');
        }

        if (isset($administrator->department->cluePublic))
        {
            //查看是否可以新增线索
            /** @var CrmClue $this->obj */
            $check_num = $this->obj->checkAddClue($administrator);
            $error = $this->obj->getFirstErrors();
            if (!empty($error)){
                return $this->response(self::FAIL,reset($error));
            }

            if (count($this->post['clue_id']) > $check_num){
                return $this->response(self::FAIL,'对不起，当前用户拥有线索已达上限');
            }
        }

        //查看是否可以转移
        $transfer = CrmClue::find()->where(['in','id',$this->post['clue_id']])->all();
        foreach ($transfer as $clue)
        {
            /** @var CrmClue $clue_one */
            $clue_one = CrmClue::find()->where(['id'=>$clue->id])->one();

            if ($clue_one->clue_public_id > 0 || $clue_one->status == 5)
            {
                return $this->response(self::FAIL,'当前线索已回收至公海，不能被转移。');
            }
        }

        $this->post['administrator_name'] = $administrator['name'];
        $data = $this->obj->transfer($this->post);
        if ($data)
        {
            //添加操作记录
            $operation = new ClueOperationRecord();
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $operation->create($this->post['clue_id'],$operation::DISTRIBUTION_CLUE,'转移新线索负责人为：'.$administrator['name'],$user->id,$user->name);

            if (count($this->post['clue_id']) > 1)
            {
                $count = count($this->post['clue_id']) - $data;

                return $this->response(self::SUCCESS,"所选线索，转移成功：{$data}；转移失败：{$count}");
            }
            else
            {
                return $this->response(self::SUCCESS,'销售线索数据转移成功');
            }

        }
        else
        {
            $error = $this->obj->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }
    }

    /**
     * @return array
     * 负责人列表接口  && 业务员列表接口
     */
    public function actionSalesmanList()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $department_id = $this->getSubordinateDepartment($user);

        if ($department_id){
            $data = Administrator::find()->where(['in','department_id',$department_id])->andWhere(['company_id'=>$user->company_id])->all();
        }
        else{
            $data = Administrator::find()->all();
        }

        if ($data)
        {
            return $this->response(self::SUCCESS,'查询成功',$data);
        }
        else
        {
            $error = $this->obj->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }

    }


    //创建人列表接口
    public function actionCreateList(){
        $data = CrmClue::find()->select('creator_id,creator_name')->distinct(true)->all();

        $error = $this->obj->getFirstErrors();

        if (empty($error))
        {
            return $this->response(self::SUCCESS,'查询成功',$data);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }
    /**
     * @return array
     * 线索更换公海池
     */
    public function actionReplace()
    {
//        $this->post['clue_id'] = [12];
//        $this->post['clue_public_id'] = 2;

        $clue = $this->obj->find()->where(['in','id',$this->post['clue_id']])->all();
        $clue_public = new CluePublic();
        $clue_public_data = $clue_public->find()->where(['id'=>$this->post['clue_public_id']])->one();
        if (empty($clue_public_data))
        {
            return $this->response(self::FAIL,'提交的clue_public_id有误');
        }
        if (!empty($clue))
        {
            /** @var CrmClue $v */
            foreach ($clue as $v)
           {
               $v->clue_public_id = $this->post['clue_public_id'];
               $user = Yii::$app->user->identity;
               $v->updater_id = $user->id;              //最后修改人ID
               $v->updater_name = $user->name;          //最后修改人名字
               $v->updated_at = time();                 //最后修改时间
               $v->save(false);
           }
        }
        else
        {
            return $this->response(self::FAIL,'提交的ID有误');
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        //添加操作记录
        $operation = new ClueOperationRecord();
        $operation->create($this->post['clue_id'],$operation::UPDATE_GROUPING,'更换分组到：'.$clue_public_data['name'],$user->id,$user->name);

        return $this->response(self::SUCCESS,'更换成功');
    }

    /**
     * @return array
     * 提取线索到我的线索
     */
    public function actionExtract()
    {
//        $this->post['clue_id'] = [14,15];
//        $this->post['clue_public_id'] = [1];
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $this->post['administrator_id'] = $user->id;
        $this->post['administrator_name'] = $user->name;

        if ($user->isBelongCompany() && $user->isCompany()){

            if (isset($user->department->cluePublic))
            {
                //查看是否可以新增线索
                /** @var CrmClue $this->obj */
                $check_num = $this->obj->checkAddClue($user);

                $error = $this->obj->getFirstErrors();
                if (!empty($error)){
                    return $this->response(self::FAIL,reset($error));
                }

                if (count($this->post['clue_id']) > $check_num){
                    return $this->response(self::FAIL,'对不起，当前用户拥有线索已达上限');
                }
            }
        }


        /** @var CrmClue $clue */
        $clue = $this->obj->find()->where(['in','id',$this->post['clue_id']])->all();
        if (!empty($clue))
        {
            $count = 0;
            foreach ($clue as $v){
                $v->administrator_id = $user->id;
                $v->administrator_name = $user->name;
                $v->status = CrmClue::STATUS_EXTRACT;
                $v->is_abandon = 0;
                $v->extract_time = time();
                $v->clue_public_id = 0;
                $user = Yii::$app->user->identity;
                $v->updater_id = $user->id;              //最后修改人ID
                $v->updater_name = $user->name;          //最后修改人名字
                $v->updated_at = time();                 //最后修改时间
                $v->save(false);
                $count++;
                //统计埋点
                $niche = new CustomerExchangeList();
                $niche->clue(['id'=>$v->id,'from'=>'','administrator_id'=>$user->id,'province_id'=> isset($v->province_id) ? $v->province_id : 0,'city_id'=> isset($v->city_id) ? $v->city_id : 0,'district_id' => isset($v->district_id) ? $v->district_id : 0,'source_id'=>isset($v->source_id) ? $v->source_id : 0,'channel_id'=>isset($v->channel_id) ? $v->channel_id : 0]);

            }
        }
        else
        {
            return $this->response(self::FAIL,'提交的ID有误');
        }
        $clue_public = new CluePublic();
        /** @var CluePublic $clue_public_data */
        $clue_public_data= $clue_public->find()->where(['id'=>$this->post['clue_public_id']])->one();

        $clue_public_name = isset($clue_public_data->name) ? $clue_public_data->name : '';
        //添加操作记录
        $operation = new ClueOperationRecord();
        $operation->create($this->post['clue_id'],$operation::EXTRACT_CLUE,'从（'.$clue_public_name.'）线索公海提取线索',$user->id,$user->name);

        if (count($this->post['clue_id']) > 1)
        {
            $num = count($this->post['clue_id']) - $count;
            return $this->response(self::SUCCESS,"所选线索，提取成功：{$count}；提取失败：{$num}");
        }
        else
        {
            return $this->response(self::SUCCESS,'提取成功');
        }
    }

    /**
     * @return array
     * 分配线索给其他人
     */
    public function actionDistribution()
    {
//        $this->post['clue_id'] = [14,15];
//        $this->post['administrator_id'] = 78;
        $administrator = Administrator::findOne($this->post['administrator_id']);

        if (empty($administrator))
        {
            return $this->response(self::FAIL,'负责人不存在');
        }
        if (isset($administrator->department->cluePublic))
        {
            //查看是否可以新增线索
            /** @var CrmClue $this->obj */
            $check_num = $this->obj->checkAddClue($administrator);
            $error = $this->obj->getFirstErrors();
            if (!empty($error)){
                return $this->response(self::FAIL,reset($error));
            }

            if (count($this->post['clue_id']) > $check_num){
                return $this->response(self::FAIL,'对不起，当前用户拥有线索已达上限');
            }
        }

        /** @var CrmClue $clue */
        $clue = $this->obj->find()->where(['in','id',$this->post['clue_id']])->all();
        if (!empty($clue))
        {

            $count = 0;
            foreach ($clue as $v)
            {
                $v->administrator_id = $this->post['administrator_id'];
                $v->administrator_name = $administrator['name'];
                $v->status = CrmClue::STATUS_DISTRIBUTION;
                $v->is_abandon = 0;
                $v->clue_public_id = 0;
                /** @var Administrator $user */
                $user = Yii::$app->user->identity;
                $v->updater_id = $user->id;              //最后修改人ID
                $v->updater_name = $user->name;          //最后修改人名字
                $v->updated_at = time();                 //最后修改时间
                $v->distribution_at = time();            //分配线索时间
                $v->save(false);
                $count++;
                //统计埋点
                $niche = new CustomerExchangeList();
                $niche->clue(['id'=>$v->id,'from'=>'','administrator_id'=>$user->id,'province_id'=> isset($v->province_id) ? $v->province_id : 0,'city_id'=> isset($v->city_id) ? $v->city_id : 0,'district_id' => isset($v->district_id) ? $v->district_id : 0,'source_id'=>isset($v->source_id) ? $v->source_id : 0,'channel_id'=>isset($v->channel_id) ? $v->channel_id : 0,'change']);
            }
        }
        else
        {
            return $this->response(self::FAIL,'提交的线索ID有误');
        }
        $user = Yii::$app->user->identity;
        //添加操作记录
        $operation = new ClueOperationRecord();
        $operation->create($this->post['clue_id'],$operation::DISTRIBUTION_CLUE,'分配线索新负责人为：'.$administrator['name'],$user->id,isset($user->name)?$user->name:'');

        if (count($this->post['clue_id']) > 1)
        {
            $num = count($this->post['clue_id']) - $count;
            return $this->response(self::SUCCESS,"所选线索，分配成功：{$count}；分配失败：{$num}");
        }
        else
        {
            return $this->response(self::SUCCESS,'分配成功');
        }

    }

    /**
     * @return array
     * 重复列表接口
     */
    public function actionRepeatList()
    {
//        $this->post['clue_id'] = 1;
//        $this->post['page'] = 1;
//        $this->post['page_num'] = 3;

        $this->post['page'] = isset($this->post['page']) ? $this->post['page'] : 1;      //页数如果没有传值 默认为1
        $this->post['page_num'] = isset($this->post['page_num']) ? $this->post['page_num'] : 20;  //偏移量如果没有传值 默认为20
        $this->post['limit'] = ($this->post['page']-1) * $this->post['page_num']; //偏移量

        $clue = CrmClue::findOne($this->post['clue_id']);

        $data['data'] = $this->obj->repeat($clue,$this->post);

        $data['page'] = $this->post['page'];
        $data['page_num'] = $this->post['page_num'];
        if (empty($data['data'])){
            $data['count'] = 0;
        }else{
            $data['count'] = (int)$this->obj->repeat($clue,$this->post,true);
        }

        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'查询成功',$data);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    /**
     * @return array
     * 所属部门接口
     */
    public function actionDepartment()
    {
        $user = Yii::$app->user->identity;
        $department_id = $this->getSubordinateDepartment($user);

        if($department_id){
            $data = CrmDepartment::find()->distinct()->select('id,name')->where(['in','id',$department_id])->asArray()->all();
        }else{
            $data = CrmDepartment::find()->distinct()->select('id,name')->asArray()->all();
        }
        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'查询成功',$data);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    /**
     * @return array
     * 放弃销售线索到公海
     */
    public function actionAbandon()
    {
//        $this->post['clue_id'] = [14,15];
//        $this->post['Reason'] = '我就是想放弃';
        if (!isset($this->post['clue_id']))
        {
            return $this->response(self::FAIL,'缺少参数clue_id');
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        if (count($this->post['clue_id']) > 1){
            $clue = CrmClue::find()->where(['in','id',$this->post['clue_id']])->all();
            $num  = 0;
            /** @var CrmClue $clue */
            foreach ($clue as $k=>$v){
                if($v->administrator->department_id == 0){
                    $num +=1;
                }else{
                    if ($v->administrator->department->clue_public_id == 0){
                        $num +=1;
                    }
                }
                if ((isset($v->administrator->department_id) && $v->administrator->department_id != 0) &&
                   (isset($v->administrator->department->clue_public_id) && $v->administrator->department->clue_public_id != 0)){
                    $this->obj->abandon($v['id'],$v->administrator->department->clue_public_id);

                    //添加过跟进你记录的，标记改为不是新线索
                    $obj = new CrmClue();
                    /** @var CrmClue $clue_one */
                    $clue_one = $obj->find()->where(['id'=>$v['id']])->one();
                    $clue_one->is_new = CrmClue::IS_NEW_NO;
                    $clue_one->save();

                    //统计埋点
                    $niche = new CustomerExchangeList();
                    $niche->clue(['id'=>$v['id'],'from'=>'','administrator_id'=>$user->id,'province_id'=> isset($v['province_id']) ? $v['province_id'] : 0,'city_id'=> isset($v['city_id']) ? $v['city_id'] : 0,'district_id' => isset($v['district_id']) ? $v['district_id'] : 0,'source_id'=>isset($v['source_id']) ? $v['source_id'] : 0,'channel_id'=>isset($v['channel_id']) ? $v['channel_id'] : 0],'giveup');

                    $clue_public = new CluePublic();

                    //放弃到公海添加操作记录
                    $clue_public_data= $clue_public->find()->where(['id'=>$v->administrator->department->clue_public_id])->one();
                    $clue_public_name = isset($clue_public_data->name) ? $clue_public_data->name : '';
                    //记录操作记录
                    $operation = new ClueOperationRecord();
                    $operation->create($v['id'],$operation::GIVE_UP_CLUE,'放弃线索到（'.$clue_public_name.'）线索公海，放弃原因为：'.$this->post['Reason'],$user->id,$user->name);

                }
            }

            if (count($this->post['clue_id']) > 1)
            {
                $count = count($this->post['clue_id']) - $num;
                return $this->response(self::SUCCESS,"所选线索，放弃成功：{$count}；放弃失败：{$num}");
            }
            else
            {
                return $this->response(self::SUCCESS,'销售线索数据放弃成功');
            }

        }else{
            /** @var CrmClue $clue_obj */
            $clue_obj = CrmClue::find()->where(['in','id',$this->post['clue_id']])->one();

            if ($clue_obj->administrator->department_id == 0)
            {
                return $this->response(self::FAIL,'对不起，当前用户没有启用公司与部门，不能放弃线索。');
            }
            if ($clue_obj->administrator->department->clue_public_id == 0)
            {
                return $this->response(self::FAIL,'对不起，当前用户所在部门没有线索公海，不能放弃线索。');
            }

            $data = $this->obj->abandon($this->post['clue_id'],$clue_obj->administrator->department->clue_public_id);

            //添加过跟进你记录的，标记改为不是新线索
            $obj = new CrmClue();
            /** @var CrmClue $clue */
            $clue = $obj->find()->where(['in','id',$this->post['clue_id']])->all();
            foreach ($clue as $v){
                $v->is_new = CrmClue::IS_NEW_NO;
                $v->save();

                //统计埋点
                $niche = new CustomerExchangeList();
                $niche->clue(['id'=>$v->id,'from'=>'','administrator_id'=>$user->id,'province_id'=> isset($v->province_id) ? $v->province_id : 0,'city_id'=> isset($v->city_id) ? $v->city_id : 0,'district_id' => isset($v->district_id) ? $v->district_id : 0,'source_id'=>isset($v->source_id) ? $v->source_id : 0,'channel_id'=>isset($v->channel_id) ? $v->channel_id : 0],'giveup');

            }
            $clue_public = new CluePublic();


            //放弃到公海添加操作记录
            $clue_public_data= $clue_public->find()->where(['id'=>$clue_obj->administrator->department->clue_public_id])->one();
            $clue_public_name = isset($clue_public_data->name) ? $clue_public_data->name : '';
            $operation = new ClueOperationRecord();
            $operation->create($this->post['clue_id'],$operation::GIVE_UP_CLUE,'放弃线索到（'.$clue_public_name.'）线索公海，放弃原因为：'.$this->post['Reason'],$user->id,isset($user->name)?$user->name:'');

            $error = $this->obj->getFirstErrors();
            if (empty($error))
            {
                return $this->response(self::SUCCESS,'销售线索数据放弃成功');
            }
            else
            {
                return $this->response(self::FAIL,reset($error));
            }
        }

    }

    //负责人接口
    public function actionGetPartner(){
//        $this->post['company_id'] = '1';
        $data = Administrator::find()->select('id,name')->where(['company_id'=>$this->post['company_id']])->andWhere(['status'=>1])->all();
        return $this->response(self::SUCCESS,'查询成功',$data);
    }
    /**
     * @return array
     * 线索废弃接口
     */
    public function actionDiscarded(){
//        $this->post['clue_id'] = [15,14];
//        $this->post['Reason'] = '就废弃了，怎么着吧';
//        $this->post['clue_public_id'] = '2';
//        $this->post['Reason'] = isset($this->post['Reason']) ? $this->post['Reason'] : '';
        if (!isset($this->post['clue_id']))
        {
            return $this->response(self::FAIL,'缺少参数clue_id');
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;

        if (isset($this->post['clue_public_id']) && $this->post['clue_public_id'] != ''){
            $clue_public_id = $this->post['clue_public_id'];
        }else{
            $clue_public_id = isset($user->department->cluePublic->id) ? $user->department->cluePublic->id : 0;
        }
        if ((int)$clue_public_id < 1){
            return $this->response(self::FAIL,'对不起，当前用户所在部门没有线索公海，不能废弃线索。');
        }
        //如果是多条的话
        if (count($this->post['clue_id']) > 1){

            /** @var CrmClue $clue */
            $clue = CrmClue::find()->where(['in','id',$this->post['clue_id']])->all();
            $num  = 0;
            foreach ($clue as $k=>$v){
                if($v->administrator->department_id == 0){
                    $num +=1;
                }else{
                    if ($clue_public_id == 0){
                        $num +=1;
                    }
                }
                $this->obj->discarded($v['id'],$clue_public_id);

                //添加过跟进你记录的，标记改为不是新线索
                $obj = new CrmClue();
                $clue_one = $obj->find()->where(['id'=>$v['id']])->one();
                $clue_one->is_new = CrmClue::IS_NEW_NO;
                $clue_one->save();

                $clue_public = new CluePublic();

                //放弃到公海添加操作记录
                $clue_public_data= $clue_public->find()->where(['id'=>$clue_public_id])->one();
                $clue_public_name = isset($clue_public_data->name) ? $clue_public_data->name : '';
                //记录操作记录
                $operation = new ClueOperationRecord();
                $operation->create($v['id'],$operation::DISCARDED_CLUE,'废弃线索到（'.$clue_public_name.'）线索公海，废弃原因为：'.$this->post['Reason'],$user->id,$user->name);

            }

            if (count($this->post['clue_id']) > 1)
            {
                $count = count($this->post['clue_id']) - $num;
                return $this->response(self::SUCCESS,"所选线索，废弃成功：{$count}；废弃失败：{$num}");
            }
            else
            {
                return $this->response(self::SUCCESS,'销售线索数据废弃成功');
            }

        }
        //如果是单条的话
        else{
            $clue_obj = CrmClue::find()->where(['in','id',$this->post['clue_id']])->one();


            if ($clue_public_id == 0)
            {
                return $this->response(self::FAIL,'对不起，当前用户所在部门没有线索公海，不能废弃线索。');
            }
            $data = $this->obj->discarded($this->post['clue_id'],$clue_public_id);

            //添加过跟进你记录的，标记改为不是新线索
            $obj = new CrmClue();
            /** @var CrmClue $clue */
            $clue = $obj->find()->where(['in','id',$this->post['clue_id']])->all();
            foreach ($clue as $v){
                $v->is_new = CrmClue::IS_NEW_NO;
                $v->save();
            }
            $clue_public = new CluePublic();

            //放弃到公海添加操作记录
            $clue_public_data= $clue_public->find()->where(['id'=>$clue_public_id])->one();
            $clue_public_name = isset($clue_public_data->name) ? $clue_public_data->name : '';
            //记录操作记录
            $operation = new ClueOperationRecord();
            $operation->create($this->post['clue_id'],$operation::DISCARDED_CLUE,'废弃线索到（'.$clue_public_name.'）线索公海，废弃原因为：'.$this->post['Reason'],$user->id,$user->name);

            $error = $this->obj->getFirstErrors();
            if (empty($error))
            {
                return $this->response(self::SUCCESS,'销售线索数据废弃成功');
            }
            else
            {
                return $this->response(self::FAIL,reset($error));
            }
        }

    }

    /**
     * @return array
     * 线索删除接口
     */
    public function actionRemove(){
//        $this->post['clue_id'] = [14,15];

        if (!isset($this->post['clue_id']))
        {
            return $this->response(self::FAIL,'缺少参数clue_id');
        }
        /** @var Administrator $user */
        $count = $this->obj->remove($this->post);
        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            if (count($this->post['clue_id']) > 1)
            {
                $num = count($this->post['clue_id']) - $count;
                return $this->response(self::SUCCESS,"所选线索，删除成功：{$count}；删除失败：{$num}");
            }
            else
            {
                return $this->response(self::SUCCESS,'销售线索数据删除成功');
            }

        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    /**
     * @return array
     * 跟进状态
     */
    public function actionFollowStatus()
    {
//        $this->post['type'] = '1';
        $data = CrmClue::FOLLOW_STATUS;
        //不要已关闭给前端 type = 1的时候全查，
        if (!isset($this->post['type'])){
            unset($data[2]);
        }
        return $this->response(self::SUCCESS,'查询成功',$data);
    }

    /**
     * @return array
     * 自定义筛选
     */
    public function actionCustomFilter()
    {
        return $this->response(self::SUCCESS,'查询成功',CrmClue::CUSTOM_FILTER);
    }

    /**
     * @return array
     * 设置标签给线索
     */
    public function actionClueAddTag()
    {
//        $this->post['id'] = [15,14];
//        $this->post['label_id'] = 5;

        if (empty($this->post)){
            return $this->response(self::FAIL,'缺少参数');
        }

        /** @var CrmClue $clue */
        $clue = CrmClue::find()->where(['in','id',$this->post['id']])->all();
        $count = 0;
        foreach ($clue as $k=>$v){
            $v->label_id = $this->post['label_id'];
            $user = Yii::$app->user->identity;
            $v->updater_id = $user->id;              //最后修改人ID
            $v->updater_name = $user->name;          //最后修改人名字
            $v->updated_at = time();                 //最后修改时间
            $v->save(false);
            $count++;
        }

        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            if (count($this->post['id']) > 1)
            {
                $num = count($this->post['id']) - $count;
                return $this->response(self::SUCCESS,"所选线索，应用标签成功：{$count}；应用标签失败：{$num}");
            }
            else
            {
                return $this->response(self::SUCCESS,'线索数据标签应用成功');
            }

        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    /**
     * @return array
     * 清除线索标签
     */
    public function actionClueRemoveTag()
    {
//        $this->post['id'] = [15,14];

        if (!isset($this->post['id'])){
            return $this->response(self::FAIL,'缺少参数');
        }
        $this->post['label_id'] = 0;
        /** @var CrmClue $clue */
        $clue = CrmClue::find()->where(['in','id',$this->post['id']])->all();
        $count = 0;
        foreach ($clue as $k=>$v){
            $v->label_id = $this->post['label_id'];
            $user = Yii::$app->user->identity;
            $v->updater_id = $user->id;              //最后修改人ID
            $v->updater_name = $user->name;          //最后修改人名字
            $v->updated_at = time();                 //最后修改时间
            $v->save(false);
            $count++;
        }

        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            if (count($this->post['id']) > 1)
            {
                $num = count($this->post['id']) - $count;
                return $this->response(self::SUCCESS,"所选线索，清除标签成功：{$count}；清除标签失败：{$num}");
            }
            else
            {
                return $this->response(self::SUCCESS,'销售线索数据标签清除成功');
            }

        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    //成为客户
    public function actionBecomeCustomer()
    {

        //subject_type  0 企业  1  自然人
//        $this->post['subject_type'] = 1;
//        $this->post['clue_id'] = 1;
//        $this->post['company_name'] = '这是个客户12';
//        $this->post['company_remark'] = '这是一个备注1';
//        $this->post['administrator_id'] = 2;
//        $this->post['administrator_name'] = '管理员';
//        $this->post['department_id'] = 22;
//        $this->post['mobile'] = '18103789921';
//
//        $this->post['name'] = '张三';
//        $this->post['gander'] = 0;
//        $this->post['wechat'] = 'asdfas12312';
//        $this->post['tel'] = '010-1233123';
////        $this->post['phone'] = '1810137721';
//        $this->post['qq'] = '8791827987';
//        $this->post['email'] = 'www.12312312@163.com';
//
//        $this->post['call'] = '18101376621';
////        $this->post['birthday'] = '19870359';
//        $this->post['source'] = 1;
//        $this->post['source_name'] = '400电话';
//        $this->post['channel_id'] = 2;
//        $this->post['channel_name'] = '百度推广';
//        $this->post['department'] = '研发部';
//        $this->post['position'] = 'PHP开发工程师';
//        $this->post['native_place'] = '这是一个籍贯';
//        $this->post['interest'] = '这是一个兴趣爱好';
//        $this->post['customer_province_id'] = 2;
//        $this->post['customer_province_name'] = '陕西省';
//        $this->post['customer_city_id'] = 2;
//        $this->post['customer_city_name'] = '宝鸡市';
//        $this->post['customer_district_id'] = 2;
//        $this->post['customer_district_name'] = '吃鸡村';
//        $this->post['address'] = '吃鸡村的头目';
//        $this->post['remark'] = '这是一个线索备注';
//        $this->post['business_name'] = '1411234234214141';   //身份证号码

        $this->post['phone'] = $this->post['mobile'];

        if (!isset($this->post['subject_type']))
        {
            return $this->response(self::FAIL, '缺少参数subject_type');
        }

        if (!in_array($this->post['subject_type'],[0,1])){
            return $this->response(self::FAIL, 'subject_type参数错误');
        }
        /** @var CrmClue $clue */
        $clue = CrmClue::find()->where(['id'=>$this->post['clue_id']])->one();

//        echo json_encode($this->post);die;
        $param['company_name'] = isset($this->post['company_name']) ? $this->post['company_name'] :'';
        $param['subject_type'] = $this->post['subject_type'];
        $param['company_remark'] = isset($this->post['company_remark']) ? $this->post['company_remark'] : '';
        $param['administrator_id'] = isset($clue->administrator->id) ? $clue->administrator->id : '';
        $param['administrator_name'] = isset($clue->administrator->name) ? $clue->administrator->name : '';
        $param['department_id'] = isset($clue->departments->id) ? $clue->departments->id : '';
//
        $param['customer_name'] = isset($this->post['name']) ? $this->post['name'] : '';
        $param['gender'] = $this->post['gander'];
        $param['phone'] = isset($this->post['phone']) ? $this->post['phone'] : '';
        $param['wechat'] = isset($this->post['wechat']) ? $this->post['wechat'] : '';
        $param['qq'] = isset($this->post['qq']) ? $this->post['qq'] : '';
        $param['tel'] = isset($this->post['tel']) ? $this->post['tel'] : '';
        $param['caller'] = isset($this->post['call']) ? $this->post['call'] : '';
        $param['email'] = isset($this->post['email']) ? $this->post['email'] : '';
        $param['birthday'] = isset($this->post['birthday']) ? $this->post['birthday'] : '';
        $param['source'] = isset($this->post['source_id']) ? $this->post['source_id'] : '';
        $param['source_name'] = isset($this->post['source_name']) ? $this->post['source_name'] : '';
        $param['channel_id'] = isset($this->post['channel_id']) ? $this->post['channel_id'] : '';
        $param['position'] = isset($this->post['position']) ? $this->post['position'] : '';
        $param['department'] = isset($this->post['department']) ? $this->post['department'] : '';
        $param['customer_province_id'] = isset($this->post['province_id']) ? $this->post['province_id'] : '';
        $param['customer_province_name'] = isset($this->post['province_name']) ? $this->post['province_name'] : '';
        $param['customer_city_id'] = isset($this->post['city_id']) ? $this->post['city_id'] : '';
        $param['customer_city_name'] = isset($this->post['city_name']) ? $this->post['city_name'] : '';
        $param['customer_district_id'] = isset($this->post['district_id']) ? $this->post['district_id'] : '';
        $param['customer_district_name'] = isset($this->post['district_name']) ? $this->post['district_name'] : '';
        $param['street'] = isset($this->post['address']) ? $this->post['address'] : '';
        $param['customer_hobby'] = isset($this->post['interest']) ? $this->post['interest'] : '';
        $param['remark'] = isset($this->post['remark']) ? $this->post['remark'] : '';
        $param['level'] = isset($this->post['level']) ? $this->post['level'] : 0;
        $param['native_place'] = isset($this->post['native_place']) ? $this->post['native_place'] : '';
        $param['business_name'] = isset($this->post['business_name']) ? $this->post['business_name'] : '';

        $model = new CrmCustomerApi();

        $model->setScenario('create');

        $model->load($param, '');

        if (!$model->validate()){
            $error = $model->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }

        $rs = $model->customerAdd();

        //记录操作记录
        $operation = new ClueOperationRecord();
        if($this->post['subject_type'] == 1){
            $content = '线索转换为了个人客户';
        }else{
            $content = '线索转换为了企业客户';
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $operation->create($this->post['clue_id'],$operation::CREATE_CUSTOMER,$content,$user->id,$user->name);

        if (isset($rs['code']) && $rs['code'] == self::SUCCESS)
        {
            //更新线索表跟进状态为已关闭，主体ID
            $clue = $this->obj->findOne($this->post['clue_id']);
            $clue->business_subject_id = $rs['data']['business_id'];
            $clue->follow_status = CrmClue::FOLLOW_STATUS_CLOSE;
            $clue->transfer_at = time();
            $clue->status = 0;
            $user = Yii::$app->user->identity;
            $clue->updater_id = $user->id;              //最后修改人ID
            $clue->updater_name = $user->name;          //最后修改人名字
            $clue->updated_at = time();                 //最后修改时间
            $clue->save();

            //统计埋点
            $customer_model = new CustomerExchangeList();
            $customer_model->correct($rs['data']['business_id']);
         
            return $this->response(self::SUCCESS, '添加成功', $rs['data']);
        }
        else
        {
            return $this->response(self::FAIL, $rs['message'], isset($rs['data']) ? $rs['data'] : '');
        }
    }


    //自定义导航列表
    public function actionCustomList()
    {
//        $this->post['type'] = '0';
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new \backend\models\ClueCustomList();

        $model->load($this->post,'');
        $model->validate();
        $model->administrator = $administrator;
        $error = $model->getFirstErrors();

        if (empty($error))
        {
            return $this->response(self::SUCCESS,'获取成功',json_decode($model->select()->field));
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    //自定义导航列表
    public function actionCustomChange()
    {
//        $this->post['fields'] = array(
//            ['field'=>'name','field_name'=>'线索id','sort'=>'1','status'=>1,'is_update'=>0],
//            ['field'=>'customer_name','field_name'=>'姓名','sort'=>'2','status'=>1,'is_update'=>0],
//            ['field'=>'total_amount','field_name'=>'公司名称','sort'=>'3','status'=>1,'is_update'=>0],
//            ['field'=>'predict_deal_time','field_name'=>'创建时间','sort'=>'4','status'=>1,'is_update'=>1],
//            ['field'=>'progress','field_name'=>'线索来源','sort'=>'5','status'=>1,'is_update'=>1],
//            ['field'=>'win_rate','field_name'=>'所属部门','sort'=>'6','status'=>1,'is_update'=>1],
//            ['field'=>'next_follow_time','field_name'=>'创建人','sort'=>'7','status'=>1,'is_update'=>1],
//            ['field'=>'id','field_name'=>'负责人','sort'=>'8','status'=>1,'is_update'=>1],
//            ['field'=>'administrator_id','field_name'=>'最后维护人','sort'=>'9','status'=>1,'is_update'=>1],
//            ['field'=>'last_record_creator_name','field_name'=>'最后维护时间','sort'=>'10','status'=>1,'is_update'=>1],
//            ['field'=>'last_record','field_name'=>'跟进状态','sort'=>'11','status'=>1,'is_update'=>1],
//            ['field'=>'creator_name','field_name'=>'来源渠道','sort'=>'12','status'=>1,'is_update'=>1],
//            ['field'=>'creator_at','field_name'=>'标签','sort'=>'13','status'=>1,'is_update'=>1],
//        );
//        $this->post['type'] = '0';
        

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new \backend\models\ClueCustomList();

        $model->load($this->post,'');
        $model->validate();
        $model->administrator = $administrator;
        $error = $model->getFirstErrors();
        if (empty($error))
        {
            $model->save();
            return $this->response(self::SUCCESS,'获取成功',$model->fields);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }



    //获取下属部门
    public function getSubordinateDepartment($administrator){
        if($administrator->isCompany())
        {
            $Department = CrmDepartment::find()->select('id')->where(['like','path','%'.$administrator->department_id.'%',false])->all();
            if (!empty($Department)){
                $Department_id = '';
                foreach ($Department as $v){
                    $Department_id.=$v['id'].',';
                }
                $department_id = explode(',',substr($Department_id, 0, -1));
            }
            else
            {
                $department_id = $administrator->department_id;
            }
            return $department_id;
        }else{
            return false;
        }
    }


    //获取权限
    public function actionGetPermissionsAll()
    {
        /** @var \yii\rbac\ManagerInterface $auth */
        $auth = Yii::$app->get('administratorAuthManager');

        return $this->response(self::SUCCESS,'获取成功',$auth->getPermissionsByUser(Yii::$app->user->id));
    }

}
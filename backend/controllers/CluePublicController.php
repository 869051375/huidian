<?php
namespace backend\controllers;

use backend\fixtures\Administrator;
use common\models\CluePublic;
use common\models\Company;
use common\models\CrmClue;
use common\models\CrmDepartment;
use Yii;
use yii\filters\AccessControl;



class CluePublicController extends ApiController
{
    public $post;
    public $obj;

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
                        'actions' => ['add','list','organize','public-admin','edit','delete','change','details','clue-list','scene-list','change-clue-public-list'],
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
        $this->obj = new CluePublic();
    }

    /**
     * @return array
     * 新增公海
     */
    public function actionAdd()
    {

//        $this->post['name'] = '掘金的线索公海';
//        $this->post['new_move_time'] = 10;          //int  时间
//        $this->post['distribution_move_time'] = '20';
//        $this->post['follow_move_time'] = 30;
//        $this->post['personal_move_time'] = 40;
//        $this->post['most_num'] = 5;
//        $this->post['is_own'] = 1;
//        $this->post['department_id'] = [1,2,3,4];       //部门ID


        $user = Yii::$app->user->identity;
        //先判断提交的部门是否是同一个公司的，如果不是提示错误
        $department = CrmDepartment::find()->select('distinct (company_id)')->where(['in','id',$this->post['department_id']])->asArray()->all();
        if (count($department) >1)
        {
            return $this->resPonse(self::FAIL,'对不起，线索公海只能设置为一个公司内的部门。');
        }
        $this->post['company_id'] = (int)$department[0]['company_id'];
        $this->post['create_id'] = $user->id;
        $this->post['created_at'] = time();
        $this->obj->load($this->post,'');
        $data = $this->obj->save(true);

        //同步部门的公海关联ID
        $department = CrmDepartment::find()->where(['in','id',$this->post['department_id']])->all();
        foreach ($department as $v){
            $v->clue_public_id = $this->obj['id'];
            $v->save(false);
        }
        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'添加成功',$this->obj);
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    /**
     * @return array
     * 线索公海列表
     */
    public function actionList()
    {
//        $this->post['page'] = 1;        //页数
//        $this->post['page_num'] = 3;   //显示条数
        $limit = ($this->post['page']-1) * $this->post['page_num']; //偏移量

        /** @var \common\models\Administrator $user */
        $user = Yii::$app->user->identity;
        $department = new CrmDepartment();

        $department_id = $user->getTreeDepartmentId(true);

//        $department_id = $this->getSubordinateDepartment($user);

        if ($department_id){
            $clue_public_id = CrmDepartment::find()->distinct()->select('clue_public_id')->where(['in','id',$department_id])->asArray()->all();
            $clue_public_arr = array();
            foreach ($clue_public_id as $item){
                array_push($clue_public_arr,$item['clue_public_id']);
            }
            $data['data'] = CluePublic::find()->andWhere(['in','id',$clue_public_arr])->limit($this->post['page_num'])->offset($limit)->orderBy(['status'=>SORT_DESC,'id'=>SORT_DESC])->asArray()->all();

            $data['count'] = CluePublic::find()->andWhere(['in','id',$clue_public_arr])->limit($this->post['page_num'])->offset($limit)->orderBy(['status'=>SORT_DESC,'id'=>SORT_DESC])->count();
        }else{
            $data['data'] = $this->obj->find()->limit($this->post['page_num'])->offset($limit)->orderBy(['status'=>SORT_DESC,'id'=>SORT_DESC])->asArray()->all();
            $data['count'] = $this->obj->find()->count();
        }
        $data['page'] = $this->post['page'];
        foreach ($data['data'] as $k=>$v){
            $department_v_data = $department->find()->where(['clue_public_id'=>$v['id']])->all();
            $department_v_str = '';
            foreach ($department_v_data as $vv){
                $department_v_str .= $vv['name'].',';
            }
            $data['data'][$k]['department_name'] = substr($department_v_str, 0, -1);
        }
        $error = $this->obj->getFirstErrors();
        if (empty($error))
        {
            return $this->resPonse(self::SUCCESS,'查询成功',$data);
        }else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    public function actionDetails()
    {
//        $this->post['id'] = 4;
        if (!isset($this->post['id']))
        {
            return $this->response(self::FAIL,'缺少字段ID');
        }

        /**
         * @var CluePublic
         */
        $data = $this->obj->find()->where(['id'=>$this->post['id']])->one();
        if ($data){
            $data->create_id = (isset($data->administrator->name) ? $data->administrator->name : '')
                . (isset($data->administrator->department->parent) ? ( '【' .$data->administrator->department->parent->name. '/') : '【-')  .
                (isset($data->administrator->department) ? ($data->administrator->department->name . '】') : '/-】') .
                (($data->administrator->title != '') ? ( '【'.$data->administrator->title . '】') : '【-】') ;

            $department = CrmDepartment::find()->select('id')->where(['clue_public_id'=>$this->post['id']])->all();

            $attributes = $data->attributes;
            $attributes['department_id'] = $department ;
            $error = $data->getFirstErrors();
            if (empty($error))
            {
                return $this->response(self::SUCCESS,'查询成功',$attributes);
            }
            else
            {
                return $this->response(self::FAIL,reset($error));
            }
        }else{
            return $this->response(self::FAIL,'无此条记录');
        }

    }
    /**
     * @return array
     * 编辑线索公海
     */
    public function actionEdit()
    {
//        $this->post['id'] = 7;
//        $this->post['name'] = '掘金的线索公555';
//        $this->post['new_move_time'] = 103;          //int  时间
//        $this->post['distribution_move_time'] = '20';
//        $this->post['follow_move_time'] = 30;
//        $this->post['personal_move_time'] = 40;
//        $this->post['most_num'] = 5;
//        $this->post['is_own'] = 1;
//        $this->post['department_id'] = [1,2,3,4];       //部门ID

        //先把所有的部门关联公海清掉
        $department_del = CrmDepartment::find()->where(['clue_public_id'=>$this->post['id']])->all();
        foreach ($department_del as $v){
            $v->clue_public_id = 0;
            $v->save(false);
        }

        if (isset($this->post['department_id']))
        {
            //先判断提交的部门是否是同一个公司的，如果不是提示错误
            $department = CrmDepartment::find()->select('distinct (company_id)')->where(['in','id',$this->post['department_id']])->asArray()->all();
            if (count($department) >1)
            {
                return $this->resPonse(self::FAIL,'对不起，线索公海只能设置为一个公司内的部门。');
            }
            $this->post['company_id'] = (int)$department;

            //同步部门的公海关联ID
            $department = CrmDepartment::find()->where(['in','id',$this->post['department_id']])->all();
            foreach ($department as $v){
                $v->clue_public_id = $this->post['id'];
                $v->save(false);
            }
        }

        $one = $this->obj->findOne($this->post['id']);
        $one->load($this->post,'');
        $data = $one->save(true);
        $error = $one->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'编辑成功');
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    /**
     * @return array
     * 删除线索公海
     */
    public function actionDelete()
    {
//        $this->post['id'] = 1;
        /** @var CluePublic $clue_public */
        $clue_public = $this->obj->findOne($this->post['id']);
        if (empty($clue_public))
        {
            return $this->response(self::FAIL,'ID输入有误');
        }
        /** @var CrmDepartment $department */
        $department = CrmDepartment::find()->where(['clue_public_id'=>$this->post['id']])->all();
//        if (!empty($department))
//        {
//            return $this->response(self::FAIL,'对不起，当前公海下有线索或部门数据，不允许被删除。');
//        }
        $clue = CrmClue::find()->where(['clue_public_id'=>$this->post['id']])->andWhere(['!=','status','-1'])->all();
        if (!empty($clue))
        {
            return $this->response(self::FAIL,'对不起，当前公海下有线索或部门数据，不允许被删除。');
        }

        $data = $clue_public->delete();

        //如果删除成功 清除部门绑定公海关系
        if ($data)
        {
            foreach ($department as $item)
            {
                $item->clue_public_id = 0;
                $item->save(false);
            }
        }
        $error = $clue_public->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'删除成功');
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }

    }

    /**
     * @return array
     * 公海生效开关
     */
    public function actionChange()
    {
//        $this->post['clue_public_id'] = 2;      //线索公海ID
//        $this->post['status'] = 1;              //线索公海状态
        if (!isset($this->post['clue_public_id'])){
            return $this->response(self::FAIL,'缺少字段clue_public_id');
        }
        if (!isset($this->post['status'])){
            return $this->response(self::FAIL,'缺少字段status');
        }
        $clue_public = $this->obj->findOne($this->post['clue_public_id']);
        $clue_public->status = $this->post['status'];
        $data = $clue_public->save(false);
        if ($data)
        {
            return $this->response(self::SUCCESS,'修改成功');
        }else
        {
           return $this->response(self::FAIL,'修改失败');
        }
    }

    /**
     * @return array
     * 公海负责人
     * 如果有部门，部门下有人 则取部门下所有人
     * 如果没有部门没有公司，则取所有Administrator
     */
    public function actionPublicAdmin()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $obj = new CrmClue();
        $data = $obj->filterRole($user);
        if (!$data)
        {
            return $this->resPonse(self::SUCCESS,'查询成功',$data);
        }else
        {
            $data = \common\models\Administrator::find()->asArray()->all();
            return $this->resPonse(self::SUCCESS,'查询成功',$data);
        }
    }

    /**
     * @return array
     * 适用范围（组织结构）
     */
    public function actionOrganize()
    {
        $array = [];
        $user = Yii::$app->user->identity;
        $company = $user->company;
        if (isset($company))
        {
            $item = [
                'id' => $company->id,
                'label' => $company->name,
            ];
            $data = CrmDepartment::find()->select('id,name as label,parent_id,clue_public_id')->where(['company_id'=>$company->id])->asArray()->all();
            $item['children'] = $this->genTree($data,0);
            $array[] = $item;
        }else
        {
            $company = Company::find()->select('id,name')->all();
            foreach ($company as $k=> $v){
                $item = [
                    'id' => $v->id,
                    'label' => $v->name,
                ];
                $data = CrmDepartment::find()->select('id,name as label,parent_id,clue_public_id')->where(['company_id'=>$v->id])->asArray()->all();
                $item['children'] = $this->genTree($data,0);
                $array[] = $item;
            }
        }

        return $this->resPonse(self::SUCCESS,'查询成功',$array);

    }

    function genTree($a,$pid){
        $tree = array();
        foreach($a as $v){
            if ($v['clue_public_id'] == 0 ){
                $v['disabled'] = false;
            }else{
                $v['disabled'] = true;
            }
            if($v['parent_id'] == $pid){
                $v['children'] = $this->genTree($a,$v['id']);
                if($v['children'] == null){
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }


    //线索公海列表
    public function actionClueList(){
        /** @var \common\models\Administrator $user */
        $user = Yii::$app->user->identity;


        if (empty($user)){
            return $this->response(self::FAIL,'请重新登录');
        }

//        $this->post['page'] = 1;        //页数
//        $this->post['page_num'] = 2;    //偏移量

//        $this->post['label_id'] = 1;                            //标签（标签给我ID就好了）
//        $this->post['id'] = 1;                                  //
//        $this->post['administrator_id'] = 2;                  //按照负责人查询
//        $this->post['department_id'] = 2;                   //按照所属部门查询
//        $this->post['creator_id'] = 1;                      //按照创建人查询
//        $this->post['follow_status'] = 1;                   //按照跟进状态查询
//        $this->post['created_at_start'] = '2018-10-21 00:00:00';  //按照创建时间
//        $this->post['updated_at'] = [1533887018,1533887019];  //按照最后修改时间
//

        //是否为新线索
//        $this->post['is_new'] = 1;
        //是否为放弃
//        $this->post['is_abandon'] = 1;

        //是否为废弃
//        $this->post['status'] = 4;
//        //自定义筛选
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

//        $this->post['clue_public_id'] = 'all';

        if (!isset($this->post['page']) || $this->post['page'] <= 1){
            $this->post['page'] = 1;
        }
        if (!isset($this->post['page_num'])){
            return $this->response(self::FAIL,'缺少参数page_num');
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

        $this->post['page'] = ($this->post['page']-1) * $this->post['page_num']; //偏移量

        $clue = new CrmClue();
//        var_dump($this->post);die;
        $clue->load($this->post,'');
        //切换公海如果要是有公海ID的话直接用
        if (isset($this->post['clue_public_id']) && ($this->post['clue_public_id'] != 0 || $this->post['clue_public_id'] != ''))
        {
            if ($this->post['clue_public_id'] == 'all')
            {
                if (!$user->isBelongCompany() && !$user->isCompany()){
                    $clue_public = CluePublic::find()->select('id,name')->orderBy('id ASC')->all();
                    $clue_public_arr = array();
                    foreach ($clue_public as $item)
                    {
                        if ($item['id'] != 0){
                            array_push($clue_public_arr,$item['id']);
                        }
                    }
                    $clue->clue_public_id = isset($clue_public_arr) ? $clue_public_arr : -1;
                    $clue_public_name = isset($clue_public->name) ? $clue_public->name : '全部线索公海';
                }else{
                    $department_id = $user->getTreeDepartmentId(true);

                    if ($department_id)
                    {
                        $clue_public_id = CrmDepartment::find()->distinct()->select('clue_public_id')->where(['in','id',$department_id])->asArray()->all();
                        $clue_public_arr = array();
                        foreach ($clue_public_id as $item)
                        {
                            if ($item['clue_public_id'] != 0){
                                array_push($clue_public_arr,$item['clue_public_id']);
                            }
                        }
                    }
                    $clue->clue_public_id = isset($clue_public_arr) ? $clue_public_arr : -1;
                    $clue_public_name = '全部线索公海';
                }
            }
            else
            {
                $clue_public = CluePublic::find()->select('id,name')->where(['id'=>$this->post['clue_public_id']])->one();
                if ($clue_public)
                {
                    $clue->clue_public_id = $clue_public->id;
                    $clue_public_name = $clue_public->name;
                }
                else
                {
                    return $this->response(self::FAIL,'无效的参数clue_public_id');
                }
            }
        }else{
            if (!isset($user->department->clue_public_id) || $user->department->clue_public_id ==0){

                if (!$user->isBelongCompany() && !$user->isCompany()){
                    $clue_public = CluePublic::find()->select('id,name')->orderBy('id ASC')->one();
                    $clue->clue_public_id = isset($clue_public->id) ? $clue_public->id : -1;
                    $clue_public_name = isset($clue_public->name) ? $clue_public->name : '线索公海';
                }
                else{
                    $department_id = $user->getTreeDepartmentId(true);
                    $clue_public = CluePublic::find()->select('id,name')->where(['in','department_id',$department_id])->orderBy('id ASC')->one();
                    $clue->clue_public_id = isset($clue_public->id) ? $clue_public->id : -1;
                    $clue_public_name = isset($clue_public->name) ? $clue_public->name : '线索公海';
                }
            }else{
                $clue->clue_public_id = $user->department->clue_public_id;
                $clue_public_name = isset($user->department->cluePublic->name) ? $user->department->cluePublic->name : '';
            }
        }

        //获取总数量，如果查询条数大于等于总数量默认取总数量
        $count = $clue->getPublicList(1);
        if (!isset($this->post['page_num']) >= $count){
            $this->post['page_num'] = $count;
        }

        $clue->page = isset($this->post['page']) ? $this->post['page'] : 0;
        $clue->page_num = isset($this->post['page_num']) ? $this->post['page_num'] : 20;        //偏移量默认为20

        $data = $clue->getPublicList();
        $data_arr = array();
        foreach ($data as $k=>$v){
            $attributes = $v->attributes;
            $attributes['label_name'] = isset($v->tag->name) ? $v->tag->name : '';
            $attributes['channel_name'] = isset($v->channel->name) ? $v->channel->name : '';
            $attributes['source_name'] = isset($v->source->name) ? $v->source->name : '';
            $attributes['label_color'] = isset($v->tag->color) ? $v->tag->color : '';
            $attributes['clue_public_name'] = isset($v->cluePublic->name) ? $v->cluePublic->name : '';
            $data_arr['data'][$k] = $attributes;
        }
        $data_arr['page'] = $this->post['page'];
        $data_arr['page_num'] = $this->post['page_num'];
        $data_arr['count'] = $count;
        $data_arr['clue_public_name'] = $clue_public_name;
        $data_arr['clue_public_id'] = $clue->clue_public_id;

        if ($data_arr)
        {
            return $this->response(self::SUCCESS,'查询成功',$data_arr);
        }else
        {
            return $this->response(self::FAIL,'查询失败');
        }

    }
    
    
    //场景列表接口
    public function actionSceneList()
    {
        /** @var \common\models\Administrator $user */
        $user = Yii::$app->user->identity;
        $department_id = $user->getTreeDepartmentId(true);
        if ($department_id)
        {
            $clue_public_id = CrmDepartment::find()->distinct()->select('clue_public_id')->where(['in','id',$department_id])->asArray()->all();
            $clue_public_arr = array();
            foreach ($clue_public_id as $item)
            {
                array_push($clue_public_arr,$item['clue_public_id']);
            }
            $data = CluePublic::find()->select('id,name')->andWhere(['in','id',$clue_public_arr])->orderBy('id ASC')->all();
        }
        else
        {
            $data = CluePublic::find()->select('id,name')->orderBy('id ASC')->all();
        }
        return $this->response(self::SUCCESS,'查询成功',$data);
    }

    //跟换公海池公海列表
    public function actionChangeCluePublicList()
    {
        $user = Yii::$app->user->identity;
//        $department_id = $this->getSubordinateDepartment($user);
        $department_id = $user->getTreeDepartmentId(true);
        if ($department_id){
            $clue_public_id = CrmDepartment::find()->distinct()->select('clue_public_id')->where(['in','id',$department_id])->asArray()->all();
            $clue_public_arr = array();
            foreach ($clue_public_id as $item){
                array_push($clue_public_arr,$item['clue_public_id']);
            }
            $data = CluePublic::find()->select('id,name')->where(['status'=>1])->andWhere(['in','id',$clue_public_arr])->andWhere(['status'=>1])->orderBy('id ASC')->all();
        }else{
            $data = CluePublic::find()->select('id,name')->where(['status'=>1])->orderBy('id ASC')->all();
        }
        return $this->response(self::SUCCESS,'查询成功',$data);
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
}
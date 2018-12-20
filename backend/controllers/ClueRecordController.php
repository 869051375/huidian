<?php
namespace backend\controllers;

use common\models\ClueOperationRecord;
use common\models\ClueRecord;
use common\models\CrmClue;
use Yii;
use yii\filters\AccessControl;


class ClueRecordController extends ApiController
{

    private $post;
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
                        'actions' => ['index','list','mode','add'],
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
        $this->obj = new ClueRecord();
    }

    /**
     * @return array
     * 跟进记录提交接口
     */
    public function actionAdd()
    {

//        $this->post['clue_id'] = 1;                 //跟进线索ID
//        $this->post['follow_mode_id'] = 1;          //跟进方式ID
////
//        $this->post['start_at'] = '';
//        $this->post['end_at'] = '';
//        $this->post['next_follow_time'] = '';
//        $this->post['content'] = 'ask的积分拉所经历的看风景埃里克时间到了'; //跟进内容


        $this->post['start_at'] = !empty($this->post['start_at']) ? strtotime($this->post['start_at']) : 0;//开始时间
        $this->post['end_at'] = !empty($this->post['end_at']) ? strtotime($this->post['end_at']) : 0;//开始时间
        $this->post['next_follow_time'] = !empty($this->post['next_follow_time']) ? strtotime($this->post['next_follow_time']) : 0;//下次跟进时间
//        echo strtotime($this->post['start_at']);die;


        if (!isset($this->post['clue_id'])){
            return $this->response(self::FAIL,'缺少参数clue_id');
        }

        if (!isset($this->post['follow_mode_id'])){
            return $this->response(self::FAIL,'缺少参数follow_mode_id');
        }
        $follow_mode_name = '';
        foreach (ClueRecord::RECORD_MODE as $k=>$v){
            if ($v['id'] == $this->post['follow_mode_id']){
                $follow_mode_name = $v['name'];
            }
        }
        $this->post['created_at'] = time();
        $this->post['follow_mode_name'] =  $follow_mode_name; //跟进方式名称
        if (empty($follow_mode_name)){
            return $this->response(self::FAIL,'非法参数follow_mode_id');
        }

        $user = Yii::$app->user->identity;
        $this->post['creator_id'] = $user->id;             //跟进人ID
        $this->post['creator_name'] = $user->name;             //跟进人名字

        //添加过跟进你记录的，标记改为不是新线索
        $obj = new CrmClue();
        $clue = $obj->find()->where(['id'=>$this->post['clue_id']])->one();
        $clue->is_new = CrmClue::IS_NEW_NO;
        $clue->follow_status = CrmClue::FOLLOW_STATUS_CONTACT;
        $user = Yii::$app->user->identity;
        $clue->updater_id = $user->id;              //最后修改人ID
        $clue->updater_name = $user->name;          //最后修改人名字
        $clue->updated_at = time();                 //最后修改时间
        $clue->save();


        //添加操作线索
        $operation = new ClueOperationRecord();
        $operation->clue_id = $this->post['clue_id'];
        $operation->content = '跟进了销售线索';
        $operation->creator_id = $user->id;
        $operation->creator_name = $user->name;
        $operation->created_at = time();
        $operation->item = $operation::FOLLOW_UP_CLUE;
        $operation->save(false);

        $this->obj->load($this->post,'');
        $data = $this->obj->save(true);
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

    //跟进记录列表接口
    public function actionList()
    {
//        $this->post['clue_id'] = 2;
        if (!isset($this->post['clue_id'])){
            return $this->response(self::FAIL,'缺少参数clue_id');
        }
        $data = $this->obj->find()->where(['clue_id'=>$this->post['clue_id']])->orderBy('id desc')->all();
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

    //跟进方式接口
    public function actionMode()
    {
        return $this->response(self::SUCCESS,'查询成功',ClueRecord::RECORD_MODE);
    }

}
<?php
namespace backend\controllers;

use backend\fixtures\Administrator;
use common\models\ClueOperationRecord;
use common\models\CluePublic;
use common\models\Company;
use common\models\CrmClue;
use common\models\CrmDepartment;
use Yii;
use yii\filters\AccessControl;



class ClueOperationRecordController extends ApiController
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
                        'actions' => ['list'],
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
    }

    /**
     * @return array
     * 操作记录列表
     */
    public function actionList()
    {
//        $this->post['clue_id'] = 2;

        if (!isset($this->post['clue_id']))
        {
            return $this->response(self::FAIL,'缺少参数clue_id');
        }
        $data = ClueOperationRecord::find()->select('item,content,creator_id,creator_name,created_at')->where(['clue_id'=>$this->post['clue_id']])->orderBy('id desc')->all();
        return $this->resPonse(self::SUCCESS,'查询成功',$data);
    }
}
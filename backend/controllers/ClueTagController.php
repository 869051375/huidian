<?php
namespace backend\controllers;

use common\models\Administrator;
use common\models\Tag;
use yii\filters\AccessControl;
use Yii;


class ClueTagController extends ApiController
{

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
                        'actions' => ['index','list','update','add'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    //标签列表
    public function actionList()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $post_arr['company_id'] = $user->company_id;
        $obj =  new Tag();
        $post_arr['type'] = $obj::TAG_CLUE;
        $obj->load($post_arr,'');
        $request = $obj->getClueList ();

        return $this->resPonse(self::SUCCESS,'查询成功',$request);
    }

    //修改标签
    public function actionUpdate()
    {
        $post = Yii::$app->request->post();

//        $post['data'][0]['name'] = '没用的客户';
//        $post['data'][0]['id'] = 2;
//        $post['data'][0]['color'] = '2222';
//        $post['data'][1]['name'] = '没用的客户';
//        $post['data'][1]['id'] = 3;
//        $post['data'][1]['color'] = '2222';

        $obj =  new Tag();
        $data = $obj->change($post['data']);
        $error = $obj->getFirstErrors();
        if (empty($error))
        {
            return $this->response(self::SUCCESS,'修改成功');
        }
        else
        {
            return $this->response(self::FAIL,reset($error));
        }
    }

    //新增标签
    public function actionAdd()
    {
        $post = Yii::$app->request->post();

//        $post['name'] = '好客户';
//        $post['color'] = '111';


        /** @var Administrator $user */
        $obj =  new Tag();
        $user = Yii::$app->user->identity;
        $post['company_id'] = $user->company_id;
        $post['type'] = $obj::TAG_CLUE;

        $obj->load($post,'');
        $data = $obj->inserts();
        $error = $obj->getFirstErrors();
        if (empty($error))
        {
            return $this->response(200,'销售线索标签添加成功');
        }
        else
        {
            return $this->response(400,reset($error));
        }

    }

}
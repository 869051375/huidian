<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/11
 * Time: 下午5:10
 */

namespace backend\controllers;


use backend\models\OpportunityRecordForm;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityRecord;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class OpportunityRecordController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['create'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['opportunity/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($is_validate = '0', $id= null)
    {
        $model = new OpportunityRecordForm();

//        var_dump(Yii::$app->request->post());die;
        if($model->load(\Yii::$app->request->post()))
        {
            if($is_validate)
            {
                return ActiveForm::validate($model);
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record = $model->save();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($record)
            {
                if($id)
                {
                    //用户客户列表添加商机跟进记录后的返回数据
                    $records = CrmOpportunityRecord::find()->select(['created_at', 'creator_name', 'next_follow_time', 'content'])->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
                    $obj = new CrmOpportunity();
                    $data = CrmOpportunity::find()->where(['id'=>$id])->one();
                    $data->progress = Yii::$app->request->post()['OpportunityRecordForm']['progress'];
                    $data->save(false);
                    return ['status' => 200, 'records' => $this->serializeData($records)];
                }
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '商机保存失败:'.reset($model->getFirstErrors())];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
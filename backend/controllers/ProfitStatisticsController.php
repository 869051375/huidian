<?php

namespace backend\controllers;

use backend\models\UpdateRateForm;
use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\MonthProfitRecord;
use common\models\OrderCalculateCollect;
use common\models\PersonMonthProfit;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/11/6
 * Time: 上午10:10
 */

class ProfitStatisticsController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'team', 'department'],
                        'allow' => true,
                        'roles' => ['profit-statistics/*'],
                    ],
                    [
                        'actions' => ['update-rate'],
                        'allow' => true,
                        'roles' => ['profit-statistics/update-rate'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 个人利润表
     * @param int|null $id MonthProfitRecord.id
     * @return string
     */
    public function actionIndex($id = null)
    {
        if(empty($id))
        {
            $record = MonthProfitRecord::getLastFinishRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || $record->status != MonthProfitRecord::STATUS_SETTLEMENT_FINISH)
            {
                $record = MonthProfitRecord::getLastFinishRecord();
            }
        }

        if(null == $record)
        {
            $cond = ['year' => '0', 'month' => '0'];
        }
        else
        {
            $cond = ['year' => $record->year, 'month' => $record->month];
        }
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $query = PersonMonthProfit::find()->where($cond);
        if($administrator->isCompany())
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        /** @var PersonMonthProfit[] $models */
        $models = $query->orderBy(['expected_profit' => SORT_DESC])->all();
        return $this->render('index', ['models' => $models,'record' => $record]);
    }

    /**
     * 团队利润表
     * @param int|null $id MonthProfitRecord.id
     * @return string
     */
    public function actionTeam($id = null)
    {
        if(empty($id))
        {
            $record = MonthProfitRecord::getLastFinishRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || $record->status != MonthProfitRecord::STATUS_SETTLEMENT_FINISH)
            {
                $record = MonthProfitRecord::getLastFinishRecord();
            }
        }

        if(null == $record)
        {
            $cond = ['year' => '0', 'month' => '0'];
        }
        else
        {
            $cond = ['year' => $record->year, 'month' => $record->month];
        }

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $departmentQuery = CrmDepartment::find()->where(['level' => 2]);
        if($administrator->isCompany())
        {
            $departmentQuery->andWhere(['company_id' => $administrator->company_id]);
        }
        /** @var CrmDepartment[] $departments */
        $departments = $departmentQuery->all();

        $models = [];
        foreach($departments as $department)
        {
            $models[] = PersonMonthProfit::getByDepartment($department, $cond['year'], $cond['month'],$administrator->isCompany()?$administrator->company_id:'');
        }

        uasort($models, function($a, $b){
            if($a['expected_profit'] == $b['expected_profit']) return 0;
            return $a['expected_profit'] > $b['expected_profit'] ? -1 : 1;
        });

        $models = array_values($models);

        return $this->render('team', ['models' => $models, 'record' => $record]);
    }

    /**
     * 部门利润表
     * @param int|null $id MonthProfitRecord.id
     * @param int|null $department_id
     * @return string
     */
    public function actionDepartment($id = null, $department_id = null)
    {
        if(empty($id))
        {
            $record = MonthProfitRecord::getLastFinishRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || $record->status != MonthProfitRecord::STATUS_SETTLEMENT_FINISH)
            {
                $record = MonthProfitRecord::getLastFinishRecord();
            }
        }

        if(null == $record)
        {
            $cond = ['year' => '0', 'month' => '0'];
        }
        else
        {
            $cond = ['year' => $record->year, 'month' => $record->month];
        }

        $query = PersonMonthProfit::find() -> where($cond);

        $department = null;
        if($department_id)
        {
            $department = CrmDepartment::findOne($department_id);
            if($department)
            {
                $query->andWhere(['or', ['department_id' => $department_id], 'department_path like :path'], [':path' => $department->path.'-%']);
            }
        }
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $departmentQuery = CrmDepartment::find()->where(['level' => 2]);
        if($administrator->isCompany())
        {
            $departmentQuery->andWhere(['company_id' => $administrator->company_id]);
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        /** @var CrmDepartment[] $departments */
        $departments = $departmentQuery->all();

        $departmentList = ['0' => '全部部门'];
        foreach($departments as $item)
        {
            $departmentList[$item->id] = $item->name;
        }
        $query -> andWhere(['<>','administrator_id',0]);

        /** @var PersonMonthProfit[] $models */
        $models = $query->andWhere($cond)->orderBy(['expected_profit' => SORT_DESC])->all();

        return $this->render('department', [
                'models' => $models,
                'record' => $record,
                'departmentList' => $departmentList,
                'department' => $department,
            ]
        );
    }

    public function actionUpdateRate($is_validate = 0)
    {
        $model = new UpdateRateForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误!'];
    }
}
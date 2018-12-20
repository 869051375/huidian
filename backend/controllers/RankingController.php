<?php

namespace backend\controllers;

use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\MonthPerformanceRank;
use common\models\MonthProfitRecord;
use Yii;
use yii\filters\AccessControl;

class RankingController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','team','department'],
                        'allow' => true,
                        'roles' => ['ranking/*'],
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
            $record = MonthProfitRecord::getLastFinishUnSettlementRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || !$record->isPerformanceFinish())
            {
                $record = MonthProfitRecord::getLastFinishUnSettlementRecord2($id);
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
        $query = MonthPerformanceRank::find()->where($cond);
        if($administrator->isCompany())
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        /** @var MonthPerformanceRank[] $models */
        $models = $query->orderBy(['performance_reward' => SORT_DESC])->all();
        return $this->render('index', ['models' => $models, 'record' => $record]);
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
            $record = MonthProfitRecord::getLastFinishUnSettlementRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || !$record->isPerformanceFinish())
            {
                $record = MonthProfitRecord::getLastFinishUnSettlementRecord2($id);
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
            $models[] = MonthPerformanceRank::getByDepartment($department, $cond['year'], $cond['month'], $administrator->isCompany()?$administrator->company_id:'');
        }

        uasort($models, function($a, $b){
            if($a['performance_reward'] == $b['performance_reward']) return 0;
            return $a['performance_reward'] > $b['performance_reward'] ? -1 : 1;
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
            $record = MonthProfitRecord::getLastFinishUnSettlementRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($id);
            if(null == $record || !$record->isPerformanceFinish())
            {
                $record = MonthProfitRecord::getLastFinishUnSettlementRecord2($id);
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

        $query = MonthPerformanceRank::find()->where($cond);
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

        /** @var MonthPerformanceRank[] $models */
        $models = $query->andWhere($cond)->orderBy(['performance_reward' => SORT_DESC])->all();

        return $this->render('department', [
                'models' => $models,
                'record' => $record,
                'departmentList' => $departmentList,
                'department' => $department,
            ]
        );
    }
}

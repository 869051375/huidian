<?php
namespace backend\controllers;

use backend\models\OpportunityConfirmClaimForm;
use backend\models\OpportunityPublicSearch;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\CrmOpportunityRecord;
use common\models\OpportunityPublic;
use common\models\CrmOpportunity;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\redis\Cache;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OpportunityPublicController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['confirm-claim', 'validation', 'delete', 'detail', 'department-list', 'company-list', 'ajax-administrator-list','ajax-public-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'update', 'ajax-administrator-list', 'ajax-public-list'],
                        'allow' => true,
                        'roles' => ['opportunity-public/list'],
                    ],
                    [
                        'actions' => ['setting', 'validation', 'delete', 'detail', 'department-list', 'company-list'],
                        'allow' => true,
                        'roles' => ['opportunity-public/setting'],
                    ],
                    [
                        'actions' => ['confirm-claim'],
                        'allow' => true,
                        'roles' => ['opportunity-public/confirm-claim'],
                    ],
                    [
                        'actions' => ['confirm-claim'],
                        'allow' => true,
                        'roles' => ['opportunity-public/confirm-claim'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['opportunity-public/export'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 商机公海列表
     * @param int|null $id
     * @return string
     */
    public function actionList($id = null)
    {
        $search = new OpportunityPublicSearch();
        $search->load(Yii::$app->request->queryParams);
        $search->search();
        $resultCount=$search->searchCount();

        /** @var CrmOpportunityRecord[] $records */
        $records = CrmOpportunityRecord::find()->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->all();

		$opportunity = null;
		if($id)
		{
			$opportunity = $this->findOpportunityModel($id);	
		}
        // 如果是ajax请求，则是请求待办列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
        if(Yii::$app->request->isAjax)
        {
            return $this->renderAjax('upcoming', [
                'records' => $records,
                'opportunity' => $opportunity
            ]);
        }
        return $this->render('list', ['search' => $search, 'resultCount'=>$resultCount, 'records' => $records,  'opportunity' => $opportunity]);
    }

    /**
     * 商机公海导出
     */
    public function actionExport(){
        $url = Yii::$app->request->getReferrer();//獲取urL
        $export_code = Yii::$app->cache->get('opportunity-public-export-' . Yii::$app->user->id);
        if($export_code)
        {//重定向
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $search = new OpportunityPublicSearch();//model對象
        $search->load(Yii::$app->request->queryParams);//查詢條件載入
        $error = $this ->requiredBySpecial(Yii::$app->request->queryParams);


        if($error != null){
            Yii::$app->session->setFlash('error', $error);
            return $this->redirect($url);
        }
        if(!$search->validate())
        {
            Yii::$app->session->setFlash('error', reset($search->getFirstErrors()));
            return $this->redirect($url);
        }
        $count = $search->searchExcel(1);

        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['商机ID','商机创建时间','商机名称','商机状态','商机状态百分比','所属公海', '创建人','标签','所属客户id',
            '所属客户名称', '所属客户来源','商机最后跟进时间','作废原因','最后一次跟进记录','回收时间'];
        $csv->insertOne($header);

        for($i = 0; $i < $batch; $i++)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
//            $models = $query->offset($i*$batchNum)->limit($batchNum)->all();
            $models = $search->searchExcel(2,$batchNum,$i*$batchNum);

            foreach ($models as $opportunity)
            {
                $csv->insertOne([
                    "\t" . $opportunity['id'],
                    "\t" . date('Y-m-d H:i:s',$opportunity['created_at']),
                    "\t" . $this->trimStr($opportunity['name']),
                    "\t" . $this->trimStr($this->getExportStatusName($opportunity['status'])),
                    "\t" . $opportunity['progress'].'%',
                    "\t" . $this->trimStr($opportunity['public_name'] ? $opportunity['public_name'] : '--'),
                    "\t" . $this->trimStr($opportunity['creator_name']),
                    "\t" . $this->trimStr($opportunity['tag_name']),
                    "\t" . $opportunity['customer_id'],
                    "\t" . $this->trimStr($opportunity['customer_name']),
                    "\t" . $opportunity['source_name'],
                    "\t" . ($opportunity['last_record'] != 0) ? Yii::$app->formatter->asDatetime($opportunity['last_record']) : '0000-00-00 00:00:00',
                    "\t" . $this->trimStr($opportunity['invalid_reason']),
                    "\t" . $this->getContent($opportunity['id']),
                    "\t" . date('Y-m-d H:i:s',$opportunity['move_public_time']),
                ]);
            }
        }
        //记录操作日志
        AdministratorLog::logExport('商机公海',$count);

        $filename = date('YmdHis').rand(10000,99999).'_商机公海记录.csv';
        Yii::$app->cache->set('opportunity-public-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset, 'gbk//IGNORE', $csv);

    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }
    public function getContent($opportunity_id){
        $content = CrmOpportunityRecord::find() ->select('content')->where(['opportunity_id' => $opportunity_id])->orderBy(['created_at'=> SORT_DESC]) -> one();
        return $content['content'];
    }
    public function getExportStatusName($status)
    {
        if($status == 0)
        {
            return '未成交';
        }
        else if($status == 1)
        {
            return '申请中';
        }
        else if($status == 2)
        {
            return '已成交';
        }
        else if($status == 3)
        {
            return '已失败';
        }
        return '--';
    }

    public function requiredBySpecial($attribute)
    {
        if(empty($attribute['start_last_record_date']) && empty($attribute['end_last_record_date']) &&
            empty($attribute['last_record_creator_id']) && empty($attribute['opportunity_public_id']) &&
            empty($attribute['customer_phone']))
        {
            return  '请选择任意一项搜索才能导出！';
        }
    }

    /**
     * 商机公海配置列表
     * @return string
     */
    public function actionSetting()
    {
        /** @var Query $query */
        $query = OpportunityPublic::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('setting', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 新增商机公海
     * @return Response
     */
    public function actionCreate()
    {
        $model = new OpportunityPublic();
        $model->setScenario('insert');
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->defaultValues();
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
            }
            return $this->redirect(['setting']);
        }
        Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        return $this->redirect(['setting']);
    }

    /**
     * 更新商机公海
     * @param int $id
     * @return Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->defaultValues();
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
            }
            return $this->redirect(['setting']);
        }
        Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        return $this->redirect(['setting']);
    }

    /**
     * 删除商机公海
     * @return array
     */
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->department_id > 0 && $model->department)
        {
            return ['status' => 400,'message' => '该商机公海不可删除！'];
        }
        if(null != $model->opportunities)
        {
            return ['status' => 400, 'message' => '对不起，当前公海中还存在未提取或提取中的商机，请提取成功后，再删除！'];
        }
        $model->delete();
        return ['status' => 200];
    }

    /**
     * 商机公海详情
     * @param int $id
     * @return array
     */
    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model),'department' => $this->serializeData($model->department),'company' => $this->serializeData($model->company)];
    }

    /**
     * 商机确认提取
     * @return array
     * @throws \Exception
     */
    public function actionConfirmClaim()
    {
        $model = new OpportunityConfirmClaimForm();
        if($model->load(Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $success = $model->confirmClaim();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }

            if($success)
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    /**
     * 获取公司
     * @param null $keyword
     * @param null $company_id
     * @return array
     */
    public function actionCompanyList($keyword = null, $company_id = null)
    {
        $query = Company::find()->select(['id', 'name']);
        if(!empty($company_id))
        {
            $query->andWhere(['id' => $company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'company' => $this->serializeData($data)];
    }

    /**
     * 获取2级部门
     * @param null|string $keyword
     * @param null|int $company_id
     * @return array
     */
    public function actionDepartmentList($keyword=null, $company_id = null)
    {
        $opportunityIds = OpportunityPublic::find()->select('department_id')->all();
        $departmentIds = [];
        foreach($opportunityIds as $opportunityId)
        {
            $departmentIds[] = $opportunityId->department_id;
        }
        /** @var ActiveQuery $query */
        $query = CrmDepartment::find()->select(['id', 'name'])
            ->andWhere(['company_id' => $company_id])
            ->andWhere(['status' => CrmDepartment::STATUS_ACTIVE])
            ->andWhere(['level' => CrmDepartment::LEVEL_TWO])
            ->andWhere(['not in', 'id', $departmentIds])
            ->orderBy(['path' => SORT_ASC]);
        if(!empty($company_id))
        {
            $query->andWhere(['company_id' => $company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        return ['status' => 200, 'items' => $this->serializeData($query->all())];
    }

    /**
     * 最后跟进人列表
     * @param null $keyword
     * @return array
     */
    public function actionAjaxAdministratorList($keyword = null)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        /** @var Cache $cache */
        $cache = Yii::$app->get('redisCache');
        $data = $cache->get('opportunity-last-record-creator-id'.$administrator->id);
        if(empty($keyword))
        {
            if(null != $data)
            {
                return ['status' => 200, 'items' => $this->serializeData($data)];
            }
        }
        $ids = $this->opportunities();
        $ids = array_unique(ArrayHelper::getColumn($ids, 'last_record_creator_id'));
        $query = Administrator::find()->select(['a.id', 'a.name'])->alias('a')
            ->andWhere(['in', 'a.id', $ids]);

        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();

        if(empty($keyword))
        {
            $cache->set('opportunity-last-record-creator-id'.$administrator->id, $data, 60);
        }
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    private function opportunities()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = CrmOpportunity::find()->alias('o')->select('last_record_creator_id')->andWhere(['>', 'o.opportunity_public_id', 0]);

        //当前登录人与商机同部门或者商机对应部门的下属部门
        if($administrator->isBelongCompany() && $administrator->department_id)
        {
//            $query->andWhere(['or', ['o.department_id' => $administrator->department_id], ['o.department_id' => $administrator->department->parent_id]]);
            //当前登录人如果属于1级部门，则选取当前登录人所对应公司下的所有部门下的公海商机
            if($administrator->company_id && $administrator->department->parent_id == 0)
            {
                $query->joinWith(['company c']);
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
            //当前登录人如果属于1级以下部门，则选取相同商机公海的商机
            elseif ($administrator->department->opportunityPublic)
            {
                $query->andWhere(['o.opportunity_public_id' => $administrator->department->opportunityPublic->id]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        $query = $query->asArray()->all();
        return $query;
    }

    public function actionAjaxPublicList($keyword = null)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = OpportunityPublic::find()->select(['id', 'name']);
        if($administrator->isBelongCompany())
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    /**
     * @param int $id
     * @return OpportunityPublic
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = OpportunityPublic::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到商机公海信息');
        }
        return $model;
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new OpportunityPublic();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
    
    /**
     * @param int $id
     * @return CrmOpportunity
     * @throws NotFoundHttpException
     */
    private function findOpportunityModel($id)
    {
        $model = CrmOpportunity::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到商机信息');
        }
        return $model;
    }
}
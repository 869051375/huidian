<?php

namespace backend\controllers;


use common\models\BusinessSubjectApi;
use Yii;
use yii\filters\AccessControl;


class BusinessSubjectApiController extends ApiController
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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
//                    [
//                        'actions' => ['business-subject-detail', 'business-subject-basic-detail', 'business-subject-basic-update', 'business-customer'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
                    [
                        'actions' => ['business-subject-detail'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['business-subject-api/business-subject-detail'],
                    ],
                    [
                        'actions' => ['business-subject-basic-detail'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['business-subject-api/business-subject-basic-detail'],
                    ],
                    [
                        'actions' => ['business-subject-basic-update'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['business-subject-api/business-subject-basic-update'],
                    ],
                    [
                        'actions' => ['business-customer'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['business-subject-api/business-customer'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 基本信息查询企业
     * @return array
     */
    public function actionBusinessSubjectBasicDetail()
    {
        $post = Yii::$app->request->post();

        //customer_public_id  1我的客户 2 公海客户

//        $post['id'] = 1400;
//        $post['customer_public_id'] = 1;
        $model = new BusinessSubjectApi();

        $model ->load($post,'');

        $model ->setScenario('detail');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }
        $rs = $model->getBusinessSubjectBasicDetail($post);

        return $this->response(self::SUCCESS, '查询成功', $rs);


    }

    /**
     * 企业基本信息修改  传business_subject 主键id
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionBusinessSubjectBasicUpdate()
    {
        $arr = Yii::$app->request->post();

//        $arr['id']='330';
//        $arr['customer_id'] = '1504';
//        $arr['company_name']='heheheheh';
//        $arr['credit_code']='';
//        $arr['register_status']='';
//        $arr['enterprise_type']='';
//        $arr['company_type_id']='';
//        $arr['legal_person_name']='';
//        $arr['registered_capital']='';
//        $arr['operating_period_begin']='2018-10-10 00:00:00';
//        $arr['operating_period_end']='2018-11-11 00:00:00';
//        $arr['register_unit']='';
//        $arr['province_id']='1';
//        $arr['province_name']='北京啊';
//        $arr['district_id']='3';
//        $arr['district_name']='朝阳区啊';
//        $arr['city_id']='2';
//        $arr['city_name']='北京啊';
//        $arr['address']='';
//        $arr['scope']='';
//        $arr['industry_id']='13';
//        $arr['industry_name']='';
//        $arr['tax_type']='';
//        $arr['official_website']='';
//        $arr['filing_tel']='';
//        $arr['filing_email']='';
//        $arr['company_remark']='';

        $model = new BusinessSubjectApi();

        $model->load($arr, '');

        $model -> setScenario('update');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->businessSubjectBasicUpdate();

        if (!$rs) {

            return $this->response(self::FAIL, '修改失败');

        } else {

            return $this->response(self::SUCCESS, '修改成功', $rs);

        }

    }

    /**
     * 个人客户转企业客户
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionBusinessCustomer()
    {
        $arr = Yii::$app->request->post();

//        $arr['business_id'] = '333';
//        $arr['company_name'] = '测试深圳市沃特测试技术服务有限公司';
//        $arr['business_name'] = '';
//        $arr['industry_id'] = '';
//        $arr['industry_name'] = '';
//        $arr['tax_type'] = '';
//        $arr['credit_code'] = '';
//        $arr['register_status'] = '存续';
//        $arr['company_type_id'] = '';
//        $arr['enterprise_type'] = '';
//        $arr['legal_person_name'] = '杨志强';
//        $arr['registered_capital'] = '3500';
//        $arr['operating_period_begin'] = '2018-10-10 11:11:11';
//        $arr['operating_period_end'] = '2019-10-10 11:11:11';
//        $arr['register_unit'] = '宝安局';
//        $arr['business_province_id'] = '';
//        $arr['business_province_name'] = '';
//        $arr['business_city_id'] = '';
//        $arr['business_city_name'] = '';
//        $arr['business_district_id'] = '';
//        $arr['business_district_name'] = '';
//        $arr['address'] = '深圳市宝安区松岗街道白马路西侧富康泰厂房(一)一楼北角A(办公场所)';
//        $arr['scope'] = '电子电器产品(家用电器、灯具、信息技术设备、音视频产品、电源、玩具)的电磁兼容、可靠性、能效与安全性的检测;电子电器、五金、塑胶、纺织品、儿童玩具产品中限制物质的化学检测;食品营养成分及食品中健康危害物质的检测;水质、空气、土壤污染物检测、噪声检测。';
//        $arr['official_website'] = '';
//        $arr['filing_tel'] = '';
//        $arr['filing_email'] = '';
//        $arr['company_remark'] = '';
//
//
//        $arr['customer_id']=1501;
//        $arr['customer_name']='王五二';
//        $arr['gender']=0;
//        $arr['phone']='13245678965';
//        $arr['wechat']='';
//        $arr['qq']='598816903';
//        $arr['tel']='010-13245678';
//        $arr['caller']='010-13245678';
//        $arr['email']='1562@qq.com';
//        $arr['birthday']='1980-11-05';
//        $arr['source']=1;
//        $arr['source_name']='客户介绍';
//        $arr['channel_id']=400;
//        $arr['position']='售后部';
//        $arr['department']='售货员';
//        $arr['customer_province_id']=20;
//        $arr['customer_province_name']='广西';
//        $arr['customer_city_id']=739;
//        $arr['customer_city_name']='贵港市';
//        $arr['customer_district_id']=3647;
//        $arr['customer_district_name']='港北区';
//        $arr['street']='王五联系人地址';
//        $arr['customer_hobby']='吃喝玩乐买东西';
//        $arr['remark']='新增我的个人客户奥术大师多';
//        $arr['level']=1;
//        $arr['native_place']='甘肃';


        $model = new BusinessSubjectApi();

        $model->load($arr, '');

        $model -> setScenario('business_customer');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->customerBusiness();

        if ($rs == true) {
            return $this->response(self::SUCCESS, '个人转企业成功', $rs);
        } else {
            return $this->response(self::FAIL, '个人转企业失败');
        }
    }

}
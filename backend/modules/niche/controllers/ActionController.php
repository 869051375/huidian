<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\BelongNicheContacts;
use backend\modules\niche\models\BelongNicheList;
use backend\modules\niche\models\NicheCorrelationContracts;
use backend\modules\niche\models\NicheCorrelationOrder;
use backend\modules\niche\models\NicheFail;
use backend\modules\niche\models\NicheGiveUp;
use backend\modules\niche\models\NicheProductEdit;
use backend\modules\niche\models\NicheProductList;
use backend\modules\niche\models\NicheProductPrice;
use backend\modules\niche\models\NicheRest;
use backend\modules\niche\models\NicheTransform;
use backend\modules\niche\models\NicheUpdateBase;
use backend\modules\niche\models\NicheWin;
use backend\modules\niche\models\ProtectNicheForm;
use backend\modules\niche\models\ShareNicheForm;
use backend\modules\niche\models\TransferNicheForm;
use common\models\Administrator;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class ActionController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['share'],
                        'allow' => true,
                        'roles' => ['niche/action/share'], // todo 示例，实际开发的时候修改
                    ],
                    [
                        'actions' => ['transfer','share','protect','cancel-protect','give-up','correlation-order','correlation-contracts','transform','win','fail','change-product','update-base','belong-niche-list','belong-niche-contacts','product-list','product-price','reset'],
                        'allow' => true,
                        'roles' => ['@'], // todo 示例，实际开发的时候修改
                    ]
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/action/share",
     *     tags={"niche"},
     *     summary="商机分享（协作）接口",
     *     description="商机分享（协作）接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ShareNicheForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionShare()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new ShareNicheForm();
        $model->load(\Yii::$app->request->bodyParams);
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $count = $model->save();
//        if(() <= 0)
//        {
//            return $this->responseError('商机分享失败！');
//        }

        $num = count(explode(',', $model->niche_ids))-$count;
        return $this->responseMessage('所选商机，分享成功：'.$count.'；分享失败：'.$num.'。');


//        return $this->responseMessage('商机分享成功'.$count.'个！');
    }

    /**
     * @SWG\Post(path="/niche/action/transfer",
     *     tags={"niche"},
     *     summary="商机转移接口",
     *     description="商机转移接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/TransferNicheForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionTransfer()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new TransferNicheForm();
        $model->load(\Yii::$app->request->bodyParams);
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $count = $model->save();
//        if(() <= 0)
//        {
//            return $this->responseError('商机转移失败！');
//        }
        $num = count(explode(',', $model->niche_ids))-$count;
        return $this->responseMessage('所选商机，转移成功：'.$count.'；转移失败：'.$num.'。');
    }

    /**
     * @SWG\Post(path="/niche/action/protect",
     *     tags={"niche"},
     *     summary="商机保护接口",
     *     description="商机保护接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ProtectNicheForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionProtect()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new ProtectNicheForm();
        $model->load(\Yii::$app->request->bodyParams);
        $model->currentAdministrator = $administrator;
        $model->is_protect = 1;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $count = $model->save();
//        if(() <= 0)
//        {
//            return $this->responseError('商机保护失败！');
//        }

        $num = count(explode(',', $model->niche_ids))-$count;
        return $this->responseMessage('所选商机，保护成功：'.$count.'；保护失败：'.$num.'。');

//        return $this->responseMessage('商机保护操作成功'.$count.'个！');
    }

    /**
     * @SWG\Post(path="/niche/action/cancel-protect",
     *     tags={"niche"},
     *     summary="商机取消保护接口",
     *     description="商机取消保护接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ProtectNicheForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionCancelProtect()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new ProtectNicheForm();
        $model->load(\Yii::$app->request->bodyParams);
        $model->currentAdministrator = $administrator;
        $model->is_protect = 0;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $count = $model->save();
//        if(() <= 0)
//        {
//            return $this->responseError('商机取消保护失败！');
//        }

        $num = count(explode(',', $model->niche_ids))-$count;
        return $this->responseMessage('所选商机，取消保护成功：'.$count.'；取消保护失败：'.$num.'。');

//        return $this->responseMessage('商机取消保护成功'.$count.'个！');
    }

    /**
     * @SWG\Post(path="/niche/action/give-up",
     *     tags={"niche"},
     *     summary="放弃商机接口",
     *     description="放弃商机接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheGiveUp")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionGiveUp()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheGiveUp();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $count = $model->save();
//        if(() <= 0)
//        {
//            return $this->responseError('商机放弃失败！');
//        }

        $num = count(explode(',', $model->niche_ids))-$count;
        return $this->responseMessage('所选商机，放弃成功：'.(int)$count.'；放弃失败：'.$num.'。');

//        return $this->responseMessage('商机放弃成功'.$count.'个！');

    }

    /**
     *
     */

    /**
     * @SWG\Post(path="/niche/action/correlation-order",
     *     tags={"niche"},
     *     summary="商机关联订单接口",
     *     description="商机关联订单接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheCorrelationOrder")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionCorrelationOrder()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheCorrelationOrder();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if(($count = $model->save()) <= 0)
        {
            return $this->responseError('商机关联订单操作失败！');
        }

        return $this->responseMessage('商机关联订单操作成功！');

    }

    /**
     * @SWG\Post(path="/niche/action/correlation-contracts",
     *     tags={"niche"},
     *     summary="商机关联合同接口",
     *     description="商机关联合同接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheCorrelationContracts")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionCorrelationContracts()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheCorrelationContracts();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if(($count = $model->save()) <= 0)
        {
            return $this->responseError('商机关联合同操作失败！');
        }

        return $this->responseMessage('商机关联合同操作成功！');
    }

    /**
     * @SWG\Post(path="/niche/action/transform",
     *     tags={"niche"},
     *     summary="商机阶段变更接口",
     *     description="商机阶段变更接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheTransform")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionTransform()
    {
        $model = new NicheTransform();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = \Yii::$app->user->identity;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if(($count = $model->save()) <= 0)
        {
            return $this->responseError('商机阶段更新失败！');
        }

        return $this->responseMessage('商机阶段更新成功！');

    }

    /**
     * @SWG\Post(path="/niche/action/reset",
     *     tags={"niche"},
     *     summary="商机阶段激活接口",
     *     description="商机阶段激活接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheRest")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionReset()
    {
        $model = new NicheRest();
        $model->load(\Yii::$app->request->post(),'');
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if(($count = $model->save()) <= 0)
        {
            return $this->responseError('商机阶段更新失败！');
        }

        return $this->responseMessage('商机阶段更新成功！');

    }

    /**
     * @SWG\Post(path="/niche/action/win",
     *     tags={"niche"},
     *     summary="商机赢单接口",
     *     description="商机赢单接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheWin")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionWin()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheWin();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if(($count = $model->save()) <= 0)
        {
            return $this->responseError('商机赢单操作失败！');
        }

        return $this->responseMessage('商机赢单操作成功！');

    }

    /**
     * @SWG\Post(path="/niche/action/fail",
     *     tags={"niche"},
     *     summary="商机输单接口",
     *     description="商机输单接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheFail")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionFail()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheFail();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }
        $save = $model->save();
        if ($save)
        {
            if($save == 2)
            {
                return $this->responseMessage('商机标记输单成功，已回收至公海！');
            }
            else
            {
                return $this->responseMessage('商机输单操作成功！');
            }
        }

        return $this->responseError('商机输单操作失败！');

    }

    /**
     * @SWG\Post(path="/niche/action/change-product",
     *     tags={"niche"},
     *     summary="商机商品编辑接口",
     *     description="商机商品编辑接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheProductEdit")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionChangeProduct()
    {
        $model = new NicheProductEdit();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = \Yii::$app->user->identity;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if($model->save())
        {
            return $this->responseMessage('商机信息保存成功！');
        }

        return $this->responseError('商机信息保存失败！');

    }

    /**
     * @SWG\Post(path="/niche/action/update-base",
     *     tags={"niche"},
     *     summary="商机商品编辑基本信息接口",
     *     description="商机商品编辑基本信息接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheUpdateBase")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "成功"
     *    )
     * )
     *
     */
    public function actionUpdateBase()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheUpdateBase();
        $model->load(\Yii::$app->request->post(),'');
        $model->currentAdministrator = $administrator;
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        if($model->save())
        {
            return $this->responseMessage('商机信息保存成功！');
        }

        return $this->responseError('商机信息保存失败！');
    }

    /**
     * @SWG\Post(path="/niche/action/belong-niche-list",
     *     tags={"niche"},
     *     summary="商机所属客户列表接口",
     *     description="商机所属客户列表接口",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/BelongNicheList")
     *    )
     * )
     * @return array
     */

    public function actionBelongNicheList()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new BelongNicheList();
        $model->currentAdministrator = $administrator;
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/action/belong-niche-contacts",
     *     tags={"niche"},
     *     summary="商机所属客户联动联系人接口",
     *     description="商机所属客户联动联系人接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "id",
     *		description = "客户ID",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/BelongNicheContacts")
     *    )
     * )
     * @param $id integer
     * @return array
     */

    public function actionBelongNicheContacts($id)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new BelongNicheContacts();
        $model->currentAdministrator = $administrator;
        return $this->response(200,'查询成功',$model->select($id));
    }

    /**
     * @SWG\Post(path="/niche/action/product-list",
     *     tags={"niche"},
     *     summary="新增/编辑商品列表",
     *     description="新增/编辑商品列表",
     *     produces={"application/json"},
     *
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheProductList")
     *    )
     * )
     * @return array
     */

    public function actionProductList()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheProductList();
        $model->currentAdministrator = $administrator;
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/action/product-price",
     *     tags={"niche"},
     *     summary="商品地区选择",
     *     description="商品地区选择",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *		in = "query",
     *		name = "id",
     *		description = "客户ID",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheProductPrice")
     *    )
     * )
     * @param $id
     * @return array
     */

    public function actionProductPrice($id)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheProductPrice();
        $model->currentAdministrator = $administrator;
        return $this->response(200,'查询成功',$model->select($id));
    }

}
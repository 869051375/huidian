<?php
use backend\widgets\LinkPager;
use common\models\BusinessSubject;
use common\models\Order;
use common\models\OrderFollowRecord;
use common\models\Property;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/** @var \yii\web\View $this */
/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\Order[] $models */
/** @var \backend\models\OrderRenewalSearch $searchModel */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
$this->title = '续费订单';
$this->params['breadcrumbs'] = [$this->title];
?>
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <?php if (!in_array($actionUniqueId, ['order-list/need-refund', 'order-list/vest'])): //财务中的订单退款功能是独立的，任何时候都不显示上面的标签页
                $orderAuditedCount = Order::getOrderAuditedCount($administrator);
                $pendingPayCount = Order::getPendingPayCount($administrator);
                $pendingAssignCount = Order::getPendingAssignCount($administrator);
                $pendingServiceCount = Order::getPendingServiceCount($administrator);
                $timeoutCount = Order::getTimeoutCount($administrator);

                $pendingRenewalCount = Order::getPendingRenewalCount($administrator);
                $alreadyRenewalCount = Order::getAlreadyRenewalCount($administrator);
                $noRenewalCount = Order::getNoRenewalCount($administrator);
                ?>
                <ul class="nav nav-tabs">
                    <?php if (Yii::$app->user->can('order-renewal/list')): ?>
                        <li<?php if ($actionUniqueId == 'order-renewal/pending-renewal'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['pending-renewal']) ?>">待续费<?php if($pendingRenewalCount > 0):?><span class="text-danger">(<?= $pendingRenewalCount;?>)</span><?php endif;?></a>
                        </li>
                        <li<?php if ($actionUniqueId == 'order-renewal/already-renewal'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['already-renewal']) ?>">已续费<?php if($alreadyRenewalCount > 0):?><span class="text-danger">(<?= $alreadyRenewalCount;?>)</span><?php endif;?></a>
                        </li>
                        <li<?php if ($actionUniqueId == 'order-renewal/no-renewal'): ?> class="active"<?php endif; ?>>
                            <a href="<?= Url::to(['no-renewal']) ?>">无意向<?php if($noRenewalCount > 0):?><span class="text-danger">(<?= $noRenewalCount;?>)</span><?php endif;?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="page-select2-area">
                            <?php
                            $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                            $productUrl = \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']);
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['order-renewal/' . Yii::$app->controller->action->id]]); ?>
                            <div style="margin-bottom:10px">
                                <?= $form->field($searchModel, 'top_category_id', $labelOptions)->widget(Select2Widget::className(), [
                                    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                                    'itemsName' => 'categories',
                                    'selectedItem' => $searchModel->topCategory ? [$searchModel->topCategory->id => $searchModel->topCategory->name] : [],
                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
                                    'placeholderId' => '0',
                                    'placeholder' => '选择类目',
                                    'width' => '100px',
                                    'eventSelect' => new JsExpression("
                                        $('#category_id').val('0').trigger('change');
                                    ")
                                ]) ?>
                                <?= $form->field($searchModel, 'category_id', $labelOptions)->widget(Select2Widget::className(), [
                                    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                                    'itemsName' => 'categories',
                                    'selectedItem' => $searchModel->category ? [$searchModel->category->id => $searchModel->category->name] : [],
                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
                                    'placeholderId' => '0',
                                    'placeholder' => '选择类目',
                                    'width' => '100px',
                                    'eventOpening' => new JsExpression("
                                        var id = $('#top_category_id').val();
                                        serverUrl = '{$categoryUrl}'.replace('__parent_id__', id ? id : '-1');
                                    ")
                                ]) ?>
                                <?= $form->field($searchModel, 'product_id', $labelOptions)->widget(Select2Widget::className(), [
                                    'serverUrl' => \yii\helpers\Url::to(['product/ajax-list', 'category_id' => '__category_id__']),
                                    'itemsName' => 'products',
                                    'selectedItem' => $searchModel->product ? [$searchModel->product->id => $searchModel->product->name] : [],
                                    'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
                                    'placeholderId' => '0',
                                    'placeholder' => '选择类目',
                                    'width' => '150px',
                                    'eventOpening' => new JsExpression("
                                        var id = $('#category_id').val();
                                        serverUrl = '{$productUrl}'.replace('__category_id__', id ? id : '-1');
                                    ")
                                ]) ?>
                                <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
                                    'selectedItem' => \backend\models\OrderSearch::getTypes(),
                                    'placeholderId' => '0',
                                    'placeholder' => '请选择类型',
                                    'width' => '120px',
                                    'options' => ['class' => 'form-control', 'prompt' => '请选择类型'],
                                    'static' => true,
                                ]) ?>
                                <?= $form->field($searchModel, 'keyword')->textInput() ?>
                                <?= $form->field($searchModel, 'status')->hiddenInput(['value' => Yii::$app->requestedAction->id]) ?>
                                <button type="submit" class="btn btn-default">搜索</button>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <?php if ($actionUniqueId == 'order-renewal/already-renewal'): ?>
                                        <th class="col-sm-2">原来订单信息</th>
                                    <?php else: ?>
                                        <th class="col-sm-2"></th>
                                    <?php endif; ?>
                                    <th class="col-sm-2">订单信息</th>
                                    <th class="col-sm-2">商品信息</th>
                                    <th class="col-sm-2">客户信息</th>
                                    <?php if ($actionUniqueId != 'order-renewal/already-renewal'): ?>
                                        <th class="col-sm-2">最近跟进情况</th>
                                        <th class="text-center col-sm-2">操作</th>
                                    <?php endif; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $order): ?>
                                    <tr>
                                        <?php if ($order->isRenewalWarning() && !$order->isAlreadyRenewalOrder()): ?>
                                            <td class="col-sm-2 text-center">
                                                <?php if ($order->end_service_cycle > 0): ?>
                                                    <h3 class="text-danger"><?= abs($order->renewalDate()); ?>天</h3>
                                                    <?php if ($order->end_service_cycle > time()): ?>
                                                        <p class="text-muted">距离服务到期剩余天数</p>
                                                    <?php else: ?>
                                                        <p class="text-muted">距离服务到期已过天数</p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <p class="text-muted">订单号：
                                                    <?php if (Yii::$app->user->can('order/info')): ?>
                                                        <a href="<?= Url::to(['order/info', 'id' => $order->id]) ?>"
                                                           class="text-info m-t-xs"><?= $order->sn; ?></a>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-muted">
                                                    服务开始：<?= Yii::$app->formatter->asDate($order->begin_service_time) ?></p>
                                                <p class="text-muted">
                                                    结束预估：<?= $order->estimate_service_time > 0 ? Yii::$app->formatter->asDate($order->estimate_service_time) : ''; ?></p>
                                                <p class="text-muted">服务周期：<?= $order->service_cycle; ?>个月<br>
                                                    (<?= $order->begin_service_cycle > 0 ? Yii::$app->formatter->asDate($order->begin_service_cycle) : ''; ?>
                                                    —<?= $order->end_service_cycle > 0 ? Yii::$app->formatter->asDate($order->end_service_cycle) : ''; ?>
                                                    )</p>
                                            </td>
                                        <?php elseif ($order->isAlreadyRenewalOrder()):
                                            $relatedRenewalOrder = $order->getRelatedRenewalOrder($order->renewal_order_id);
                                            ?>
                                            <td>
                                                <p class="text-muted">订单号：
                                                    <?php if (Yii::$app->user->can('order/info')): ?>
                                                        <a href="<?= Url::to(['order/info', 'id' => $order->id]) ?>"
                                                           class="text-info m-t-xs"><?= $order->sn; ?></a>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-muted">
                                                    下单时间：<?= Yii::$app->formatter->asDate($order->created_at); ?></p>
                                                <p class="text-muted">
                                                    付款时间：<?= Yii::$app->formatter->asDate($order->virtualOrder->payment_time); ?></p>
                                                <p class="text-muted">服务周期：<?= $order->service_cycle; ?>个月<br>
                                                    (<?= $order->begin_service_cycle > 0 ? Yii::$app->formatter->asDate($order->begin_service_cycle) : ''; ?>
                                                    —<?= $order->end_service_cycle > 0 ? Yii::$app->formatter->asDate($order->end_service_cycle) : ''; ?>
                                                    )</p>
                                            </td>
                                            <td>
                                                <?php if (!empty($relatedRenewalOrder)): ?>
                                                    <p class="text-muted">订单号：
                                                        <?php if (Yii::$app->user->can('order/info')): ?>
                                                            <a href="<?= Url::to(['order/info', 'id' => $relatedRenewalOrder->id]) ?>"
                                                               class="text-info m-t-xs"><?= $relatedRenewalOrder->sn; ?></a>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="text-muted">
                                                        下单时间：<?= Yii::$app->formatter->asDate($relatedRenewalOrder->created_at); ?></p>
                                                    <p class="text-muted">
                                                        付款时间：<?= Yii::$app->formatter->asDate($relatedRenewalOrder->virtualOrder->payment_time); ?></p>
                                                    <p class="text-muted">
                                                        服务周期：<?= $relatedRenewalOrder->service_cycle; ?>个月<br>
                                                        (<?= $relatedRenewalOrder->begin_service_cycle > 0 ? Yii::$app->formatter->asDate($relatedRenewalOrder->begin_service_cycle) : ''; ?>
                                                        —<?= $relatedRenewalOrder->end_service_cycle > 0 ? Yii::$app->formatter->asDate($relatedRenewalOrder->end_service_cycle) : ''; ?>
                                                        )</p>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <p class="text-muted">商品名称：<?= $order->product_name; ?></p>
                                            <p class="text-muted">服务地区：
                                                <?php if ($order->district_id): ?>
                                                    <?= $order->province_name; ?>—<?= $order->city_name; ?>—<?= $order->district_name; ?>
                                                <?php else: ?>
                                                    <?= $order->service_area; ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php if (!empty($order->businessSubject)): ?>
                                                <?php if ($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED): ?>
                                                    <?php if (!empty($order->businessSubject->company_name)): ?>
                                                        <p class="text-muted">
                                                            公司名称：<?= $order->businessSubject->company_name; ?></p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?= $order->businessSubject->region; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <p class="text-muted"><?= $order->user->name ?></>
                                            <p class="text-muted"><?= $order->user->phone; ?></p>
                                            <?php if ($order->renewal_order_id <= 0): ?>
                                                <span class="label label-warning">未续费</span>
                                            <?php else: ?>
                                                <?php if ($order->isPendingRenewal()): ?>
                                                    <span class="label label-primary">已续费</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (!$order->isAlreadyRenewal()): ?>
                                            <td>
                                                <?php if (!empty($order->lastOrderFollowRecord)): ?>
                                                    <?php if ($order->lastOrderFollowRecord['is_follow'] != OrderFollowRecord::FOLLOW_ACTIVE): ?>
                                                        <p class="text-muted">停止跟进</p>
                                                        <p class="text-muted"><?= $order->lastOrderFollowRecord['follow_remark'] ?></p>
                                                        <p class="text-muted"><?= Yii::$app->formatter->asDatetime($order->lastOrderFollowRecord['created_at'], 'yyyy-MM-dd HH:mm') ?></p>
                                                    <?php else: ?>
                                                        <p class="text-muted">跟进中</p>
                                                        <p class="text-muted"><?= $order->lastOrderFollowRecord['follow_remark'] ?></p>
                                                        <p class="text-muted"><?= Yii::$app->formatter->asDatetime($order->lastOrderFollowRecord['created_at'], 'yyyy-MM-dd HH:mm') ?></p>
                                                        <p class="text-muted">
                                                            下次跟进：<?= Yii::$app->formatter->asRelativeTime($order->lastOrderFollowRecord['next_follow_time']) ?></p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <!--以下是操作部分-->
                                        <?php if (!$order->isAlreadyRenewal()): ?>
                                            <td class="text-right" style="vertical-align: middle; text-align: center;">
                                                <?php if (Yii::$app->user->can('order-follow-record/create')): ?>
                                                    <span class="btn btn-xs btn-primary see-order-follow-record m-t-xs"
                                                          data-target="#order-follow-record-modal"
                                                          data-toggle="modal"
                                                          data-product-id="<?= $order->product_id ?>"
                                                          data-is-cancel="<?php if (!empty($order->lastOrderFollowRecord)): ?><?= $order->lastOrderFollowRecord['is_follow'] ? '0' : '1' ?><?php endif; ?>"
                                                          data-id="<?= $order->id ?>">跟进记录</span>
                                                <?php endif; ?>
                                                <?php if ($order->isPendingRenewal()): ?>
                                                    <?php if (Yii::$app->user->can('order-renewal/create')): ?>
                                                        <?php $q = $order->getRenewalOrdersQuery(); $c = $q ? $q->count() : 0; ?>
                                                        <span class="btn btn-xs btn-primary m-t-xs renewal-already-btn m-r-sm"
                                                              style="position: relative"
                                                              data-target="#renewal-already-modal"
                                                              data-toggle="modal"
                                                              data-id="<?= $order->id ?>">已续费<?php if($c > 0): ?>
                                                                <span class="label label-danger"
                                                                   style="position: absolute; right: -15px; top: -15px;"><?= $c ?></span><?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (Yii::$app->user->can('order-renewal/send-remind-sms')): ?>
                                                        <span class="btn btn-xs btn-primary send-remind-sms-btn m-t-xs"
                                                              data-target="#send-remind-sms-modal"
                                                              data-toggle="modal"
                                                              data-user-name="<?= $order->user->name ?>"
                                                              data-product-name="<?= $order->product_name ?>"
                                                              data-id="<?= $order->id ?>">给客户发送短信</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="11">
                                        <?=
                                        LinkPager::widget([
                                            'pagination' => $pagination
                                        ]);
                                        ?>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--跟进记录start-->
<?php if (Yii::$app->user->can('order-follow-record/create')): ?>
    <div class="modal fade" id="order-follow-record-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <?php
            $orderFollowRecordForm = new \backend\models\OrderFollowRecordForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['order-follow-record/create'],
                'validationUrl' => ['order-follow-record/create', 'is_validate' => 1],
                'enableAjaxValidation' => true,
                'id' => 'order-follow-record-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-3',
                        'offset' => 'col-sm-offset-3',
                        'wrapper' => 'col-sm-7',
                        'hint' => 'col-sm-offset-3 col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close close-btn" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">跟进记录</h4>
                </div>
                <div class="modal-body input_box">
                    <p id="delete-order-follow-record-hint">确定删除吗？</p>
                    <table class="table table-bordered table-hover order-follow-record-list">
                        <thead>
                        <tr>
                            <th>跟进时间</th>
                            <th>跟进状态</th>
                            <th>备注信息</th>
                            <th>跟进人</th>
                            <th>下次跟进时间</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="6"><span class="btn btn-default order-follow-record-add">添加跟进记录</span></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="order-follow-record-add-form">
                        <?= $form->field($orderFollowRecordForm, 'is_follow')->checkbox() ?>
                        <?= $form->field($orderFollowRecordForm, 'next_follow_time')->widget(DateTimePicker::className(), [
                            'clientOptions' => [
                                'format' => 'yyyy-mm-dd hh:00',
                                'language' => 'zh-CN',
                                'autoclose' => true,
                            ],
                            'clientEvents' => [],
                        ]) ?>
                        <?php $ajaxRenewalUrl = \yii\helpers\Url::to(['order-follow-record/ajax-renewal-product-list', 'product_id' => '__product_id__']); ?>
                        <?= /** @var Order $order */
                        $form->field($orderFollowRecordForm, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                            'serverUrl' => $ajaxRenewalUrl,
                            'itemsName' => 'products',
                            'nameField' => 'name',
                            'searchKeywordName' => 'keyword',
                            'eventOpening' => new JsExpression("
                            var id = $('#order-follow-record-form_product_id').val();
                            serverUrl = '{$ajaxRenewalUrl}'.replace('__product_id__', id ? id : '');
                        ")
                        ]); ?>
                        <?= $form->field($orderFollowRecordForm, 'follow_remark')->textarea(['maxlength' => 80]) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($orderFollowRecordForm, 'order_id', ['id' => 'order-follow-record-form_order_id']); ?>
                    <?= Html::activeHiddenInput($orderFollowRecordForm, 'product_id', ['id' => 'order-follow-record-form_product_id']); ?>
                    <div class="save-btn-order-follow-record" style="display: none;">
                        <button type="button" class="btn btn-default cancel-add-order-follow-record">取消</button>
                        <button type="submit" class="btn btn-primary" id="confirm-order-follow-record-add">确定</button>
                    </div>
                    <div class="delete-btn-order-follow-record" style="display: none;">
                        <button type="button" class="btn btn-default cancel-delete-order-follow-record">取消</button>
                        <button type="button" class="btn btn-primary" id="confirm-order-follow-record-delete">确定
                        </button>
                    </div>
                </div>
            </div>
            <?php
            $followRecordTemplate = '<tr><td>{time}</td><td>{follow}</td><td>{remark}</td><td>{creator_name}</td><td>{next_follow_time}</td></tr>';
            $ajaxFollowRecordInfoUrl = \yii\helpers\Url::to(['order-follow-record/ajax-list']);
            $this->registerJs(<<<JS
    var followRecordModal = $('#order-follow-record-modal');
    var followRecordTemplate = '{$followRecordTemplate}';
    $('.see-order-follow-record').click(function(){
        showList();
        followRecordModal.find('table tbody').empty();
        var id = $(this).attr('data-id');
        var product_id = $(this).attr('data-product-id');
        var isCancel = $(this).attr('data-is-cancel');
        if(isCancel === '1')
        {
            followRecordModal.find('.order-follow-record-add').hide();
        }
        else
        {
            followRecordModal.find('.order-follow-record-add').show();
        }
        $('#order-follow-record-form_order_id').val(id);
        $('#order-follow-record-form_product_id').val(product_id);
        $.get('{$ajaxFollowRecordInfoUrl}', {order_id:id}, function(rs){
            if(rs.status === 200)
            {
                var models = rs['models'];
                for(var i = 0; i < models.length; i++)
                {
                    var item = followRecordTemplate.replace('{time}', models[i]['created_at'])
                        .replace('{follow}', models[i]['is_follow'] ? '跟进中' : '停止跟进')
                        .replace('{remark}', models[i]['follow_remark'])
                        .replace('{creator_name}', models[i]['creator_name'])
                        .replace('{next_follow_time}', models[i]['next_follow_time']);
                    followRecordModal.find('table tbody').append(item);
                    $('#order-follow-record-modal').trigger('reset.yiiActiveForm');
                }
            }
            else
            {
                followRecordModal.find('.warning-active').text(rs.message);
            }
        }, 'json');
    });
    $('.order-follow-record-add').click(function(){
        $('.field-orderfollowrecordform-next_follow_time').show();
        $('.field-orderfollowrecordform-product_id').show();
        $('#order-follow-record-form').trigger('reset.yiiActiveForm');
        showAddDistrictPriceDetail();
    });
    $('.cancel-add-order-follow-record').click(function(){
        showList();
    });
    $('#orderfollowrecordform-is_follow').click(function(){
        if($('#orderfollowrecordform-is_follow').is(':checked')){
            $('.field-orderfollowrecordform-next_follow_time').show();
            $('.field-orderfollowrecordform-product_id').show();
        }else{
            $('.field-orderfollowrecordform-next_follow_time').hide();
            $('.field-orderfollowrecordform-product_id').hide();
        }
    });
    showList();
    function showList()
    {
        followRecordModal.find('.modal-title').text('跟进记录');
        $('.order-follow-record-add-form').hide();
        $('.order-follow-record-list').show();
        $('.save-btn-order-follow-record').hide();
        $('.delete-btn-order-follow-record').hide();
        $('#delete-order-follow-record-hint').hide();
        $('#order-follow-record-modal').find('.warning-active').text('');
    }
    
    function showAddDistrictPriceDetail()
    {
        followRecordModal.find('.modal-title').text('添加跟进记录');
        $('.order-follow-record-add-form').show();
        $('.save-btn-order-follow-record').show();
        $('.order-follow-record-list').hide();
        $('.delete-btn-order-follow-record').hide();
         $('#delete-order-follow-record-hint').hide();
        followRecordModal.find('.warning-active').text('');
    }
    $('#order-follow-record-form').on('beforeSubmit', function(){
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(rs){
            if(rs.status === 200)
            {
                form.trigger('reset.yiiActiveForm');
                var item = followRecordTemplate.replace('{time}', rs['model']['created_at'])
                        .replace('{follow}', rs['model']['is_follow'] ? '跟进中' : '停止跟进')
                        .replace('{remark}', rs['model']['follow_remark'])
                        .replace('{creator_name}', '{$administrator->name}')
                        .replace('{next_follow_time}', rs['model']['next_follow_time']);
                    followRecordModal.find('table tbody').append(item);
                showList();
                if(!rs['model']['is_follow']){
                    window.location.reload();
                }
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
    
    $('.close-btn').click(function() {
      window.location.reload();
    });
    
JS
            ) ?>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
        </div>
    </div>
<?php endif; ?>
<!--跟进记录end-->

<!--已续费列表start-->
<div class="modal fade" id="renewal-already-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \backend\models\OrderRenewalForm();
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['order-renewal/create'],
        'validationUrl' => ['order-renewal/validation'],
        'id' => 'renewal-already-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-8',
            ],
        ],
    ]); ?>
    <div class="modal-dialog  modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">已续费选择</h4>
            </div>
            <div class="modal-body input_box">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th class="col-sm-1"></th>
                                        <th class="col-sm-2">订单信息</th>
                                        <th class="col-sm-2">商品信息</th>
                                        <th class="col-sm-2">客户信息</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= Html::activeHiddenInput($model, 'order_id', ['id' => 'renewal-already-form_order_id']); ?>
            <?= Html::activeHiddenInput($model, 'renewal_order_id', ['id' => 'renewal-already-form_renewal_order_id']); ?>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php
    $ajaxOrderRenewalUrl = \yii\helpers\Url::to(['order-renewal/ajax-list']);
    $orderTemplate = '<tr data-renewal-order-id = ""><td><input type="radio" name="orderName" value="{id}"></td><td><p class="text-muted">订单号：{sn}</p><p class="text-muted">下单时间：{created_at}</p><p class="text-muted">付款时间：{payment_time}</p><p class="text-muted">服务周期：{service_cycle}个月<br>({begin_service_cycle}—{end_service_cycle})</p></td><td><p class="text-muted">商品名称：{product_name}</p><p class="text-muted">服务地区：{area_name}</p><p class="text-muted">公司名称：{business_name}</p><td><p class="text-muted">{user_name}<p class="text-muted">{user_phone}</p></td></tr>';
    $itemData = '<tr class="text-center"><td colspan="6">{data}</td></tr>';
    $this->registerJs("
     $('.renewal-already-btn').click(function(){
        $('#renewal-already-modal table tbody').empty();
        var id = $(this).attr('data-id');
        $('#renewal-already-form_order_id').val(id);
        var order_id = $('#renewal-already-form_order_id').val();
        $.get('{$ajaxOrderRenewalUrl}', {id:id}, function(rs){
            var orderTemplate = '{$orderTemplate}';
            var itemData = '{$itemData}';
            if(rs.status == 200)
            {
                if(rs.products == '')
                {
                    var item = itemData.replace('{data}', '暂无数据');
                    $('#renewal-already-modal table tbody').append(item);
                }
                for(var i in rs.products)
                {
                    var item = orderTemplate.replace('{id}', rs.products[i].id).replace('{sn}', rs.products[i].sn)
                    .replace('{created_at}', rs.products[i].created_at)
                    .replace('{payment_time}', rs.products[i].payment_time)
                    .replace('{service_cycle}', rs.products[i].service_cycle)
                    .replace('{begin_service_cycle}', rs.products[i].begin_service_cycle)
                    .replace('{end_service_cycle}', rs.products[i].end_service_cycle)
                    .replace('{product_name}', rs.products[i].product_name)
                    .replace('{area_name}', rs.products[i].area_name)
                    .replace('{business_name}', rs.products[i].business_name)
                    .replace('{user_name}', rs.products[i].user_name)
                    .replace('{user_phone}', rs.products[i].user_phone);
                    $('#renewal-already-modal table tbody').append(item);
                }
            }
            else
            {
                $('#renewal-already-modal .warning-active').text(rs.message);
            }
        }, 'json');
     });
    $('.table-responsive').on('click', 'input', function(){
        var id = $('input:radio:checked').val();
        $('#renewal-already-form_renewal_order_id').val(id);
    });
        ") ?>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>
<!--已续费列表end-->


<!--续费提醒短信start-->
<?php if (Yii::$app->user->can('order-renewal/send-remind-sms')): ?>
    <div class="modal fade" id="send-remind-sms-modal" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $sendRemindSmsForm = new \backend\models\SendRemindSmsForm();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['order-renewal/send-remind-sms'],
            'id' => 'send-remind-sms-form',
            'validationUrl' => ['order-renewal/send-remind-sms', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-1',
                    'offset' => 'col-sm-offset-0',
                    'wrapper' => 'col-sm-11',
//                    'hint' => 'col-sm-offset-1 col-sm-11',
                ],
            ],
        ]);
        ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">续费提醒</h4>
                </div>
                <div class="modal-body input_box">
                    <div class="send-renewal-remind-sms-preview">
                        <?php $send_renewal_remind_sms_id = Property::get('send_renewal_remind_sms_id'); ?>
                        <?php $send_renewal_remind_sms_preview = Property::get('send_renewal_remind_sms_preview'); ?>
                        <?php if (!empty($send_renewal_remind_sms_id) && !empty($send_renewal_remind_sms_preview)): ?>
                            <h4>将给客户发送以下信息：</h4>
                            <p id="send-renewal-remind-sms-preview"></p>
                        <?php else: ?>
                            <h4 id="send-renewal-remind-sms-preview" class="text-danger"></h4>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?= Html::activeHiddenInput($sendRemindSmsForm, 'order_id', ['id' => 'send-remind-sms-form_order_id']); ?>
                    <span class="text-danger warning-active"><?= Html::error($sendRemindSmsForm, 'order_id'); ?></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php
        $sendRenewalRemindSmsPreview = Property::get('send_renewal_remind_sms_preview');
        $sendRenewalRemindSmsId = Property::get('send_renewal_remind_sms_id');
        $this->registerJs(<<<JS
        $('.send-remind-sms-btn').on('click', function(){
            $('#send-remind-sms-form').trigger('reset.yiiActiveForm');
            var id = $(this).attr('data-id');
            var sendRenewalRemindSmsPreview = '{$sendRenewalRemindSmsPreview}';
            var sendRenewalRemindSmsId = '{$sendRenewalRemindSmsId}';
            $('#send-remind-sms-form_order_id').val(id);
            $('.warning-active').text('');
            //获取信息，进行替换短信参数
            if(!sendRenewalRemindSmsPreview || !sendRenewalRemindSmsId)
            {
                $('#send-renewal-remind-sms-preview').text('短信尚未配置，快找产品经理！');
            }
            else
            {
                var userName = $(this).attr('data-user-name');
                var productName = $(this).attr('data-product-name');
                sendRenewalRemindSmsPreview = sendRenewalRemindSmsPreview.replace('{1}', userName);
                sendRenewalRemindSmsPreview = sendRenewalRemindSmsPreview.replace('{2}', productName);
                $('#send-renewal-remind-sms-preview').text(sendRenewalRemindSmsPreview);
            }
        });
        $('#send-remind-sms-form').on('beforeSubmit', function(){
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function(rs){
            if(rs.status === 200)
            {
                form.trigger('reset.yiiActiveForm');
                window.location.reload();
            }
            else
            {
                form.find('.warning-active').text(rs.message);
            }
        }, 'json');
        return false;
        });
JS
        ) ?>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
<?php endif; ?>
<!--续费提醒短信end-->
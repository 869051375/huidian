<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\Administrator;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\JsExpression;

$this->title = '服务人员管理';

$this->params['breadcrumbs'] = [$this->title];

/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\Clerk[] $models */
/** @var \backend\models\ClerkSearch $searchModel */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="panel-body">
                <div class="page-select2-area">
                    <?php
                    $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                    $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                    $districtUrl = \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                    $labelOptions = ['labelOptions' => ['class' => false]];
                    $form = ActiveForm::begin(['layout' => 'inline', 'method' => 'get', 'action' => ['administrator/list-clerk']]); ?>
                    <?= $form->field($searchModel, 'top_category_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                        'itemsName' => 'categories',
                        'selectedItem' => $searchModel->topCategory ? [$searchModel->topCategory->id => $searchModel->topCategory->name] : [],
                        'options' => ['prompt' => '选择类目', 'class' => 'form-control'],
                        'placeholderId' => '0',
                        'placeholder' => '选择类目',
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
                        'eventOpening' => new JsExpression("
                                    var id = $('#top_category_id').val();
                                    serverUrl = '{$categoryUrl}'.replace('__parent_id__', id ? id : '-1');
                                ")
                    ]) ?>
                    <?= $form->field($searchModel, 'province_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['region/ajax-provinces']),
                        'itemsName' => 'provinces',
                        'selectedItem' => $searchModel->province ? [$searchModel->province->id => $searchModel->province->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择省份'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择省份',
                        'eventSelect' => new JsExpression("
                                    $('#city_id').val('0').trigger('change');
                                    $('#district_id').val('0').trigger('change');
                                ")
                    ]); ?>
                    <?= $form->field($searchModel, 'city_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']),
                        'itemsName' => 'cities',
                        'selectedItem' => $searchModel->city ? [$searchModel->city->id => $searchModel->city->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择城市'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择城市',
                        'eventSelect' => new JsExpression("
                                    $('#district_id').val('0').trigger('change');
                                "),
                        'eventOpening' => new JsExpression("
                                    var id = $('#province_id').val();
                                    serverUrl = '{$cityUrl}'.replace('__province_id__', id ? id : '-1');
                                ")
                    ]); ?>
                    <?= $form->field($searchModel, 'district_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']),
                        'selectedItem' => $searchModel->district ? [$searchModel->district->id => $searchModel->district->name] : [],
                        'options' => ['class' => 'form-control', 'prompt' => '请选择地区'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择地区',
                        'itemsName' => 'districts',
                        'eventOpening' => new JsExpression("
                                    var id = $('#city_id').val();
                                    serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
                                ")
                    ]); ?>
                    <?= $form->field($searchModel, 'type', $labelOptions)->widget(Select2Widget::className(), [
                        'selectedItem' => \backend\models\ClerkSearch::getTypes(),
                        'placeholderId' => '0',
                        'placeholder' => '请选择类型',
                        'options' => ['class' => 'form-control', 'prompt' => '请选择类型'],
                        'static' => true,
                    ]) ?>
                    <?= $form->field($searchModel, 'keyword')->textInput() ?>
                    <button type="submit" class="btn btn-default">搜索</button>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                </div>
            </div>
            <div class="ibox-title">
                <h5>服务人员列表 </h5>
                <div class="ibox-tools">
                    <?php if (Yii::$app->user->can('administrator/add-clerk')): ?>
                        <a href="<?= \yii\helpers\Url::to(['administrator/add-clerk', 'type' => Administrator::TYPE_CLERK]);?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>添加服务人员</a>
                    <?php endif;?>
                </div>
            </div>
            <div class="ibox-content">

                <div class="table-responsive">
                    <table class="footable table table-striped">
                        <thead>
                        <tr>
                            <th>姓名/手机</th>
                            <th>邮箱</th>
                            <th>服务类目</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($models as $model):
                            $options = [
                                'id' => false,
                                'class' => 'change-status-checkbox',
                                'label' => false,
                                'data-id' => isset($model->administrator->id) ? $model->administrator->id : 0,
                                'data-type' => isset($model->administrator->type) ? $model->administrator->type : 0,
                            ];
                            if (!Yii::$app->user->can('administrator/status-clerk')) {
                                $options['readonly'] = 'readonly';
                            }
                            ?>
                            <tr>
                                <td><?= $model->name; ?><br/><?= $model->phone; ?></td>
                                <td><?= $model->email; ?></td>
                                <td>
                                    <ul class="list-unstyled">
                                        <?php
                                        /** @var \common\models\ClerkItems[] $items */
                                        $items = $model->getClerkItems()->orderBy(['top_category_id' => SORT_ASC])->all();
                                        foreach ($items as $clerkItem): ?>
                                            <?php if ($clerkItem && $clerkItem->topCategory && $clerkItem->category): ?>
                                                <li><?= $clerkItem->topCategory->name; ?>
                                                - <?= $clerkItem->category->name; ?></li><?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <label>
                                        <?= Html::activeCheckbox($model->administrator, 'status', $options); ?>
                                    </label>
                                </td>
                                <td class="text-right">
                                    <?php if(Yii::$app->user->can('administrator/force-login')):?>
                                        <a class="btn btn-xs btn-link"
                                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/force-login', 'id' => $model->administrator->id]) ?>">Force Login</a>
                                    <?php endif; ?>
                                    <?php if (Yii::$app->user->can('administrator/update-clerk')): ?>
                                        <a class="btn btn-xs btn-white"
                                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/clerk-update', 'id' => $model->id]) ?>">编辑服务人员</a>
                                        <a class="btn btn-xs btn-white"
                                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-clerk', 'id' => $model->administrator->id, 'type' => $model->administrator->type]) ?>">编辑</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="6">
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
    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">修改账号状态</h4>
                </div>
                <div class="modal-body">
                    确定禁用吗?
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary sure-btn">确定</button>
                </div>
            </div>
        </div>
    </div>
<?php
$statusUrl = \yii\helpers\Url::to(['status-clerk']);
$this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                modal.find('.warning-active').empty().text('');
                var status = checkbox.isChecked() ? 0 : 1;
                if(status === 0)
                {
                    modal.find('.modal-body').text('确定启用吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定禁用吗？');
                }
                modal.modal('show');
                currentCheckbox = checkbox;
                return false;
            });
        })(statusSwitchery);
    }
    
    modal.find('.sure-btn').click(function(){
        changeStatus(currentCheckbox);
    });
    
    function changeStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post('{$statusUrl}', {status: status, id: $(checkbox.element).attr('data-id'), type: $(checkbox.element).attr('data-type')}, function(rs){
            if(rs.status === 200)
            {
                checkbox.setPosition(true);
                checkbox.handleChange();
                modal.modal('hide');
            }
            else 
            {
                modal.find('.warning-active').empty().text(rs.message);
            }
        }, 'json');
    }
JS
);?>
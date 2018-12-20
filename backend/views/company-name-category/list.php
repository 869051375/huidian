<?php
/* @var $this yii\web\View */

use backend\models\ProductSearch;
use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = '公司名称管理';

$this->params['breadcrumbs'] = [$this->title];

/** @var \yii\data\DataProviderInterface $provider */
/** @var \common\models\CompanyNameCategory[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>公司分类列表 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('company-name/create')): ?>
                            <a href="#" class="btn btn-primary btn-sm" data-target="#add-company-category" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="footable table table-striped">
                            <thead>
                            <tr>
                                <th>公司分类</th>
                                <th>备注说明</th>
                                <th>状态</th>
                                <th class="text-right" data-sort-ignore="true">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($models as $model):
                                $options = [
                                    'id' => false,
                                    'class' => 'change-status-checkbox',
                                    'label' => false,
                                    'data-id' => $model->id,
                                ];
                                if (!Yii::$app->user->can('company-name/create')) {
                                    $options['readonly'] = 'readonly';
                                }
                                ?>
                                <tr>
                                    <td><?= $model->name; ?></td>
                                    <td><?= $model->remark; ?></td>
                                    <td>
                                        <label>
                                            <?= Html::activeCheckbox($model, 'status', $options); ?>
                                        </label>
                                    </td>
                                    <td class="text-right">
                                        <?php if (Yii::$app->user->can('company-name/create')): ?>
                                            <button class="btn btn-xs btn-default update-category" data-id="<?= $model->id ?>" data-target="#update-company-category" data-toggle="modal">编辑</button>
                                        <?php endif;?>
                                        <a href="<?= \yii\helpers\Url::to(['company-name/list', 'category_id' => $model->id])?>" class="btn btn-xs btn-default">公司名称</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="9">
                                    <?= LinkPager::widget(['pagination' => $pagination]); ?>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php if (Yii::$app->user->can('company-name/create')): ?>
    <div class="modal fade" id="add-company-category" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\CompanyNameCategory();
        $model->loadDefaultValues();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['create'],
            'validationUrl' => ['create', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'company-category-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-2',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-8',
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">新增</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($model, 'name')->textInput() ?>
                    <?= $form->field($model, 'remark')->textInput() ?>
                    <?= $form->field($model, 'sort')->textInput() ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
        <?php $this->registerJs(<<<JS
        $('#company-category-form').on('beforeSubmit', function(){
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
)?>
    </div>

    <div class="modal fade" id="update-company-category" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\CompanyNameCategory();
        $model->loadDefaultValues();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['update'],
            'validationUrl' => ['update', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'update-company-category-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-2',
                    'offset' => 'col-sm-offset-2',
                    'wrapper' => 'col-sm-8',
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">编辑</h4>
                </div>
                <div class="spiner-example loading" style="display: block">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>
                <div class="modal-body input_box" style="display: none">
                    <?= $form->field($model, 'name')->textInput(['id' => 'update-category-name']) ?>
                    <?= $form->field($model, 'remark')->textInput(['id' => 'update-category-remark']) ?>
                    <?= $form->field($model, 'sort')->textInput(['id' => 'update-category-sort']) ?>
                    <div style="display: none;">
                        <?= $form->field($model, 'id')->textInput(['id' => 'update-category-id']) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
        <?php
        $getInfoUrl = Url::to(['ajax-info']);
        $this->registerJs(<<<JS
        $('.update-category').click(function(){
            var box = $('#update-company-category');
            box.find('.loading').show();
            box.find('.modal-body').hide();
            $.getJSON('{$getInfoUrl}', {id: $(this).attr('data-id')}, function(rs){
                box.find('#update-category-name').val((rs['model']['name']);
                box.find('#update-category-remark').val(rs['model']['remark']);
                box.find('#update-category-sort').val(rs['model']['sort']);
                box.find('#update-category-id').val(rs['model']['id']);
                box.find('.loading').hide();
                box.find('.modal-body').show();
            });
        });
        $('#update-company-category-form').on('beforeSubmit', function(){
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
        )?>
    </div>



    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">确认操作</h4>
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
    $statusUrl = Url::to(['company-name-category/status']);
    $this->registerJs(<<<JS
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small", "className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
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
        changeProductStatus(currentCheckbox);
    });
    
    function changeProductStatus(checkbox)
    {
        var status = checkbox.isChecked() ? 0 : 1;
        $.post('{$statusUrl}', {status: status, id: $(checkbox.element).attr('data-id')}, function(rs){
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
<?php else: ?>
    <?php
    $this->registerJs(<<<JS
    var statusList = document.querySelectorAll('.change-status-checkbox');
    for(var i = 0; i < statusList.length; i++)
    {
        new Switchery(statusList[i], {"size":"small","className":"switchery"});
    }
JS
    );?>
<?php endif; ?>
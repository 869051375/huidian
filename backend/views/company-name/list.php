<?php
/* @var $this yii\web\View */

use backend\models\ProductSearch;
use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = '公司名称列表';

$this->params['breadcrumbs'] = [
    ['label' => '公司名称管理', 'url' => ['company-name-category/list']],
    $this->title
];

/** @var \yii\data\DataProviderInterface $provider */
/** @var int $category_id */
/** @var \common\models\CompanyName[] $models */
$models = $provider->getModels();
$pagination = $provider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>公司名称列表 </h5>
                    <div class="ibox-tools">
                        <?php if (Yii::$app->user->can('company-name/create')): ?>
                            <a href="#" class="btn btn-primary btn-sm" data-target="#add-company-name" data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="footable table table-striped">
                            <thead>
                            <tr>
                                <th>名称</th>
                                <th>排序</th>
                                <th class="text-right" data-sort-ignore="true">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($models as $model): ?>
                                <tr>
                                    <td><?= $model->name; ?></td>
                                    <td><?= $model->sort; ?></td>
                                    <td class="text-right">
                                        <?php if (Yii::$app->user->can('company-name/delete')): ?>
                                            <a href="#" class="btn btn-danger btn-xs delete-name" data-id="<?= $model->id ?>" data-target="#delete-modal" data-toggle="modal">
                                                <span class="fa fa-trash"></span> 删除</a>
                                        <?php endif;?>
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

    <div class="modal fade" id="delete-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">确认删除</h4>
                </div>
                <div class="modal-body">
                    确定删除吗?
                    <?= Html::hiddenInput('delete-id', '', ['id' => 'delete-name-id']); ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary delete-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php
        $deleteUrl = Url::to(['delete']);
        $this->registerJs(<<<JS
        $('.delete-name').click(function(){
            $('#delete-name-id').val($(this).attr('data-id'));
        });
        $('.delete-sure-btn').on('click', function(){
            $.post('{$deleteUrl}', {id: $('#delete-name-id').val()}, function(rs){
                if(rs.status === 200)
                {
                    window.location.reload();
                }
                else 
                {
                    modal.find('.warning-active').empty().text(rs.message);
                }
            }, 'json');
        });
JS
        )?>
    </div>

    <div class="modal fade" id="add-company-name" role="dialog" aria-labelledby="modal-title">
        <?php
        $model = new \common\models\CompanyName();
        $model->loadDefaultValues();
        $model->category_id = $category_id;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['create'],
            'validationUrl' => ['create', 'is_validate' => 1],
            'enableAjaxValidation' => true,
            'id' => 'company-name-form',
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
                    <?= $form->field($model, 'sort')->textInput() ?>
                    <?= Html::activeHiddenInput($model, 'category_id') ?>
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
        $('#company-name-form').on('beforeSubmit', function(){
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
<?php endif; ?>
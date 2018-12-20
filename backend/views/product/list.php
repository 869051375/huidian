<?php
/* @var $this yii\web\View */

use backend\models\ProductSearch;
use backend\widgets\LinkPager;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$actionUniqueId = Yii::$app->controller->action->uniqueId;
$name = '';
$add_button_name = '';
if($actionUniqueId == 'product/package-list')
{
    $name = '套餐商品列表';
    $add_button_name = '添加套餐商品';
    $searchAction = ['package-list'];
}
else
{
    $name = '标准商品列表';
    $add_button_name = '添加标准商品';
    $searchAction = ['list'];
}

//$this->title = '标准商品列表';
$this->title = $name;
$this->params['breadcrumbs'] = [$this->title];

/** @var \yii\data\DataProviderInterface $dataProvider */
/** @var \common\models\Product[] $models */
/** @var \backend\models\ProductSearch $searchModel */
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
\toxor88\switchery\SwitcheryAsset::register($this);
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5><?= $name ?></h5>
                <div class="ibox-tools">
                    <?php if($actionUniqueId == 'product/package-list'):?>
                        <?php if (Yii::$app->user->can('product/create')): ?>
                            <a href="<?= \yii\helpers\Url::to(['package-create']) ?>" class="btn btn-primary btn-sm"><span
                                        class="fa fa-plus"></span> 添加套餐</a>
                        <?php endif; ?>
                    <?php else:?>
                        <?php if (Yii::$app->user->can('product/create')): ?>
                            <a href="<?= \yii\helpers\Url::to(['create']) ?>" class="btn btn-primary btn-sm"><span
                                        class="fa fa-plus"></span> 添加标准商品</a>
                        <?php endif; ?>
                    <?php endif;?>
                </div>
            </div>
            <div class="ibox-content page-select2-area">

                    <?php
                    $categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
                    $companyUrl = \yii\helpers\Url::to(['crm-department/ajax-list', 'company_id' => '__company_id__']);
                    $labelOptions = ['labelOptions' => ['class' => false]];
                    $form = \yii\bootstrap\ActiveForm::begin([
//                    'id' => 'product-search-form',
                        'action' => $searchAction,
                        'layout' => 'inline',
                        'method' => 'get',
                    ]); ?>
                <div class="m-t-md">
                    <?= $form->field($searchModel, 'top_category_id', $labelOptions)->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                        'itemsName' => 'categories',
                        'selectedItem' => $searchModel->topCategory ? [$searchModel->topCategory->id => $searchModel->topCategory->name] : [],
                        'options' => ['prompt'=>'选择类目', 'class' => 'form-control'],
                        'placeholderId' => '0',
                        'placeholder' => '选择类目',
                        'eventSelect' => new JsExpression("
                        $('#category_id').val('0').trigger('change');
                    ")
                    ])?>

                    <?= $form->field($searchModel, 'category_id')->widget(Select2Widget::className(), [
                        'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
                        'itemsName' => 'categories',
                        'selectedItem' => $searchModel->category ? [$searchModel->category->id => $searchModel->category->name] : [],
                        'options' => ['prompt'=>'选择类目', 'class' => 'form-control'],
                        'placeholderId' => '0',
                        'placeholder' => '选择类目',
                        'eventOpening' => new JsExpression("
                        var id = $('#top_category_id').val();
                        serverUrl = '{$categoryUrl}'.replace('__parent_id__', id > 0 ? id : '-1');
                    ")
                    ])?>
                    <?php if($actionUniqueId != 'product/package-list'):?>

                     <?= $form->field($searchModel, 'flow_id', $labelOptions)->widget(Select2Widget::className(),[
                            'serverUrl' => \yii\helpers\Url::to(['flow/ajax-list']),
                            'itemsName' => 'flows',
                            'selectedItem' => $searchModel->flow ? [$searchModel->flow->id => $searchModel->flow->name] : [],
                            'options' => ['prompt'=>'选择流程', 'class' => 'form-control'],
                            'placeholder' => '选择流程',
                            'placeholderId' => '0'
                        ]) ?>
                    <?php endif;?>

                        <?= $form->field($searchModel, 'company_id', $labelOptions)->widget(Select2Widget::className(),[
                            'attribute' => 'company_id',
                            'serverUrl' => \yii\helpers\Url::to(['company/ajax-list']),
                            'itemsName' => 'company',
                            'selectedItem' => $searchModel->company ? [$searchModel->company->id => $searchModel->company->name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择公司'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择公司',
                            'eventSelect' => new JsExpression("
                                $('#department_id').val('0').trigger('change');
                            ")
                        ]);?>
                        <?= $form->field($searchModel, 'department_id')->widget(Select2Widget::className(), [
                            'attribute' => 'department_id',
                            'selectedItem' => $searchModel->companyDepartment ? [$searchModel->companyDepartment->id => $searchModel->companyDepartment->name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择部门'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择部门',
                            'serverUrl' => \yii\helpers\Url::to(['crm-department/ajax-list', 'company_id' => '__company_id__']),
                            'itemsName' => 'items',
                            'eventOpening' => new JsExpression("
                                var id = $('#company_id').val();
                                serverUrl = '{$companyUrl}'.replace('__company_id__', id ? id : '-1');
                            ")
                        ]);?>
                    <?php if($actionUniqueId != 'product/package-list'):?>
                    <?php endif;?>

                </div>
                <div class="m-t-md row">
                    <div class="col-sm-10">
                        <?= $form->field($searchModel, 'status', $labelOptions)->dropDownList(\common\models\Product::getStatusList(),['class' => 'form-control', 'prompt'=>'状态']) ?>
                        <?= $form->field($searchModel, 'platform_id', $labelOptions)->dropDownList(\common\models\Product::getProductFromList(),['class' => 'form-control', 'prompt'=>'商品来源']) ?>
                        <?php if($actionUniqueId != 'product/package-list'):?>
                         <?= $form->field($searchModel, 'show_type', $labelOptions)->dropDownList(ProductSearch::getShowTypeList(),['class' => 'form-control', 'prompt'=>'显示类型']) ?>
                        <?php endif;?>
                        <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput() ?>
                        <button type="submit" class="btn btn-sm btn-primary">搜索</button>
                    </div>
                    <div class="col-sm-2">
                        <?php if (Yii::$app->user->can('product/export')): ?>
                            <a href="<?= Url::to(['export']) ?>" class="btn btn-sm btn-primary" style="float:right">导出全部商品</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="footable table table-striped">
                        <thead>
                        <tr>
                            <th><?php if($actionUniqueId == 'product/package-list'):?>套餐ID<?php else:?>商品ID<?php endif;?></th>
                            <th><?php if($actionUniqueId == 'product/package-list'):?>套餐别名<?php else:?>商品别名<?php endif;?></th>
                            <th>商品来源</th>
                            <?php if($actionUniqueId != 'product/package-list'):?>
                                <th>商品流程</th>
                            <?php endif;?>
                            <th>销售价</th>
                            <th>原价</th>
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
                            if (!$model->canOnline() || !Yii::$app->user->can('product/status')) {
                                $options['readonly'] = 'readonly';
                            }
                            ?>
                            <tr>
                                <td><?= $model->id; ?></td>
                                <td><?= $model->alias; ?></td>
                                <td><?= $model::getSource($model->platform_id)?></td>
                                <?php if($actionUniqueId != 'product/package-list'):?>
                                    <td><?= $model->flow ? $model->flow->name : '无'; ?></td>
                                <?php endif;?>
                                <?php if($model->isAreaPrice()):
                                    $dpp = $model->getDefaultProductPrice();?>
                                    <td><?= $dpp ? Yii::$app->formatter->asCurrency($dpp->price) : '未设置' ?></td>
                                    <td><?= $dpp ? Yii::$app->formatter->asCurrency($dpp->original_price) : '未设置' ?></td>
                                <?php elseif($model->isBargain()):?>
                                    <td>面议</td>
                                    <td><?= Yii::$app->formatter->asCurrency($model->original_price); ?></td>
                                <?php else:?>
                                    <td><?= Yii::$app->formatter->asCurrency($model->price); ?></td>
                                    <td><?= Yii::$app->formatter->asCurrency($model->original_price); ?></td>
                                <?php endif; ?>
                                <td><?= $model::getStatus($model->status)?></td>

                                <td class="text-right">
                                    <?php if($actionUniqueId == 'product/list'):?>
                                        <?php if (Yii::$app->user->can('product/update')): ?>
                                        <a href="<?= \yii\helpers\Url::to(['update', 'id' => $model->id])?>" target="_blank" class="btn btn-xs btn-default">编辑</a>
                                        <?php endif;?>
                                    <?php else:?>
                                        <?php if (Yii::$app->user->can('product/update')): ?>
                                            <a href="<?= \yii\helpers\Url::to(['package-update', 'id' => $model->id])?>" target="_blank" class="btn btn-xs btn-default">编辑</a>
                                        <?php endif;?>
                                    <?php endif;?>
                                    <?php if (Yii::$app->user->can('product-price/list') && $actionUniqueId == 'product/list'): ?>
                                        <a href="<?= \yii\helpers\Url::to(['price', 'product_id' => $model->id])?>" target="_blank" class="btn btn-xs btn-default">商品价格</a>
                                    <?php endif;?>
                                    <?php if (Yii::$app->user->can('product/update') && $actionUniqueId == 'product/package-list'): ?>
                                        <a href="<?= \yii\helpers\Url::to(['package-product/list', 'id' => $model->id])?>" target="_blank" class="btn btn-xs btn-default">套餐商品</a>
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

<?php if (Yii::$app->user->can('product/status')): ?>
    <div class="modal fade" id="up-down-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">商品上下线</h4>
                </div>
                <div class="modal-body">
                    确定下线吗?
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
$statusUrl = Url::to(['product/status']);
$this->registerJs(<<<JS
    $.fn.select2.defaults.set('width', '150px');
    var currentCheckbox = null;
    var statusList = document.querySelectorAll('.change-status-checkbox');
    var statusSwitchery = null;
    var modal = $('#up-down-modal');
    for(var i = 0; i < statusList.length; i++)
    {
        statusSwitchery = new Switchery(statusList[i], {"size":"small","className":"switchery"});
        (function (checkbox){
            $(checkbox.element).click(function(){
                var status = checkbox.isChecked() ? 0 : 1;
                if(status === 0)
                {
                    modal.find('.modal-body').text('确定上线吗？');
                }
                else
                {
                    modal.find('.modal-body').text('确定下线吗？');
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
        $.post('{$statusUrl}', {status: status, product_id: $(checkbox.element).attr('data-id')}, function(rs){
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
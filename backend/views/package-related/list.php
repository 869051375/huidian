<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var \common\models\Product $product */
/** @var \yii\data\DataProviderInterface $provider */
/** @var [] $models */
$labelName = '';
$breadcrumbsUrl = '';
if($product->isPackage())
{
    $labelName = '套餐商品列表';
    $breadcrumbsUrl = ['product/package-list'];
}
else
{
    $labelName = '标准商品列表';
    $breadcrumbsUrl = ['product/list'];
}
$this->title = '关联商品设置';
$this->params['breadcrumbs'] = [
    ['label' => $labelName, 'url' => $breadcrumbsUrl],
    $this->title
];
$models = $provider->getModels();
$pagination = $provider->getPagination();
?>

<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('/product/package-nav-tabs', ['product' => $product]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <ul class="nav nav-tabs">
                            <li><a href="<?= Url::to(['product-related/list', 'id' => $product->id]) ?>">关联其他类型商品</a></li>
                            <?php if(!$product->isPayAfterService()):?>
                                <li class="active"><a href="<?= Url::to(['package-related/list', 'id' => $product->id]) ?>">关联套餐商品</a></li>
                                <?php if(!$product->isPackage()):?>
                                <li><a href="<?= Url::to(['collocation/list', 'id' => $product->id]) ?>">关联搭配商品</a></li>
                                <?php endif;?>
                            <?php endif;?>
                        </ul>
                        <div class="tab-content">
                            <p></p>
                            <?php if (Yii::$app->user->can('product/update')): ?>
                                <a href="#" class="btn btn-primary btn-sm add-related-modal" data-target="#add-related-modal"
                               data-toggle="modal"><span class="fa fa-plus"></span> 新增</a>
                            <?php endif;?>
                            <table class="footable table table-striped">
                                <thead>
                                <tr>
                                    <th>套餐命名</th>
                                    <th>关联套餐名称</th>
                                    <th class="text-right" data-sort-ignore="true">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td><?= $model['spec_name']; ?></td>
                                        <td><?= $model['name']; ?></td>
                                        <?php if (Yii::$app->user->can('product/update')): ?>
                                        <td class="text-right">
                                                <span class="btn btn-xs btn-white delete-btn"
                                                      data-target="#delete-related-modal"
                                                      data-toggle="modal"
                                                      data-package-id="<?= $model['package_id'] ?>"
                                                      data-related-product-id="<?= $model['package_related_id'] ?>">删除</span>
                                        </td>
                                        <?php endif;?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4">
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
<?php if (Yii::$app->user->can('product/update')): ?>
<div class="modal fade" id="add-related-modal" role="dialog" aria-labelledby="modal-title">
    <?php
    $model = new \backend\models\PackageRelatedForm();
    $model->package_id = $product->id;
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['package-related/create'],
        'validationUrl' => ['package-related/validation'],
        'id' => 'package-related-form',
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
                <h4 class="modal-title">新增关联套餐商品</h4>
            </div>
            <div class="modal-body input_box">
                <?= $form->field($model, 'package_related_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                    'serverUrl' => ['package-related/ajax-list', 'id' => $product->id],
                    'itemsName' => 'products',
                    'nameField' => 'name',
                    'searchKeywordName' => 'keyword',
                ]); ?>
            </div>
            <?= Html::activeHiddenInput($model, 'package_id') ?>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
</div>

<div class="modal fade" id="delete-related-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除关联套餐商品</h4>
            </div>
            <div class="modal-body">
                确定删除吗?
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary sure-btn">确定</button>
            </div>
        </div>
    </div>
</div>
<?php endif;?>
<?php
$this->registerJs("
    
    $('#add-related-modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });

    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })
    $.fn.select2.defaults.set('width', '100%');
	$('.delete-btn').on('click',function(){
	    var delete_id = $(this).attr('data-package-id');
	    var related_delete_id = $(this).attr('data-related-product-id');
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete']) . "',{p_id:delete_id,r_id:related_delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	        
	            window.location.reload();
	        }
	    },'json')
	    })
	})
    $('.add-related-modal').on('click',function(){
        $('#package-related-form').trigger('reset.yiiActiveForm');
    });
	") ?>

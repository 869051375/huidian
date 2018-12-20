<?php

/* @var $this yii\web\View */
use backend\models\FaqForm;
use yii\bootstrap\Html;

/* @var $provider yii\data\ActiveDataProvider */
/** @var \common\models\Product $product */

$this->title = '常见问题';
if($product->isPackage())
{
    $breadcrumbsUrl = ['product/package-list'];
}
else
{
    $breadcrumbsUrl = ['product/list'];
}
$this->params['breadcrumbs'] = [
    ['label' => '商品管理', 'url' => $breadcrumbsUrl],
    $this->title
];
$imageStorage = Yii::$app->get('imageStorage');
?>
    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-xs-12">
                <div class="tabs-container">
                    <?php if($product->isPackage()):?>
                        <?= $this->render('/product/package-nav-tabs', ['product' => $product]) ?>
                    <?php else:?>
                        <?= $this->render('/product/nav-tabs', ['product' => $product]) ?>
                    <?php endif;?>
                    <div class="tab-content">
                        <div class="panel-body" style="border-top: none">
                            <a href="#" class="btn btn-primary btn-sm" data-target="#add-faq-modal" data-toggle="modal" data-whatever="新增问题">
                                <span class="fa fa-plus"></span> 新增</a>
                            
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>问题</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($provider as $model): ?>
                                <tr>
                                    <td><?= $model->question ?></td>
                                    <td>
                                        <span class="btn btn-xs btn-white update-btn"
                                              data-target="#add-faq-modal" data-toggle="modal" data-id="<?= $model->id; ?>"
                                              data-whatever="编辑问题">编辑</span>
                                        <span class="btn btn-xs btn-white delete-btn"
                                              data-target="#delete-faq-modal" data-toggle="modal"
                                              data-id="<?= $model->id ?>">删除</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php \backend\assets\SortAsset::register($this); ?>
    <div class="modal fade" id="add-faq-modal" role="dialog" aria-labelledby="modal-title">
        <?php
        $faqModel = new \common\models\ProductFaq();
        $faqModel->product_id = $product->id;
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['product-faq/create'],
            'validationUrl' => ['product-faq/validation'],
            'enableAjaxValidation' => true,
            'id' => 'product-faq-form',
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
                    <h4 class="modal-title">新增问题</h4>
                </div>

                <div class="modal-body input_box">
                    <?= $form->field($faqModel, 'question')->textInput(['id' => 'faq-question']) ?>
                    <?= $form->field($faqModel, 'answer')->textarea(['id' => 'faq-answer', 'rows' => '8']) ?>
                    <?= Html::activeHiddenInput($faqModel, 'product_id') ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>

    <div class="modal fade" id="delete-faq-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除问题</h4>
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
<?php
$this->registerJs("
        $('.sortablelist').find('.move-up,.move-down').show();
        var div1 = $('.so1:first');
        var div2 = $('.so1:last');
        div1.find('.move-up').hide();
        div2.find('.move-down').hide();
        
        $('#add-faq-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var recipient = button.data('whatever');
            var modal = $(this);
            console.log(button)
            modal.find('.modal-title').text(recipient);
        });
    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })
	$('.delete-btn').on('click',function(){
	    var delete_id = $(this).attr('data-id');
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	        
	            window.location.reload();
	        }
	    },'json')
	    })
	})

    var createAction1 = '" . \yii\helpers\Url::to(['create']) . "';
    
    $('.add-Carousel').on('click',function(){
        $('#product-faq-form').trigger('reset.yiiActiveForm');
    });
    
    $('.update-btn').on('click',function(){
        var id = $(this).attr('data-id');
        
        var updateAction = '" . \yii\helpers\Url::to(['update', 'id' => '__id__']) . "';
        $('.modal form').attr('action', updateAction.replace('__id__', id));
        $.get('" . \yii\helpers\Url::to(['detail', 'id' => '__id__']) . "'.replace('__id__', id),function(rs){
            if(rs.status!=200){
                
            }else{
                $('#faq-question').val(rs.model.question);
                $('#faq-answer').val(rs.model.answer);
            }
        },'json')
        
    })   
    ");
?>
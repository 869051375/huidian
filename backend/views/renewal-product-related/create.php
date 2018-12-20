<?php
/* @var $this yii\web\View */

$this->title = '关联续费商品';
$this->params['breadcrumbs'] = [['label' => $this->title, 'url' => ['renewal-product-related/list']]];
/** @var \common\models\RenewalProductRelated $model */
?>

<div class="wrapper wrapper-content animated fadeIn">
    <div class="row page-select2-area">
        <div class="col-xs-12">
            <?php
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['renewal-product-related/update'],
                'enableAjaxValidation' => false,
                'id' => 'link-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-2',
                        'offset' => 'col-sm-offset-2',
                        'wrapper' => 'col-sm-8',
                    ],
                ],
            ]); ?>
            <div class="tabs-container">
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-1">
                            <?= $form->field($model, 'name')->textInput() ?>
                            <?= $form->field($model, 'remark')->textarea() ?>
                            <?= \yii\bootstrap\Html::activeHiddenInput($model, 'id') ?>
                            </div>
                        </div>
                        <!--包含商品start-->
                        <?php if(!empty($model->id)):?>
                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <div class="modal-body input_box">
                                        <p>包含商品</p>
                                        <div id="district-price-list">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                <tr class="text-center">
                                                    <th class="text-center">商品</th>
                                                    <?php if(Yii::$app->user->can('renewal-product-related/update')): ?>
                                                        <th class="text-center">操作</th>
                                                    <?php endif; ?>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($model->getProductList() as $product): ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span style="cursor:pointer;"><?= $product->name; ?></span>
                                                        </td>
                                                        <?php if(Yii::$app->user->can('renewal-product-related/update')): ?>
                                                            <td class="text-center">
                                                                <span class="btn btn-xs text-danger del-product"
                                                                      data-target="#delete-product-model"
                                                                      data-toggle="modal"
                                                                      data-name="删除包含商品"
                                                                      data-renewal-id="<?=$model->id ?>"
                                                                      data-id="<?=$product->id;?>">
                                                                    删除
                                                            </span>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if(Yii::$app->user->can('renewal-product-related/update')): ?>
                                                <span class="btn btn-default renewal-product-add" data-target="#renewal-product-add-modal" data-toggle="modal">添加</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif;?>
                        <?php if(Yii::$app->user->can('renewal-product-related/update')): ?>
                            <div class="form-group" style="margin:0 0 0 28px;">
                                <div class="col-sm-8 col-sm-offset-2">
                                    <button type="submit" class="btn btn-primary sure-btn">保存</button>
                                </div>
                            </div>
                        <?php endif;?>
                        <!--包含商品end-->
                    </div>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php if(Yii::$app->user->can('renewal-product-related/update')): ?>
                <!--删除包含商品start-->
                <div class="modal fade" id="delete-product-model" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">删除包含商品</h4>
                            </div>
                            <div class="modal-body">
                                确定删除吗?
                            </div>
                            <div class="modal-footer">
                                <span class="text-danger warning-active"></span>
                                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                                <button type="button" class="btn btn-primary sure-btn delete-btn">确定</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--删除包含商品end-->

                <!--添加包含商品start-->
                <div class="modal fade" id="renewal-product-add-modal" role="dialog" aria-labelledby="modal-title">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <?php
                            $renewalProductForm = new \backend\models\RenewalProductRelatedForm();
                            $renewalProductForm->id = $model->id;
                            $form = \yii\bootstrap\ActiveForm::begin([
                                'action' => ['renewal-product-related/add-renewal-product'],
                                'id' => 'renewal-product-form',
                                'layout' => 'horizontal',
                                'fieldConfig' => [
                                    'horizontalCssClasses' => [
                                        'label' => 'col-sm-4',
                                        'offset' => 'col-sm-offset-2',
                                        'wrapper' => 'col-sm-6',
        //                                'hint' => 'col-sm-offset-2 col-sm-8',
                                    ],
                                ],
                            ]); ?>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">添加包含商品</h4>
                            </div>
                            <div class="modal-body input_box">
                                <?= $form->field($renewalProductForm, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                                    'serverUrl' => ['renewal-product-related/ajax-list', 'id' => $model->id],
                                    'itemsName' => 'products',
        //                                'options' => ['class' => 'form-control', 'prompt'=>'请选择'],
                                    'nameField' => 'name',
                                    'searchKeywordName' => 'keyword',
                                ]); ?>
                            </div>
                            <?= \yii\bootstrap\Html::activeHiddenInput($renewalProductForm, 'id') ?>
                            <div class="modal-footer">
                                <span class="text-danger"></span>
                                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                <button type="submit" class="btn btn-primary" id="confirm-add">确定</button>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
                <!--添加包含商品end-->
            <?php endif; ?>
        </div>
    </div>
<?php
$deleteProduct = \yii\helpers\Url::to(['ajax-delete-product']);
$this->registerJs(<<<JS
    $.fn.select2.defaults.set('width', '100%');
    $('.renewal-product-add').click(function() {
      $('#renewal-product-form').trigger('reset.yiiActiveForm');
    });
   //删除商品
    $('.del-product').on('click',function(){
      var renewal_id = $(this).attr('data-renewal-id');
      var product_id = $(this).attr('data-id');
      var dataname = $(this).attr('data-name');
      $('#myModalLabel').html(dataname);
      $('.delete-btn').click(function(){
        $.post('{$deleteProduct}',{product_id:product_id,renewal_id:renewal_id},function(rs){
                if(rs.status == 200){
                    window.location.reload(); 
                }else{
                    
                }
            },'json')
        })
    });
JS
);
?>
</div>
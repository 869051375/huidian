<?php
/* @var $this yii\web\View */
use backend\models\OrderForm;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\web\JsExpression;

/* @var $model backend\models\OrderForm */
/* @var $user \common\models\User */
/* @var $experienceApply \common\models\ExperienceApply */

$this->title = '新增订单';
$this->params['breadcrumbs'] = [
    ['label' => '订单管理', 'url' => ['order-list/all']],
    $this->title
];
$experienceApplyId = $experienceApply ? $experienceApply->id : '';
?>
<?php if (Yii::$app->user->can('order/create')): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>代客户【<?= $user->name ?>】下单</h5>
            </div>
            <div class="ibox-content">
                <?php
                $orderForm = new \backend\models\OrderForm();
//                $orderForm->setScenario('need_invoice');
                $form = \yii\bootstrap\ActiveForm::begin([
                    'id' => 'order-create-form',
                    'action' => ['order/create', 'user_id' => $user->id, 'experience_id' => $experienceApplyId],
                    'validationUrl' => ['order/create', 'user_id' => $user->id, 'is_validate' => 1],
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label' => 'col-md-2',
                            'offset' => 'col-md-offset-2',
                            'wrapper' => 'col-md-4',
                            'hint' => 'col-md-6',
                        ],
                    ],
                ]); ?>
                <div class="form-group">
                    <div class="col-sm-2 control-label">
                        客户
                    </div>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= $user->name; ?>
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2 control-label">
                        手机号
                    </div>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= $user->phone; ?>
                        </p>
                    </div>
                </div>
                <?php
                $orderRow = '';
                $price = '0.00';
                $tax = '0.00';
                if($experienceApply)
                {
                    $productPrice = null;
                    if($experienceApply->product->isAreaPrice())
                    {
                        $productPrice = $experienceApply->product->getProductPriceByDistrict($experienceApply->district_id);
                    }
                    $price = $productPrice ? $productPrice->price : $experienceApply->product->price;
                    $tax = $productPrice ? $productPrice->tax : $experienceApply->product->tax;
                    $orderRow = '<tr><th>'.$experienceApply->product->name.'</th><th class="item-price" data-price="'.$price.'" data-tax="'.$tax.'">'.$price.'</th><th>1</th><th>'.$price.'</th><th><button type="button"  class="btn btn-xs btn-white delete-btn  delete-confirm-btn">删除</button></th></tr>';
                }
                ?>
                    <?= $form->field($orderForm, 'items', ['template' => '{label} {beginWrapper} 
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>商品名称</th>
                                <th>价格</th>
                                <th>数量</th>
                                <th>小计</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="order-items">
                            '.$orderRow.'
                        </tbody>
                        <tfoot>
                            <tr style="display: none">
                                <td colspan="4" class="text-right">
                                合计：
                                <span id="total-price">'.$price.'</span>
                                包含税额：
                                <span id="total-tax">0</span>
                                </td>
                            </tr>
                        </tfoot>
                        </table>
                        <span class="btn btn-default add-product-btn" data-target="#product-add-modal" data-toggle="modal">添加</span>{error} {endWrapper} {hint}'])->textInput(); ?>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <div class="col-sm-8 col-sm-offset-2">
                        <button class="main-bg btn btn-primary" type="submit" value="save">提交</button>
                    </div>
                </div>
                <div id="item-box">
                <?php
                if($experienceApply)
                {
                    echo '<div class="item">';
                    $modelHidden = new OrderForm();
                    echo Html::activeHiddenInput($modelHidden, 'items[product_id][]', ['id' => false, 'class' => 'product_id', 'value' => $experienceApply->product->id]);
                    echo Html::activeHiddenInput($modelHidden, 'items[product_price_id][]', ['id' => false, 'class' => 'product_price_id', 'value' => $productPrice ? $productPrice->id : 0]);
                    echo Html::activeHiddenInput($modelHidden, 'items[qty][]', ['id' => false, 'class' => 'qty','value' => 1]);
                    echo '</div>';
                }
                ?>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>


                <!--添加商品start-->
                <div class="modal fade" id="product-add-modal" role="dialog" aria-labelledby="modal-title">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <?php
                            $orderItemForm = new \backend\models\OrderItemForm();
                            $form = \yii\bootstrap\ActiveForm::begin([
                                'layout' => 'horizontal',
                                'id' => 'product-add-form',
                                'fieldConfig' => [
                                    'horizontalCssClasses' => [
                                        'label' => 'col-sm-2',
                                        'offset' => 'col-sm-offset-2',
                                        'wrapper' => 'col-sm-8',
                                        'hint' => 'col-sm-offset-2 col-sm-8',
                                    ],
                                ],
                            ]); ?>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">添加商品</h4>
                            </div>

                            <div class="modal-body input_box">
                                <?= $form->field($orderItemForm, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                                    'selectedItem' => [],
                                    'serverUrl' => ['product/ajax-list', 'status' => 1],
                                    'itemsName' => 'products',
                                    'nameField' => 'name',
                                    'attribute' => 'product_id',
                                    'placeholderId' => '0',
                                    'placeholder' => '请选择商品',
                                    'searchKeywordName' => 'keyword',
                                    'eventSelect' => new JsExpression("
                        selectedProduct = env.params.data;
                        if(env.params.data.is_area_price){
                            $('.field-orderitemform-product_price_id').css('display', 'block');
                        }else{
                            $('.field-orderitemform-product_price_id').css('display', 'none');
                        }
                        $('#orderitemform-product_price_id').val('0').trigger('change');
                    ")
                                ]);
                                $districtsUrl = \yii\helpers\Url::to(['product/ajax-districts', 'product_id' => '__product_id__']);
                                echo $form->field($orderItemForm, 'product_price_id')->widget(Select2Widget::className(), [
                                    'selectedItem' => [],
                                    'options' => ['class' => 'form-control', 'prompt'=>'请选择地区'],
                                    'placeholderId' => '0',
                                    'placeholder' => '请选择地区',
                                    'serverUrl' => $districtsUrl,
                                    'itemsName' => 'districts',
                                    'eventOpening' => new JsExpression("
                        var id = $('#orderitemform-product_id').val();
                        serverUrl = '{$districtsUrl}'.replace('__product_id__', id ? id : '-1');
                    "),
                                    'eventSelect' => new JsExpression("
                        selectedProductPrice = env.params.data;
                        console.log(env.params.data.price);
                    ")
                                ])?>
                                <?= $form->field($orderItemForm, 'qty')->textInput()?>
                            </div>
                            <div class="modal-footer">
                                <span class="text-danger warning-active"></span>
                                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                <button type="submit" class="btn btn-primary" id="confirm-add">确定</button>
                            </div>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
                <div id="order-item-template" class="hidden">
                    <div class="item">
                        <?php $modelHidden = new OrderForm(); ?>
                        <?= Html::activeHiddenInput($modelHidden, 'items[product_id][]', ['id' => false, 'class' => 'product_id']); ?>
                        <?= Html::activeHiddenInput($modelHidden, 'items[product_price_id][]', ['id' => false, 'class' => 'product_price_id']); ?>
                        <?= Html::activeHiddenInput($modelHidden, 'items[qty][]', ['id' => false, 'class' => 'qty']); ?>
                    </div>
                </div>
                <!--添加商品end-->
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    function deletClick()
    { 
        $('.delete-confirm-btn').unbind('click');
        $('.delete-confirm-btn').on('click',function()
        {
            var trIndex = $(this).parents('tr').index();
            $(this).parents('tr').remove();
            $('#item-box').children('.item').eq(trIndex).remove();
        })
    };
    deletClick();
    var total = '{$price}'*100;
    var taxTotal = '{$tax}'*100;
    var selectedProduct = null;
    var selectedProductPrice = null;
    $('#product-add-modal').on('show.bs.modal', function(){
        selectedProductPrice = null;
        selectedProduct = null;
        $('#product-add-form').trigger('reset.yiiActiveForm');
        $('#orderitemform-product_id').val('0').trigger('change');
        $('#orderitemform-product_price_id').trigger('change');
        // console.log($('#orderitemform-product_price_id'));
    });
    var orderRowTemplate = '<tr><th>{product_name}</th><th class="item-price" data-price="{price}" data-tax="{tax}">{price}</th><th>{qty}</th><th>{sub_total}</th><th><button type="button"  class="btn btn-xs btn-white delete-btn delete-confirm-btn">删除</button></th></tr>';
    $.fn.select2.defaults.set('width', '100%');
    $('#confirm-add').click(function(){
        var qty = $('#orderitemform-qty').val();
        if(null == selectedProduct)
        {
            $('#product-add-form .warning-active').text('请添加商品');
            return false;
        }
        if(null == selectedProductPrice && selectedProduct.is_area_price)
        {
            $('#product-add-form .warning-active').text('请选择地区');
            return false;
        }
        else
        {
           $('#product-add-form .warning-active').text('');
        }
        if('' == qty || parseInt(qty) < 1)
        {
            return false;
        }
        if( !/^\d+$/.test(qty))
        {
            return false;
        }
        
        if(!selectedProduct.is_area_price){
            
            selectedProductPrice = null;
        }
        var qty = $('#orderitemform-qty').val();
        var item = $($('#order-item-template').find('.item').clone());
        item.find('.product_id').val($('#orderitemform-product_id').val());
        item.find('.product_price_id').val($('#orderitemform-product_price_id').val());
        item.find('.qty').val(qty);
        item.appendTo('#item-box');
        //价格
        var price = selectedProductPrice == null ? selectedProduct.price : selectedProductPrice.price;
        price = price * 100;
        var sub_total = qty * price;
        total += sub_total;
        $('#total-price').text(total/100);
        
        //税额
        var tax = selectedProductPrice == null ? selectedProduct.tax : selectedProductPrice.tax;
        if(isNaN(tax))
        {
           tax = 0;
        }
        tax = tax * 100;
        var tax_total = qty * tax;
        taxTotal += tax_total;
        
        //包含税额总计
        var tax_price_total = taxTotal + total;
        
        //选中开发票后添加商品
        // if($('#is_need_invoice').is(':checked')){
        //     $('#total-price').text(tax_price_total/100);
        //     $('#total-tax').text(taxTotal/100); 
        // }

        var row = orderRowTemplate.replace('{product_name}', selectedProduct.name).replace('{price}', price/100)
        .replace('{price}', price/100).replace('{qty}', qty)
        .replace('{sub_total}', sub_total/100).replace('{tax}', tax/100);
        $('#order-items').append($(row));
        $('#product-add-modal').modal('hide');
        deletClick();
        return false;
    });
    
    $('.add-product-btn').click(function() {
        //特殊处理清空，可优化js
      $('#product-add-form .warning-active').text('');
    });
    //开具发票
    // $('#is_need_invoice').click(function(){
    //     if($('#is_need_invoice').is(':checked'))
    //     {
    //         var items = $('.item-price');
    //         var tax = 0;
    //         for(var i = 0; i < items.length; i++)
    //         {
    //             tax += ($(items[i]).attr('data-tax')*100);
    //         }
    //         $('#total-tax').text(tax/100);
    //         $('#total-price').text((tax+total)/100);
    //         $('.invoice-message').show();
    //     }
    //     else
    //     {
    //         $('#total-tax').text('0.00');
    //         $('#total-price').text(total/100);
    //         $('.invoice-message').hide(); 
    //     }
    // });
JS
);?>
<?php endif;?>
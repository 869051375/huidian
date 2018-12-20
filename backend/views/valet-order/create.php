<?php
/* @var $this yii\web\View */
use backend\models\OrderForm;
use backend\models\ValetOrderForm;
use common\models\Administrator;
use common\models\Salesman;
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;


$this->title = '创建订单';
$this->params['breadcrumbs'] = [
    ['label' => '订单管理', 'url' => ['order-list/all']],
    $this->title
];
/** @var \common\models\Salesman $salesman */
$administrator_id = Yii::$app->user->id;
$salesman = Salesman::find()->where(['administrator_id'=>$administrator_id,'status'=> Salesman::STATUS_ACTIVE])->one();
?>
<?php if (Yii::$app->user->can('order/create')): ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>基本信息</h5>
                </div>
                <div class="ibox-content">
                    <?php
                    $index = 0;
                    $item = new ValetOrderForm();
                    $hidden_input_product_id = Html::activeHiddenInput($item, '[products][{index}]product_id', ['id' => false,'value' => '{val_product_id}']);
                    if(Yii::$app->user->can('order-action/adjust-price'))
                    {
                        $input_price = Html::activeTextInput($item, '[products][{index}]price', ['class' => 'form-control text-right adjust_price', 'id' => false,'value' => '{val_price}']);
                    }
                    else
                    {
                        $input_price = Html::activeTextInput($item, '[products][{index}]price', ['class' => 'form-control text-right adjust_price', 'id' => false,'value' => '{val_price}','disabled' => 'disabled']);
                    }
                    $hidden_input_product_price_id = Html::activeHiddenInput($item, '[products][{index}]product_price_id', ['id' => false,'value' => '{val_product_price_id}']);
                    $input_qty = Html::activeHiddenInput($item, '[products][{index}]qty', ['class' => 'form-input-qty text-center input-sty27', 'id' => false,'value' => '{val_qty}']);
                    $line = str_replace(["\n", "\r", "\n\r"], ' ', '<tr data-id="{id}">
                <td style="vertical-align: middle;" class="text-center">{hidden_input_product_id}{product_name}</td>
                <td style="vertical-align: middle;" class="text-center">{district}</td>
                <td style="vertical-align: middle;" width="100px" class="text-center">{input_price}</td>
                <td style="vertical-align: middle;" class="text-center"><span class="original-price">{original_price}</span> {hidden_input_product_price_id}</td>
                <td style="vertical-align: middle;" class="text-center">{input_qty}{qty}</td>
                <td style="vertical-align: middle;" class="text-center subtotal_price">{subtotal_price}</td>
                <td style="vertical-align: middle;" class="text-center">{installment}</td>
                <td style="vertical-align: middle;" class="text-center">
                    <button type="button" class="btn btn-xs btn-danger delete-product">删除</button>
                </td>
            </tr>');
                    $orderForm = new ValetOrderForm();
                    $orderForm->order_time = date('Y-m-d H:i');
                    $orderForm->salesman_id = $salesman ? $salesman->administrator_id :'';
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'id' => 'order-create-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-md-2',
                                'offset' => 'col-md-offset-2',
                                'wrapper' => 'col-md-8',
                                'hint' => 'col-md-2',
                            ],
                        ],
                    ]); ?>

                    <?php if (isset($niche['customer_name'])):?>
                    <?= $form->field($orderForm, 'user_name')->textInput(['readonly'=>'true','value'=>$niche['customer_name']]); ?>
                        <?= Html::activeHiddenInput($orderForm,'user_id',['value'=>$niche['customer_id']]) ?>
                        <span class="niche_span" style="display: none"><?php echo $niche['customer_name'];?></span>
                    <?php else:?>
                    <?= $form->field($orderForm, 'user_name')->textInput(['readonly'=>'true','placeholder'=>'请选择已有客户'])->hint('<a id="sel-user" data-target="#sel_Carousel" data-toggle="modal">选择已有</a>'); ?>
                        <?= Html::activeHiddenInput($orderForm,'user_id') ?>
                    <?php endif;?>
                    <?= $form->field($orderForm, 'order_time')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd h:i:s',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'hour',
                        ],
                        'clientEvents' => []]) ?>
                    <?php if (isset($niche['business_subject_id'])):?>
                        <?= $form->field($orderForm,'subject_info')->dropDownList([$niche['business_subject_id'] => $niche['business_subject_name']]); ?>
                    <?php else:?>
                        <?= $form->field($orderForm,'subject_info')->dropDownList(['0' => '请选择业务办理主体']); ?>
                    <?php endif;?>
                    <div class="page-select2-area">
                        <?php if (isset($niche['administrator_id'])):?>
                            <?= $form->field($orderForm, 'salesman_id')->widget(Select2Widget::className(), [
                                'selectedItem' => [$niche['administrator_id']=>$niche['administrator_name']],
                                'nameField' => 'name',
                                'searchKeywordName' => 'keyword',
                                'width' => '733px',
                                'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN]),
                                'itemsName' => 'items',
                            ])->label('业务员');?>
                        <?php else:?>
                            <?= $form->field($orderForm, 'salesman_id')->widget(Select2Widget::className(), [
                                'selectedItem' => $salesman ? [$salesman->administrator_id => $salesman->name] : [],
                                'nameField' => 'name',
                                'searchKeywordName' => 'keyword',
                                'width' => '733px',
                                'serverUrl' => \yii\helpers\Url::to(['administrator/ajax-list', 'type' => \common\models\Administrator::TYPE_SALESMAN]),
                                'itemsName' => 'items',
                            ])->label('业务员');?>
                        <?php endif;?>


                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 text-right">添加商品*</label>
                        <div class="col-sm-8">

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center">商品名称</th>
                                    <th class="text-center">服务区域</th>
                                    <th class="text-center">变动金额(元)</th>
                                    <th class="text-center">销售单价(元)</th>
                                    <th class="text-center">数量</th>
                                    <th class="text-center">销售价格小计</th>
                                    <th class="text-center">付款方式</th>
                                    <th class="text-center">操作</th>
                                </tr>
                                </thead>
                                <tbody id="order-items">

                                <?php if (!empty($niche_product)):?>
                                <?php foreach ($niche_product as $k => $item)?>
                                    <tr data-id="<?php echo $item['product_id'];?>">
                                        <td style="vertical-align: middle;" class="text-center"><input type="hidden" name="ValetOrderForm[products][<?php echo $k+1;?>][product_id]" value="<?php echo $item['product_id'];?>"><?php echo $item['product_name'];?></td>

                                        <td style="vertical-align: middle;" class="text-center"><?php echo $item['province_name'],'   ',$item['city_name'],'  ',$item['district_name'];?></td>

                                    <td style="vertical-align: middle;" width="100px" class="text-center">
                                            <input type="text" class="form-control text-right adjust_price" name="ValetOrderForm[products][<?php echo $k+1;?>][price]" value="0"></td>

                                        <td style="vertical-align: middle;" class="text-center"><span class="original-price"><?php echo $item['price'];?></span> <input type="hidden" name="ValetOrderForm[products][<?php echo $k+1;?>][product_price_id]" value="<?php echo $item['service_area'];?>"></td>

                                        <td style="vertical-align: middle;" class="text-center"><input type="hidden" class="form-input-qty text-center input-sty27" name="ValetOrderForm[products][<?php echo $k+1;?>][qty]" value="1"><?php echo $item['qty'];?></td>

                                    <td style="vertical-align: middle;" class="text-center subtotal_price"><?php echo $item['price'];?></td>

                                        <td style="vertical-align: middle;" class="text-center"><?php if ($item['is_installment'] == '1'){ echo '分期付款';} else {echo '一次付款';};?></td>

                                        <td style="vertical-align: middle;" class="text-center"><button type="button" class="btn btn-xs btn-danger delete-product">删除</button></td>
                                    </tr>
                                <?php endif;?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="8">
                                        <div class="row">
                                            <div class="col-sm-6 text-left" style="display: inline-block;">
                                                <button type="button" class="btn btn-primary btn-sm" data-target="#add-modal" data-toggle="modal">添加商品</button>
                                            <span style="font-size:16px;color:red;padding-top: 16px; display:none;padding-left: 20px;" id="changeRed">订单变动金额超出范围 , 请检查！</span>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                申请变动价格总计：<span id="total-adjust-price" style="margin-right: 20px;"></span>
                                                订单价格总计：<span id="total-amount"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                            <div class="row">
                                <div class="col-sm-12 text-left">
                                    提交订单后，如存在订单价格变动情况，将给订单负责业务员的当前部门主管发送订单价格变动审批通知短信
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-2">
                            <button class="main-bg btn btn-primary sub"  type="submit" value="save">提交</button>
                            <a href="<?= \yii\helpers\Url::to(['order-list/all']) ?>" class="btn btn-default" style="margin-left: 25px;">取消</a>
                        </div>
                    </div>
                    <div id="item-box">
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
                                        'serverUrl' => ['product/ajax-list', 'is_show_list' => '1'],
                                        'itemsName' => 'products',
                                        'nameField' => 'alias',
                                        'attribute' => 'product_id',
                                        'placeholderId' => '0',
                                        'placeholder' => '请选择商品',
                                        'searchKeywordName' => 'keyword',
                                        'eventSelect' => new JsExpression("
                                $('#product-add-form .warning-active').text('');
                                selectedProduct = env.params.data;
                                selectedProductPrice = null;
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
                                        $('#product-add-form .warning-active').text('');
                                        selectedProductPrice = null;
                                        if(env.params.data.id != 0)
                                        {
                                            selectedProductPrice = env.params.data;
                                        }
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
                            <?php $modelHidden = new ValetOrderForm(); ?>
                            <?= Html::activeHiddenInput($modelHidden, 'items[product_id][]', ['id' => false, 'class' => 'product_id']); ?>
                            <?= Html::activeHiddenInput($modelHidden, 'items[product_price_id][]', ['id' => false, 'class' => 'product_price_id']); ?>
                            <?= Html::activeHiddenInput($modelHidden, 'items[qty][]', ['id' => false, 'class' => 'qty']); ?>
                        </div>
                    </div>
                    <!--添加商品end-->
                    <!-- 选择客户start -->
                    <div class="modal fade" id="sel_Carousel" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document"  style="width: 855px;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">选择客户</h4>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    $form = \yii\bootstrap\ActiveForm::begin([
                                        'layout' => 'inline',
                                        'method' => 'get',
                                    ]); ?>
                                    <?= $form->field($searchModel, 'keyword')->textInput(['placeholder'=>'请输入客户名称或联系方式','style'=>'width:700px;']) ?>
                                    <button type="button" class="btn btn-default sosuo">搜索</button>
                                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                                    <span class="warning-text" style="color: red"></span>
                                    <br><div class="row" id="user"></div>
                                </div>
                                <div class="modal-footer">
                                    <span class="text-danger warning-active"></span>
                                    <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                                    <button type="button" class="btn btn-primary sure-btn sel-sure">确定</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 选择客户end -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" data-id="" id="add-modal" role="dialog" aria-labelledby="modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                $formModel = new \backend\models\OpportunityProductForm();
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
                    <?= $form->field($formModel, 'product_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                        'selectedItem' => [],
                        'serverUrl' => ['product/ajax-list'],
                        'itemsName' => 'products',
                        'nameField' => 'alias',
                        'attribute' => 'product_id',
                        'placeholderId' => '0',
                        'placeholder' => '请选择商品',
                        'searchKeywordName' => 'keyword',
                        'eventSelect' => new JsExpression("
                        selectedProduct = env.params.data;
                        if(env.params.data.is_area_price){
                            $('.field-opportunityproductform-product_price_id').css('display', 'block');
                        }else{
                            $('.field-opportunityproductform-product_price_id').css('display', 'none');
                        }
                        $('#opportunityproductform-product_price_id').val('0').trigger('change');
                    ")
                    ]);
                    $districtsUrl = \yii\helpers\Url::to(['product/ajax-districts', 'product_id' => '__product_id__']);
                    echo $form->field($formModel, 'product_price_id')->widget(\imxiangli\select2\Select2Widget::className(), [
                        'selectedItem' => [],
                        'options' => ['class' => 'form-control', 'prompt'=>'请选择地区'],
                        'placeholderId' => '0',
                        'placeholder' => '请选择地区',
                        'serverUrl' => $districtsUrl,
                        'itemsName' => 'districts',
                        'eventOpening' => new JsExpression("
                        var id = $('#opportunityproductform-product_id').val();
                        serverUrl = '{$districtsUrl}'.replace('__product_id__', id ? id : '-1');
                    "),
                        'eventSelect' => new JsExpression("
                        selectedProductPrice = env.params.data;
                    ")
                    ])?>
                    <?= $form->field($formModel, 'qty')->textInput() ?>
                </div>
                <div class="modal-footer">
                    <span class="text-danger warning-active"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary add-item-confirm">确定</button>
                </div>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>

<div class="modal fade" id="order-modal" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">错误提示</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button class="btn btn-primary sure-btn btn-url">确定</button>
            </div>
        </div>
    </div>
</div>

<?php
$status = Yii::$app->getRequest()->get('status');
$order_list_url = Url::to(['order-list/all']);
$virtual_order_list_url = Url::to(['virtual-order-list/all']);
$this->registerJs(<<<JS
$('#order-create-form').on('beforeSubmit', function()
{
    var url_status = '{$status}';
    var order_url = '{$order_list_url}';
    var virtual_order_list_url = '{$virtual_order_list_url}';
    var form = $(this);
    form.find('.sub').attr('disabled','disabled').text('提交中...');
    $.post(form.attr('action'), form.serialize(), function(rs)
    {
        if(rs.status === 200)
        {
            if(url_status == 1)
            {
                var niche_span = $('.niche_span').text();
                if (niche_span === '')
                {
                   window.location.href = order_url;
                }
                else
                {
                    window.history.go(-1);    
                }
            }
            else if(url_status == 2)
            {
                window.location.href = virtual_order_list_url;
            }
            else
            {
                window.location.href = order_url;
            }
        }
        else if(rs.status === 400)
        {
            var order_modal = $('#order-modal');
            order_modal.find('.modal-body').empty();
            $('.modal-footer .cancel-btn').html('确定').show();
            order_modal.find('.modal-body').append('<div class="text-danger">'+rs.err+'</div>');
            order_modal.modal('show');
            $('.modal-footer .sure-btn').hide();
            form.find('.sub').removeAttr('disabled').text('提交');
        }
    }, 'json');
    return false;
});
JS
    , \yii\web\View::POS_END);
?>

<?php
$this->registerJs(<<<JS
var index = parseInt('{$index}');
var selectedProduct = null;
var selectedProductPrice = null;
var line = '{$line}';
var hidden_input_product_price_id = '{$hidden_input_product_price_id}';
var hidden_input_product_id = '{$hidden_input_product_id}';
var input_price = '{$input_price}';

var input_qty = '{$input_qty}';
$.fn.select2.defaults.set('width', '100%');
$('#add-modal').on('show.bs.modal', function(){
    selectedProductPrice = null;
    selectedProduct = null;
    $('#opportunityproductform-product_id').val('0').trigger('change');
    $('#opportunityproductform-product_price_id').val('0').trigger('change');
    $('#add-modal').find('.warning-active').text('');
    $('#product-add-form').trigger('reset.yiiActiveForm');
});
$('.add-item-confirm').click(function(){
    var indexStr = ""+(++index);
    var product_price_id = 0;
    var qty = $('#opportunityproductform-qty').val();
    if(!selectedProduct['is_area_price'])
    {
        selectedProductPrice = null;
    }
    
    if(null === selectedProduct)
    {
        $('#add-modal').find('.warning-active').text('请添加商品');
        return false;
    }
    if(selectedProduct['is_area_price'] && null === selectedProductPrice)
    {
        $('#add-modal').find('.warning-active').text('请选择地区');
        return false;
    }
    else
    {
       $('#add-modal').find('.warning-active').text('');
    }
    if((/^(\+|-)?\d+$/.test(qty))&& qty > 0){
    
    }else{  
        $('#add-modal').find('.warning-active').text('数量必须是整数，并且不能小于1');
        $("#opportunityproductform-qty").val('1');  
        return false;  
    } 
    var price = selectedProduct['is_bargain'] == 1 ? 0 : selectedProduct['price'];
    if(selectedProductPrice)
    {
        product_price_id = selectedProductPrice['id'];
        price = selectedProductPrice['price'];
    }
    var productIdInput = hidden_input_product_id.replace('{val_product_id}', selectedProduct.id).replace('{index}', indexStr);
    
    var priceInput = input_price.replace('{val_price}',0).replace('{index}', indexStr);
    
    var productPriceIdInput = hidden_input_product_price_id.replace('{val_product_price_id}', product_price_id).replace('{index}', indexStr);

    var district = '全国';
    if(selectedProduct['is_area_price'] == 1)
    {
        district = selectedProductPrice.name.slice(0,selectedProductPrice.name.indexOf("("));
    }
    var installment = '一次付款';
    if(selectedProduct['is_installment'] == 1)
    {
        installment = '分次付款';
    }
    var product_name = selectedProduct.name;
    
    var qtyInput = input_qty.replace('{val_qty}', qty).replace('{index}', indexStr);
    var  subtotal_price = accMul(price, qty);
    $('#order-items').append($(line
    .replace('{product_name}', product_name)
    .replace('{district}', district)
    .replace('{input_price}', priceInput)
    .replace('{original_price}', price)
    .replace('{hidden_input_product_id}', productIdInput)
    .replace('{hidden_input_product_price_id}', productPriceIdInput)
    .replace('{input_qty}', qtyInput)
    .replace('{qty}', qty)
    .replace('{installment}', installment)
    .replace('{subtotal_price}', fmoney(subtotal_price,2))
    ));
    $('#add-modal').modal('hide');
    sum();

});
$('#order-items').delegate('tr td input.adjust_price','blur',function(){
    sum();
});
$('#order-items').delegate('tr td input.form-control','blur',function(){
    sum();
});
$('#order-items').delegate('tr td button.delete-product','click',function(){
    $(this).parents('tr').remove();
    sum();
});

$('#order-items').delegate('tr td input.form-input-qty','blur',function(){
    sum();
});
$('#order-items').delegate('tr td button.delete-input-qty','click',function(){
    $(this).parents('tr').remove();
    sum();
});


function sum()
{
    var order_items = $('#order-items tr');
    var length = order_items.size();
    var sum = 0;  //存需要多少钱
    var cheap = 0;  //存便宜多少钱
    var cheapMoney =0 ;
    $("#changeRed").css('display','none');
    $(".sub").removeAttr('disabled').text('提交');
    for (var i=0; i < length; i++)
    {
        var val = order_items.eq(i).children('td').find('.original-price').text();                         //单个商品价格
        var adjust_price = order_items.eq(i).children('td').find('.adjust_price').val();                   //变动金额
        var qty = order_items.eq(i).children('td').find('input.form-input-qty').val();                     //数量           
        var unit_price = add(val,adjust_price);                                                            //销售单价
        var total = accMul(unit_price, qty);                                                               //销售价格小计
        cheap += adjust_price*qty;
        order_items.eq(i).find('.subtotal_price').text(total);
        if(total<0 ){
          $("#changeRed").css('display','inline-block');
           $(".sub").attr('disabled','disabled').text('提交');
        }
        sum += Number(total);
    }
      $('#total-amount').text('￥'+sum);
      $('#total-adjust-price').text('￥'+cheap);
}

sum();


function accMul(arg1,arg2)     
{
    var m=0,s1=arg1.toString(),s2=arg2.toString();
    try{m+=s1.split(".")[1].length}catch(e){}     
    try{m+=s2.split(".")[1].length}catch(e){}     
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)     
}

function add(a, b) 
{
    var c, d, e;
    try {
        c = a.toString().split(".")[1].length;
    } catch (f) {
        c = 0;
    }
    try {
        d = b.toString().split(".")[1].length;
    } catch (f) {
        d = 0;
    }
    return e = Math.pow(10, Math.max(c, d)), (mul(a, e) + mul(b, e)) / e;
}

function mul(a, b) 
{
    var c = 0,
        d = a.toString(),
        e = b.toString();
    try {
        c += d.split(".")[1].length;
    } catch (f) {}
    try {
        c += e.split(".")[1].length;
    } catch (f) {}
    return Number(d.replace(".", "")) * Number(e.replace(".", "")) / Math.pow(10, c);
}

JS
)?>


<?php
$getUser = \yii\helpers\Url::to(['get-user']);
$subjectInfo = \yii\helpers\Url::to(['subject-info']);
$this->registerJs(<<<JS
$(function() 
{
    var niche_span = $('.niche_span').text();
    if (niche_span === '')
    {
        $('#valetorderform-subject_info').attr('disabled','true');
    }
})

$('#sel-user').click(function()
{
  $('#user').empty();
  $('.warning-active').empty();
})
//获取已有客户
$('.sosuo').click(function()
{
  // var starting_time = $('#starting_time').val();
  // var end_time = $('#end_time').val();
  var keyword = $('#keyword').val();
  $.post('{$getUser}',{keyword:keyword},function(rs)
  {
    if(rs.status === 200)
    {
        if(rs.data !== null)
        {
            $('.warning-active').empty();
            var result ='<table class="table table-bordered">'+
                    '<thead>'+
                    '<tr>'+
                        '<th>选择</th>'+
                        '<th>客户姓名/昵称</th>'+
                        '<th>手机号</th>'+
                        '<th>客户来源</th>'+
                        '<th>创建时间</th>'+
                        '<th>最后登录时间</th>'+
                    '</tr>'+
                    '</thead>'+
                    '<tbody class="user-tab">';
            for(var i = 0;i < rs.data.length ; i++)
            {
                result +=
                    '<tr>'+
                    '<td><input type="radio" value='+rs.data[i]['id']+' name="sel" data-name='+rs.data[i]['name']+'></td>'+
                    '<td>'+rs.data[i]['name']+'</td>'+
                    '<td>'+rs.data[i]['phone']+'</td>'+
                    '<td>'+rs.data[i]['source_name']+'</td>'+
                    '<td>'+rs.data[i]['created_at']+'</td>'+
                    '<td>'+rs.data[i]['last_login']+'</td>'+
                    '</tr>';
            }
            result +='</table></tbody>';
            $('#user').html(result);
            $('.warning-text').html('');
        }
    }else{
        $('.warning-text').html(rs.error);
        $('#user').empty();
    }
    
  })
})
//选择客户
$('.sel-sure').click(function()
{
  var user_id = $('.user-tab input[type="radio"]:checked').val();
  var user_name = $('.user-tab input[type="radio"]:checked').attr('data-name');
  $('#valetorderform-user_name').val(user_name);
  $('#valetorderform-user_id').val(user_id);
  $.get('{$subjectInfo}',{user_id:user_id},function(rs) 
  {
    if(rs.status==200)
    {
        var result = '<option value="0">请选择业务办理主体</option>';
        for(var i = 0;i < rs.data.length ; i++)
        {
            result +=
                    '<option value='+rs.data[i]['id']+'>'+rs.data[i]['name']+'</option>';
        }
        $('#valetorderform-subject_info').removeAttr('disabled').html(result);
    }
    
  })
  $('#sel_Carousel').modal('hide');
})



JS
);?>
<?php endif;?>
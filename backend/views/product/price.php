<?php
/* @var $this yii\web\View */
use common\models\PackageProduct;
use common\utils\BC;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;

/** @var \backend\models\PriceForm $model */
/** @var \backend\models\PackagePriceForm $model */
/** @var \backend\models\DistrictPriceForm $districtPriceModel */
/** @var \common\models\Product $product */
/** @var array $industries */
$hasSavePrice = Yii::$app->user->can('product-price/update');
$titleName = '';
$labelName = '';
$breadcrumbsUrl = '';
if($product->isPackage())
{
    $titleName = '套餐价格';
    $labelName = '套餐商品列表';
    $breadcrumbsUrl = ['package-list'];
    $productPriceForm = 'packagepriceform';
    $priceAction = ['product/save-package-price', 'product_id'=>$product->id, 'save_name' => '__save_name__'];
//    $priceForm = 'package-price-form';
}
else
{
    $titleName = '商品价格';
    $labelName = '标准商品列表';
    $breadcrumbsUrl = ['list'];
    $productPriceForm = 'priceform';
    $priceAction = ['product/save-price', 'product_id'=>$product->id, 'save_name' => '__save_name__'];
//    $priceForm = 'product-price-form';
}
$this->title = $titleName;
$this->params['breadcrumbs'] = [
    ['label' => $labelName, 'url' => $breadcrumbsUrl],
    $this->title
];
?>
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
            <?php if($product->isPackage()):?>
                <?= $this->render('package-nav-tabs', ['product' => $product]) ?>
            <?php else:?>
                <?= $this->render('nav-tabs', ['product' => $product]) ?>
            <?php endif;?>
            <div class="tab-content">
                <div class="panel-body" style="border-top: none">
                    <?php
                    if($product->isPackage())
                    {
                        $original_price = PackageProduct::getPackageOriginalPrice($product);
                        $remit_amount = BC::sub($original_price,$product->price) > 0 ? BC::sub($original_price, $product->price) : 0.00;
                    }
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => $priceAction,
                        'enableAjaxValidation' => false,
                        'id' => 'product-price-form',
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
                    <?php /* 押金暂时去掉！ ?>
                    <?= $form->field($model, 'deposit')->textInput($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?php */ ?>
                    <?= $form->field($model,'is_installment')->dropDownList(\backend\models\PriceForm::getInstallmentList()); ?>
                    <?php if(!$product->isPackage()):?>
                        <?= $form->field($model, 'is_bargain')->checkbox($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?php endif;?>
                    <?php if($product->isPackage() && PackageProduct::isAreaPrice($product->id)):?>
                        <?= $form->field($model, 'is_area_price')->checkbox(['disabled' => 'disabled']) ?>
                    <?php else:?>
                        <?= $form->field($model, 'is_area_price')->checkbox($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?php endif;?>
                    <?= $form->field($model, 'service_area')->textInput($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?php if($product->isPackage()):?>
                        <?= /** @var string $original_price */
                        $form->field($model, 'original_price')->textInput(['disabled' => 'disabled', 'value' => $original_price]) ?>
                    <?php else:?>
                        <?= $form->field($model, 'original_price')->textInput($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?php endif;?>
                    <?= $form->field($model, 'wx_remit_amount')->textInput($hasSavePrice ? [] : ['disabled' => 'disabled']) ?>
                    <?= $form->field($model, 'price')->textInput(['disabled'=>'disabled']) ?>
                    <?php if($product->isPackage() && !$product->isBargain() && !$product->isAreaPrice()):?>
                        <?= $form->field($model, 'remit_amount')->textInput(['disabled'=>'disabled', 'value' => $remit_amount]) ?>
                    <?php endif;?>
                    <?= $form->field($model, 'tax')->textInput(['disabled'=>'disabled']) ?>
                    <!--新增价格明细start-->
                    <?php
                    $priceDetail = $product->getPriceDetail();
                    $priceDetailList = '';
                    $priceDetailTemplate = '<tr data-key="{key}"><td>{name}</td><td>{price}</td><td>{unit}</td><td>{tax_rate}%</td><td>{is_invoice}</td><td>'.($hasSavePrice ? '<span class="price-detail-delete btn btn-xs btn-link text-danger" data-target="#delete-price-detail" data-toggle="modal" data-id="'.$product->id.'">删除</span>' : '').'</td></tr>';
                    foreach ($priceDetail as $key => $value){
                        $priceDetailList .= str_replace(['{key}', '{name}', '{price}', '{unit}', '{tax_rate}', '{is_invoice}'],
                            [$value['key'], $value['name'], $value['price'], $value['unit'], $value['tax_rate'], $value['is_invoice'] ? '是' : '否'], $priceDetailTemplate);
                    }
                    ?>
                    <?= $form->field($model, 'price_detail', [
                        //'options' => ['wrapper' => 'col-xs-5'],
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-xs-8',
                        ],
                        'template' => '{label} {beginWrapper} <div id="price-detail-list"><table class="table table-bordered table-hover"><thead>
                                            <tr><th>费用类型</th><th>费用(元)</th><th>单位</th><th>税率</th><th>支持开发票</th><th>操作</th></tr></thead><tbody>' .$priceDetailList.'</tbody></table>
                                            </div>'.($hasSavePrice ?'<span class="btn btn-default" data-target="#price-detail-add-modal" data-toggle="modal">添加</span>' : '').' {error} {endWrapper} {hint}'
                    ])->textInput(); ?>
                    <!--新增价格明细end-->
                    <!--新增省市区及价格start-->
                    <?php
                    //套餐商品有优惠价格
                    $remitAmount = '';
                    $replaceRemitAmount = '';
                    if($product->isPackage())
                    {
                        $remitAmount = '<th class="text-right">优惠金额</th>';
                        $replaceRemitAmount = '<td class="text-right remit_amount">{remit_amount}</td>';
                    }
                    //获取商品的区分区域商品原价
                    $districtsPrice = $product->productPrices;
                    $districtPriceList = '';
                    // $districtPriceTemplate = '<tr data-id="{id}"><td>{district_name}</td><td class="text-right price">{price}</td><td class="text-right tax">{tax}</td><td class="text-right original_price">{original_price}</td>'.$replaceRemitAmount.'<td><span class="btn btn-xs btn-link see-district-price-detail" data-target="#district-price-detail-add-modal" data-toggle="modal">查看</span></td><td>{sort}</td><td><span class="btn btn-xs btn-link district-price-status" data-target="#district-price-status-modal" data-toggle="modal" data-id="{id}" data-status="{status}">{statusName}</span><span class="btn btn-xs btn-link district-price-update" data-target="#district-price-update-modal" data-toggle="modal" data-original_price="{original_price}" data-sort="{sort}" data-district_name="{district_name}" data-id="{id}">编辑</span>'.($hasSavePrice ? '<span class="btn btn-xs btn-link text-danger district-price-delete" data-target="#district-price-delete-modal"  data-toggle="modal">删除</span>' : '').'</td></tr>';                    $districtPriceTemplate = '<tr data-id="{id}"><td>{district_name}</td><td class="text-right price">{price}</td><td class="text-right tax">{tax}</td><td class="text-right original_price">{original_price}</td>'.$replaceRemitAmount.'<td><span class="btn btn-xs btn-link see-district-price-detail" data-target="#district-price-detail-add-modal" data-toggle="modal">查看</span></td><td>{sort}</td><td><span class="btn btn-xs btn-link district-price-status" data-target="#district-price-status-modal" data-toggle="modal" data-id="{id}" data-status="{status}">{statusName}</span><span class="btn btn-xs btn-link district-price-update" data-target="#district-price-update-modal" data-toggle="modal" data-original_price="{original_price}" data-sort="{sort}" data-district_name="{district_name}" data-id="{id}">编辑</span>'.($hasSavePrice ? '<span class="btn btn-xs btn-link text-danger district-price-delete" data-target="#district-price-delete-modal"  data-toggle="modal">删除</span>' : '').'</td></tr>';
                    $districtPriceTemplate = '<tr data-id="{id}"><td><input class="price_checkbox" type="checkbox"/></td><td>{district_name}</td><td class="text-right price">{price}</td><td class="text-right tax">{tax}</td><td class="text-right original_price">{original_price}</td>'.$replaceRemitAmount.'<td><span class="btn btn-xs btn-link see-district-price-detail" data-target="#district-price-detail-add-modal" data-toggle="modal">查看</span></td><td>{sort}</td><td><span class="btn btn-xs btn-link district-price-update" data-target="#district-price-update-modal" data-toggle="modal" data-original_price="{original_price}" data-sort="{sort}" data-district_name="{district_name}" data-id="{id}">编辑</span>'.($hasSavePrice ? '<span class="btn btn-xs btn-link text-danger district-price-delete" data-target="#district-price-delete-modal"  data-toggle="modal">删除</span>' : '').'</td></tr>';
                    foreach ($districtsPrice as $priceModel){
                        $districtPriceList .= str_replace(['{id}', '{district_name}', '{price}', '{tax}', '{original_price}',  '{sort}', '{remit_amount}', '{status}', '{statusName}'],
                            [$priceModel->id, $priceModel->getRegionFullName(), $priceModel->price, $priceModel->tax, $priceModel->original_price, $priceModel->sort, $priceModel->getRemitAmount(), $priceModel->status, $hasSavePrice ? ($priceModel->isEnabled() ? '禁用' : '启用'): ''], $districtPriceTemplate);
                    }
                    ?>
                    <?= $form->field($model, 'district_price', [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-xs-8',
                        ],
                        'template' => '{label} {beginWrapper} <div id="district-price-list"><table class="table table-bordered table-hover"><thead>
                        <tr><th><input type="checkbox" id="product_check_all"/></th><th>地区</th><th class="text-right">售价</th><th class="text-right">税额</th><th class="text-right">原价</th>'.$remitAmount.'<th>价格明细</th><th>排序</th><th>操作</th></tr></thead><tbody>'.$districtPriceList.'</tbody></table>
                        </div>'.($hasSavePrice ? '<span class="btn btn-default package-confirm-add" data-target="#district-price-add-modal" data-toggle="modal">添加</span>':'').' {error} {endWrapper} {hint}'
                    ])->textInput(); ?>
                    <?php
                    $bathUrl = \yii\helpers\Url::to(['bath-district-price-status', 'status' => '__status__']);
                    $this->registerJs(<<<JS
                        let table = $('#district-price-list table');
                        $('#product_check_all').on('click',function(e){
                            let isChecked = $(this).prop('checked');
                            let items = table.find('.price_checkbox');
                            items.prop('checked', isChecked);
                        })
                        $('.price_checkbox').on('click',function(){
                            let curentChecked = $(this).prop('checked');
                            let isAllChecked = [].slice.call(table.find('.price_checkbox')).every(function(item){
                                return $(item).prop('checked') === true;
                            });
                            let isAllUnChecked = [].slice.call(table.find('.price_checkbox')).every(function(item){
                                return $(item).prop('checked') === false;
                            });
                            if(curentChecked&&isAllChecked) {
                                $('#product_check_all').prop('checked',true);
                                $('#product_check_all').prop('indeterminate',false);
                            }else if(curentChecked&&!isAllChecked) {
                                $('#product_check_all').prop('checked',false);
                                $('#product_check_all').prop('indeterminate',true);
                            }else if(!curentChecked&&isAllChecked) {
                                $('#product_check_all').prop('checked',false);
                                $('#product_check_all').prop('indeterminate',true);
                            }else if(!curentChecked&&isAllUnChecked) {
                                $('#product_check_all').prop('checked',false);
                                $('#product_check_all').prop('indeterminate',false);
                            }else if(!curentChecked&&!isAllChecked&&!isAllUnChecked) {
                                $('#product_check_all').prop('checked',false);
                                $('#product_check_all').prop('indeterminate',true);
                            }
                        });
                        //全部启用或禁用
                        $('.common-btn').on('click',function(){
                            let enableIds = [];
                            [].slice.call(table.find('.price_checkbox')).forEach(function(item){
                                if($(item).prop('checked')) {
                                    enableIds.push($(item).parent().parent().attr('data-id'));
                                }
                            });
                            if(enableIds.length > 0) {
                                let bathUrl = '{$bathUrl}';
                                // let url = $(this).attr('class').includes('enable-all') ? 'enableUrl' : 'disableUrl';
                                let url = $(this).attr('class').includes('enable-all') ? bathUrl.replace('__status__', '1') : bathUrl.replace('__status__', '0');
                                console.log(url);
                                let promise = new Promise(function(resolve,reject){
                                    $('#bath-district-price-status-modal .warning-active').text(''); 
                                    $('#bath-district-price-status-modal .sure-btn').unbind('click');
                                    $('#bath-district-price-status-modal .sure-btn').on('click',function(){
                                        $.post(url, {ids:enableIds}, function(result){
                                            if(result.status === 200){
                                                // resolve(result);
                                                window.location.reload();
                                            }else{
                                                // reject(result.message);
                                                $('#bath-district-price-status-modal .warning-active').text(result.message);   
                                            }
                                        }, 'json');
                                        return false;
                                    });
                                });
                                promise.then(function(result){
                                    window.location.reload();
                                }).catch(function(errMessage){
                                    console.log(errMessage);
                                });
                            }
                        })
JS
                    );?>

                    <!--新增省市区及价格end-->
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-2">
                            <div class="row data-save-name" data-name="">
                                <div class="col-xs-3">
                                    <?php if(!$product->isPackage() && !empty($product->packages) ):?>
                                        <?php echo $hasSavePrice ? '<button class="main-bg btn btn-primary save-btn" type="submit" name="next" value="package-save">保存</button>' : '';?>
                                    <?php else:?>
                                        <?php echo $hasSavePrice ? '<button class="main-bg btn btn-primary save-btn" type="submit" name="next" value="save">保存</button>' : '';?>
                                    <?php endif;?>
                                </div>
                                <div class="col-xs-4">
                                    <?php echo $hasSavePrice ? '<button class="main-bg btn btn-primary save-btn" type="submit" name="next" value="save-next">保存并下一步</button>' : '';?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php \yii\bootstrap\ActiveForm::end(); ?>
<?php
$this->registerJs(<<<JS
                $('.save-btn').click(function() {
                  var name = $(this).val();
                  $('.data-save-name').attr('data-save-name', name);
                });
                 $('#product-price-form').on('beforeSubmit', function(){
                    var saveName = $('.data-save-name').attr('data-save-name');
                    $.post($(this).attr('action').replace('__save_name__', saveName), $('#product-price-form').serialize(), function(rs){
                        if(rs.status === 200)
                        {
                            if(saveName == 'package-save')
                            {
                                $('#confirm-district-price-modal').modal('show');
                            }
                            else if(saveName == 'save-next')
                            {
                                window.location.href=rs.url;
                            }
                            else
                            {
                                window.location.reload();
                            }
                        }
                        else
                        {
                            $('#confirm-district-price-modal').modal('show');
                            $('#confirm-district-price-modal .warning-active').text(rs.message);
                        }
                    }, 'json');
                    return false;
                });
JS
)?>
<!--非区域时的价格明细start-->
<div class="modal fade" id="price-detail-add-modal" role="dialog" aria-labelledby="modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $priceDetailForm = new \backend\models\PriceDetailForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['product/save-price-detail', 'product_id' => $product->id],
                'validationUrl' => ['product/validation-price-detail'],
                'enableAjaxValidation' => true,
                'id' => 'price-detail-form',
                'layout' => 'horizontal',
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
                <h4 class="modal-title">新增价格明细</h4>
            </div>
            <div class="modal-body input_box">
                <?php if($product->isPackage()):?>
                    <?= $form->field($priceDetailForm, 'name')->textInput(['value' => '套餐售价', 'readonly' => true]) ?>
                <?php else:?>
                    <?= $form->field($priceDetailForm, 'name')->textInput() ?>
                <?php endif;?>
                <?= $form->field($priceDetailForm, 'price')->textInput()?>
                <?= $form->field($priceDetailForm, 'unit')->textInput()?>
                <?= $form->field($priceDetailForm, 'tax_rate')->textInput(['disabled'=>'disabled'])->hint('该功能暂时禁用。如税率为3.5%，请输入3.5即可。')?>
                <?= $form->field($priceDetailForm, 'is_invoice')->checkbox()->hint('如不勾选则用户不能对该金额申请开发票。')?>
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
<!--非区域时的价格明细end-->
<!--非区域时的价格删除start-->
<?php if ($hasSavePrice): ?>
    <div class="modal fade" id="delete-price-detail" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除价格明细</h4>
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
<!--非区域时的价格删除end-->
<!--区域时的价格明细start-->
<div class="modal fade" data-id="" id="district-price-detail-add-modal"  role="dialog" aria-labelledby="modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $priceDetailForm = new \backend\models\PriceDetailForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['product/save-price-detail', 'product_price_id' => '__product_price_id__'],
                'validationUrl' => ['product/validation-price-detail'],
                'enableAjaxValidation' => true,
                'id' => 'district-price-detail-form',
                'layout' => 'horizontal',
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
                <h4 class="modal-title">价格明细</h4>
            </div>
            <div class="modal-body input_box">
                <p id="delete-district-price-detail-hint">确定删除吗？</p>
                <table class="table table-bordered table-hover district-price-detail-list">
                    <thead><tr><th>费用类型</th><th>费用(元)</th><th>单位</th><th>税率</th><th>支持开发票</th><th>操作</th></tr></thead>
                    <tbody></tbody>
                    <tfoot><tr><td colspan="6"><?= $hasSavePrice ? '<span class="btn btn-default district-price-detail-add">添加</span>':'';?></td></tr></tfoot>
                </table>
                <div class="district-price-add-form">
                    <?php if($product->isPackage()):?>
                        <?= $form->field($priceDetailForm, 'name')->textInput(['value' => '套餐售价', 'readonly' => true]) ?>
                    <?php else:?>
                        <?= $form->field($priceDetailForm, 'name')->textInput() ?>
                    <?php endif;?>

                    <?= $form->field($priceDetailForm, 'price')->textInput()?>
                    <?= $form->field($priceDetailForm, 'unit')->textInput()?>
                    <?= $form->field($priceDetailForm, 'tax_rate')->textInput(['disabled'=>'disabled'])->hint('该功能暂时禁用。如税率为3.5%，请输入3.5即可。')?>
                    <?= $form->field($priceDetailForm, 'is_invoice')->checkbox()->hint('如不勾选则用户不能对该金额申请开发票。')?>
                </div>
            </div>
            <div class="modal-footer">
                <div class="save-btn-detail-price" style="display: none;">
                    <button type="button" class="btn btn-default cancel-add-district-price">取消</button>
                    <button type="submit" class="btn btn-primary" id="confirm-district-price-add">确定</button>
                </div>
                <div class="delete-btn-detail-price" style="display: none;">
                    <button type="button" class="btn btn-default cancel-delete-district-price">取消</button>
                    <button type="button" class="btn btn-primary" id="confirm-district-price-delete">确定</button>
                </div>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
        </div>
    </div>
</div>
<!--区域时的价格明细end-->
<!--新增省市区及价格start-->
<div class="modal fade" id="district-price-add-modal" role="dialog" aria-labelledby="modal-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php
            $districtPriceForm = new \backend\models\DistrictPriceForm();
            $districtPriceForm->product_id = $product->id;
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['product/save-district-price', 'product_id' => $product->id],
                'validationUrl' => ['product/validation-district-price'],
                'enableAjaxValidation' => true,
                'id' => 'district-price-form',
                'layout' => 'horizontal',
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
                <h4 class="modal-title">添加区域价格</h4>
            </div>

            <div class="modal-body input_box">
                <?php
                $provinceField = Select2Widget::widget([
                    'model' => $districtPriceForm,
                    'attribute' => 'province_id',
                    'serverUrl' => \yii\helpers\Url::to(['region/ajax-provinces']),
                    'itemsName' => 'provinces',
                    'selectedItem' => [],
                    'options' => ['class' => 'form-control', 'prompt'=>'请选择省份'],
                    'placeholderId' => '0',
                    'placeholder' => '请选择省份',
                    'eventSelect' => new JsExpression("
                        $('#districtpriceform-city_id').val('0').trigger('change');
                        $('#districtpriceform-district_id').val('0').trigger('change');
                    ")
                ]);
                $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                $cityField = Select2Widget::widget([
                    'model' => $districtPriceForm,
                    'attribute' => 'city_id',
                    'serverUrl' => $cityUrl,
                    'itemsName' => 'cities',
                    'selectedItem' => [],
                    'options' => ['class' => 'form-control', 'prompt'=>'请选择城市'],
                    'placeholderId' => '0',
                    'placeholder' => '请选择城市',
                    'eventSelect' => new JsExpression("
                        $('#districtpriceform-district_id').val('0').trigger('change');
                    "),
                    'eventOpening' => new JsExpression("
                        var id = $('#districtpriceform-province_id').val();
                        serverUrl = '{$cityUrl}'.replace('__province_id__', id ? id : '-1');
                    ")
                ]);
                $districtUrl = \yii\helpers\Url::to(['product/ajax-un-set-districts', 'city_id' => '__city_id__', 'product_id' => $product->id]);
                $packageOriginalPriceUrl = \yii\helpers\Url::to(['package-product/package-product-price']);
                echo $form->field($districtPriceForm, 'district_id', [
                    'template' => "{label}\n<div class='col-sm-8'><div class='row'><div class='col-sm-4'>{$provinceField}</div><div class='col-sm-4'>{$cityField}</div><div class='col-sm-4'>{input}</div></div>\n{hint}\n{error}</div>",
                ])->widget(Select2Widget::className(), [
                    'model' => $districtPriceForm,
                    'attribute' => 'district_id',
                    'selectedItem' => [],
                    'options' => ['class' => 'form-control', 'prompt'=>'请选择地区'],
                    'placeholderId' => '0',
                    'placeholder' => '请选择地区',
                    'serverUrl' => $districtUrl,
                    'itemsName' => 'districts',
                    'eventSelect' => new JsExpression("
                   
                    //选择地区后获取套餐下的地区商品原价
                    var district_id = $('#districtpriceform-district_id').val();
                    var product_id = $product->id;
                    $.get('{$packageOriginalPriceUrl}', {product_id:product_id, district_id:district_id}, function(rs){
                        if(rs.status === 200)
                        {
                            $('#update-district-price-form').trigger('reset.yiiActiveForm');
                            $('#districtpriceform-original_price').val(rs.packageOriginalPrice);
                        }
                        else
                        {
                            $('#districtpriceform-original_price').val('');
                            $('#district-price-update-modal .warning-active').text(rs.message);
                        }
                    }, 'json');
                    "),
                    'eventOpening' => new JsExpression("
                        var id = $('#districtpriceform-city_id').val();
                        serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
                    ")
                ])
                ?>
                <?= $form->field($districtPriceForm, 'original_price')->textInput()?>
                <?= $form->field($districtPriceForm, 'sort')->textInput()->hint('升序排序，数字越小越靠前，越大越靠后')?>
                <?= \yii\bootstrap\Html::activeHiddenInput($districtPriceForm, 'product_id') ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary" id="confirm-add">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php
            $packageOriginalPriceUrl = \yii\helpers\Url::to(['package-product/package-product-price']);
            $this->registerJs(<<<JS
                $('.package-confirm-add').click(function(){
                  $('#district-price-form').trigger('reset.yiiActiveForm');
                  if('{$product->isPackage()}'){
                      $('#districtpriceform-original_price').attr('readonly','true');
                  }else{
                      $('#districtpriceform-original_price').removeAttr('readonly');
                  }
                });
                var updateDistrictPriceRow = null;
                $('#district-price-list').on('click', '.district-price-update', function(){
                    updateDistrictPriceRow = $(this).parent().parent();
                    $('#districtpriceupdateform-id').val($(this).attr('data-id'));
                    $('#districtpriceupdateform-original_price').val($(this).attr('data-original_price'));
                    $('#update-district-price-area').text($(this).attr('data-district_name'));
                });
                $('#update-district-price-form').on('beforeSubmit', function(){
                    var districtPriceTemplate = '{$districtPriceTemplate}';
                    $.post($(this).attr('action'), $('#update-district-price-form').serialize(), function(rs){
                        if(rs.status === 200)
                        {
                            //编辑区域价格
                            // var remt = parseFloat(rs.productPrice.original_price)- parseFloat(rs.productPrice.price);
                            // updateDistrictPriceRow.find('.remit_amount').text(rs.packageOriginalPrice);
                            updateDistrictPriceRow.find('.original_price').text(rs.productPrice.original_price);
                            $('#district-price-update-modal').modal('hide');
                            $('#update-district-price-form').trigger('reset.yiiActiveForm');
                            $('#district-price-update-modal').unbind('hidden.bs.modal');
                            window.location.reload();
                        }
                        else
                        {
                            $('#district-price-update-modal .warning-active').text(rs.message);
                        }
                    }, 'json');
                    return false;
                });
JS
            )?>
        </div>
    </div>
</div>
<!--新增省市区及价格end-->
<!--编辑省市区及价格start-->
<div class="modal fade" id="district-price-update-modal" role="dialog" aria-labelledby="modal-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php
            $districtPriceUpdateForm = new \backend\models\DistrictPriceUpdateForm();
            $districtPriceUpdateForm->product_id = $product->id;
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['product/update-district-price', 'product_id' => $product->id],
                'enableAjaxValidation' => false,
                'id' => 'update-district-price-form',
                'layout' => 'horizontal',
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
                <h4 class="modal-title">编辑区域价格</h4>
            </div>
            <div class="modal-body input_box">
                <div class="form-group">
                    <label class="control-label col-sm-2">地区</label>
                    <div class="col-sm-8">
                        <p class="form-control-static" id="update-district-price-area"></p>
                    </div>
                </div>
                <?= $form->field($districtPriceUpdateForm, 'original_price')->textInput()?>
                <?= $form->field($districtPriceUpdateForm, 'sort')->textInput()->hint('升序排序，数字越小越靠前，越大越靠后')?>
                <?= \yii\bootstrap\Html::activeHiddenInput($districtPriceUpdateForm, 'product_id') ?>
                <?= \yii\bootstrap\Html::activeHiddenInput($districtPriceUpdateForm, 'id') ?>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary" id="confirm-add">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php
            $this->registerJs(<<<JS
                var updateDistrictPriceRow = null;
                $('#district-price-list').on('click', '.district-price-update', function(){
                    updateDistrictPriceRow = $(this).parent().parent();
                    $('#districtpriceupdateform-id').val($(this).attr('data-id'));
                    $('#districtpriceupdateform-original_price').val($(this).attr('data-original_price'));
                    $('#districtpriceupdateform-sort').val($(this).attr('data-sort'));
                    $('#update-district-price-area').text($(this).attr('data-district_name'));
                    if('{$product->isPackage()}'){
                        $('#districtpriceupdateform-original_price').attr('readonly', 'true');
                    }else{
                        $('#districtpriceupdateform-original_price').removeAttr('readonly');
                    }
                });
                $('#update-district-price-form').on('beforeSubmit', function(){
                    var districtPriceTemplate = '{$districtPriceTemplate}';
                    $.post($(this).attr('action'), $('#update-district-price-form').serialize(), function(rs){
                        if(rs.status === 200)
                        {
                            // var remt = parseFloat(rs.productPrice.original_price)- parseFloat(rs.productPrice.price);
                            // updateDistrictPriceRow.find('.remit_amount').text(remt);
                            updateDistrictPriceRow.find('.original_price').text(rs.productPrice.original_price);
                            $('#district-price-update-modal').modal('hide');
                            $('#update-district-price-form').trigger('reset.yiiActiveForm');
                            $('#district-price-update-modal').unbind('hidden.bs.modal');
                            window.location.reload();
                        }
                        else
                        {
                            $('#district-price-update-modal .warning-active').text(rs.message);
                        }
                    }, 'json');
                    return false;
                });
JS
            )?>
        </div>
    </div>
</div>
<!--编辑省市区及价格end-->
<!--区域及价格启用-禁用start-->
<?php if ($hasSavePrice): ?>
    <div class="modal fade" id="district-price-status-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">启用-禁用</h4>
                </div>
                <div class="modal-body">
                    确定修改吗?
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
<!--区域及价格启用-禁用end-->
<!--区域及价格批量启用-禁用start-->
<?php if ($hasSavePrice): ?>
    <div class="modal fade" id="bath-district-price-status-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">批量启用-禁用</h4>
                </div>
                <div class="modal-body">
                    确定批量修改吗?
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
<!--区域及价格批量启用-禁用end-->
<!--区域及价格删除start-->
<?php if ($hasSavePrice): ?>
    <div class="modal fade" id="district-price-delete-modal" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除地区及价格</h4>
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
<!--区域及价格删除end-->
<!--保存按钮弹框start-->
<div class="modal fade" id="confirm-district-price-modal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <!--                <h4 class="modal-title" id="myModalLabel">删除地区及价格</h4>-->
            </div>
            <div class="modal-body">
                <h4>该商品设置了相关套餐，如需修改套餐价格，请尽快操作！</h4>
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <div class="text-center">
                    <a href="<?= \yii\helpers\Url::to(['product/package-list'])?>" class="btn btn-primary">去修改套餐价格</a>
                    <button type="button" class="btn btn-primary text-right" data-dismiss="modal">一会儿修改</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!--保存按钮弹框end-->
<?php
$priceDetailDeleteUrl = \yii\helpers\Url::to(['delete-price-detail']);
$priceChangeStatusUrl = \yii\helpers\Url::to(['district-price-status']);
$priceDistrictPriceDeleteUrl = \yii\helpers\Url::to(['delete-district-price']);
$priceDistrictPriceDetailUrl = \yii\helpers\Url::to(['ajax-district-price-detail']);
$this->registerJs(<<<JS
    $.fn.select2.defaults.set('width', '100%');
    initPrice();
    //是否议价商品
    $('#'+'{$productPriceForm}'+'-is_bargain').click(function(){
        initPrice();
    });
    //是否区域及价格
    $('#'+'{$productPriceForm}'+'-is_area_price').click(function(){
        initPrice();
    });
    function initPrice()
    {
        if($('#'+'{$productPriceForm}'+'-is_bargain').is(':checked'))
        {
            hidePrice();
            hideAreaPrice();
            hideAreaPriceControl();
        }
        else if($('#'+'{$productPriceForm}'+'-is_area_price').is(':checked'))
        {
            hidePrice();
            showAreaPrice(); 
            showAreaPriceControl()
        }
        else
        {
            showPrice();
            hideAreaPrice();
            showAreaPriceControl()
        }
    }
    function showPrice()
    {
        $('.field-'+'{$productPriceForm}'+'-price').show();
        $('.field-'+'{$productPriceForm}'+'-tax').show();
        $('.field-'+'{$productPriceForm}'+'-original_price').show();
        $('.field-'+'{$productPriceForm}'+'-price_detail').show();
        $('.field-'+'{$productPriceForm}'+'-city_id').show();
        $('.field-'+'{$productPriceForm}'+'-remit_amount').show();
        
    }
    function hidePrice()
    {
        $('.field-'+'{$productPriceForm}'+'-price').hide();
        $('.field-'+'{$productPriceForm}'+'-tax').hide();
        $('.field-'+'{$productPriceForm}'+'-original_price').hide();
        $('.field-'+'{$productPriceForm}'+'-price_detail').hide();
        $('.field-'+'{$productPriceForm}'+'-is_area_price').hide();
        $('.field-'+'{$productPriceForm}'+'-city_id').hide();
        $('.field-'+'{$productPriceForm}'+'-remit_amount').hide();
    }
    function showAreaPrice()
    {
        $('.field-'+'{$productPriceForm}'+'-district_price').show();
        $('.field-'+'{$productPriceForm}'+'-service_area').hide();
    }
    
    function hideAreaPrice()
    {
        $('.field-'+'{$productPriceForm}'+'-district_price').hide();
        $('.field-'+'{$productPriceForm}'+'-service_area').show();
    }
    
    function showAreaPriceControl() {
        $('.field-'+'{$productPriceForm}'+'-is_area_price').show();
    }
    function hideAreaPriceControl() {
        $('.field-'+'{$productPriceForm}'+'-is_area_price').hide();
    }

    $('#price-detail-list').on('click', '.price-detail-delete', function(){
	     var id = $(this).attr('data-id');
	     var key = $(this).parents('tr').attr('data-key');
	     $('#delete-price-detail .sure-btn').unbind('click');
	    $('#delete-price-detail .sure-btn').on('click',function(){
	        $.post('{$priceDetailDeleteUrl}',{id:id, key:key},function(rs){
	        if(rs.status == 200){
	            $("#price-detail-list table tbody tr[data-key='"+key+"']").remove();
	            setPrice(rs.total_price, rs.total_tax);
	            setRemitAmount(rs.remit_amount);
	            $('#delete-price-detail').modal('hide');
	        }else{
	            
	        }
	    },'json')
	    });
	});
	
    $('#price-detail-form').on('beforeSubmit', function(){
        var priceDetailTemplate = '{$priceDetailTemplate}';
        $.post($(this).attr('action'), $('#price-detail-form').serialize(), function(rs){
            if(rs.status == 200)
            {
                $('#price-detail-add-modal').modal('hide');
                var item = priceDetailTemplate.replace('{key}', rs.priceDetail.key).replace('{name}', rs.priceDetail.name).replace('{price}', rs.priceDetail.price)
                                    .replace('{unit}', rs.priceDetail.unit).replace('{tax_rate}', rs.priceDetail.tax_rate).replace('{is_invoice}', rs.priceDetail.is_invoice ? '是' : '否');
                setPrice(rs.total_price, rs.total_tax);
                setRemitAmount(rs.remit_amount);
                $('#price-detail-list table tbody').append(item);
                $('#price-detail-form').trigger('reset.yiiActiveForm');
            }
            else
            {
                $('#price-detail-add-modal .warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
    
    function setPrice(price, tax) {
        $('#'+'{$productPriceForm}'+'-price').val(price);
        $('#'+'{$productPriceForm}'+'-tax').val(tax);
    }
    
    function setRemitAmount(remit_amount) {
        $('#packagepriceform-remit_amount').val(remit_amount);
    }
    
    $('#district-price-add-modal').on('show.bs.modal', function(){
        //$('#district-price-form').trigger('reset.yiiActiveForm');
        $('#districtpriceform-province_id').trigger('change');
        $('#districtpriceform-city_id').trigger('change');
        $('#districtpriceform-district_id').trigger('change');
    });
    
    $('#district-price-form').on('beforeSubmit', function(){
        var districtPriceTemplate = '{$districtPriceTemplate}';
        $.post($(this).attr('action'), $('#district-price-form').serialize(), function(rs){
            if(rs.status == 200)
            {
                $('#district-price-add-modal').modal('hide');
                var district_name = rs.productPrice.province_name+' '+rs.productPrice.city_name+' '+rs.productPrice.district_name;
                var item = districtPriceTemplate
                                    .replace('{id}', rs.productPrice.id)
                                    .replace('{id}', rs.productPrice.id)
                                    .replace('{district_name}', district_name)
                                    .replace('{price}', rs.productPrice.price)
                                    .replace('{original_price}', rs.productPrice.original_price)
                                    .replace('{sort}', rs.productPrice.sort)
                                    .replace('{sort}', rs.productPrice.sort)
                                    .replace('{remit_amount}', rs.packageOriginalPrice)
                                    .replace('{tax}', rs.productPrice.tax)
                                    .replace('{status}', rs.productPrice.status)
                                    .replace('{statusName}', rs.productPrice.status != 0 ? '禁用' : '启用')
                                    .replace('{original_price}', rs.productPrice.original_price)
                                    .replace('{district_name}', district_name)
                                    .replace('{id}', rs.productPrice.id);
                $('#district-price-list table tbody').append(item);
                $('#district-price-form').trigger('reset.yiiActiveForm');
                $('#district-price-add-modal').unbind('hidden.bs.modal');
                setTimeout(function(){
                    $('#district-price-detail-add-modal').attr('data-id', rs.productPrice.id);
                    $('#district-price-detail-add-modal').modal('show');
                }, 400);
            }
            else
            {
                $('#district-price-add-modal .warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
    
    //启用-禁用
    $('#district-price-list').on('click', '.district-price-status', function(){
	     var id = $(this).attr('data-id');
	     var _this = $(this);
	     var status = _this.attr('data-status') == 1 ? 0 : 1;
	     $('#district-price-status-modal .sure-btn').unbind('click');
	    $('#district-price-status-modal .sure-btn').on('click',function(){
	        $.post('{$priceChangeStatusUrl}',{id:id, status:status},function(rs){
	        if(rs.status == 200){
	            _this.attr('data-status', rs.is_enabled ? 1 : 0);
	            _this.text(rs.is_enabled ? '禁用' : '启用');
	            $('#district-price-status-modal').modal('hide');
	        }else{
	            $('#district-price-status-modal .warning-active').text(rs.message);
	        }
	    },'json')
	    });
	});
    
    //删除地区及价格
    $('#district-price-list').on('click', '.district-price-delete', function(){
        var row = $(this).parents('tr');
        var id = row.attr('data-id');
	    $('#district-price-delete-modal .sure-btn').unbind('click');
	    $('#district-price-delete-modal .sure-btn').on('click',function(){
	        $.post('{$priceDistrictPriceDeleteUrl}',{id:id},function(rs){
	        if(rs.status == 200){
	            row.remove();
	            setPrice(rs.total_price, rs.total_tax);
	            $('#district-price-delete-modal').modal('hide');
	        }else{
	            $('#district-price-delete-modal .warning-active').text(rs.message);
	        }
	    },'json')
	    });
	});
    
    $('#district-price-detail-add-modal').on('show.bs.modal', function() {
        var id = $(this).attr('data-id');
        $(this).find('table tbody').empty();
        $.get('{$priceDistrictPriceDetailUrl}', {product_price_id:id}, function(rs){
            var priceDetailTemplate = '{$priceDetailTemplate}';
            if(rs.status == 200)
            {
                for(var i in rs.priceDetail)
                {
                    var item = priceDetailTemplate.replace('{key}', rs.priceDetail[i].key).replace('{name}', rs.priceDetail[i].name).replace('{price}', rs.priceDetail[i].price)
                                    .replace('{unit}', rs.priceDetail[i].unit).replace('{tax_rate}', rs.priceDetail[i].tax_rate).replace('{is_invoice}', rs.priceDetail[i].is_invoice ? '是' : '否');
                    $('#district-price-detail-add-modal table tbody').append(item);
                }
            }
            else
            {
                $('#district-price-detail-add-modal .warning-active').text(rs.message);
            }
        }, 'json');
    });
   
    $('.district-price-detail-add').click(function(){
        $('#district-price-detail-form').trigger('reset.yiiActiveForm');
        showAddDistrictPriceDetail();
    });
    $('.cancel-add-district-price').click(function(){
        showList();
    });

    showList();
    function showList()
    {
        $('#district-price-detail-add-modal .modal-title').text('价格明细');
        $('.district-price-add-form').hide();
        $('.district-price-detail-list').show();
        $('.save-btn-detail-price').hide();
        $('.delete-btn-detail-price').hide();
        $('#delete-district-price-detail-hint').hide();
        $('#district-price-detail-add-modal .warning-active').text('');
    }
    
    function showAddDistrictPriceDetail()
    {
        $('#district-price-detail-add-modal .modal-title').text('添加价格明细');
        $('.district-price-add-form').show();
        $('.save-btn-detail-price').show();
        $('.district-price-detail-list').hide();
        $('.delete-btn-detail-price').hide();
         $('#delete-district-price-detail-hint').hide();
        $('#district-price-detail-add-modal .warning-active').text('');
    }
    
    function showDeleteDistrictPriceDetail()
    {
        $('#district-price-detail-add-modal .modal-title').text('删除价格明细');
        $('.delete-btn-detail-price').show();
        $('.district-price-add-form').hide();
        $('.save-btn-detail-price').hide();
        $('.district-price-detail-list').hide();
         $('#delete-district-price-detail-hint').show();
        $('#district-price-detail-add-modal .warning-active').text('');
    }
    
   $('#district-price-list').on('click', '.see-district-price-detail', function(){    
       var id = $(this).parents('tr').attr('data-id');
       $('#district-price-detail-add-modal').attr('data-id', id);
    });
    //区分区域
    $('#district-price-detail-form').on('beforeSubmit', function(){
        var priceDetailTemplate = '{$priceDetailTemplate}';
        var product_price_id = $('#district-price-detail-add-modal').attr('data-id');
        var _form = $(this);
        $.post(_form.attr('action').replace('__product_price_id__', product_price_id), $(this).serialize(), function(rs){
            if(rs.status == 200)
            {
                showList();
                var item = priceDetailTemplate.replace('{key}', rs.priceDetail.key).replace('{name}', rs.priceDetail.name).replace('{price}', rs.priceDetail.price)
                                    .replace('{unit}', rs.priceDetail.unit).replace('{tax_rate}', rs.priceDetail.tax_rate).replace('{is_invoice}', rs.priceDetail.is_invoice ? '是' : '否');
                setDistrictPrice(product_price_id, rs.total_price, rs.total_tax, rs.remit_amount);
                $('#district-price-detail-add-modal table tbody').append(item);
                _form.trigger('reset.yiiActiveForm');
            }
            else
            {
                $('#district-price-detail-add-modal .warning-active').text(rs.message);
            }
        }, 'json');
        return false;
    });
    
    function setDistrictPrice(product_price_id, price, tax, remit_amount) {
        var row = $('#district-price-list table tbody tr[data-id="'+product_price_id+'"]');
        row.find('.price').text(price);
        row.find('.tax').text(tax);
        row.find('.remit_amount').text(remit_amount);
    }
    
    $('.district-price-detail-list').on('click', '.price-detail-delete', function(){
        showDeleteDistrictPriceDetail();
	     var id = $('#district-price-detail-add-modal').attr('data-id');
	     var key = $(this).parents('tr').attr('data-key');
	     $('#confirm-district-price-delete').unbind('click');
	    $('#confirm-district-price-delete').on('click',function(){
	        $.post('{$priceDetailDeleteUrl}',{product_price_id:id, key:key},function(rs){
                if(rs.status == 200){
                    $(".district-price-detail-list tbody tr[data-key='"+key+"']").remove();
                    setDistrictPrice(id, rs.total_price, rs.total_tax);
                    showList();
                    window.location.reload();
                }else{
                    $('#district-price-detail-add-modal .warning-active').text(rs.message);
                }
            },'json')
            return false;
	    });
	    return false;
	});
    $('.cancel-delete-district-price').click(function(){
        showList();
    });
    $('#district-price-detail-add-modal').on('show.bs.modal', function(){
        showList();
    });
JS
);
?>





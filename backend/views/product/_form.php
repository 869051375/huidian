<?php
/* @var $this yii\web\View */
/* @var $product common\models\Product|null */
use imxiangli\select2\Select2Widget;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/** @var \backend\models\ProductForm $model */
/** @var array $industries */
$actionUniqueId = Yii::$app->controller->action->uniqueId;
if($actionUniqueId == 'product/create' || $actionUniqueId == 'product/update')
{
    $productForm = 'productform';
}
else
{
    $productForm = 'packageproductsform';
}
?>
<?php
$form = \yii\bootstrap\ActiveForm::begin([
    //'action' => ['product/create'],
    //'validationUrl' => ['product/validation'],
    //'enableAjaxValidation' => true,
    'id' => 'product-create-form',
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
<!--<?= $form->field($model, 'name')->textInput() ?>-->
<?= $form->field($model, 'alias')->textInput() ?>
<!--<?= $form->field($model, 'spec_name')->textInput() ?>-->
<!--
    <?php if($actionUniqueId == 'product/create' || $actionUniqueId == 'product/update'):?>
    <?= $form->field($model, 'spec_explain')->textarea(['rows'=>3]) ?>
    <?php endif;?>
-->

<?php
$categoryField = Select2Widget::widget([
    'model' => $model,
    'attribute' => 'top_category_id',
    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
    'itemsName' => 'categories',
    'selectedItem' => $model->topCategory ? [$model->topCategory->id => $model->topCategory->name] : [],
    'options' => ['class' => 'form-control', 'prompt'=>'请选择分类'],
    'placeholderId' => '0',
    'placeholder' => '请选择分类',
    'eventSelect' => new JsExpression("
                                //$('#productform-category_id').val('0').trigger('change');
                                $('#'+'{$productForm}'+'-category_id').val('0').trigger('change');
                            ")
]);
//$categoryField .= Html::error($model, 'top_category_id', ['tag' => 'div', 'class' => 'help-block help-block-error']);
?>

<?php
$categoryUrl = \yii\helpers\Url::to(['product-category/ajax-list', 'parent_id' => '__parent_id__']);
echo $form->field($model, 'category_id', [
    'template' => "{label}\n<div class='col-md-4'><div class='row'><div class='col-sm-6'>{$categoryField}</div><div class='col-sm-6'>{input}\n{hint}\n{error}</div></div></div>",
])->widget(Select2Widget::className(), [
    'model' => $model,
    'attribute' => 'category_id',
    'selectedItem' => $model->category ? [$model->category->id => $model->category->name] : [],
    'options' => ['class' => 'form-control', 'prompt'=>'请选择二级分类'],
    'placeholderId' => '0',
    'placeholder' => '请选择二级分类',
    'serverUrl' => \yii\helpers\Url::to(['product-category/ajax-list']),
    'itemsName' => 'categories',
    'eventOpening' => new JsExpression("
                                //var id = $('#productform-top_category_id').val();
                                var id = $('#'+'{$productForm}'+'-top_category_id').val();
                                serverUrl = '{$categoryUrl}'.replace('__parent_id__', id ? id : '-1');
                            ")
])
?>

<?php
//$model->keywords = trim($model->keywords, ',');
//echo $form->field($model, 'keywords')->textarea(['rows'=>3]) ?>
<?= $form->field($model, 'explain')->textarea(['rows'=>3]) ?>
<?php if($actionUniqueId == 'product/create' || $actionUniqueId == 'product/update'):?>
    <?php
    $typeAddress = \common\models\Product::TYPE_ADDRESS;
    echo $form->field($model, 'type')->widget(Select2Widget::className(), [
        'selectedItem' => \common\models\Product::getTypes(),
        'placeholderId' => '0',
        'options' => ['class' => 'form-control', 'prompt'=>'请选择类型'],
        'placeholder' => '请选择类型',
        'static' => true,
        'eventSelect' => new JsExpression("
                                    var type = $(this).val();
                                    if(type == '{$typeAddress}')
                                    {
                                        //$('.field-productform-address_list').hide();
                                        $('.field-'+'{$productForm}'+'-address_list').hide();
                                    }
                                    else
                                    {
                                        //$('.field-productform-address_list').show();
                                        $('.field-'+'{$productForm}'+'-address_list').show();
                                    }
                                ")
    ]) ?>
<?php endif;?>
<!--<?= $form->field($model, 'traded_init')->textInput(['maxlength' => true]) ?>
-->
<!---->
<?php if($actionUniqueId == 'product/create' || $actionUniqueId == 'product/update'):?>
    <!--    <?= $form->field($model, 'buy_limit')->textInput(['maxlength' => true]) ?>-->

    <!--
        <?= $form->field($model, 'industries', [
        'template' => '{label} {beginWrapper} <div class="checkbox">'.($industries ? '<label><input type="checkbox" id="checked_all_industry"/> 全选</label>' : '<span class="text-muted">暂无行业分类</span>').'</div>{input} {error} {endWrapper} {hint}',
    ])->checkboxList(ArrayHelper::map($industries, 'id', 'name'), [
        'item' => function ($index, $label, $name, $checked, $value){
            $options = ['label' => $label, 'value' => $value];
            return '<div class="checkbox checkbox-inline" style="margin-left:0;">' . Html::checkbox($name, $checked, $options) . '</div>';
        }
    ]) ?>
    -->
    <?= $form->field($model, 'flow_id')->widget(Select2Widget::className(),[
        'serverUrl' => \yii\helpers\Url::to(['flow/ajax-list']),
        'itemsName' => 'flows',
        'selectedItem' => $model->flow ? [$model->flow->id => $model->flow->name] : [],
        'options' => ['prompt'=>'选择流程'],
        'placeholder' => '选择流程',
    ]) ?>
    <?= $form->field($model, 'is_trademark')->checkbox() ?>
<?php endif;?>
<!--<?= $form->field($model, 'is_home')->checkbox() ?>
    <?= $form->field($model, 'is_experience')->checkbox() ?>
    <?= $form->field($model, 'show_list_sort')->textInput() ?>
    <?= $form->field($model, 'is_inventory_limit')->checkbox() ?>
    <?php if($model->is_package===0)
    echo $form->field($model, 'is_show_list')->checkbox();
?>
-->


<?= $form->field($model, 'inventory_qty')->textInput(['maxlength' => true]) ?>
<?php if($actionUniqueId == 'product/create' || $actionUniqueId == 'product/update'):?>
    <?= $form->field($model, 'is_renewal')->checkbox() ?>
    <?= $form->field($model, 'service_cycle')->widget(Select2Widget::className(), [
    'selectedItem' => \common\models\Product::getServiceCycles(),
    'static' => true,
    ]) ?>
    <!--
            <?= $form->field($model, 'tags')->textInput(['maxlength' => true]) ?>

    -->
    <!--
        <?= $form->field($model, 'address_list', [
        'template' => '{label} {beginWrapper} <div class="checkbox">'.($addressList ? '<label><input type="checkbox" id="checked_all_address"/> 全选</label>' : '<span class="text-muted">暂无地址类型商品</span>').'</div>{input} {error} {endWrapper} {hint}'
    ])->checkboxList(ArrayHelper::map($addressList, 'id', 'name'), [
        'item' => function ($index, $label, $name, $checked, $value){
            $options = ['label' => $label, 'value' => $value];
            return '<div class="checkbox checkbox-inline" style="margin-left:0;">' . Html::checkbox($name, $checked, $options) . '</div>';
        }
    ]) ?>
    -->

<?php endif;?>
<div class="hr-line-dashed"></div>

<div class="form-group">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="row">
            <div class="col-xs-3">
                <button class="main-bg btn btn-primary" type="submit" name="next" value="save">保存</button>
            </div>
            <div class="col-xs-4">
                <button class="main-bg btn btn-primary" type="submit" name="next" value="save-next">保存并下一步</button>
            </div>
        </div>
    </div>
</div>
<?php \yii\bootstrap\ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
    $.fn.select2.defaults.set('width', '100%');
    $('#checked_all_industry').click(function(){
        //var checkboxs = $('#productform-industries input[type=checkbox]');
        var checkboxs = $('#'+'{$productForm}'+'-industries input[type=checkbox]');
        if($(this).is(':checked'))
        {
            checkboxs.prop('checked', true);
        }
        else
        {
            checkboxs.prop('checked', false);
        }
    });

    //是否显示在首页
    //$('#productform-is_home').click(function(){
    $('#'+'{$productForm}'+'-is_home').click(function(){
        showIsHome();
    });
    showIsHome();
    function showIsHome()
    {
        //if($('#productform-is_home').is(':checked'))
        if($('#'+'{$productForm}'+'-is_home').is(':checked'))
        {
            //$('.field-productform-home_sort').show();
            $('.field-'+'{$productForm}'+'-home_sort').show();
        }
        else
        {
             //$('.field-productform-home_sort').hide();     
             $('.field-'+'{$productForm}'+'-home_sort').hide();     
        }
    }
    
    //是否首页顶部导航热门商品
    //$('#productform-is_home_nav').click(function(){
    $('#'+'{$productForm}'+'-is_home_nav').click(function(){
        showIsHomeNav();
    });
    
    showIsHomeNav();
    
    function showIsHomeNav()
    {
        // if($('#productform-is_home_nav').is(':checked'))
        if($('#'+'{$productForm}'+'-is_home_nav').is(':checked'))
        {
            //$('.field-productform-home_nav_sort').show();
            $('.field-'+'{$productForm}'+'-home_nav_sort').show();
        }
        else
        {
             //$('.field-productform-home_nav_sort').hide();     
             $('.field-'+'{$productForm}'+'-home_nav_sort').hide();     
        }
    }
    
    //是否显示在列表页
    $('#'+'{$productForm}'+'-is_show_list').click(function(){
        showList();
    });
    showList();
    function showList()
    {
        if($('#'+'{$productForm}'+'-is_show_list').is(':checked'))
        {
            $('.field-'+'{$productForm}'+'-show_list_sort').show();
        }
        else
        {   
             $('.field-'+'{$productForm}'+'-show_list_sort').hide();     
        }
    }


    //关联地址 
    $('#checked_all_address').click(function(){
    //var checkboxs = $('#productform-address_list input[type=checkbox]');
    var checkboxs = $('#'+'{$productForm}'+'-address_list input[type=checkbox]');
    if($(this).is(':checked'))
    {
        checkboxs.prop('checked', true);
    }
    else
    {
        checkboxs.prop('checked', false);
    }
});
    
    //是否支持续费
    $('#'+'{$productForm}'+'-is_renewal').click(function(){
        showIsRenewal();
    });
    showIsRenewal();
    function showIsRenewal()
    {
        if($('#'+'{$productForm}'+'-is_renewal').is(':checked'))
        {
            $('.field-'+'{$productForm}'+'-service_cycle').show();
        }
        else
        {
             $('.field-'+'{$productForm}'+'-service_cycle').hide();
        }
    };
    
    //是否库存限制
    $('#'+'{$productForm}'+'-is_inventory_limit').click(function(){
        showIsInventoryLimit();
    });
    showIsInventoryLimit();
    function showIsInventoryLimit()
    {
        if($('#'+'{$productForm}'+'-is_inventory_limit').is(':checked'))
        {
            $('.field-'+'{$productForm}'+'-inventory_qty').show();
        }
        else
        {
             $('.field-'+'{$productForm}'+'-inventory_qty').hide();     
        }
    }
JS
);
?>

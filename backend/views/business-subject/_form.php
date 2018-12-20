<?php
/* @var $this yii\web\View */
use common\models\BusinessSubject;
use common\models\Industry;
use imxiangli\select2\Select2Widget;
use yii\web\JsExpression;
use zhuravljov\yii\widgets\DateTimePicker;

/* @var $model BusinessSubject */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $id */
/* @var $subject */
$aid = empty($model->customer_id) ? $id : $model->id;
/** @var \common\models\CrmCustomer $customer */
/** @var \common\models\Administrator $administrator */
$administrator = Yii::$app->user->identity;
?>
<div class="ibox">
    <div class="ibox-title">
        <h4>基本信息</h4>
    </div>
<?php
$industry_id = $model->industry_id;
$form = \yii\bootstrap\ActiveForm::begin([
    'action' => ['','id'=>$aid],
    'validationUrl' => ['business-subject/validation'],
    'enableAjaxValidation' => true,
    'id' => 'shop-type-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-md-2',
            'offset' => 'col-md-offset-2',
            'wrapper' => 'col-md-8',
            'hint' => 'col-md-2',
        ],
    ]]);
?>
<?php if($subject):  ?>
<div class="ibox">
    <div class="ibox-content">
        <fieldset class="form-horizontal" style="padding-top: 10px;">
            <div class="row">
                <div class="col-lg-6">
    <?= $form->field($model, 'region')->textInput(['placeholder'=>'请输入姓名'])->label('姓名') ?>
    <?= $form->field($model, 'name')->textInput(['placeholder'=>'请输入身份证'])->label('身份证') ?>
    <?= $form->field($model, 'scope')->textInput(['placeholder'=>'请输入户籍地址'])->label('户籍地址') ?>
    <?= \yii\bootstrap\Html::activeHiddenInput($model,'subject_type',['value'=>1]) ?>
    <?= \yii\bootstrap\Html::activeHiddenInput($model,'customer_id',['value'=>$customer->id]) ?>
    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-2">
            <span id="error"></span>
            <button class="btn btn-primary" type="submit">保存</button>
        </div>
        </div>
        </div>
    </div>
    </div>
</div>
<?php else: ?>
<div class="ibox">
    <div class="ibox-content">
        <fieldset class="form-horizontal" style="padding-top: 10px;">
            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'company_name')->textInput(['placeholder'=>'请输入公司名称'])->label('公司名称*')->hint('<a class="btn btn-xs btn-primary" id="search-company" style="width: 60px;height: 33px;line-height:30px;position: relative; top:-5px;left:-91px;">搜索</a>') ?>
                    <?= $form->field($model, 'register_status')->dropDownList(\common\models\BusinessSubject::getRegisterStatus())->label('登记状态') ?>
                    <?= $form->field($model, 'legal_person_name')->textInput()->label('法定代表人*') ?>
                    <?= $form->field($model, 'operating_period_begin')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                        'class'=>'col-lg-8',
                    ]); ?>
                    <div class="page-select2-area">
                        <?php
                        $provinceField = Select2Widget::widget([
                            'model' => $model,
                            'attribute' => 'province_id',
                            'serverUrl' => \yii\helpers\Url::to(['region/ajax-provinces']),
                            'itemsName' => 'provinces',
                            'selectedItem' => $model->province_id ? [$model->province_id => $model->province_name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择省份'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择省份',
                            'eventSelect' => new JsExpression("
                            $('#businesssubject-city_id').val('0').trigger('change');
                            $('#businesssubject-district_id').val('0').trigger('change');
                        ")
                        ]);
                        $cityUrl = \yii\helpers\Url::to(['region/ajax-cities', 'province_id' => '__province_id__']);
                        $cityField = Select2Widget::widget([
                            'model' => $model,
                            'attribute' => 'city_id',
                            'serverUrl' => $cityUrl,
                            'itemsName' => 'cities',
                            'selectedItem' => $model->city_id ? [$model->city_id => $model->city_name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择城市'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择城市',

                            'eventSelect' => new JsExpression("
                            $('#businesssubject-district_id').val('0').trigger('change');
                        "),
                            'eventOpening' => new JsExpression("
                            var id = $('#businesssubjectform-province_id').val();
                            serverUrl = '{$cityUrl}'.replace('__province_id__', id ? id : '-1');
                        ")
                        ]);
                        $districtUrl = \yii\helpers\Url::to(['region/ajax-districts', 'city_id' => '__city_id__']);
                        echo $form->field($model, 'district_id', [
                            'template' => "{label}\n<div class='col-sm-8'><div class='row'><div class='col-sm-4'>{$provinceField}</div><div class='col-sm-4'>{$cityField}</div><div class='col-sm-4'>{input}</div></div>\n{hint}\n{error}</div>",
                        ])->widget(Select2Widget::className(), [
                            'model' => $model,
                            'attribute' => 'district_id',
                            'selectedItem' => $model->district_id ? [$model->district_id => $model->district_name] : [],
                            'options' => ['class' => 'form-control', 'prompt'=>'请选择地区'],
                            'placeholderId' => '0',
                            'placeholder' => '请选择地区',
                            'serverUrl' => $districtUrl,
                            'itemsName' => 'districts',
                            'eventOpening' => new JsExpression("
                        var id = $('#businesssubjectform-city_id').val();
                        serverUrl = '{$districtUrl}'.replace('__city_id__', id ? id : '-1');
                    ")
                        ])->label('注册地址');
                        ?>
                    </div>
                    <?= $form->field($model, 'address')->textInput(['placeholder'=>'请输入具体地址'])->label('具体地址'); ?>
                    <?= $form->field($model, 'industry_id')->dropDownList(Industry::getIndustry())->label('行业类型') ?>
                    <div class="industry_name">
                        <?= $form->field($model, 'industry_name')->textInput()->label('') ?>
                    </div>
                    <?= $form->field($model, 'official_website')->textInput(['placeholder'=>'请输入官网链接地址'])->label('官网地址'); ?>
                    <?= $form->field($model, 'filing_email')->textInput()->label('备案邮箱'); ?>
                    <?= $form->field($model, 'company_remark')->textarea(['rows'=>4,'placeholder'=>'请输入其他需要备注的信息'])->label('备注描述'); ?>
                </div>
                <div class="col-lg-6">
                    <?= $form->field($model, 'credit_code')->textInput()->label('信用代码') ?>
                    <?= $form->field($model, 'enterprise_type')->textInput()->label('公司类型') ?>
                    <?= $form->field($model, 'registered_capital')->textInput()->label('注册资金') ?>
                    <?= $form->field($model, 'operating_period_end')->widget(DateTimePicker::className(), [
                        'clientOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'language' => 'zh-CN',
                            'autoclose' => true,
                            'minView' => 'month',
                        ],
                        'clientEvents' => [],
                        'class'=>'col-sm-4',
                    ]) ?>
                    <?= $form->field($model, 'register_unit')->textInput()->label('登记机关') ?>
                    <?= $form->field($model, 'scope')->textarea(['rows'=>6])->label('经营范围'); ?>
                    <?= $form->field($model, 'tax_type')->dropDownList(BusinessSubject::getTaxType())->label('税务类型') ?>
                    <?= $form->field($model, 'filing_tel')->textInput()->label('备案电话'); ?>
                </div>
            </div>
            <?= \yii\bootstrap\Html::activeHiddenInput($model,'subject_type',['value'=>$subject]) ?>
            <?= \yii\bootstrap\Html::activeHiddenInput($model,'customer_id',['value'=>$id]) ?>
        </fieldset>
        <?php
        if($customer->isPrincipal($administrator) || $customer->isCombine($administrator) || $customer->isSubFor($administrator) || Yii::$app->user->can('business-subject/update') ||
            (!isset($businessSubject) && Yii::$app->user->can('business-subject/create'))): ?>
        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit" style="width: 100px;">保存</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php \yii\bootstrap\ActiveForm::end(); ?>

<!--如果是企业主体则显示-->
<?php if(empty($subject)): ?>

<!-- 搜索公司start -->
<div class="modal fade" id="sel_Carousel" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document"  style="width: 60%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">工商查询结果</h4>
            </div>
            <div class="modal-body" id="result">
            </div>
        </div>
    </div>
</div>
<!-- 搜索公司end -->
<?php
$this->registerJs(<<<JS
$(function()
{   
     $(".wrapper-content").css("position","relative");
     $("#businesssubjectform-company_name").css("width","330px");
    //加载时的行业类型  
    if('{$industry_id}'!=999)
    {
        $('.industry_name').css('display','none');
    }  
});

$('#businesssubjectform-industry_id').change(function()
{
    var industry = $(this).val();
    if(industry!=999)
    {
        $('#businesssubjectform-industry_name').val(industry);
        $('.industry_name').css('display','none');
    }else
    {
        $('#businesssubjectform-industry_name').val('');
        $('.industry_name').css('display','block');
    }
})
JS
);
?>
<?php
//查询列表地址
$searchList = \yii\helpers\Url::to(['search-company/list']);
//详细信息地址
$searchDetail = \yii\helpers\Url::to(['search-company/detail']);
$this->registerJs(<<<JS
//工商查询
$('#search-company').click(function()
{
     var company = $('#businesssubjectform-company_name').val();
     $.get('{$searchList}',{company:company},function(rs)
     {
        if(rs.status==200)
        {
            if(rs.data != null)
            {
                $('.warning-active').empty();
                var result ='<table  class="table">'+
                        '<thead>'+
                        '<tr>'+
                            '<th>以下为本次搜索返回结果，共'+rs.data.length+'条记录！</th>'+
                        '</tr>'+
                        '<tr>'+
                            '<th>企业名称</th>'+
                            '<th>法定代表人</th>'+
                            '<th>登记状态</th>'+
                            '<th>日期</th>'+
                            '<th>操作</th>'+
                        '</tr>'+
                        '</thead>'+
                        '<tbody class="user-tab">';
                for(var i = 0;i < rs.data.length ; i++)
                {
                    result +=
                        '<tr>'+
                        '<td>'+rs.data[i].name+'</td>'+
                        '<td>'+rs.data[i].operName+'</td>'+
                        '<td>'+rs.data[i].status+'</td>'+
                        '<td>'+rs.data[i].startDate+'</td>'+
                        '<td><a class="btn btn-primary sel-company" data-id="'+rs.data[i].keyNo+'">选择</a></td>'+
                        '</tr>';
                }
                result +='</tbody></table>';
                $('#result').html(result);
                $('#sel_Carousel').modal('show');
            }
        }
        else
        {
            $('.warning-active').html(rs.message);
        }
     })
})
//查询详细数据回填到表单
$('#result').on( "click",'.sel-company',function() 
{
    var id = $(this).attr('data-id');
    $.get('{$searchDetail}',{id:id},function(rs)
    {
        rs = JSON.parse(rs);
        
        if(rs.Status == 200)
        {
           var business_status = null;
           if(rs.Result.Status)
           {
               business_status = rs.Result.Status.length > 2 ? rs.Result.Status.slice(0,rs.Result.Status.indexOf('（')) : rs.Result.Status;
           }
           $("#businesssubjectform-register_status").val(business_status).attr("selected",true);
           $('#businesssubjectform-company_name').val(rs.Result.Name);
           $('#businesssubjectform-credit_code').val(rs.Result.CreditCode);
           $('#businesssubjectform-enterprise_type').val(rs.Result.EconKind);
           $('#businesssubjectform-legal_person_name').val(rs.Result.OperName);
           var start_time = null;
           if(rs.Result.TermStart)
           {
               start_time = rs.Result.TermStart.length > 10 ? rs.Result.TermStart.slice(0,rs.Result.TermStart.indexOf('T')) : rs.Result.TermStart;
           }
           var end_time = null;
           if(rs.Result.TeamEnd)
           {
               var first = rs.Result.TeamEnd.slice(0,1);
               console.log(first);
               if(first > 0 && first < 3)
               {
                  end_time = rs.Result.TeamEnd.length > 10 ? rs.Result.TeamEnd.slice(0,rs.Result.TeamEnd.indexOf('T')) : rs.Result.TeamEnd;
               }
               else
               {
                   end_time = null;
               }
           }
           $('#businesssubjectform-operating_period_begin').val(start_time);
           $('#businesssubjectform-operating_period_end').val(end_time);
           $('#businesssubjectform-register_unit').val(rs.Result.BelongOrg);
           if(rs.Result.RegistCapi)
           {
               if(rs.Result.RegistCapi.indexOf('.') == -1)
               {
                   $('#businesssubjectform-registered_capital').val(rs.Result.RegistCapi.slice(0,rs.Result.RegistCapi.indexOf('万')));
               }
               else
               {
                   $('#businesssubjectform-registered_capital').val(rs.Result.RegistCapi.slice(0,rs.Result.RegistCapi.indexOf('.')));
               }
           }
           $('#businesssubjectform-scope').val(rs.Result.Scope);
           $('#businesssubjectform-address').val(rs.Result.Address);
           $('#sel_Carousel').modal('hide');
        }
    })
})
JS
);
?>
<?php endif; ?>

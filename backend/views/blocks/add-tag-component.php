<?php

use common\models\CustomerCustomField;
use common\models\OpportunityCustomField;

$actionUniqueId = Yii::$app->controller->action->uniqueId;
$type = \common\models\Tag::TAG_CUSTOMER;
$tagTitle = '添加客户标签';
$addBatchTagUrl = \yii\helpers\Url::to(['tag/add-batch-customer-tag']);
$cancelBatchTagUrl = \yii\helpers\Url::to(['tag/cancel-batch-customer-tag']);
//查询自定义列数据接口地址
$customFieldList = \yii\helpers\Url::to(['crm-customer/ajax-custom-field-list']);
//自定义列保存接口地址
$customSaveField = \yii\helpers\Url::to(['crm-customer/custom-field']);
$fieldUrl = ['crm-customer/custom-field'];
$model = new CustomerCustomField();
$tagsForm = new \backend\models\CustomerTagForm();
$flog = false;
if($actionUniqueId == 'opportunity/list' || $actionUniqueId == 'opportunity/all')
{
    $model = new OpportunityCustomField();
    $type = \common\models\Tag::TAG_OPPORTUNITY;
    $flog = true;
    $tagTitle = '添加商机标签';
    $tagsForm = new \backend\models\OpportunityTagForm();
    $addBatchTagUrl = \yii\helpers\Url::to(['tag/add-batch-opportunity-tag']);
    $cancelBatchTagUrl = \yii\helpers\Url::to(['tag/cancel-batch-opportunity-tag']);
    //查询自定义列数据接口地址
    $customFieldList = \yii\helpers\Url::to(['opportunity/ajax-custom-field-list']);
    //自定义列保存接口地址
    $customSaveField = \yii\helpers\Url::to(['opportunity/custom-field']);
    $fieldUrl = ['opportunity/custom-field'];
}
$getTag = \yii\helpers\Url::to(['tag/ajax-list', 'type' => $type, 'status' => '__status__']);
?>
<!--添加标签与自定义列表字段，开始-->
<div class="add-label-and-self-td">
    <ul class="add-label-ul">
        <li class="add-babel-li" id="add-tag-button"><a class="color-66 add-label-text" href="javascript:;"><i class="add-label-icon"></i>添加标签</a><i class="down-select-icon"></i>
            <!--添加标签小窗口-->
            <div class="add-label-container">
                <i class="up-triangle"></i>
                <!--单选框按钮组，点击添加标签显示的内容-->
                <div class="radio-type-content">
                    <?php
                    $options = ['labelOptions' => ['class' => false]];
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['tag/add-batch-customer-tag'],
//                       'validationUrl' => ['tag/validation'],
                        'id' => 'add-batch-tag-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-0',
                                'offset' => 'col-sm-offset-0',
                                'wrapper' => 'col-sm-12 tag-content-item',
                            ],
                        ],
                    ]); ?>
                    <div class="radio-container">
                        
                    </div>
                    <?= \yii\helpers\Html::activeHiddenInput($tagsForm, 'ids', ['id' => 'customer-tag-id'])?>
                    <?php \yii\bootstrap\ActiveForm::end(); ?>
                    <?php
                    $this->registerJs(<<<JS
                        $('.apply-btn').click(function() {
                                var form = $('add-batch-tag-form');
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
                        });
JS
                    );?>
                </div>
                <!--点击设置按钮显示的内容-->
                <div class="constomer-set-content">
                    <?php
                    $updateTagForm = new \backend\models\TagForm();
                    $options = ['labelOptions' => ['class' => false]];
                    $updateTagForm->setScenario('update');
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'action' => ['tag/update'],
                        'validationUrl' => ['tag/validation'],
                        'id' => 'update-tag-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-0',
                                'offset' => 'col-sm-offset-0',
                                'wrapper' => 'col-sm-12 tag-content-item',
                            ],
                        ],
                    ]); ?>
                    <div class="group-container">
                        <div class="constomer-set-item" style="display: none">
                            <?= $form->field($updateTagForm, 'full_names[]', $options)->textInput()->label('');?>
                            <div class="show-color-content" style="background-color: #ff9292;"></div>
                        </div>
                    </div>
                    <?php if (Yii::$app->user->can('tag/update')):?>
                        <div id="addBtnText" class="constomer-set-item">
                            <a class="add-babel-text" data-target="#add-babel-modal" data-toggle="modal">+添加标签</a>
                        </div>
                    <?php endif;?>
                    <?php \yii\bootstrap\ActiveForm::end();?>
                </div>
                <hr style="width: 100%; height: 1px; padding: 0; margin: 0;" />
                <!--底部功能按钮组-->
                <div class="button-content">
                    <button type="button" class="add-babel-btn apply-btn">应用</button>
                    <?php if (Yii::$app->user->can('tag/update')):?>
                        <button type="button" class="add-babel-btn cacel-btn">取消</button>
                        <button type="button" class="add-babel-btn save-btn">保存</button>
                        <button type="button" class="add-babel-btn set-btn">设置</button>
                    <?php endif;?>
                </div>
            </div>
        </li>
       <li><span class="vertical-text"></span></li>
        <!-- <li class="user-define-td"><a class="color-66 user-define-text" href="javascript:void(0);" data-target="#user-define-td-modal" data-toggle="modal"><i class="self-set-icon"></i>自定义列表字段</a></li> -->
        <li class="user-define-td"><a class="color-66 user-define-text" href="javascript:void(0);"><i class="self-set-icon"></i>自定义列表字段</a></li>
    </ul>
</div>
<?php
$this->registerJs(<<<JS
    function stopPropaga(event){
        if(event.stopPropagation){
            event.stopPropagation();
        }else{
            event.cancelBubble = true;
        }
    }
    //判断点击位置，关闭添加标签内容框
    $(document).on('click',function(e){
        var event = e.target || e.srcElement;
        if(!($(event).attr('class') === 'add-label-container' || $(event).parents().hasClass('add-label-container') || $(event).parents().hasClass('table-left'))){
            $('.add-label-container').hide();
        }
    })
    //点击外部添加标签字段，显示单选按钮组及功能按钮组内容，隐藏设置内容
    $('.add-babel-li').click(function(event){
        if(event.target.className === 'add-babel-li' || event.target.className === 'color-66 add-label-text' || event.target.className === 'add-label-icon' || event.target.className === 'down-select-icon'){//点击 '添加标签'
            stopPropaga(event);
            if($('.add-label-container').is(':hidden')){//客户类型框为隐藏状态
                //查询标签数据，动态生成标签结构
                $.get('{$getTag}'.replace('__status__', '0'), function(rs){
                    if(rs.status === 200){
                        $('.radio-container').html('');
                        rs.data.map(function (item, index) {
                            var input = $('<input data-color=' + item.color + ' data-id=' + item.id + ' type="radio" name="tag-radio">');
                            var radioName = $('<span title=' + item.name + '>' + item.name + '</span>');
                            var label = $('<label></label>');
                            label.append(input);
                            label.append(radioName);
                            var colorDiv = $('<div class="constomer-type-color" style="background-color: #' + item.color + ';"></div>');
                            var constomerRadio = $('<div class="constomer-radio"></div>');
                            constomerRadio.append(label);
                            constomerRadio.append(colorDiv);
                            $('.radio-container').append(constomerRadio);
                        });
                        if(rs.data.length > 0) {
                            $('.radio-container .constomer-radio:first').find('input').attr('checked', true);
                        }
                        // form.trigger('reset.yiiActiveForm');
                    }else{
                        setGlobalTip(rs.message, true);
                    }
                }, 'json');

                $('.add-label-container').show();
                $('.radio-type-content').show();
                $('.constomer-set-content').hide();
                $('.cacel-btn').hide();
                $('.save-btn').hide();
                $('.apply-btn').show();
                $('.set-btn').show();
                //$('.add-label-text').css({'color':'#1eb293'});
            }else{
                $('.add-label-container').hide();
                $('.radio-type-content').hide();
                $('.constomer-set-content').hide();
                //$('.add-label-text').css({'color':'#666'});
            }
        }
    });
    //点击底部应用按钮
    $('.apply-btn').click(function(event){
        var flog = '{$flog}';//false->客户页面 true->商机页面
        stopPropaga(event);
        var checkedRows = [];
        if(flog){//商机
            checkedRows = [].slice.call($('#opportunity-table .checkbox-opportunity'));
        }else{//客户
            checkedRows = [].slice.call($('#customer-table .checkbox-customer'));
        }
        var isChecked = checkedRows.some(function (item) {
            return item.checked === true;
        });
        //若未勾选表格行
        if(!isChecked){
            setGlobalTip('请您勾选要应用的数据行！',true);
        }else{
            var setRows = [];
            if(flog){//商机
                setRows = [].slice.call($('#opportunity-table .checkbox-opportunity'));
            }else{//客户
                setRows = [].slice.call($('#customer-table .checkbox-customer'));
            }
            //选中的行数据id集合
            var postSetIds = [];
            setRows.map(function (item) {
                if (item.checked === true) {
                    postSetIds.push(item.value);
                }
            });
            $('#customer-tag-id').val(postSetIds);
            var radioColorId = $('.radio-container input:checked').attr('data-id');
            $.post('{$addBatchTagUrl}',{ids: postSetIds, tag_id: radioColorId},function(result){
                if(result.status === 200) {
                    setGlobalTip(result.message, false);
                    //若请求成功设置表格颜色
                    var leftTrs = undefined;
                    if(flog){
                        leftTrs = $('#opportunity-table tbody tr');
                    }else{
                        leftTrs = $('#customer-table tbody tr');
                    }
                    var color = $('.radio-container input:checked').attr('data-color');
                    //左侧表
                    [].slice.call(leftTrs).map(function (item) {
                        postSetIds.map(function (idItem) {
                            if (item.getAttribute('data-id') === idItem) {
                                item.childNodes[3].childNodes[1].childNodes[3].style.color = '#' + color;
                                item.childNodes[5].childNodes[1].style.color = '#' + color;
                            }
                        });
                    });
                    var centerTrs = $('.table-center tbody tr');
                    [].slice.call(centerTrs).map(function (item) {
                        postSetIds.map(function (idItem) {
                            if (item.getAttribute('data-id') === idItem) {
                                [].slice.call(item.childNodes).map(function (innerItem) {
                                    if (innerItem.style) {
                                        innerItem.style.color = '#' + color;
                                    }
                                });
                            }
                        });
                    });
                    if(flog){//商机
                        $('.btn-batch-change-administrator').hide();
                        $('.cancel-tag-btn').hide();
                        $('#opportunity-table tr input:checked').prop('checked', false);
                    }else{//客户
                        $('.btn-batch-customer-share').hide();
                        $('.cancel-tag-btn').hide();
                        $('#customer-table tr input:checked').prop('checked', false);
                    }
                    $('#check-all').prop('indeterminate',false);
                }else{
                    setGlobalTip(result.message, true);
                }
            });
        }
    });
    //点击底部取消按钮
    $('.cacel-btn').click(function(event){
        stopPropaga(event);
        $('.constomer-set-content').hide();
        $('.radio-type-content').show();
        //查询标签数据，动态生成标签结构
        $.get('{$getTag}'.replace('__status__', '0'), function(rs){
            if(rs.status === 200){
                $('.radio-container').html('');
                rs.data.map(function (item, index) {
                    var input = $('<input data-color=' + item.color + ' data-id=' + item.id + ' type="radio" name="tag-radio">');
                    var radioName = $('<span title=' + item.name + '>' + item.name + '</span>');
                    var label = $('<label></label>');
                    label.append(input);
                    label.append(radioName);
                    var colorDiv = $('<div class="constomer-type-color" style="background-color: #' + item.color + ';"></div>');
                    var constomerRadio = $('<div class="constomer-radio"></div>');
                    constomerRadio.append(label);
                    constomerRadio.append(colorDiv);
                    $('.radio-container').append(constomerRadio);
                });
                if(rs.data.length > 0) {
                    $('.radio-container .constomer-radio:first').find('input').attr('checked', true);
                }
                // form.trigger('reset.yiiActiveForm');
            }else{
                setGlobalTip(rs.message, true);
            }
        }, 'json');
        $('.cacel-btn').hide();
        $('.save-btn').hide();
        $('.apply-btn').show();
        $('.set-btn').show();
        $('.add-label-text').css({'color':'#666'});
    });
    //点击底部保存按钮
    $('.save-btn').click(function(event){
        stopPropaga(event);
        var errorInputCount = $('.error-constomer-set-item').length;
        if(errorInputCount > 0) {
            setGlobalTip('请您完善添加标签输入框信息。', true);
            return false;
        }
        var form = $('#update-tag-form');
        var postData = [];
        [].slice.call($('.tagInput')).map(function (item) {
            var postItem = {
                'id': item.getAttribute('data-id'),
                'full_name': item.value
            };
            postData.push(postItem);
        });
        var hiddenFormInput = $('#update-tag-form input[name = "_csrf-backend"]').val();postData
        $.post(form.attr('action'), {'postData': postData, '_csrf-backend': hiddenFormInput}, function(rs){
            if(rs.status === 200){
                //form.trigger('reset.yiiActiveForm');
                setGlobalTip('保存成功。', false);
            }else{
                setGlobalTip(rs.message, true);
            }
        }, 'json');
    });
    //点击底部设置按钮，显示设置内容及功能按钮组内容，隐藏单选按钮组
    $('.set-btn').click(function(event){
        stopPropaga(event);
        $('.radio-type-content').hide();
        $('.constomer-set-content').show();
        $('.cacel-btn').show();
        $('.save-btn').show();
        $('.apply-btn').hide();
        $('.set-btn').hide();
        $.post('{$getTag}'.replace('__status__', '1'),function(result){
            if(result.status === 200) {
                $('.group-container').html('');
                createTags(result);
            }else{
                setGlobalTip(result.message, true);
            }
        }, 'json');
    });
    //清除表格行标签特性
    $('.cancel-tag-btn').on('click',function(){
        var flog = '{$flog}';//false->客户页面 true->商机页面
        var checkedRows = [];
        if(flog){//商机
            checkedRows = [].slice.call($('#opportunity-table .checkbox-opportunity'));
        }else{//客户
            checkedRows = [].slice.call($('#customer-table .checkbox-customer'));
        }
        var isChecked = checkedRows.some(function (item) {
            return item.checked === true;
        });
        //若未勾选表格行
        if(!isChecked){
            setGlobalTip('请您勾选要清除的数据行！', true);
        }else{
            var setRows = [];
            if(flog){//商机
                setRows = [].slice.call($('#opportunity-table .checkbox-opportunity'));
            }else{//客户
                setRows = [].slice.call($('#customer-table .checkbox-customer'));
            }
            //选中的行数据id集合
            var postSetIds = [];
            setRows.map(function (item) {
                if (item.checked === true) {
                    postSetIds.push(item.value);
                }
            });

            //$('#customer-tag-id').val(postSetIds);
            $.post('{$cancelBatchTagUrl}',{ids: postSetIds, state: 1},function(result){
                if(result.status === 200) {
                    setGlobalTip(result.message, false);
                    //若请求成功恢复表格颜色
                    var leftTrs = undefined;
                    if(flog){
                        leftTrs = $('#opportunity-table tbody tr');
                    }else{
                        leftTrs = $('#customer-table tbody tr');
                    }
                    var color = $('.radio-container input:checked').attr('data-color');
                    //左侧表
                    [].slice.call(leftTrs).map(function (item) {
                        postSetIds.map(function (idItem) {
                            if (item.getAttribute('data-id') === idItem) {
                                item.childNodes[3].childNodes[1].childNodes[3].style.color = '#666666';
                                item.childNodes[5].childNodes[1].style.color = '#337ab7';
                            }
                        });
                    });
                    var centerTrs = $('.table-center tbody tr');
                    [].slice.call(centerTrs).map(function (item) {
                        postSetIds.map(function (idItem) {
                            if (item.getAttribute('data-id') === idItem) {
                                [].slice.call(item.childNodes).map(function (innerItem) {
                                    if (innerItem.style) {
                                        innerItem.style.color = '#666666';
                                    }
                                });
                            }
                        });
                    });
                    if(flog){//商机
                        $('.btn-batch-change-administrator').hide();
                        $('.cancel-tag-btn').hide();
                        $('#opportunity-table tr input:checked').prop('checked', false);
                    }else{//客户
                        $('.btn-batch-customer-share').hide();
                        $('.cancel-tag-btn').hide();
                        $('#customer-table tr input:checked').prop('checked', false);
                    }
                    $('#check-all').prop('indeterminate',false);
                }else{
                    setGlobalTip(result.message, true);
                }
            });
        }
    });
    //校验标签名称输入框变化
    (function(){
        var flag = true;
        $('.group-container').on('compositionstart','input',function(e){
            flag = false;
            console.log(flag,'compositionstart');
        });
        $('.group-container').on('compositionend','input',function(e){
            flag = true;
            console.log(flag,'compositionend');
        });
        var count = 0;
        $('.group-container').on('input','input',function(e){
            var _this = this;
            setTimeout(function(){
                if(flag){
                    var inputValue = $(_this).val();
                    console.log(inputValue.length,'length');
                    if(inputValue.length < 1) {
                        $(_this).addClass('error-tip-input');
                        $(_this).parents('.constomer-set-item').addClass('error-constomer-set-item');
                        $(_this).siblings().html('标签名不能为空');
                        count = 0;
                    }else if(inputValue.length > 6) {
                        if(count === 0){
                            count++;
                            $(_this).addClass('error-tip-input');
                            $(_this).val(inputValue.substr(0,7));
                            $(_this).parents('.constomer-set-item').addClass('error-constomer-set-item');
                            $(_this).siblings().html('标签名不能超过6个字符');
                        }
                    }else{
                        $(_this).parents('.constomer-set-item').removeClass('error-constomer-set-item');
                        $(_this).removeClass('error-tip-input');
                        $(_this).siblings().html('');
                        count = 0;
                    }
                }
            },0)
        });
    })()
    // $('.group-container').on('input propertychange','input',function(e){
    //     if(flag){
    //         var inputValue = $(this).val();
    //         if(inputValue.length < 1) {
    //             $(this).addClass('error-tip-input');
    //             $(this).parents('.constomer-set-item').addClass('error-constomer-set-item');
    //             $(this).siblings().html('标签名不能为空');
    //         }else if(inputValue.length > 6) {
    //             $(this).addClass('error-tip-input');
    //             $(this).val(inputValue.substr(0,7));
    //             $(this).parents('.constomer-set-item').addClass('error-constomer-set-item');
    //             $(this).siblings().html('标签名不能超过6个字符');
    //         }else{
    //             $(this).parents('.constomer-set-item').removeClass('error-constomer-set-item');
    //             $(this).removeClass('error-tip-input');
    //             $(this).siblings().html('');
    //         }
    //     }
    // });
JS
                            );?>
<!--添加标签与自定义列表字段，结束-->

<!-- 点击设置中的添加标签字段显示添加标签模态框，开始 -->
<div class="modal fade" id="add-babel-modal" role="dialog" aria-labelledby="add-babel-modal"  aria-hidden="true">
    <?php
    $addTagForm = new \backend\models\TagForm();
    $addTagForm->setScenario('insert');
    $options = ['labelOptions' => ['class' => false]];
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => ['tag/add'],
        'validationUrl' => ['tag/validation'],
        'id' => 'add-tag-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-6',
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="confirm-receive-label"><?= $tagTitle;?></h4>
            </div>
            <div class="modal-body">
                <?= $form->field($addTagForm, 'name')->textInput(['placeholder' => '请输入标签名称']) ?>
                <div class="form-group">
                    <label for="label-color" class="col-sm-2 control-label">标签颜色</label>
                    <div class="col-sm-6">
                        <div class="row-color">
                            <div data-self-color="#ff9292" class="color-td" style="background-color: #ff9292;"></div>
                            <div data-self-color="#83c535" class="color-td" style="background-color: #83c535;"></div>
                            <div data-self-color="#5bbaff" class="color-td" style="background-color: #5bbaff;"></div>
                            <div data-self-color="#a17734" class="color-td" style="background-color: #a17734;"></div>
                        </div>
                        <div class="row-color">
                            <div data-self-color="#ff9334" class="color-td" style="background-color: #ff9334;"></div>
                            <div data-self-color="#5a81ff" class="color-td" style="background-color: #5a81ff;"></div>
                            <div data-self-color="#9268ff" class="color-td" style="background-color: #9268ff;"></div>
                            <div data-self-color="#ff77fb" class="color-td" style="background-color: #ff77fb;"></div>
                        </div>
                        <div class="row-color">
                            <div data-self-color="#e74883" class="color-td" style="background-color: #e74883;"></div>
                            <div data-self-color="#19caa2" class="color-td" style="background-color: #19caa2;"></div>
                            <div data-self-color="#7f1919" class="color-td" style="background-color: #7f1919;"></div>
                            <div data-self-color="#bfb425" class="color-td" style="background-color: #bfb425;"></div>
                        </div>
                    </div>
                </div>
                <?= $form->field($addTagForm, 'color', $options)->textInput(['class' => 'row-color color-text', 'readonly'=>'readonly'])->label('');?>
            </div>
            <div class="modal-footer self-customer-modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" id="color-cancel" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" id="color-sure" class="btn btn-primary sure-btn">确定</button>
                <?= \yii\helpers\Html::activeHiddenInput($addTagForm, 'type', ['value' => $type, 'id' => 'add-tag-type'])?>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>

    <?php $this->registerJs(<<<JS
        $('.add-babel-text').click(function(){
            $('#add-tag-form').trigger('reset.yiiActiveForm');
        });
        //点击标签颜色，将颜色值设置到下方灰色框内显示
        $('.color-td').click(function(){
            var selectColor = $(this).attr('data-self-color');
            $('.color-text').val( selectColor.substr(1));
        });
        //点击添加标签模态框中的取消按钮，将下方灰色框内值清空
        $('#color-cancel').click(function(){
            $('#label-name').val('');
            $('.color-text').val('');
        });
        //点击添加标签模态框中的确定按钮
        $('#add-tag-form').on('beforeSubmit', function(){
            var form = $(this);
            $('#add-tag-type').val('{$type}');
            $.post(form.attr('action'), form.serialize(), function(rs){
                if(rs.status === 200){
                    form.trigger('reset.yiiActiveForm');
                    //window.location.reload();
                    $('.group-container').html('');
                    createTags(rs);

                    $('#add-babel-modal').modal('hide');
                    setGlobalTip('添加标签成功！', false);
                }else{
                    $('#add-babel-modal').modal('hide');
                    setGlobalTip('添加标签失败！', true);
                }
            }, 'json');
        return false;
        });      
JS
    );?>
</div>
<!-- 点击设置中的添加标签字段显示添加标签模态框，结束 -->

<!-- 点击自定义列表字段显示表格字段模态框，开始 -->
<div class="modal fade" id="user-define-td-modal" role="dialog" aria-labelledby="user-define-td-modal"  aria-hidden="true">
    <?php
    //$model = new CustomerCustomField();
//    $addTagForm->setScenario('insert');
    $options = ['labelOptions' => ['class' => false]];
    $form = \yii\bootstrap\ActiveForm::begin([
        'action' => $fieldUrl,
//        'validationUrl' => ['crm-customer/custom-field'],
        'id' => 'user-define-td-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-sm-2',
                'offset' => 'col-sm-offset-2',
                'wrapper' => 'col-sm-6',
            ],
        ],
    ]); ?>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="confirm-receive-label">自定义列表字段</h4>
            </div>
            <div class="modal-body">
<!--                <form id="user-define-td-form" class="form-horizontal" role="form">-->
                    <ul id="sortUl">

                    </ul>
<!--                </form>-->
            </div>
            <div class="modal-footer self-customer-modal-footer">
                <?= \yii\helpers\Html::activeHiddenInput($model, 'fields', ['value' => ''])?>
                <button type="button" id="user-define-cancel" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" id="user-define-recover" class="btn btn-primary recover-btn">恢复默认</button>
                <button type="submit" id="user-define-sure" class="btn btn-primary define-sure-btn">保存</button>
            </div>
        </div>
    </div>
    <?php \yii\bootstrap\ActiveForm::end(); ?>
    <?php $this->registerJs(<<<JS
    $(function(){
        //查新自定义列字段数据
        function queryDefineData(isRecover){
            $.post('{$customFieldList}', function(result){
                if(result.status === 200){
                    returnLis(result['fields'],isRecover ? true : undefined);
                }else{
                    setGlobalTip(result.message, true);
                }
            }, 'json');
        }
        //保存字段顺序
        function saveDefineData(postDefineData, remove) {
            console.log(postDefineData);
            var postJsonStr = JSON.stringify(postDefineData);
            $('#user-define-td-form').on('beforeSubmit', function(){
                var form = $(this);
                if('{$flog}')
                {
                    $('#opportunitycustomfield-fields').val(postJsonStr);
                }
                else
                {
                    $('#customercustomfield-fields').val(postJsonStr);
                }
                $.post(form.attr('action'), form.serialize(), function(result){
                    if(result.status === 200){
                        $('.define-sure-btn').off('click');
                        $('#user-define-td-modal').modal('hide');
                        window.location.reload();
                        if(remove === 'remove'){
                            setGlobalTip('恢复默认成功', false);
                        }
                        setGlobalTip('保存自定义列表字段成功！', false);
                    }else{
                        $('.define-sure-btn').off('click');
                        if(remove === 'remove'){
                            setGlobalTip('恢复默认失败', true);
                        }
                        setGlobalTip('保存自定义列表字段失败！', true);
                    }
                }, 'json');
                return false;
            });   
        }
        $('.user-define-td').on('click',function(){
            queryDefineData();
            $('#user-define-td-modal').modal('show');
        });
        //Li鼠标滑过
        $('.user-define-td').hover(function(){
            $(this).find('i').addClass('set-define');
            $(this).find('a').css('color','#1AB493');
        },function(){
            $(this).find('a').css('color','#666');
            $(this).find('i').removeClass('set-define');
        });
        //生成配置自定义字段li集合
        function returnLis(selfDefineTds, isRecover) {
            var resultDefineTds = selfDefineTds.filter(function(item){
                return item.zh_name != undefined;
            });
            var flog = '{$flog}';//false->客户页面 true->商机页面
            if(isRecover) {//恢复默认
                var tdFront = [];
                if(flog) {//商机
                    // tdFront = ['客户ID','客户名称','创建时间','跟进人','下次跟进时间','最后跟进人','最后跟进时间','商机状态','标签'];
                    tdFront = ['创建时间','跟进人','下次跟进时间','最后跟进人','最后跟进时间','商机状态','标签'];
                    var topList = [];
                    resultDefineTds.forEach(function(item){
                        tdFront.forEach(function(filterItem, index){
                            if(item.zh_name === filterItem) {
                                item.sortNum = index;
                                item.show = '1';
                                // if(item.zh_name === '客户ID' || item.zh_name === '客户名称') {
                                //     item.require = true;
                                // }
                                topList.push(item);
                            }
                        })
                    });
                    topList.sort(function(item1, item2){
                        return item1.sortNum - item2.sortNum;
                    });
                    var bottomList = resultDefineTds.filter(function(item){
                        // return item.zh_name != '客户ID' && item.zh_name != '客户名称' && item.zh_name != '创建时间' && item.zh_name != '跟进人' && item.zh_name != '下次跟进时间' && item.zh_name != '最后跟进人' && item.zh_name != '最后跟进时间' && item.zh_name != '商机状态' && item.zh_name != '标签';
                        return  item.zh_name != '创建时间' && item.zh_name != '跟进人' && item.zh_name != '下次跟进时间' && item.zh_name != '最后跟进人' && item.zh_name != '最后跟进时间' && item.zh_name != '商机状态' && item.zh_name != '标签';
                    });
                    bottomList.forEach(function(item){
                        item.show = '0';
                    })
                    resultDefineTds = topList.concat(bottomList);
                    var firstLi = {name: '商机ID',require: true,show: 1,zh_name:'商机ID'};
                    var secondLi = {name: '商机名称',require: true,show: 1,zh_name:'商机名称'};
                    resultDefineTds.unshift(secondLi);
                    resultDefineTds.unshift(firstLi);
                }else{//客户
                    tdFront = ['创建时间','客户来源','负责人','负责人跟进状态'];
                    var topList = [];
                    resultDefineTds.forEach(function(item){
                        tdFront.forEach(function(filterItem, index){
                            if(item.zh_name === filterItem) {
                                item.sortNum = index;
                                item.show = '1';
                                topList.push(item);
                            }
                        })
                    });
                    topList.sort(function(item1, item2){
                        return item1.sortNum - item2.sortNum;
                    });
                    var bottomList = resultDefineTds.filter(function(item){
                        return item.zh_name != '创建时间' && item.zh_name != '客户来源' && item.zh_name != '负责人' && item.zh_name != '负责人跟进状态';
                    });
                    bottomList.forEach(function(item){
                        item.show = '0';
                    })
                    resultDefineTds = topList.concat(bottomList);
                    var firstLi = {name: '客户ID',require: true,show: 1,zh_name:'客户ID'};
                    var secondLi = {name: '客户名称',require: true,show: 1,zh_name:'客户名称'};
                    resultDefineTds.unshift(secondLi);
                    resultDefineTds.unshift(firstLi);
                }
            }else{
                if(!flog) {//客户列表后台没有返回客户id和客户名称
                    var firstLi = {name: '客户ID',require: true,show: 1,zh_name:'客户ID'};
                    var secondLi = {name: '客户名称',require: true,show: 1,zh_name:'客户名称'};
                    resultDefineTds.unshift(secondLi);
                    resultDefineTds.unshift(firstLi);
                }else{
                    resultDefineTds = resultDefineTds.filter(function(item){
                        return item.zh_name !== '商机ID' && item.zh_name !== '商机名称';
                    });
                    var firstLi = {name: '商机ID',require: true,show: 1,zh_name:'商机ID'};
                    var secondLi = {name: '商机名称',require: true,show: 1,zh_name:'商机名称'};
                    resultDefineTds.unshift(secondLi);
                    resultDefineTds.unshift(firstLi);
                }
            }
            $('#sortUl').html('');
            resultDefineTds.forEach(function (item) {
                var isShowData = item.show == '1' ? 1 : 0;
                var liItem = $('<li data-show=' + isShowData + ' data-name=' + item.name + ' class="ui-state-default"><span class="li-icon"></span><span class="define-td-name">' + item["zh_name"] + '</span></li>');
                if (item.require) {
                    liItem.addClass('ui-state-disabled');
                    var requireSpan = $('<span class="is-require-text">（必选）</span>');
                    liItem.append(requireSpan);
                }
                var controlShow = $('<span class="isShowGroup"><a class="showBtn">显示</a>/<a class="hideBtn">隐藏</a></span>');
                if (item.show == 1) {
                    liItem.find('.li-icon').addClass('is-show');
                    controlShow.find('a:first').css('color', '#29b699');
                } else {
                    liItem.find('.li-icon').addClass('is-hide');
                    controlShow.find('a:last').css('color', '#29b699');
                }
                controlShow.attr('data-isShow', item.show ? true : false);
                liItem.append(controlShow);
                $('#sortUl').append(liItem);
            });
            if(isRecover) { //恢复默认
                var postDefineData = Array.from($('#sortUl li')).map(function(item,index){
                    if($(item).attr('class').indexOf('ui-state-disabled') == -1){
                        return {name: $(item).attr('data-name'), show: $(item).attr('data-show')};
                    }
                });
                postDefineData.shift();
                postDefineData.shift();
                saveDefineData(postDefineData, 'remove');
            }
            $('#sortUl li').hover(function(){
                $(this).find('.li-icon').addClass('is-move').removeClass('is-show,is-hide');
            },function(){
                var showState = $(this).find('.isShowGroup').attr('data-isShow');
                if(showState) {
                    $(this).find('.li-icon').removeClass('is-move is-hide').addClass('is-show');
                }else{
                    $(this).find('.li-icon').removeClass('is-move is-show').addClass('is-hide');
                }
            });
            //点击显示
            $('.showBtn').on('click',function(){
                console.log(1)
                if($(this).parent().parent().attr('class').indexOf('ui-state-disabled') == -1){
                    $(this).parent().parent().attr('data-show',1);
                    $(this).css('color','rgb(41, 182, 153)');
                    $(this).siblings().css('color','#454545');
                }
            });
            //点击隐藏
            $('.hideBtn').on('click',function(){
                console.log(2)
                if($(this).parent().parent().attr('class').indexOf('ui-state-disabled') == -1){
                    $(this).parent().parent().attr('data-show',0);
                    $(this).css('color','rgb(41, 182, 153)');
                    $(this).siblings().css('color','#454545');
                }
            });
            clickSureBtn();
        }
        //恢复默认
        $('.recover-btn').on('click',function(){
            queryDefineData(true);
        });
        //点击确定按钮
        function clickSureBtn(){
            $('.define-sure-btn').on('click',function(){
                var postDefineData = Array.from($('#sortUl li')).map(function(item,index){
                    if($(item).attr('class').indexOf('ui-state-disabled') == -1){
                        return {name: $(item).attr('data-name'), show: $(item).attr('data-show')};
                    }
                });
                postDefineData.shift();
                postDefineData.shift();
                saveDefineData(postDefineData);
            });
        }
        $('#sortUl').sortable({
            items: 'li:not(.ui-state-disabled)'
        });
        $('#sortUl li').disableSelection();
        
    });
JS
);?>
</div>
<!-- 点击自定义列表字段显示表格字段模态框，结束 -->
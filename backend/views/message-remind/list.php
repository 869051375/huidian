<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '消息提醒';
$this->params['breadcrumbs'][] = $this->title;
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
    <div class="row">
        <div class="col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <button type="button" class="btn btn-default btn-sm" id="batch-delete"
                            data-target="#batch-delete-modal" data-toggle="modal">批量删除</button>
                    <button type="button" class="btn btn-default btn-sm" id="batch-read"
                            data-target="#batch-read-modal" data-toggle="modal">全部标记为已读</button>
                        <span class="remind"></span>
                </div>
                <div class="ibox-content">
                    <table class="table" id="message-table">
                        <thead>
                        <tr>
                            <th><?= Html::checkbox('check-all', false, ['id' => 'check-all']); ?>
                                用户ID</th>
                            <th><span class="glyphicon glyphicon-envelope"></span></th>
                            <th>来自</th>
                            <th>主题</th>
                            <th>时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $checkbox = Html::checkbox('ids[]', false, ['value' => '{v_ids}', 'class' => 'checkbox-message']);
                        /**@var \common\models\MessageRemind[] $models **/
                        foreach ($models as $model): ?>
                            <tr>
                                <td><?= str_replace('{v_ids}', $model->id, $checkbox)?>
                                    <?= $model->id?></td>
                                <td><span class="<?php if ($model->isRead()):?>glyphicon glyphicon-print<?php else:?>glyphicon glyphicon-envelope<?php endif;?>"></span></td>
                                <td><?= $model->creator_name?></td>
                                <td><a class="<?php if (!$model->isRead()):?>text-danger<?php endif;?>" href="<?= Url::to(['message-remind/read', 'id' => $model->id, 'status' => $model->type_url])?>"><?= $model->message?></a></td>
                                <td><?= $model->created_at > 0 ? Yii::$app->formatter->asDatetime($model->created_at) : '0';?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="margin-auto">
                            <?= LinkPager::widget(['pagination' => $pagination]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!--批量删除start-->
<div class="modal fade" id="batch-delete-modal" role="dialog" aria-labelledby="batch-delete-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $batchDeleteForm = new \backend\models\MessageRemindDeleteForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['batch-delete'],
                'id' => 'batch-delete-form',
            ]); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="batch-delete-label">删除</h4>
            </div>
            <div class="modal-body">
                <div id="selected-message" style="display: none;">
                </div>
                确定删除吗？
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php $this->registerJs(<<<JS
            $(function(){
                $.fn.modal.Constructor.prototype.enforceFocus = function(){};
                var table = $('#message-table');
                $('#batch-delete').click(function() {
                    $('#batch-delete-form').find('.warning-active').text('');
                });
                $('#check-all').click(function(){
                    var isChecked = $(this).prop('checked');
                    var items = table.find('.checkbox-message');
                    items.prop('checked', isChecked);
                    setBatchBtnState(isChecked && items.length > 0)
                });
                table.find('.checkbox-message').click(function(){
                    var isAllChecked = true;
                    var all = table.find('.checkbox-message');
                    var checkNum = 0;
                    for(var i = 0; i < all.length; i++)
                    {
                        var isChecked = $(all[i]).prop('checked');
                        isAllChecked = isChecked && isAllChecked;
                        if(isChecked) checkNum++;
                    }
                    if(!isAllChecked && checkNum > 0)
                    {
                        $('#check-all').prop('indeterminate', true);
                    }
                    else
                    {
                        $('#check-all').prop('checked', isAllChecked).prop('indeterminate', false);
                    }
                    setBatchBtnState(checkNum > 0);
                });
                
                function setBatchBtnState(isShow)
                {
                    $('#selected-message').empty();
                    var all = $('#message-table').find('.checkbox-message');
                    for(var i = 0; i < all.length; i++)
                    {
                        var isChecked = $(all[i]).prop('checked');
                        if(isChecked)
                        {
                            $(all[i]).clone().prop('checked', true).appendTo('#selected-message');
                        }
                    }
                }
                
                $('#batch-delete-form').on('beforeSubmit', function(){
                    var form = $(this);
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
                    return false;
                });
            });
JS
            );?>
        </div>
    </div>
</div>
<!--批量删除end-->

<!--批量阅读start-->
<div class="modal fade" id="batch-read-modal" role="dialog" aria-labelledby="batch-read-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $batchReadForm = new \backend\models\MessageRemindReadForm();
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['batch-read'],
                'id' => 'batch-read-form',
            ]); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="batch-read-label">标记为已读</h4>
            </div>
            <div class="modal-body">
                <div id="selected-message-read" style="display: none;">
                </div>
                确定标记为已读吗？
            </div>
            <div class="modal-footer">
                <span class="text-danger warning-active"></span>
                <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary sure-btn">确定</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end(); ?>
            <?php $this->registerJs(<<<JS
            $(function(){
                $.fn.modal.Constructor.prototype.enforceFocus = function(){};
                var table = $('#message-table');
                $('#batch-read').click(function() {
                    $('#batch-read-form').find('.warning-active').text('');
                });
                $('#check-all').click(function(){
                    var isChecked = $(this).prop('checked');
                    var items = table.find('.checkbox-message');
                    items.prop('checked', isChecked);
                    setBatchBtnState(isChecked && items.length > 0)
                });
                table.find('.checkbox-message').click(function(){
                    var isAllChecked = true;
                    var all = table.find('.checkbox-message');
                    var checkNum = 0;
                    for(var i = 0; i < all.length; i++)
                    {
                        var isChecked = $(all[i]).prop('checked');
                        isAllChecked = isChecked && isAllChecked;
                        if(isChecked) checkNum++;
                    }
                    if(!isAllChecked && checkNum > 0)
                    {
                        $('#check-all').prop('indeterminate', true);
                    }
                    else
                    {
                        $('#check-all').prop('checked', isAllChecked).prop('indeterminate', false);
                    }
                    setBatchBtnState(checkNum > 0);
                });
                
                function setBatchBtnState(isShow)
                {
                    $('#selected-message-read').empty();
                    var all = $('#message-table').find('.checkbox-message');
                    for(var i = 0; i < all.length; i++)
                    {
                        var isChecked = $(all[i]).prop('checked');
                        if(isChecked)
                        {
                            $(all[i]).clone().prop('checked', true).appendTo('#selected-message-read');
                        }
                    }
                }
                
                $('#batch-read-form').on('beforeSubmit', function(){
                    var form = $(this);
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
                    return false;
                });
            });
JS
            );?>
        </div>
    </div>
</div>
<!--批量阅读end-->
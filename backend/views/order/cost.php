<?php
/* @var $provider yii\data\ActiveDataProvider */

use backend\widgets\LinkPager;
use yii\helpers\Url;
/** @var \common\models\CostItem[] $models */
$models = $provider->getModels();
$pagination = $provider->pagination;
$nowUrl = Yii::$app->request->getUrl();
$costItemUrl = Url::to(['order-cost/create']);
?>
<!--成本类型库弹框开始-->
<?php
$costItem = new \common\models\CostItem();
$form = \yii\bootstrap\ActiveForm::begin([
    'action' => [$costItemUrl],
    'id' => 'cost-item-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-2',
            'offset' => 'col-sm-offset-2',
            'wrapper' => 'col-sm-6',
            'hint' => 'col-sm-2'
        ],
    ],
]); ?>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
            </button>
                <h4 class="modal-title">成本类型库</h4>
        </div>
        <div class="modal-body input_box">
            <?= $form->field($costItem, 'name')->textInput()?>
            <?= $form->field($costItem, 'price')->textInput()->hint('元')?>
            <input type="hidden" id="save-input" name="save">
            <div class="form-group">
                <div class="col-sm-8 col-sm-offset-2">
                    <span class="text-danger warning-active"></span><br>
                    <button type="button" class="btn btn-default cost-item-btn" id="btn-no-use">保存不使用</button>
                    <button type="button" class="btn btn-default cost-item-btn" id="btn-use">保存并使用</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>成本名称</th>
                    <th>成本金额（元）</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody class="list-cost">
                <?php foreach ($models as $model):?>
                <tr class="list-item" data-id="<?= $model->id; ?>" data-name="<?= $model->name; ?>" data-price="<?= $model->price ?>">
                    <th class="cost-list-name"><?= $model->name; ?></th>
                    <th><?= $model->price ?></th>
                    <th>
                        <span class="btn btn-xs btn-white del-cost-btn">删除</span>
                        <span class="btn btn-xs btn-white edit-cost-btn">编辑</span>
                    </th>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?=
            LinkPager::widget([
                'pagination' => $provider->pagination,
            ]);
            ?>
        </div>
    </div>
</div>
<?php \yii\bootstrap\ActiveForm::end(); ?>
<!--成本类型库弹框结束-->
<?php
$deleteUrl = Url::to(['order-cost/delete']);
$updateUrl = Url::to(['order-cost/create','cid' => '__id__']);
$this->registerJs(<<<JS
$('#btn-no-use').click(function() 
{
   $('#save-input').val('1');
});
$('#btn-use').click(function() 
{
   $('#save-input').val('');
});
//创建新的成本类型
$('.cost-item-btn').click(function()
{
      var url = $('#edit_cost form').attr('action');
      $.post(url, $('#cost-item-form').serialize(), function(rs)
      {
            if(rs['status'] === 200)
            {
                $('.warning-active').text('');
                var result = null;
                var li_result = null;
                if(rs['cid'])
                {
                     result = 
                     '<th class="cost-list-name">'+rs.item.name+'</th>'+
                     '<th>'+rs.item.price+'</th>'+
                     '<th>'+
                     '<span class="btn btn-xs btn-white del-cost-btn">删除</span>' +
                     '<span class="btn btn-xs btn-white edit-cost-btn">编辑</span>' +
                     '</th>';
                    $('.list-cost').find('tr[data-id="'+rs.cid+'"]').html(result);
                    $('#li-cost').find('li[data-id="'+rs.cid+'"]').text(rs.item.name).attr('data-price',rs.item.price);
                }
                else
                {
                     result = 
                     '<tr class="list-item" data-id="'+rs.item.id+'" data-name="'+rs.item.name+'" data-price="'+rs.item.price+'">'+
                     '<th class="cost-list-name">'+rs.item.name+'</th>'+
                     '<th>'+rs.item.price+'</th>'+
                     '<th>'+
                     '<span class="btn btn-xs btn-white del-cost-btn">删除</span>' +
                     '<span class="btn btn-xs btn-white edit-cost-btn">编辑</span>' +
                     '</th>'+
                     '</tr>';
                     $('.list-cost').prepend(result);
                     li_result = '<li data-price="'+rs.item.price+'">'+rs.item.name+'</li>';
                     $('.li-cost').prepend(li_result);
                 }
                 $('.page-total-count').text('总数：'+rs.num+'条');
                 if(rs['save'])
                 {
                     $('#cost-item-form').trigger('reset.yiiActiveForm');
                 }
                 else 
                 {
                     $('#cost-name').val(rs.item.name);
                     $('.class-cost-name').val(rs.item.name);
			         $('.class-cost-price').val(rs.item.price);
			         $('.cost-modal').modal('hide');
                 }
            }
            else
            {
                 $('.warning-active').text(rs.message);
            }
       }, 'json');
           return false;
});

//修改
$('.list-cost').on('click','.edit-cost-btn',function() 
{
   $('.warning-active').text('');
   var id = $(this).parents('.list-item').attr('data-id');
   var cost_name = $(this).parents('.list-item').attr('data-name');
   var cost_price = $(this).parents('.list-item').attr('data-price');
   $('#costitem-name').val(cost_name);
   $('#costitem-price').val(cost_price);
   $('#edit_cost form').attr('action', '{$updateUrl}'.replace('__id__', id));
});

$('.list-cost').on('click','.cost-list-name',function() 
{
    var name = $(this).parents('.list-item').attr('data-name');
    var price = $(this).parents('.list-item').attr('data-price');
    $('#cost-name').val(name);
    $('.class-cost-name').val(name);
	$('.class-cost-price').val(price);
	$('.cost-modal').modal('hide');
});

//删除
$('.list-cost').on('click','.del-cost-btn',function()
{
    var _this = $(this);
	var id = _this.parents('.list-item').attr('data-id');
	$.post('{$deleteUrl}',{id:id},function(rs)
	{
	    if(rs.status !== 200)
	    {
	        $('.warning-active').html(rs.message);
	    }else{
	        $('.page-total-count').text('总数：'+rs.num+'条');
	        _this.parents('.list-item').remove();
	        $('#li-cost').find('li[data-id="'+id+'"]').remove();
	    }
	},'json')
})


JS
)?>

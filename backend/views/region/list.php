<?php
/** @var \yii\web\View $this */

use yii\helpers\Url;

/** @var \common\models\Province[] $provinces */
/** @var int $pid */
/** @var int $cid */
$this->title = '服务地区管理';

$this->params['breadcrumbs'] = [$this->title];
?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="text-right"><a href="<?= Url::to(['region/export']) ?>" class="btn btn-sm btn-primary text-right">导出服务地区</a></div>
                    <div class="row">
                        <div class="col-xs-3">
                            <strong>省</strong>
                        </div>
                        <div class="col-xs-3 col-xs-offset-1">
                            <strong>市</strong>
                        </div>
                        <div class="col-xs-3 col-xs-offset-1">
                            <strong>区/县</strong>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-xs-3">
                            <ul class="list-group sortablelist" data-type="province">

                                <?php
                                $currentProvince = null;
                                $cities = [];
                                foreach ($provinces as $province):
                                    if (empty($currentProvince) && ($pid == 0 || $pid == $province->id)) {
                                        $currentProvince = $province;
                                        $cities = $province->cities;
                                    }
                                    ?>
                                    <li class="list-group-item hover_li sortableitem so1 <?= $currentProvince && $currentProvince->id == $province->id ? 'list-group-item-info' : '' ?>"
                                        data-sort="<?= $province->sort ?>" data-id="<?= $province->id ?>">
                                        <div class="row">
                                            <div class="col-xs-5">
                                                <a class="color_66"
                                                   href="<?= \yii\helpers\Url::to(['list', 'pid' => $province->id]) ?>"><?= $province->name ?></a>
                                            </div>
                                            <div class="col-xs-7">
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal_province" href="#"
                                                       data-whatever="编辑省份">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-btn delete-province"
                                                       data-toggle="modal" data-whatever="删除省份"
                                                       data-target="#myModal2" href="#">删除</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up"><i
                                                                class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (Yii::$app->user->can('region/create')): ?>
                                <span class="btn btn-default btn-block add-province" data-toggle="modal"
                                      data-whatever="新增省份"
                                      data-target="#myModal_province" data-pid="0">新增</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-chevron-right text-center center-block"
                                  style="font-size: 24px;color: #1ab394;margin-top: 60px;"></span>

                        </div>
                        <div class="col-xs-3">
                            <ul class="list-group sortablelist" data-type="city">
                                <?php
                                $currentCity = null;
                                $districts = [];
                                foreach ($cities as $city):
                                    if (empty($currentCity) && ($cid == 0 || $cid == $city->id)) {
                                        $currentCity = $city;
                                        $districts = $city->districts;
                                    }
                                    ?>
                                    <li class="list-group-item hover_li sortableitem so2 <?= $currentCity && $currentCity->id == $city->id ? 'list-group-item-info' : '' ?>"
                                        data-sort="<?= $city->sort ?>" data-id="<?= $city->id ?>">
                                        <div class="row">
                                            <div class="col-xs-5">
                                                <a class="color_66"
                                                   href="<?= \yii\helpers\Url::to(['list', 'pid' => $city->province_id, 'cid' => $city->id]) ?>"><?= $city->name ?></a>
                                            </div>
                                            <div class="col-xs-7">
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal_city"
                                                       data-whatever="编辑城市"
                                                       href="#">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-city" href="#"
                                                       data-toggle="modal" data-whatever="删除市"
                                                       data-target="#myModal2">删除</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up"><i
                                                                class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>

                            </ul>

                            <?php if ($currentProvince && Yii::$app->user->can('region/create')): ?>
                                <span class="btn btn-default btn-block add-city" data-toggle="modal"
                                      data-whatever="新增城市"
                                      data-pid="<?= $currentProvince->id ?>" data-target="#myModal_city">新增</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-1">
                            <span class="glyphicon glyphicon-chevron-right text-center center-block"
                                  style="font-size: 24px;color: #1ab394;margin-top: 60px;"></span>

                        </div>
                        <div class="col-xs-3">
                            <ul class="list-group sortablelist" data-type="district">
                                <?php foreach ($districts as $district): ?>
                                    <li class="list-group-item hover_li sortableitem so3"
                                        data-sort="<?= $district->sort ?>" data-id="<?= $district->id ?>">
                                        <div class="row">
                                            <div class="col-xs-5">
                                                <a class="color_66" href="#"><?= $district->name ?></a>
                                            </div>
                                            <div class="col-xs-7">
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <a class="btn btn-xs btn-white pull-right update-btn"
                                                       data-toggle="modal" data-target="#myModal_district"
                                                       data-whatever="编辑区县"
                                                       href="#">编辑</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/delete')): ?>
                                                    <a class="btn btn-xs btn-white pull-right delete-district" href="#"
                                                       data-toggle="modal" data-whatever="删除区县"
                                                       data-target="#myModal2">删除</a>
                                                <?php endif; ?>
                                                <?php if (Yii::$app->user->can('region/update')): ?>
                                                    <span class="btn btn-xs btn-link move-up"><i
                                                                class="glyphicon glyphicon-arrow-up"></i></span>
                                                    <span class="btn btn-xs btn-link move-down"><i
                                                                class="glyphicon glyphicon-arrow-down"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if ($currentCity && Yii::$app->user->can('region/create')): ?>
                                <span class="btn btn-default btn-block add-district" data-whatever="新增区县"
                                      data-toggle="modal"
                                      data-target="#myModal_district" data-pid="<?= $currentCity->id ?>">新增</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal fade" id="myModal_province" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $province = new \common\models\Province();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['region/create', 'data' => 'province'],
            'enableAjaxValidation' => true,
            'validationUrl' => ['region/validation', 'data' => 'province'],
            'id' => 'province-form',
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
                    <h4 class="modal-title province">新增省份</h4>
                </div>
                <div class="modal-body input_box">
                    <?= $form->field($province, 'name')->textInput() ?>
                    <?= $form->field($province, 'letter')->textInput() ?>
                    <?= $form->field($province, 'pinyin')->textInput() ?>
                    <?= $form->field($province, 'code')->textInput() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>

    <div class="modal fade" id="myModal_city" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $city = new \common\models\City();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['region/create', 'data' => 'city'],
            'enableAjaxValidation' => true,
            'validationUrl' => ['region/validation', 'data' => 'city'],
            'id' => 'city-form',
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
                    <h4 class="modal-title city">新增城市</h4>
                </div>
                <div class="modal-body input_box" id="city-modal">
                    <?= \yii\bootstrap\Html::activeHiddenInput($city, 'province_id') ?>
                    <?= $form->field($city, 'name')->textInput() ?>
                    <?= $form->field($city, 'letter')->textInput() ?>
                    <?= $form->field($city, 'pinyin')->textInput() ?>
                    <?= $form->field($city, 'code')->textInput() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>

    <div class="modal fade" id="myModal_district" role="dialog" aria-labelledby="myModalLabel">
        <?php
        $district = new \common\models\District();
        $form = \yii\bootstrap\ActiveForm::begin([
            'action' => ['region/create', 'data' => 'province'],
            'enableAjaxValidation' => true,
            'validationUrl' => ['region/validation', 'data' => 'province'],
            'id' => 'district-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-3',
                    'offset' => 'col-sm-offset-3',
                    'wrapper' => 'col-sm-7',
                ],
            ],
        ]); ?>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title district">新增区县</h4>
                </div>
                <div class="modal-body input_box" id="district-modal">
                    <?= \yii\bootstrap\Html::activeHiddenInput($district, 'province_id') ?>
                    <?= $form->field($district, 'name')->textInput() ?>
                    <?= $form->field($district, 'letter')->textInput() ?>
                    <?= $form->field($district, 'pinyin')->textInput() ?>
                    <?= $form->field($district, 'code')->textInput() ?>
                    <?= \yii\bootstrap\Html::activeHiddenInput($district, 'city_id') ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary add-sure-btn">确定</button>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
    <div class="modal fade" id="myModal2" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">删除分类</h4>
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

<?php \backend\assets\SortAsset::register($this); ?>
<?php
$this->registerJs("
        
    $('.cancel-btn').on('click',function(){
        $('.warning-active').html('');
    })
	$('.delete-province').on('click',function(){
	    var active_id = $(this).parents('.sortablelist').children('.list-group-item-info').attr('data-id');
	    var delete_id = $(this).parents('.sortableitem').attr('data-id');
	    console.log(active_id);
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete', 'data' => 'province']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            if(active_id == delete_id){
	                location.href='" . \yii\helpers\Url::to(['list']) . "';
	            }else{
	            window.location.reload();
	            }
	        }
	    },'json')
	    })
	})
	$('.delete-city').on('click',function(){
        var active_id = $(this).parents('.sortablelist').children('.list-group-item-info').attr('data-id');
	    var delete_id = $(this).parents('.sortableitem').attr('data-id');
	    $('.sure-btn').on('click',function(){
	    
	        $.post('" . \yii\helpers\Url::to(['delete', 'data' => 'city']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            if(active_id == delete_id){
	                location.href='" . \yii\helpers\Url::to(['list']) . "';
	            }else{
	            window.location.reload();
	            }
	        }
	    },'json')
	    })
	})
	$('.delete-district').on('click',function(){
	    var delete_id = $(this).parents('.sortableitem').attr('data-id');
	    $('.sure-btn').on('click',function(){
	        $.post('" . \yii\helpers\Url::to(['delete', 'data' => 'district']) . "',{id:delete_id},function(rs){
	        if(rs.status != 200){
	            $('.warning-active').html(rs.message);
	        }else{
	            window.location.reload();
	        }
	    },'json')
	    })
	})
	$('#myModal2').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });
    
    var createAction1 = '" . \yii\helpers\Url::to(['create', 'data' => 'province']) . "';
    var createAction2 = '" . \yii\helpers\Url::to(['create', 'data' => 'city']) . "';
    var createAction3 = '" . \yii\helpers\Url::to(['create', 'data' => 'district']) . "';
    
    $('.add-province').on('click',function(){
        $('#province-form').trigger('reset.yiiActiveForm');
        $('.modal form').attr('action', createAction1);
        $('.input_box input').val('');
        $('.input_box textarea').val('');
    });
    $('.add-city').on('click',function(){
        var whatever = $(this).attr('data-whatever');
        $('.city').text(whatever);
        $('#city-form').trigger('reset.yiiActiveForm');
        $('.modal form').attr('action', createAction2);
        var province_id = $(this).attr('data-pid');
        console.log(province_id);
        var hidden_input = $('#city-modal input:first');
        $('.input_box input').val('');
        $('.input_box textarea').val('');
        hidden_input.attr('value',province_id);
    });
    $('.add-district').on('click',function(){
        var whatever = $(this).attr('data-whatever');
        $('.district').text(whatever);
        $('#district-form').trigger('reset.yiiActiveForm');
        $('.modal form').attr('action', createAction3);
        var district_id = $(this).attr('data-pid');
        var hidden_input = $('#district-modal input:first');
        var hidden_input2 = $('#district-modal input:last');
        $('.input_box input').val('');
        $('.input_box textarea').val('');
        hidden_input.attr('value',district_id);
        hidden_input2.attr('value',district_id);
    });
    
    $('.update-btn').on('click',function(){
        $('#province-form').trigger('reset.yiiActiveForm');
        $('#city-form').trigger('reset.yiiActiveForm');
        $('#district-form').trigger('reset.yiiActiveForm');
        var data_type = $(this).parents('.sortablelist').attr('data-type');
        var id = $(this).parents('.list-group-item').attr('data-id');
        var updateAction = '" . \yii\helpers\Url::to(['update', 'data' => '__data_type__', 'id' => '__id__']) . "';
        $('.modal form').attr('action', updateAction.replace('__id__', id).replace('__data_type__', data_type));
        $.get('" . \yii\helpers\Url::to(['detail', 'data' => '__data_type__', 'id' => '__id__']) . "'.replace('__id__', id).replace('__data_type__', data_type),function(rs){
            if(rs.status!=200){
            }else{
                $('#'+data_type+'-name').val(rs.model.name);
                $('#'+data_type+'-letter').val(rs.model.letter);
                $('#'+data_type+'-pinyin').val(rs.model.pinyin);
                $('#'+data_type+'-code').val(rs.model.code);
                $('#'+data_type+'-province_id').val(rs.model.province_id);
                $('#'+data_type+'-city_id').val(rs.model.city_id);
            }
        },'json')
        
    })
    $('#myModal_province').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });
    $('#myModal_city').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });
    $('#myModal_district').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-title').text(recipient);
    });
    
    
    $('.sortablelist').clickSort({
        speed:200,
        moveCallback: function(source_id, target_id, dataType){
            $.post('" . \yii\helpers\Url::to(['region/sort', 'data' => '__data_type__']) . "'.replace('__data_type__', dataType), {source_id: source_id,target_id: target_id}, function(rs){
                console.log('aa');
            }, 'json');
        },
        callback:function(){
            setTimeout(function(){
                $('.sortableitem').find('.move-up,.move-down').show();
                var div1 = $('.so1:first');
                var div2 = $('.so1:last');
                var div3 = $('.so2:first');
                var div4 = $('.so2:last');
                var div5 = $('.so3:first');
                var div6 = $('.so3:last');
                div1.find('.move-up').hide();
                div2.find('.move-down').hide();
                div3.find('.move-up').hide();
                div4.find('.move-down').hide();
                div5.find('.move-up').hide();
                div6.find('.move-down').hide();
            }, 30);
        }

    });
$('.sortablelist').find('.move-up,.move-down').show();
var div1 = $('.so1:first');
var div2 = $('.so1:last');
var div3 = $('.so2:first');
var div4 = $('.so2:last');
var div5 = $('.so3:first');
var div6 = $('.so3:last');
div1.find('.move-up').hide();
div2.find('.move-down').hide();
div3.find('.move-up').hide();
div4.find('.move-down').hide();
div5.find('.move-up').hide();
div6.find('.move-down').hide();

$('#myModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var recipient = button.data('whatever');
    var modal = $(this);
    modal.find('.modal-title').text(recipient);
});

    ");
?>
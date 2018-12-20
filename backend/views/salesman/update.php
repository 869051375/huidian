<?php
/* @var $this yii\web\View */
$this->title = '业务人员管理';
$this->params['breadcrumbs'] = [['label' => $this->title, 'url' => ['administrator/list-salesman']]];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row page-select2-area">
        <div class="col-xs-12">
            <?php
            $form = \yii\bootstrap\ActiveForm::begin([
                'action' => ['administrator/salesman-update', 'id' => $model->id],
                'validationUrl' => ['administrator/salesman-validation'],
                'enableAjaxValidation' => true,
                'id' => 'salesman-form',
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
                        <!--基本信息开始-->
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-2">
                                基本信息
                            </div>
                            <div class="col-sm-8 col-sm-offset-2">
                                <?= $form->field($model, 'id')->hiddenInput() ?>
                                <?= $form->field($model, 'qq')->textInput(['id'=> 'salesman-qq']) ?>
                            </div>
                        </div>
                        <!--基本信息结束-->
                        <?php if(Yii::$app->user->can('administrator/update-salesman')): ?>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-2">
                                <button type="submit" class="btn btn-primary sure-btn">保存</button>
                            </div>
                        </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        <?php \yii\bootstrap\ActiveForm::end(); ?>
    </div>
    </div>
<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use zhuravljov\yii\widgets\DateTimePicker;

$actionUniqueId = Yii::$app->controller->action->uniqueId;
/** @var \yii\data\DataProviderInterface $provider */
/** @var \common\models\AdministratorLog[] $models */
/** @var string $type */
/** @var \backend\models\AdministratorLogSearch $searchModel */
$models = $provider->getModels();
$pagination = $provider->getPagination();
if ($actionUniqueId == 'administrator-log/record')
{
    $this->title = '操作日志';
}
else
{
    $this->title = '风险操作告警';
}
$this->params['breadcrumbs'] = [$this->title];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <?php if (Yii::$app->user->can('administrator-log/record')): ?>
                    <li <?php if ($actionUniqueId == 'administrator-log/record'):?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['administrator-log/record']) ?>">操作日志</a>
                    </li>
                    <li <?php if ($actionUniqueId == 'administrator-log/warning'):?> class="active"<?php endif; ?>>
                        <a href="<?= Url::to(['administrator-log/warning']) ?>">风险操作告警</a>
                    </li>
                <?php endif; ?>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="page-select2-area">
                            <?php
                            $labelOptions = ['labelOptions' => ['class' => false]];
                            $form = ActiveForm::begin([
                                    'layout' => 'inline',
                                    'method' => 'get',
                                    'action' => ['administrator-log/'.Yii::$app->controller->action->id]]
                            ); ?>
                            <!--下单时间-->
                            <div  class="select2-options">
                                <b>时间</b>
                                <?= $form->field($searchModel, 'starting_time')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px;'],
                                ]) ?>
                                <?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::className(), [
                                    'clientOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'language' => 'zh-CN',
                                        'autoclose' => true,
                                        'minView' => 'month',
                                    ],
                                    'clientEvents' => [],
                                    'options' => ['class' => 'form-control', 'style'=>'width:146px;margin-left:6px'],
                                ]) ?>
                            </div>
                            <?php if ($actionUniqueId == 'administrator-log/record'):?>
                            <?= $form->field($searchModel, 'keyword', $labelOptions)->textInput(['placeholder'=>'请输入用户名'])->label('用户') ?>
                            <?php endif;?>
                            <button type="submit" class="btn btn-primary">搜索</button>
                            <?php \yii\bootstrap\ActiveForm::end(); ?>
                        </div>
                        <br>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>操作时间</th>
                                <th>用户</th>
                                <th>操作内容</th>
                                <th>操作IP</th>
                                <?php if ($actionUniqueId == 'administrator-log/warning'):?>
                                    <th>登录次数总计</th>
                                <?php endif;?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($models as $model): ?>
                                <tr>
                                    <td>
                                        <?= Yii::$app->formatter->asDatetime($model->created_at);?>
                                    </td>
                                    <td>
                                        <?= $model->administrator_name;?>
                                    </td>
                                    <td <?php if($actionUniqueId == 'administrator-log/warning' && $model->sign): ?>class="text-danger"<?php endif; ?>>
                                        <?= $model->desc;?>
                                    </td>
                                    <td>
                                        <?= $model->ip;?>
                                    </td>
                                    <?php if ($actionUniqueId == 'administrator-log/warning'):?>
                                        <td>
                                            <?php if ($model->type == \common\models\AdministratorLog::TYPE_LOGIN_SUCCESS):?>
                                            <?= $model->total;?>
                                            <?php endif;?>
                                        </td>
                                    <?php endif;?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?=
                        LinkPager::widget([
                            'pagination' => $pagination
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



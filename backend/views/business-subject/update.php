<?php
/* @var $this yii\web\View */
/* @var $customer \common\models\CrmCustomer */
/* @var $model \backend\models\BusinessSubjectForm */
/* @var $id  */
/* @var $subject  */

if($subject)
{
    $this->title = '编辑自然人主体信息';
} else
{
    $this->title = '编辑企业主体信息';
}
$this->params['breadcrumbs'] = [
    ['label' => '客户详情', 'url' => ['customer-detail/business-subject','id'=>$id]],
    $this->title
];
$model->operating_period_begin = empty($model->operating_period_begin)? '' : date('Y-m-d',$model->operating_period_begin);
$model->operating_period_end = empty($model->operating_period_end)? '' : date('Y-m-d',$model->operating_period_end);
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?=
    $this->render('_form', [
        'model' => $model,
        'subject' => $subject,
        'customer' => $customer,
        'id' => $id,
    ])
    ?>
</div>
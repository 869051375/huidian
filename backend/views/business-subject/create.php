<?php
/* @var $this yii\web\View */
/* @var $customer \common\models\CrmCustomer */
/* @var $model \common\models\BusinessSubject */
/* @var $id  */
/* @var $subject  */

if($subject)
{
    $this->title = '新建自然人主体信息';
} else
{
    $this->title = '新建企业主体信息';
}
$this->params['breadcrumbs'] = [
    ['label' => '客户详情', 'url' => ['customer-detail/business-subject','id'=>$id]],
    $this->title
];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?=
    $this->render('_form', [
        'model' => $model,
        'id' => $id,
        'subject' => $subject,
        'customer' => $customer,
    ])
    ?>
</div>
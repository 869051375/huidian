<?php
/* @var $this yii\web\View */
/* @var $role backend\models\AdministratorRole */

$this->title = '添加角色';
$this->params['breadcrumbs'] = [
    ['label' => '角色管理', 'url' => ['list']],
    $this->title
];
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?=
    /** @var array $permissionGroup */
    $this->render('_form', [
        'role' => $role,
        'permissionGroup' => $permissionGroup,
    ])
    ?>
</div>
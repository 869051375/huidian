<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

/* @var $role backend\models\AdministratorRole */

$this->title = '编辑角色';
$this->params['breadcrumbs'] = [
    ['label' => '角色管理', 'url' => ['list']],
    $this->title
];
$actionName = Yii::$app->controller->action->uniqueId;
?>
<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li <?php if($actionName == 'role/update'): ?>class="active"<?php endif; ?>>
                    <a href="<?= Url::to(['update','id' => $role->id]) ?>">角色编辑</a>
                </li>
                <li <?php if($actionName == 'role/member'): ?>class="active"<?php endif; ?>>
                    <a href="<?= Url::to(['member','id' => $role->id]) ?>">成员管理</a>
                </li>
            </ul>
            <?=
            /** @var array $permissionGroup */
            $this->render('_form', [
                'role' => $role,
                'permissionGroup' => $permissionGroup,
            ])
            ?>
        </div>
    </div>
</div>
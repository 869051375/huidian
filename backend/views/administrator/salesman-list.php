<table class="footable table table-striped">
    <thead>
    <tr>
        <th>真实姓名/手机</th>
        <th>邮箱</th>
        <th>QQ</th>
        <th>状态</th>
        <th class="text-right" data-sort-ignore="true">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php use backend\widgets\LinkPager;
    use common\models\Administrator;
    use yii\bootstrap\Html;
    use yii\helpers\Url;

    foreach ($models as $model):
        $options = [
            'id' => false,
            'class' => 'change-status-checkbox',
            'label' => false,
            'data-id' => $model->id,
            'data-type' => $model->type,
        ];
        if($model->is_root == 1)
        {
            $options['readonly'] = 'readonly';
        }
        if($actionUniqueId == 'administrator/list-manager')
        {
            if(!Yii::$app->user->can('administrator/status-manager'))
            {
                $options['readonly'] = 'readonly';
            }
        }
        elseif($actionUniqueId == 'administrator/list-customer-service')
        {
            if(!Yii::$app->user->can('administrator/status-customer-service'))
            {
                $options['readonly'] = 'readonly';
            }
        }
        elseif($actionUniqueId == 'administrator/list-supervisor')
        {
            if(!Yii::$app->user->can('administrator/status-supervisor'))
            {
                $options['readonly'] = 'readonly';
            }
        }
        elseif($actionUniqueId == 'administrator/list-clerk')
        {
            if(!Yii::$app->user->can('administrator/status-clerk'))
            {
                $options['readonly'] = 'readonly';
            }
        }
        elseif($actionUniqueId == 'administrator/list-salesman')
        {
            if(!Yii::$app->user->can('administrator/status-salesman'))
            {
                $options['readonly'] = 'readonly';
            }
        }
        ?>
        <tr>
            <td><?= $model->name;?><br/><?= $model->phone;?></td>
            <td><?= $model->email;?></td>
            <td><?= isset($model->salesman->qq) ? $model->salesman->qq : '';?></td>
            <td>
                <label>
                    <?= Html::activeCheckbox($model, 'status', $options); ?>
                </label>
            </td>
            <td class="text-right">
                <?php if(Yii::$app->user->can('administrator/force-login')):?>
                    <a class="btn btn-xs btn-link"
                       href="<?= Yii::$app->urlManager->createUrl(['/administrator/force-login', 'id' => $model->id]) ?>">Force Login</a>
                <?php endif; ?>
                <?php if($actionUniqueId == 'administrator/list-manager'):?>
                    <?php if (Yii::$app->user->can('administrator/update-manager')): ?>
                        <a class="btn btn-xs btn-white"
                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-manager', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                    <?php endif; ?>
                <?php elseif($actionUniqueId == 'administrator/list-customer-service'):?>
                    <?php if (Yii::$app->user->can('administrator/update-customer-service')): ?>
                        <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/customer-service-update', 'id' => $model->customerService->id]) ?>">编辑客服</a>
                        <a class="btn btn-xs btn-white"
                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-customer-service', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                    <?php endif;?>
                <?php elseif($actionUniqueId == 'administrator/list-supervisor'):?>
                    <?php if (Yii::$app->user->can('administrator/update-supervisor')): ?>
                        <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/supervisor-update', 'id' => $model->supervisor->id]) ?>">编辑嘟嘟妹</a>
                        <a class="btn btn-xs btn-white"
                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-supervisor', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                    <?php endif;?>

                <?php elseif($actionUniqueId == 'administrator/list-clerk'):?>
                    <?php if(Yii::$app->user->can('administrator/update-clerk')): ?>
                        <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/clerk-update', 'id' => $model->clerk->id]) ?>">编辑服务人员</a>
                        <a class="btn btn-xs btn-white"
                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-clerk', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                    <?php endif;?>

                <?php elseif($actionUniqueId == 'administrator/list-salesman'):?>
                    <?php if (Yii::$app->user->can('administrator/update-salesman')): ?>
                        <a class="btn btn-xs btn-white" href="<?= Yii::$app->urlManager->createUrl(['/administrator/salesman-update', 'id' => isset($model->salesman->id) ? $model->salesman->id : '']) ?>">编辑业务员</a>
                        <a class="btn btn-xs btn-white"
                           href="<?= Yii::$app->urlManager->createUrl(['/administrator/update-salesman', 'id' => $model->id, 'type' => $model->type]) ?>">编辑</a>
                    <?php endif;?>
                <?php endif;?>
                <?php if($model->is_root == 0): ?>
                    <?php if(Yii::$app->user->can('administrator/change-jobs')): ?>
                        <a class="btn btn-xs btn-white"
                           href="<?= Url::to(['administrator/change-jobs', 'id' => $model->id]) ?>">调岗</a>
                    <?php endif; ?>
                    <?php if(Yii::$app->user->can('administrator/leave')
                        && ($model->type == Administrator::TYPE_ADMIN
                            || $model->type == Administrator::TYPE_SALESMAN)): ?>
                        <a class="btn btn-xs btn-white"
                           href="<?= Url::to(['administrator/leave', 'id' => $model->id]) ?>">离职</a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5">
            <?=
            LinkPager::widget([
                'pagination' => $pagination,
            ]);
            ?>
        </td>
    </tr>
    </tfoot>
</table>
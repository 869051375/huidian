<?php /** @var \common\models\CrmCustomer $customer */?>
<ul class="nav nav-tabs m-t-md">
        <li<?php if (Yii::$app->controller->action->id == 'record'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/record', 'id' => $customer->id]) ?>">跟进记录</a>
        </li>
        <?php if($customer->isReceive()): ?>
        <li<?php if (Yii::$app->controller->action->id == 'information'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/information', 'id' => $customer->id]) ?>">详细信息</a>
        </li>
        <?php endif; ?>
        <li<?php if (Yii::$app->controller->action->id == 'business-subject'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/business-subject', 'id' => $customer->id]) ?>">业务办理主体</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'trademark'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/trademark', 'id' => $customer->id]) ?>">商标信息</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'order'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/order', 'id' => $customer->id]) ?>">关联订单</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'salesman-list'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/salesman-list', 'id' => $customer->id]) ?>">所属合伙人</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'do-record'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/do-record', 'id'=>$customer->id]) ?>">操作记录</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'login-log'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['customer-detail/login-log', 'id'=>$customer->id]) ?>">客户登录记录</a>
        </li>

</ul>
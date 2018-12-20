<?php /** @var \common\models\BusinessSubject $subject */?>
<ul class="nav nav-tabs">
        <li<?php if (Yii::$app->controller->action->id == 'information'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['business-subject/information', 'id' => $subject->id]) ?>">详细信息</a>
        </li>

        <li<?php if (Yii::$app->controller->action->id == 'order'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['business-subject/order', 'id' => $subject->id]) ?>">关联订单</a>
        </li>
    
        <li<?php if (Yii::$app->controller->action->id == 'do-record'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['business-subject/do-record', 'id'=>$subject->id]) ?>">操作记录</a>
        </li>
</ul>
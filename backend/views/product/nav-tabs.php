<?php /** @var \common\models\Product $product */?>
<ul class="nav nav-tabs">
    <?php if (Yii::$app->user->can('product/update')): ?>
        <li<?php if (Yii::$app->controller->action->id == 'update'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product/update', 'id' => $product->id]) ?>">商品基本信息设置</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-price/list')): ?>
        <li<?php if (Yii::$app->controller->action->id == 'price'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product/price', 'product_id' => $product->id]) ?>">商品价格</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product/update')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'opportunity-assign-department/list'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['opportunity-assign-department/list', 'product_id' => $product->id]) ?>">商机分配部门</a>
        </li>
    <?php endif; ?>
    <!--
        <?php if (Yii::$app->user->can('product-related/*') || Yii::$app->user->can('collocation/list')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'product-related/list'
        || Yii::$app->controller->action->uniqueId == 'collocation/list'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-related/list', 'id' => $product->id]) ?>">关联商品设置</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-introduce/*')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'product-introduce/list'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-introduce/list', 'product_id' => $product->id]) ?>">商品图片</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-introduce/*')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'product-introduce/pc-desc'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-introduce/pc-desc', 'product_id' => $product->id]) ?>">电脑端描述</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-introduce/*')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'product-introduce/mobile-desc'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-introduce/mobile-desc', 'product_id' => $product->id]) ?>">手机端描述</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-faq/*')): ?>
        <li<?php if (Yii::$app->controller->action->uniqueId == 'product-faq/list'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-faq/list', 'product_id' => $product->id]) ?>">常见问题</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product-introduce/*')): ?>
        <li<?php if (Yii::$app->controller->action->id == 'guarantee'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product-introduce/guarantee', 'product_id' => $product->id]) ?>">服务保障</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('product/seo')): ?>
        <li<?php if (Yii::$app->controller->action->id == 'seo'): ?> class="active"<?php endif; ?>>
            <a href="<?= \yii\helpers\Url::to(['product/seo', 'product_id' => $product->id]) ?>">SEO设置</a>
        </li>
    <?php endif; ?>
    -->

</ul>
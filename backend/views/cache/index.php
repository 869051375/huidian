<?php
/* @var $this yii\web\View */
$this->title = '缓存管理';
$this->params['breadcrumbs'] = [$this->title];
?>
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>缓存管理 </h5>
            </div>
            <div class="ibox-content">
                <p>
                    <a href="<?= \yii\helpers\Url::to(['flush-cache'])?>" class="btn btn-default"><i class="glyphicon glyphicon-flash"></i> 刷新数据缓存</a>
                </p>
                <p>
                    <a href="<?= \yii\helpers\Url::to(['clear-assets'])?>" class="btn btn-default"><i class="glyphicon glyphicon-trash"></i> 删除样式缓存</a>
                </p>
                <p>
                    <a href="<?= \yii\helpers\Url::to(['flush-crm-cache'])?>" class="btn btn-default"><i class="glyphicon glyphicon-flash"></i> 刷新后台数据缓存</a>
                </p>
            </div>
        </div>
    </div>
</div>


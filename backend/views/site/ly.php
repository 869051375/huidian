<?php
/* @var $this yii\web\View */

$this->title = '添加&编辑商品';

$this->params['breadcrumbs'] = [$this->title];

/** @var \yii\data\DataProviderInterface $provider */
/** @var \common\models\Administrator[] $models */
/*$models = $provider->getModels();
$pagination = $provider->getPagination();*/
?>
<div class="row">
    <div class="col-xs-12">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#" data-toggle="tab" class="">商品基本信息设置</a>
                </li>
                <li class="">
                    <a href="#" data-toggle="tab" class="">商品基本信息设置</a>
                </li>
                <li class="">
                    <a href="#" data-toggle="tab" class="">商品基本信息设置</a>
                </li>
                <li class="">
                    <a href="#" data-toggle="tab" class="">商品基本信息设置</a>
                </li>
                <li class="">
                    <a href="#" data-toggle="tab" class="">商品基本信息设置</a>
                </li>

            </ul>
            <div class="tab-content">
                <div class="panel-body" style="border-top: none">
                    <form class="form-horizontal">
                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>商品名称</label>
                                <div class="col-xs-5">
                                    <input class="form-control" type="text">
                                </div>
                                <div class="col-xs-3"><small> (在此输入提示文字)</small></div>
                            </div>
                        </div>
                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label">商品别名</label>
                                <div class="col-xs-5">
                                    <input class="form-control" type="text">
                                </div>
                                <div class="col-xs-3"><small> (在此输入提示文字)</small></div>
                            </div>
                        </div>
                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>所属分类</label>
                                <div class="col-xs-5">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <select class="form-control m-b" name="account">
                                                <option>一级分类</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-6">
                                            <select class="form-control m-b" name="account">
                                                <option>二级分类</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>商品名称</label>
                                <div class="col-xs-5">
                                    <textarea class="form-control"></textarea>
                                </div>
                                <div class="col-xs-3"><small> (在此输入提示文字)</small></div>
                            </div>
                        </div>

                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>商品类型</label>
                                <div class="col-xs-5">
                                    <select class="form-control m-b" name="account">
                                        <option>一级分类</option>
                                        <option>option 2</option>
                                        <option>option 3</option>
                                        <option>option 4</option>
                                    </select>
                                </div>
                                <div class="col-xs-3"><small> (在此输入提示文字)</small></div>
                            </div>
                        </div>


                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>行业分类</label>
                                <div class="col-xs-5">
                                    <div class="block">
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">全选</label>
                                    </div>
                                    <div class="block">
                                        <label class="checkbox-inline margin-left"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><input type="checkbox">关联商品流程</label>
                                <div class="col-xs-5">
                                    <select class="form-control m-b" name="account">
                                        <option>一级分类</option>
                                        <option>option 2</option>
                                        <option>option 3</option>
                                        <option>option 4</option>
                                    </select>
                                </div>
                                <div class="col-xs-3"><small> (在此输入提示文字)</small></div>
                            </div>
                        </div>

                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>关联商品流程</label>
                                <div class="col-xs-5">
                                    <label class="checkbox-inline"> <input value="option1" name="related-commodity" type="radio">&nbsp;是</label>
                                    <label class="checkbox-inline"> <input value="option1" name="related-commodity" type="radio" checked="checked">&nbsp;否</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>服务区域及价格</label>
                                <div class="col-xs-5">
                                    <div class="row">
                                        <div class="col-xs-5">
                                            <select class="form-control m-b" name="account">
                                                <option>省份</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-5">
                                            <select class="form-control m-b" name="account">
                                                <option>城市</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="block">
                                        <textarea class="form-control"></textarea>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="form-group margin-top20">
                            <div class="row">
                                <label class="col-xs-2 control-label"><span class="text-danger">*&nbsp;</span>关联地址</label>
                                <div class="col-xs-5">
                                    <div class="block">
                                        <label class="checkbox-inline margin-left"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                        <label class="checkbox-inline"> <input value="option1" type="checkbox">信息科技类</label>
                                    </div>
                                    <textarea class="form-control margin-top20"></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-xs-3">
                            <button class="btn pull-right main-bg">保存</button>
                        </div>
                        <div class="col-xs-4">
                            <button class="btn main-bg pull-left">保存并下一步</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
</div>

<?php \backend\assets\FlowsortAsset::register($this); ?>
<?php
    $this->registerJs("
       
       
    ")
?>

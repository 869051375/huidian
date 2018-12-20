<?php
/* @var $this yii\web\View */

use backend\widgets\LinkPager;
use common\models\CrmCustomerLog;
use yii\data\ActiveDataProvider;

?>
<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['customer' => $customer]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('nav-tabs', ['customer' => $customer]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="no-borders no-paddings" style="padding:0;">
                            <div style="overflow: hidden;">
                                <div class="border-bottom p-sm">
                                    <div class="font-bold "><i class="border-left-color m-r-sm"></i>后台录入基本信息</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">客户名称：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->name; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">性别：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->gender?'女':'男'; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">客户生日：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->birthday ? $customer->birthday : '--'; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md" >客户来源：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->getSourceName(); ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">来电电话：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->caller; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">联系地区：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->province_name.$customer->city_name.$customer->district_name; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-offset-1 col-md-3 p-md">备注描述：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->remark; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">手机号：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->phone; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">联系座机：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->tel; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">微信：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->wechat; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">邮箱：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->email; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">QQ：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->qq; ?></div>
                                        </div>
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">具体地址：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->street; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php if($customer->user): ?>
                            <div class="border-bottom">
                                <div class="ibox-title">
                                    <div class="font-bold "><i class="border-left-color m-r-sm"></i>客户前台基本信息</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row hr-line-bottom-dashed">
                                        <div class="col-md-offset-1 col-md-3 p-md">姓名/昵称：</div>
                                        <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->user->name; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-offset-1 col-md-3 p-md">常用邮箱：</div>
                                        <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->user->email; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row hr-line-bottom-dashed">
                                        <div class="col-md-offset-1 col-md-3 p-md">注册手机号：</div>
                                        <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->user->phone; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-offset-1 col-md-3 p-md">邮寄地址：</div>
                                        <div class="col-md-8 p-md" style="margin-left: -45px;"><?= $customer->user->address; ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                                <div class="border-bottom">
                                    <div class="ibox-title">
                                        <div class="font-bold "><i class="border-left-color m-r-sm"></i>系统信息</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">创建时间：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?=  date('Y-m-d H:i:s',$customer->created_at) ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-offset-1 col-md-3 p-md">最后修改时间：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;"><?= date('Y-m-d H:i:s',$customer->updated_at) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row hr-line-bottom-dashed">
                                            <div class="col-md-offset-1 col-md-3 p-md">注册时间：</div>
                                            <div class="col-md-8 p-md" style="margin-left: -45px;">
                                                <?php if($customer->user): ?>
                                                <?= $customer->user->created_at ? date('Y-m-d H:i:s',$customer->user->created_at) : '--'; ?>
                                                <?php else: ?>
                                                    --
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

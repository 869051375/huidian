<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;use common\models\Order;use common\utils\BC;use yii\helpers\Html;use yii\redis\Connection;

/* @var $user \common\models\Administrator */
$user = Yii::$app->user->identity;
$logoutUrl = Yii::$app->urlManager->createUrl(['/site/logout']);

AppAsset::register($this);
$actionUniqueId = Yii::$app->requestedAction->getUniqueId();
$controllerUniqueId = Yii::$app->requestedAction->controller->getUniqueId();
?><?php $this->beginPage() ?><!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?= Html::csrfMetaTags() ?>
    <title><?php if ($this->title): ?><?= Html::encode($this->title) ?> | <?php endif ?>掘金CRM管理后台</title>
    <?php $this->head() ?>
    <!--[if lt IE 9]>
    <script src="<?= Yii::$app->urlManager->baseUrl ?>/js/html5shiv.min.js"></script>
    <script src="<?= Yii::$app->urlManager->baseUrl ?>/js/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<?php $this->beginBody() ?>
<div id="wrapper">

    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <?php
            $backendNavItemsKey = 'backend-nav-'.Yii::$app->user->id;
            $navItems = Yii::$app->cache->get($backendNavItemsKey);
//            $navItems = false;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $system_id = $redis->get('system'.Yii::$app->user->id);
            if ($navItems === false && $user)
            {
//                $countNeedConfirm = \common\models\CrmOpportunity::countNeedConfirm($user);
//                $countSubNeedConfirm = \common\models\CrmOpportunity::countSubNeedConfirm($user);
//
//                $pendingPayCount = Order::getPendingPayCount($user);
//                $pendingAssignCount = Order::getPendingAssignCount($user);
//                $pendingServiceCount = Order::getPendingServiceCount($user);
//                $inServiceCount = Order::getInServiceCount($user);
//
//                $timeoutCount = Order::getTimeoutCount($user);
//                $receiveCount = Order::getAllReceiveCount();
//                $receiveRecordCount = BC::add(Order::getAllReceiveRecordCount(),Order::getReceiveRecordCount(),0);
//                $applyCount = Order::getApplyCount();
//
//                $pendingRenewalCount = Order::getPendingRenewalCount($user);
//
//                $countNeedConfirmCustomer = \common\models\CrmCustomer::countNeedConfirm($user);
//                $countSubNeedConfirmCustomer = \common\models\CrmCustomer::countSubNeedConfirm($user);

                //普通菜单
                $generalNav = [
                    'items' => [
                        '<li class="nav-header">
                    <div class="dropdown profile-element">
                        <span>
                        </span>
                        <p class="nav-header-text">惠点订餐平台</p>
                    </div>
                    <div class="logo-element">
                        JJ
                    </div>
                </li>',
                        [
                            'label' => '<i class="icon icon-home"></i><span class="nav-label">系统首页</span>',
                            'url' => ['/crm/index'],
                            'visible' => (Yii::$app->user->can('opportunity/*') || Yii::$app->user->can('customer/*') || Yii::$app->user->can('opportunity/all') || Yii::$app->user->can('customer/all'))
                        ],
                        [
                            'label' => '<i class="icon icon-clue"></i><span class="nav-label">商品管理</span>',
                            'items' => [
                                //新系统
                                [
                                    'label' => '跟进中线索', 'url' => ['crm-vue/#/mySaleCue'],
                                    'visible' => Yii::$app->user->can('clue/my_follow_up_list')
                                ],
                                [
                                    'label' => '已转化线索', 'url' => ['crm-vue/#/switchCue'],
                                    'visible' => Yii::$app->user->can('clue/my_transformed_list')
                                ],
                                [
                                    'label' => '线索公海', 'url' => ['crm-vue/#/cueSeas'],
                                    'visible' => Yii::$app->user->can('clue/clue_public_list')
                                ],
                                [
                                    'label' => '线索公海配置', 'url' => ['crm-vue/#/cueSeasSettingList'],
                                    'visible' => Yii::$app->user->can('clue/clue_public_set_up')
                                ],
                            ],
                        ],

                        [
                            'label' => '<i class="icon icon-file-o"></i><span class="nav-label">帮助文档</span>',
                            'url' => ['/document-category/list'],
                            'visible' => Yii::$app->user->can('document-category/list'),
                        ],
                        [
                            'label' => '<i class="fa fa-cogs"></i><span class="nav-label">服务管理</span>',
                            'url' => ['/clerk-service/list'],
                            'visible' => $user->type == \common\models\Administrator::TYPE_CLERK,
                        ],
                        [
                            'label' => '<i class="icon icon-setup"></i><span class="nav-label">系统设置</span>',
                            'url' => ['/administrator/system'],
                            'visible' => Yii::$app->user->can('administrator/system'),
                        ],
                    ],
                    'options' => ['class' => 'metismenu', 'id' => 'side-menu'],
                    'encodeLabels' => false,
                    'childOptions' => ['class' => 'nav nav-second-level collapse'],
                    'activateParents' => true,
                ];

                //系统菜单
                $systemNav = [
                    'items' => [
                        '<li class="nav-header">
                    <div class="dropdown profile-element">
                        <span>
                        </span>
                        <p class="nav-header-text">惠点订餐平台</p>
                    </div>
                    <div class="logo-element">
                        JJ
                    </div>
                </li>',
                        [
                            'label' => '<i class="icon icon-key"></i><span class="nav-label">组织与权限</span>',
                            'items' => [
                                [
                                    'label' => '组织机构', 'url' => ['/company/all'],
                                    'visible' => Yii::$app->user->can('company/all')
//                                    'visible' => Yii::$app->user->can('department/list')
                                ],
//                                [
//                                    'label' => '部门管理', 'url' => ['/crm-department/list'],
//                                    'visible' => Yii::$app->user->can('department/list')
//                                ],
                                [
                                    'label' => '角色管理', 'url' => ['/role/list'],
                                    'visible' => Yii::$app->user->can('role/list')
                                ],
                                [
                                    'label' => '管理员设置', 'url' => ['/administrator/list-manager'],
                                    'visible' => Yii::$app->user->can('administrator/list-manager')
                                ],

                                [
                                    'label' => '服务人员管理', 'url' => ['/administrator/list-clerk'],
                                    'visible' => Yii::$app->user->can('administrator/list-clerk')
                                ],

                            ],
                        ],
                        [
                            'label' => '<i class="icon icon-cog"></i><span class="nav-label">业务参数设定</span>',
                            'items' => [
////                                [
////                                    'label' => '商机公海设置', 'url' => ['/opportunity-public/setting'],
////                                    'visible' => Yii::$app->user->can('opportunity-public/setting')
////                                ],
////                                [
////                                    'label' => '客户公海设置', 'url' => ['/customer-public/setting'],
////                                    'visible' => Yii::$app->user->can('customer-public/setting')
////                                ],
//                                [
//                                    'label' => '来源管理', 'url' => ['/customer-source/list'],
//                                    'visible' => Yii::$app->user->can('customer-source/list')
//                                ],
//                                [
//                                    'label' => '来源渠道管理', 'url' => ['/admin-channel/list'],
//                                    'visible' => Yii::$app->user->can('customer-source/list')
//                                ],
//                                [
//                                    'label' => '合同类型管理', 'url' => ['/contract/type'],
//                                    'visible' => Yii::$app->user->can('contract/type-list')
//                                ],
//                                [
//                                    'label' => '行业类型管理', 'url' => ['/industry/list'],
//                                    'visible' => Yii::$app->user->can('industry/list'),
//                                ],
//                                [
//                                    'label' => '服务地区管理', 'url' => ['region/list'],
//                                    'visible' => Yii::$app->user->can('region/list'),
//                                ],
//                                [
//                                    'label' => '商品流程管理', 'url' => ['flow/list'],
//                                    'visible' => Yii::$app->user->can('flow/list'),
//                                ],
//                                [
//                                    'label' => '商品分类管理', 'url' => ['product-category/list'],
//                                    'visible' => Yii::$app->user->can('product-category/list'),
//                                ],
//                                [
//                                    'label' => '提成方案管理', 'url' => ['/reward-proportion/list'],
//                                    'visible' => Yii::$app->user->can('reward-proportion/list')
//                                ],
//                                [
//                                    'label' => '年度工作日管理', 'url' => ['/holidays/list'],
//                                    'visible' => Yii::$app->user->can('holidays/list')
//                                ],
                                [
                                    'label' => '缓存管理', 'url' => ['/cache/index'],
                                    'visible' => Yii::$app->user->can('cache/flush')
                                ],
                            ],
                        ],
//
                        [
                            'label' => '<i class="icon icon-book"></i><span class="nav-label">日志管理</span>',
                            'items' => [
                                [
                                    'label' => '操作日志', 'url' => ['/administrator-log/record'],
                                    'visible' => Yii::$app->user->can('administrator-log/record')
                                ],
                                [
                                    'label' => '风险操作告警', 'url' => ['/administrator-log/warning'],
                                    'visible' => Yii::$app->user->can('administrator-log/warning')
                                ],
                            ],
                        ],
                        [
                            'label' => '<i class="icon icon-home"></i><span class="nav-label">系统首页</span>',
                            'url' => ['/administrator/system'],
                            'visible' => Yii::$app->user->can('administrator/system'),
                        ],
                    ],
                    'options' => ['class' => 'metismenu', 'id' => 'side-menu'],
                    'encodeLabels' => false,
                    'childOptions' => ['class' => 'nav nav-second-level collapse'],
                    'activateParents' => true,
                ];

                $navItems = $system_id ? $systemNav : $generalNav;
                Yii::$app->cache->set($backendNavItemsKey, $navItems);
            }
            ?>
            <?= \backend\widgets\Nav::widget($navItems); ?>
        </div>
    </nav>

    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i>
                    </a>
                    <form role="search" class="navbar-form-custom" action="search_results.html">
                        <div class="form-group">
                            <input type="text" placeholder="Search for something..." class="form-control"
                                   name="top-search" id="top-search">
                        </div>
                    </form>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li class="left-list">
                        <span class="remind-all"><span class="remind"></span><a href="<?= \yii\helpers\Url::to(['message-remind/list']) ?>">查看全部&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></span>
                    </li>
                    <li>
                        <span class="m-r-sm text-muted welcome-message">惠点订餐平台</span>
                    </li>
                    <?php /*
                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-envelope"></i> <span class="label label-warning">16</span>
                        </a>
                        <ul class="dropdown-menu dropdown-messages">
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="profile.html" class="pull-left">
                                        <img alt="image" class="img-circle" src="/img/a7.jpg">
                                    </a>
                                    <div class="media-body">
                                        <small class="pull-right">46h ago</small>
                                        <strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>.
                                        <br>
                                        <small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="profile.html" class="pull-left">
                                        <img alt="image" class="img-circle" src="/img/a4.jpg">
                                    </a>
                                    <div class="media-body ">
                                        <small class="pull-right text-navy">5h ago</small>
                                        <strong>Chris Johnatan Overtunk</strong> started following <strong>Monica
                                            Smith</strong>. <br>
                                        <small class="text-muted">Yesterday 1:21 pm - 11.06.2014</small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="profile.html" class="pull-left">
                                        <img alt="image" class="img-circle" src="/img/profile.jpg">
                                    </a>
                                    <div class="media-body ">
                                        <small class="pull-right">23h ago</small>
                                        <strong>Monica Smith</strong> love <strong>Kim Smith</strong>. <br>
                                        <small class="text-muted">2 days ago at 2:30 am - 11.06.2014</small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <div class="text-center link-block">
                                    <a href="mailbox.html">
                                        <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-bell"></i> <span class="label label-primary">8</span>
                        </a>
                        <ul class="dropdown-menu dropdown-alerts">
                            <li>
                                <a href="mailbox.html">
                                    <div>
                                        <i class="fa fa-envelope fa-fw"></i> You have 16 messages
                                        <span class="pull-right text-muted small">4 minutes ago</span>
                                    </div>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="profile.html">
                                    <div>
                                        <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                        <span class="pull-right text-muted small">12 minutes ago</span>
                                    </div>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="grid_options.html">
                                    <div>
                                        <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                        <span class="pull-right text-muted small">4 minutes ago</span>
                                    </div>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <div class="text-center link-block">
                                    <a href="notifications.html">
                                        <strong>See All Alerts</strong>
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    */ ?>
                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-user"></i> <?= $user->name ?> <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu m-t-xs">
                            <li><a href="<?= \yii\helpers\Url::to(['profile/info']) ?>"><i class="fa fa-info"></i>
                                    个人设置</a></li>
                            <li class="divider"></li>
                            <li><a href="<?= \yii\helpers\Url::to(['profile/password']) ?>"><i class="fa fa-lock"></i>
                                    修改密码</a></li>
                            <li class="divider"></li>
                            <li><a href="<?= \yii\helpers\Url::to(['message-remind/list']); ?>"><i class="fa fa-bell"></i>消息提醒<span class="text-danger no_read"></span></a></li>
                            <?php if(Yii::$app->session->get('rootGoBack') == 'root'):?>
                                <li class="divider"></li>
                                <li><a href="<?= \yii\helpers\Url::to(['administrator/force-login', 'id' => 1]) ?>"><i class="fa fa-lock"></i>
                                        Go Home</a></li>
                            <?php endif; ?>
                            <li class="divider"></li>
                            <li><a href="<?= $logoutUrl ?>"><i class="fa fa-sign-out"></i> 退出</a></li>
                        </ul>
                    </li>
                </ul>

            </nav>
        </div>

        <?php /* 面包屑区域 */ ?>
        <?php if (isset($this->params['breadcrumbs'])): ?>
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-9">
                    <h2><?= $this->title ?></h2>
                    <?=
                    \yii\widgets\Breadcrumbs::widget([
                        'homeLink' => ['label' => '后台首页', 'url' => Yii::$app->homeUrl],
                        'tag' => 'ol',
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
                    ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php /* 成功/警告/错误消息区域 */ ?>
        <?= \common\widgets\Alert::widget(['options' => ['class' => 'alert-dismissible fade in m-b-sm m-t-md']]) ?>

        <?php /* 页面主内容区域 */ ?>
        <div class="wrapper wrapper-content animated fadeIn">
            <?= $content ?>
        </div>

        <?php /* footer区域 */ ?>
        <div class="footer">
            <?php /* ?>
            <div class="pull-right">
                10GB of <strong>250GB</strong> Free.
            </div><?php */ ?>
            <div>
                惠点订餐平台 &copy; 2004 - <?= date('Y') ?>
            </div>
        </div>

    </div>
</div>
<div id="myxxDIV"></div>
<?php
$checkUrl = \yii\helpers\Url::to(['message-remind/check']);
$toUrl = \yii\helpers\Url::to(['message-remind/list']);
$showUrl = \yii\helpers\Url::to(['message-remind/show']);
$this->registerJs(<<<JS
setTimeout(function(){
    $('.alert-dismissible.alert-success').alert('close');
}, 3000);

    //消息提醒
    var remind = 1;
    var play = 0;
    var noRead = 0;
    var remindText = $(".remind");
    var noReadText = $(".no_read");
    var remindAll = $(".remind-all");
    var noReadNum = 0;
    var url = '{$toUrl}';

   //alert(noReadNum);
   if(parseInt(noReadNum)){
     remindText.html('<i class="fa fa-bell" style="color:#ed5565;padding-right:4px;"></i>'+'<span class="text-danger">'+'您有'+noReadNum+'条未读消息'+'</span>');
   }else if(sessionStorage.num){
    remindText.html('<i class="fa fa-bell" style="padding-right:4px;color:#18a689;"></i>'+'<span class="">'+'共计'+ sessionStorage.num +'条消息'+'</span>');
   }
    
    $.post('{$checkUrl}', function(rs){
        if(rs.status === 200)
        {
           sessionStorage.num = rs.data.total_count;
           noReadNum =  rs.data.no_read_count;
           noReadText.html('(未读'+ rs.data.no_read_count +')');
            if(parseInt(noReadNum)){
                 remindText.html('<i class="fa fa-bell" style="color:#ed5565;padding-right:4px;"></i>'+'<span class="text-danger">'+'您有'+noReadNum+'条未读消息'+'</span>');
               }else if(sessionStorage.num){
                remindText.html('<i class="fa fa-bell" style="padding-right:4px;color:#18a689;"></i>'+'<span class="">'+'共计'+ sessionStorage.num +'条消息'+'</span>');
               }
           //remindText.html('消息提醒('+'<span class="text-danger">'+'共计'+ rs.data.total_count +'条提醒，其中未读'+ rs.data.no_read_count +'条'+'</span>'+')');
           remind = rs.data.total_count;
           play = rs.data.total_count;
           remind <= 0 ? remindAll.hide() : remindAll.show();
           remind <= 0 ? noReadText.hide() : noReadText.show();
           if(rs.data.popup.length > 0)
           {
               mesgNotice(url, rs.data.popup);
           }
        }
    }, 'json');
    
     setInterval(function () {
         $.post('{$checkUrl}', function(rs){
            if(rs.status === 200)
            {
                remind = rs.data.total_count;
                noRead = rs.data.no_read_count;
                sessionStorage.num = rs.data.total_count;

           noReadNum =  rs.data.no_read_count;
                if(play == noRead)
                {
                    remind <= 0 ? remindAll.hide() : remindAll.show();
                    remind <= 0 ? noReadText.hide() : noReadText.show();
                }
                else
                {
                    noReadText.html('(未读'+ noRead +')');
                    remindAll.show();
                    if(parseInt(noRead)){
                            remindText.show().html('<i class="fa fa-bell" style="color:#ed5565;padding-right:4px;"></i>'+'<span class="text-danger">'+'您有'+noRead+'条未读消息'+'</span>');
                        }else if(sessionStorage.num){
                            remindText.show().html('<i class="fa fa-bell" style="padding-right:4px;color:#18a689;"></i>'+'<span class="">'+'共计'+ remind +'条消息'+'</span>');
                     }
                    play = noRead;
                }
                if(rs.data.popup.length > 0)
                {
                    mesgNotice(url, rs.data.popup);
                }
            }
        }, 'json');
     },30000);//30秒查询一次

   function mesgNotice(url, data){
       if(window.Notification){
           for(var i=0; i< data.length; i++)
           {
               var popNotice = function() {
                   if (Notification.permission == "granted") {
                       var notification = new Notification("Hi，您有新消息：", {
                           body: data[i].popup_message,
                           icon: '',
                       });

                       isShow(data[i].popup_id);
                       //onshow函数在消息框显示时会被调用  
                       notification.onshow = function() {  

                       }; 
                      
                       //消息框被点击时被调用  
                       notification.onclick = function() {
                           window.location.href = url;
                           notification.close();
                       };
                      
                       //一个消息框关闭时onclose函数会被调用  
                       notification.onclose = function() { 
                           
                       }; 
                   }
               };

               if(Notification.permission == "granted"){
                   popNotice();
               }else if(Notification.permission != "denied"){
                   //最新语法
                   Notification.requestPermission().then(function(permission) {
                       popNotice();
                   });
               }
           }
       }
   }

     function isShow(id) {
       $.get('{$showUrl}', {id:id}, function(rs){
           
       }, 'json');
     }
JS
)?>
<?php $this->endBody() ?>
</body>

</html><?php $this->endPage() ?>
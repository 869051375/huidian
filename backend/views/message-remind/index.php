<?php

/* @var $this yii\web\View */

$this->title = '首页';
////\backend\assets\FlotAsset::register($this);
?>
<div class="site-index">

    <div class="jumbotron">
        <h1 class="text-center">轮询测试！</h1>
        <p class="lead"></p>

    </div>
    <div class="remind text-danger">

    </div>
    <button id="button">有人想加你为好友</button>
    <p id="text"></p>
</div>

<?php
$pollingUrl = \yii\helpers\Url::to(['ajax-check']);
$checkUrl = \yii\helpers\Url::to(['check']);
$this->registerJs(<<<JS
        var remind = 1;
        var play = 0;
        var remindText = $(".remind");
        if(sessionStorage.num){
            remindText.text(sessionStorage.num);
        }

        $.post('{$checkUrl}', function(rs){
            if(rs.status === 200)
            {
                sessionStorage.num = rs.data;
                remindText.text(rs.data);
                remind = rs.data;
                play = rs.data;
                remind <= 0 ? remindText.hide() : remindText.show();
            }
        }, 'json');

        setInterval(function () {
            $.post('{$checkUrl}', function(rs){
                    if(rs.status === 200)
                    {
                        remind = rs.data;
                        sessionStorage.num = rs.data;
                        if(play == remind)
                        {
                            remind <= 0 ? remindText.hide() : remindText.show();
                        }
                        else
                        {
                            remindText.show().text(remind);
                            play = remind;
                        }
                    }
                }, 'json');
        },60000);//60秒查询一次


// if (window.Notification) {
//     var button = document.getElementById('button'), text = document.getElementById('text');
//
//     var popNotice = function() {
//         if (Notification.permission == "granted") {
//             var notification = new Notification("Hi，帅哥：", {
//                 body: '可以加你为好友吗？',
//                 icon: 'http://image.zhangxinxu.com/image/study/s/s128/mm1.jpg'
//             });
//
//             notification.onclick = function() {
//                 text.innerHTML = '张小姐已于' + new Date().toTimeString().split(' ')[0] + '加你为好友！';
//                 notification.close();
//             };
//         }
//     };
//
//     button.onclick = function() {
//         if (Notification.permission == "granted") {
//             popNotice();
//         } else if (Notification.permission != "denied") {
//             Notification.requestPermission(function (permission) {
//               popNotice();
//             });
//         }
//     };
// } else {
//     alert('浏览器不支持Notification');
// }


JS
);?>

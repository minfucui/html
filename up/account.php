<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title>Account</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/jobs.css">

    <style type="text/css">
        input:disabled,
        select:disabled {
            background-color: #535353!important;
        }
        
        .buttoncreate {
            padding: 10px 25px 10px 25px;
            border-radius: 4px;
            width: 120px !important;
            margin-left: 30px;
        }
        
        table {
            font-size: 15px;
        }
        
        tr {
            text-align: left
        }
        
        .filename {
            width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .folder {
            text-decoration: underline;
            color: darkblue;
            cursor: pointer;
        }
    </style>
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script type="text/javascript" src="./component/layer-v3.0.3/layer/layer.js"></script>
</head>

<body>
    <h3>账号信息</h3>

    <form action="" method="post" class="bootstrap-frm" id="acct-form">
        <label> 
            <span >用户名:</span>
            <input type="text" id="username" readonly> 
        </label>
        <!-- <label> -->
        <label>
            <span >姓名:</span>
            <input type="text" id="name" >
        </label>
        <label>
            <span >电话:</span>
            <input type="text" id="tel" >
        </label>
        <label>
            <span >邮箱:</span>
            <input type="text" id="email" >
        </label>
        <input type="button" onclick="changeInfo()", class="restart buttoncreate"
               id="change" value="修改">
    </form>
    <h3>修改密码</h3>
    <form action="" method="post" class="bootstrap-frm" id="acct-passwd">
        <label>
            <span >当前密码:</span>
            <input type="password" id="oldpasswd">
        </label>
        <label>
            密码必须至少8个字符，含有大小写字母、数字或特殊字符!@#%^_+=~<>,./:
        </label>
        <label>
            <span >新密码:</span>
            <input type="password" id="newpasswd">
        </label>
        <label>
            <span >验证新密码:</span>
            <input type="password" id="newpasswd1">
        </label>
        <p> </p>
        <input type="button" onclick="chPasswd()", class="restart buttoncreate"
               id="ch_pw" value="修改密码">
    </form>

    <div id="budget"></div>
    <div id="support"></div>

    <script type="text/javascript">

    $('#change').addClass('disable-button');
    $('#change').prop('disabled', true);
    $('#ch_pw').addClass('disable-button');
    $('#ch_pw').prop('disabled', true);

    $('#acct-form').bind('input propertychange', function() {
        $('#change').removeClass('disable-button');
        $('#change').prop('disabled', false);
    });

    $('#acct-passwd').bind('input propertychange', function() {
        if ($('#oldpasswd').val() != '' &&
            $('#newpasswd').val() != '' &&
            $('#newpasswd1').val() != '') {
            $('#ch_pw').removeClass('disable-button');
            $('#ch_pw').prop('disabled', false);
        } else {
            $('#ch_pw').addClass('disable-button');
            $('#ch_pw').prop('disabled', true);
        }
    });

    $.ajax({
        type: 'post',
        url: 'php/account.php',  // 获取账户信息接口
        async: false,
        data: JSON.stringify({action:'info'}),
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
               $('#username').val(d.data.username);
               if (d.data.op_control == 1) {
                   withOpControl(d.data);  // 判断是否有充值权限，有的话显示充值框
               } else {
                   $('#name').prop('disabled', true);
                   $('#tel').prop('disabled', true);
                   $('#email').prop('disabled', true);
               }
            } else {
               $('#name').prop('disabled', true);
               $('#tel').prop('disabled', true);
               $('#email').prop('disabled', true);
            }
        },
        error: function(e) {
            Win10_child.close()
            Win10_child.toLogin(e)
        }
    });

    function withOpControl(d) {
        $('#name').val(d.name);
        $('#tel').val(d.phone);
        $('#email').val(d.email);
        form = '<h3>余额与充值申请</h3><form class="bootstrap-frm" id="charge-form">' +
               '<label><span>余额:</span><input type="text" id="balance" readonly '+
               'value="' + d.balance + '"></label>' +
               '<label><span>充值申请金额:</span><input type="text" id="pay"></label>' +
               '<input type="button" onclick="chargeReq()", class="restart buttoncreate" ' +
               'id="charge_req" value="充值申请"></form>';
        $('#budget').html(form);
        $('#balance').val(d.balance);

        form1 = '<h3>平台技术支持</h3><form class="bootstrap-frm" id="support-form">' +
               '<label><span>请求支持内容:</span><textarea style="height:60px;" type="text" maxlength="200" id="reqtext"></textarea></label>' +
               '<label><span></span><input type="hidden" id="hidden"></label>' +
               '<input type="button" onclick="supportReq()", class="restart buttoncreate" ' +
               'id="support_req" value="提交请求"></form>';
        $('#support').html(form1);
    }

    function popError(m) {
        Win10_child.childLayer(m);  // 弹出提示框
    }

    function changeInfo() {
        var d = {username: $('#username').val(),
                 name: $('#name').val(),
                 phone: $('#tel').val(),
                 email: $('#email').val()};
        if (d.name.length > 20) {
            popError('名字太长');
            return;
        }
        if (d.phone.length > 20) {
            popError('电话号码太长');
            return;
        }
        if (!d.email.includes('@') || !d.email.includes('.')) {
            popError('邮箱地址不正确');
            return;
        }
        $.ajax({
            type: 'post',
            url: 'php/account.php',  // 账户信息管理接口，同上
            async: false,
            data: JSON.stringify({action:'change', data: d}),  // 注明执行动作
            dataType: "json",
            success: function(r) {
                if (r.code == 0) {
                    Win10_child.childLayer('修改成功');
                    $('#change').addClass('disable-button');
                    $('#change').prop('disabled', true);
                } else
                    Win10_child.childLayer(r.message);
            },
            error: function(e){
                Win10_child.childLayer('backend error');
            }
        });
    }

    function chargeReq() {  // 充值申请
        var d = {username: $('#username').val(),
                 pay: parseFloat($('#pay').val())};
        if (isNaN(d.pay) || d.pay < 100) {
            popError('最小充值为100');
            return;
        }
        $.ajax({
            type: 'post',
            url: 'php/account.php',
            async: false,
            data: JSON.stringify({action:'charge', data: d}),  // 充值
            dataType: "json",
            success: function(r) {
                if (r.code == 0) {
                    Win10_child.childLayer('申请成功，请等候线下服务');  // 需要系统管理员后续修改
                } else
                    Win10_child.childLayer(r.message);
            },
            error: function(e){
                Win10_child.childLayer('backend error');
            }
        });
    }

    function supportReq() {  // 技术支持申请
        var d = {creator: $('#username').val(),
                 task: $('#reqtext').val()};
        if (!d.task) {
            popError('请求内容为空！');
            return;
        }
        $.ajax({
            type: 'post',
            url: 'php/itSupport.php',
            async: false,
            data: JSON.stringify({action:'support', data: d}),
            dataType: "json",
            success: function(r) {
                if (r.code == 0) {
                    Win10_child.childLayer('请求成功，请等候线下联系');
                    $('#reqtext').val('');
                } else
                    Win10_child.childLayer(r.message);
            },
            error: function(e){
                Win10_child.childLayer('backend error');
            }
        });
    }

    function chPasswd() {  // 修改密码
        var d = {username: $('#username').val(),
                 pw: encodeURI($('#oldpasswd').val()),
                 npw1: encodeURI($('#newpasswd').val()),
                 npw2: encodeURI($('#newpasswd1').val())};
        var regex = new RegExp('^(?![a-zA-Z]+$)(?![A-Z0-9]+$)(?![A-Z\W_!@#$%^&*`~()-+=]+$)(?![a-z0-9]+$)(?![a-z\W_!@#$%^&*`~()-+=]+$)(?![0-9\W_!@#$%^&*`~()-+=]+$)[a-zA-Z0-9\W_!@#$%^&*`~()-+=]{8,30}$');
        if (d.npw1.length < 8 ) {
            console.log(d.npw1)
            Win10_child.childLayer('新密码不符要求');
            return;
        }
        if(!regex.test(d.npw1)){
            Win10_child.childLayer('新密码不符要求');
            return;
        }
        if (d.npw1 != d.npw2) {
            Win10_child.childLayer('两个新密码不同');
            return;
        }
        $.ajax({
            type: 'post',
            url: 'php/chPasswd.php',  // 修改密码接口
            async: false,
            data: JSON.stringify({action:'change', data: d}),
            dataType: "json",
            success: function(r) {
                if (r.code == 0) {
                    modalopen();
                } else
                    layer.confirm(r.message, {icon: 3, title:'错误', btn:false});
            },
            error: function(e){
                Win10_child.childLayer('backend error');
            }
        });
    }

    function modalopen() {
        layer.confirm('密码修改成功。请重新登录', {icon: 3, title:'退出', closeBtn:false}, logout, logout);
    }
    function logout()
    {
        Win10_child.toLogin(true);
    }
    </script>
</body>

</html>

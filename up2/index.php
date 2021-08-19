<?php
$title = 'SkyForm工业仿真云';
$login_logo = '';
$customize = FALSE;
if (file_exists('config.yaml') &&
    ($config = yaml_parse_file('config.yaml')) !== FALSE &&
    isset($config['title'])) {
    $title = $config['title'];
    $login_logo = $config['login_logo'];
    $customize = TRUE;
}
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <title><?php echo $title;?>登录</title>
    <link rel='Shortcut Icon' type='image/x-icon' href='./img/skyform.ico'>
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <link rel="stylesheet" href="css/fonts.css">
    <style>
        #win10-login {
            background: url('./img/wallpapers/login.png') no-repeat fixed center left;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            position: fixed;
            background-color: #d2d6de;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            z-index: -1;
        }
        
        #win10-login-box {
            width: 500px;
            overflow: hidden;
            margin: 0 auto;
        }
        
        .win10-login-box-square {
            width: 105px;
            margin: 0 auto;
            border-radius: 50%;
            background-color: darkgray;
            position: relative;
            overflow: hidden;
        }
        
        .win10-login-box-square::after {
            content: "";
            display: block;
            padding-bottom: 100%;
        }
        
        .win10-login-box-square .content {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        
        input {
            width: 90%;
            display: block;
            border: 0;
            margin: 0 auto;
            line-height: 36px;
            font-size: 20px;
            padding: 0 1em;
            border-radius: 5px;
            margin-bottom: 11px;
        }
        
        .login-username,
        .login-password {
            width: 91%;
            font-size: 13px;
            color: #999;
        }
        
        .login-password {
            width: calc(91% - 54px);
            -webkit-border-radius: 2px 0 0 2px;
            -moz-border-radius: 2px 0 0 2px;
            border-radius: 5px 0 0 5px;
            margin: 0px;
            float: left;
        }
        
        .login-submit {
            margin: 0px;
            float: left;
            -webkit-border-radius: 0 5px 5px 0;
            -moz-border-radius: 0 5px 5px 0;
            border-radius: 0 5px 5px 0;
            background-color: #009688;
            width: 54px;
            display: inline-block;
            height: 36px;
            line-height: 36px;
            padding: 0 auto;
            color: #fff;
            white-space: nowrap;
            text-align: center;
            font-size: 14px;
            border: none;
            cursor: pointer;
            opacity: .9;
            filter: alpha(opacity=90);
        }
        
        .errormsg {
            font-size: 16px;
            color: red;
            text-align: left;
            margin: 0;
            padding-top: 44px;
        }

        .bottom-logo {
            font-size: 3;
            color: #707070;
            position: absolute;
            top: 85%;
            right: 20px;
            /* transform: translate(-50%, -50%); */
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div id="win10-login">
        <div style="height: 10%;min-height: 120px"></div>
        <div id="win10-login-box">
            <p style="font-size: 24px;color: #0075b3;text-align: center"><b>
            <?php
                echo ($customize ? '<img src="'.$login_logo.'" width="500">' : $title);
            ?>
            </b></p>
            <div style="width: 300px;margin:auto">
            <p style="font-size: 24px;text-align: center">用户登录</p>
            <form target="_self" method="get" action="#">
                <!--用户名-->
                <input id="username" type="text" placeholder="请输入登录名" class="login-username">
                <!--密码-->
                <input id="password" type="password" placeholder="请输入密码" class="login-password">
                <!--登陆按钮-->
                <input onclick="login()" type="button" value="登录" id="btn-login" class="login-submit" />
            </form>
            </div>
            <div id="errmsg"></div>
        </div>
    </div>
    <div class="bottom-logo">
       <p><img src="img/nsccwx.png" width="200">提供算力支撑<img src="/imgs/logo.png" width="150">
       <?php if ($customize) echo '提供平台支撑';?>
       </center></p>
    </div>

    <script type="text/javascript">
        var input = document.getElementById("password");
        input.addEventListener("keyup", function(event) {
            if (event.keyCode == 13) { /* press enter to login */
                event.preventDefault();
                document.getElementById("btn-login").click();
            }
        });

        function login() {
            console.log("data");
            var valuser = $(".login-username").val();
            var valpwd = $(".login-password").val();
            $.ajax({
                type: "post",
                url: "php/login.php?username=" + valuser + "&password=" + valpwd,
                contentType: "application/json",
                dataType: "html",
                success: function(data) {
                    var d = JSON.parse(data)
                    console.log(d);
                    if (d.code == 0) {
                        localStorage.setItem("userinfo", JSON.stringify(d.data));
                        localStorage.setItem("username", JSON.stringify(valuser));
                        window.location.href = "./main.php";
                    } else {
                        if (d.code == 401) {
                            $('#errmsg').html('<p class="errormsg">*用户名或密码错误,请重试</p>')
                        } else {
                            $('#errmsg').html('<p class="errormsg">*' + d.message + '</p>')
                        }
                    }
                },
            });

        }
    </script>

</body>

</html>

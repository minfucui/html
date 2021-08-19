<?php
include 'header.php';  // 头部，判断是否有登录信息
include 'php/config.php';  // 用来读取session，配置用户的基本信息，记住所有业务都是在用户的身份上完成的，所以这两个php一般必加
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title><?php echo $_SESSION['title'];?></title>
    <link rel='Shortcut Icon' type='image/x-icon' href='./img/skyform.ico'>
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>

    <link href="./css/animate.css" rel="stylesheet">
    <script type="text/javascript" src="./component/layer-v3.0.3/layer/layer.js"></script>
    <link rel="stylesheet" href="./component/font-awesome-4.7.0/css/font-awesome.min.css">
    <link href="./css/default.css" rel="stylesheet">
    <link href="./css/shortcut-drawer.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fonts.css">
    <script type="text/javascript" src="./js/desktop.js?filever=<?=filesize('js/desktop.js')?>"></script>  <!--主桌面js-->
    <script type="text/javascript" src="./js/disable_shortcuts_hidden.js"></script>  <!--不允许隐藏桌面图标-->
    <script type="text/javascript" src="./js/shortcut-drawer.js?filever=<?=filesize('js/shortcut-drawer.js')?>"></script>  <!--定义了桌面图标的打开方式以及右键功能等-->
    <script src="./js/vnc.js"></script>  <!--vnc远程控制模块，主要用于返回作业gui界面的url地址-->
    <script type="text/javascript" src="./js/child.js"></script>  <!--子页面-->
    <link href="./css/addtomain.css" rel="stylesheet">
    <style>
        /*磁贴自定义样式*/
        
        .win10-block-content-text {
            line-height: 44px;
            text-align: center;
            font-size: 16px;
        }
        .watermark {
            opacity: 0.5;
            color: #5e9e90;
            position: absolute;
            font-size: 30px;
            bottom: 10%;
            right: 5%;
        }
    </style>
    <script>
        Win10.onReady(function() {

            //设置壁纸
            Win10.setBgUrl({
                main: './img/wallpapers/main.jpg',
                mobile: './img/wallpapers/main.jpg',
            });

            Win10.setAnimated([
                'animated flip',
                'animated bounceIn',
            ], 0.01);
        });
    </script>
</head>

<body>
    <!-- 新增 -->
    <div id="win10">
    <!-- <button id="modalBtn" class="button">Click Here</button> -->

  <div id="simpleModal" class="modal_agree">  <!--同意许可-->
    <div class="modal-content">
      <div class="modal-header">
        
        <h2>免责声明</h2>
      </div>
      <div class="modal-body">
      <p align="justify">&nbsp&nbsp平台所提供各商业软件(Fluent、Abaqus、HypeMesh等)仅供用户测试平台功能使用，不得用于任何研究、生产和商业用途。因用户违规使用而产生的任何法律纠纷，由用户自行承担责任。</p>
              <p align="justify">&nbsp&nbsp&nbsp若需要利用商业软件完成仿真业务，请联系客服，签订委托代算协议。</p>
      </div>
      <div class="modal-footer">
        <h3><span class="closeBtn" >我已认真阅读免责声明</span></h3>
      </div>
    </div>
     </div>
    <div id="myModal" class="modal">
        <!-- 弹窗内容 -->
            <div class="modal1">
           
                  <p>尊敬的用户：<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您的以下作业运行时间已超过10h，请注意查看，以免产生额外费用!</p>
             </div>
            <div class="modal2" >
              <table>
              <tr >
                  <th style="width:25%;">ID</th>
                  <th style="width:25%;">名称</th>
                  <th style="width:25%;">开始时间</th>
                  <th style="width:25%;">持续运行时间</th>
              </tr>
              <tbody id = 'jobs'></tbody>
              </table>
            </div>
            <div class="modal3">
              <button class="ok" onclick="ok()" >关闭</button>
            </div>
  </div>
        <!-- <div class="Floating" id="mydiv" onmousemove="myshow()" onmouseout="myhide()">

        <div class="leftpic">
        <img src="./img/slack.png"  alt="slack" />
        </div>
        <div class="righttext">
            <a href="http://www.jishulink.com/" target="_blank">技术邻论坛</a>
        </div>
        </div> -->
    <div class="side-bar">
        <a href="http://www.jishulink.com/qa/18650" target="_blank" class="icon-jishuling"><img class="imgcode" src="./img/slack.png" alt="技术邻"><div class="textcode">技术论坛</div></a>
        <a href="#" class="icon-chat"><img class="imgcode" src="./img/contact.png" alt="客服微信号"><div class="textcode">微信客服</div><div class="chat-tips"><i></i>
        <img style="width:138px;height:138px;" src="./img/simforgecode.png" alt="客服微信号"></div></a>
        <a href="#" class="icon-chat"><img class="imgcode" src="./img/wechat.png" alt="微信订阅号"><div class="textcode">公众号</div><div class="chat-tips"><i></i>
            <img style="width:138px;height:138px;" src="./img/gongzhonghao.png" alt="微信订阅号"></div></a>
    </div>

        <div class="desktop">
            <div id="win10-shortcuts" class="shortcuts-hidden">  <!--桌面图标-->
            </div>
            <div id="win10-desktop-scene"></div>
        </div>
        <div class="watermark">
           <?php echo isset($config['watermark']) ? '<img src="'.$config['watermark'].'">' : $_SESSION['title'];?>
        </div>
        <div id="win10-menu" class="hidden">  <!--开始菜单-->
            <div class="list win10-menu-hidden animated animated-slideOutLeft">
                <div class="item" onclick="toWorks()"><i class="orange icon fa fa-file fa-fw"></i><span>作业</span></div>
                <div class="item" onclick="Win10.openUrl('./folder.php','文件',[['80%','80%'], ['100px','100px']])"><i class="green icon fa fa-folder-open fa-fw"></i><span>文件</span></div>
                <div class="item" onclick="Win10.openUrl('./appsub.php','应用订阅',[['80%','80%'],['100px','100px']])"><i class="blue icon fa fa-adn fa-fw"></i><span>应用订阅</span></div>
                <div class="item" onclick="toProjects()"><i class="purple icon fa fa-building fa-fw"></i><span>应用实例</span></div>
                <?php
                   if (sizeof($_SESSION['licenses']) != 0)  // 没用到
                echo '<div class="item" onclick="toLicenses()"><i class="blue icon fa fa-list fa-fw"></i><span>应用许可证</span></div>';
                   if (sizeof($_SESSION['download']) != 0)  // 没用到
                echo '<div class="item" onclick="location.href=\''.$_SESSION['download'][0]['url'].
                  '\';"><i class="orange icon fa fa-download fa-fw"></i><span>下载'.
                  $_SESSION['download'][0]['name'].'</span></div>';
                ?>
                <div class="item" onclick="usage()"><i class="white icon fa fa-server fa-fw"></i><span>资源使用历史</span></div>
                <div class="item" onclick="Win10.openUrl('./account.php','帐户',[['60%','60%'], ['100px','100px']])"><i class="red icon fa fa-user fa-fw"></i><span>帐户</span></div>
                <?php if (isset($_SESSION['op_control'])) echo '
                <div class="item" onclick="Win10.openUrl(\'./prices.html\', \'产品价格\')"><i class="orange icon fa fa-product-hunt fa-fw"></i>
                    <span>产品价格</span></div>';  // 如果设置了op_control，这里才显示产品价格
                ?>
                <div class="item" onclick="Win10.exit();"><i class="black icon fa fa-power-off fa-fw"></i>退出</div>
            </div>
            <div id="win10-menu-switcher"></div>
        </div>
        <div id="win10_command_center" class="hidden_right">  <!--消息中心展开页面-->
            <div class="title">
                <h4 style="float: left">消息中心 </h4>
                <span id="win10_btn_command_center_clean_all">全部清除</span>
            </div>
            <div class="msgs"></div>
        </div>
        <div id="win10_task_bar">  <!--任务栏，规定底部显示的内容，如右下角时间等-->
            <div id="win10_btn_group_left" class="btn_group">
                <div id="win10_btn_win" class="btn"><span class="fa fa-bars"></span></div>
                <!-- <div class="btn" id="win10-btn-browser"><span class="fa fa-internet-explorer"></span></div> -->
            </div>
            <div id="win10_btn_group_middle" class="btn_group"></div>
            <div id="win10_btn_group_right" class="btn_group">
                <div class="btn" id="win10_btn_time"></div>
                <div class="btn" id="win10_btn_command"><span id="win10-msg-nof" class="fa fa-bell-o"></span></div>
                <div class="btn" id="win10_btn_show_desktop"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="./js/main.js?filever=<?=filesize('js/main.js')?>"></script>
    <script type="text/javascript" src="./js/addtomain.js?filever=<?=filesize('js/addtomain.js')?>"></script>
    <script type="text/javascript" src="./js/addtomain1.js?filever=<?=filesize('js/addtomain1.js')?>"></script>
</body>

</html>

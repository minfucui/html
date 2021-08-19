<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>作业详细</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/jobs.css">
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script type="text/javascript" src="./js/jobs.js"></script>
    <script type="text/javascript" src="./component/layer-v3.0.3/layer/layer.js"></script>
    <script type="text/javascript" src="./js/vnc.js"></script>
</head>

<body>
    <div class="tab-head">
      <div id="jobid_title">
        <h3 class="selected">作业号: <span class="jobid"></span> 
        <span class="jobstatus"></span>
        </h3>
      </div>
    </div>
    <div class="tab-content">
        <div class="show">
            <section class="operation buttonGroup">
                <button class="origin" onclick="toVnc()">打开应用图形窗</button>
                <button class="file disabled-button" id="file" onclick="openFile()" disabled>数据文件</button>
                <!--button class="stop" onclick="toStop()" style="float: right">停止</button>
                <button class="recovery" onclick="toRecovery()" style="float: right">恢复</button-->
                <!-- <button class="restart" onclick="toRestart()">重运行</button> -->
                <button class="stop" onclick="modRunLimit()" style="float:right;margin-left:5px;">修改运行时限</button>
                <button class="kill" onclick="toKill()" style="float: right">杀掉</button>
            </section>
            <section class="operation">
                <p>终端输出</p>
                <ul style="list-style-type:none;max-height: 500px;overflow: auto;background-color: #fff;font-size:10px;" class="logs">
                   <li>...</li>
                </ul>
                
            </section>

            <section class="operation details">
            </section>
        </div>
    </div>

    <script type="text/javascript" src="js/detail.js?filever=<?=filesize('js/detail.js')?>"></script>
</body>

</html>

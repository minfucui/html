<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>实例</title>
    <!-- qiu -->
    <link rel="stylesheet" href="./css/filelist.css">
    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/jobs.css">

    <style type="text/css">
        input:disabled,
        select:disabled {
            background-color: #f3f3f3!important;
        }
        
        .buttoncreate {
            padding: 10px 25px 10px 25px;
            border-radius: 4px;
            width: 120px !important;
            margin-left: 30px;
        }
        
        table {
            font-size: 12px;
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

        h1 {
            font-size: 12px;
            padding: 0px 0px 0px 10px;
        }
        h3 {
            font-size: 12px;
            padding: 0px 0px 0px 10px;
        }
        label {
            font-size: 12px;
        }
        input {
            font-size: 12px;
            
        }
    </style>
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script type="text/javascript" src="./js/vnc.js"></script>
</head>

<body>
    <form action="" method="post" class="bootstrap-frm" id="win10-form">
        <h3>应用和实例</h3>
        <label style="display:none" id="applabel">
            <span >应用名:</span>
            <input type="text" id="appName" value="" disabled>
        </label>
        <label>
            <span >实例名:</span> 
            <input type="text" id="projectname" value=""> 
        </label>
        <h1></h1>
        <label>
            <span>队列可用CPU核数:</span>
            <span id="cpun"></span>
        </label>
        <h1></h1>
        <div id="app-form"></div>
    </form>
    <!-- qiu -->
    <div id="myModal" class="modal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>您选择了以下文件</h2>
            </div>
            <div class="modal-body" id = 'filelist'>
            </div>
            <div class="modal-footer">
             <button class="ok" >确定</button>&nbsp;&nbsp;&nbsp;&nbsp;
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class="no">重选</button>
            </div>
          </div>
  </div>
    <div id="back"></div>
    <div id="login" class="fileList">
        <span id="close_all" onclick="modalback()">×</span>
        <span>/home</span>
        <hr/>
        <table cellspacing=0 class="" style="bordercolor:#C0C0C0;" width="100%">

            <tr style="background:#CBDAEB">
                <td>选择</td>
                <td>文件名</td>
                <td>修改时间</td>
                <td>文件大小</td>
            </tr>
            <tr>
                <td>
                    <input name="" type="checkbox" value="" /></td>
                <td>..</td>
                <td>...</td>
                <td>...</td>
            </tr>
        </table>
        <hr/>
        <div class="pagebutton">
            <button class="ppage" onclick="pPageBtn()">上一页</button>
            <span class="currentpage"></span>
            <button class="npage" onclick="nPageBtn()">下一页</button>
        </div>
    </div>
    <script type="text/javascript" src="./js/form_copy2.0.js?filever=<?=filesize('js/form_copy.js')?>"></script>  <!--方法js-->
</body>

</html>

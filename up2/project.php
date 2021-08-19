<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>应用实例列表</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/datatables.min.css">
    <style>
        .iconimg {
            width: 32px;
            height: 32px;
            overflow: hidden;
            margin: 0 auto;
            color: white;
            box-sizing: border-box;
            margin-bottom: 5px;
            margin-top: 5px;
            text-align: center;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            display: block;
            font-size: 37px;
            line-height: 50px;
        }
        
        .buttonGroup {
            margin: 10px 0;
            background-color: #f3f3f3;
            border-radius: 4px;
            padding: 10px;
        }
        
        .delete {
            background-color: #ca2000!important;
            color: white!important;
        }
        
        .disable-button {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script src="./js/datatables.min.js"></script>
</head>

<body>

    <div class="content-wrapper">

        <section class="operation buttonGroup">
            <button class="delete disable-button" onclick="toDel()">删除</button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-body">
                            <table class="cell-boader compact stripe" style="text-align: center;" id="projectTables">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="select_all" value="1" id="example-select-all" /></th>
                                        <th>应用</th>
                                        <th>名称</th>
                                        <th>数据文件</th>
                                        <th>属性</th>
                                    </tr>
                                </thead>
                                <tbody id="joblist">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript" src="js/project.js?filever=<?filesize('js/project.js')?>"></script>

</body>

</html>

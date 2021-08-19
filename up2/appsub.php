<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>应用订阅</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/jobs.css">
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
        .appselect {
            width: 16.6%;
            display:inline-block;
            line-height: 60px;
            height: 60px;
            cursor: pointer;
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

<body class="layout-top-nav">

    <div class="content-wrapper">

        <section class="operation buttonGroup">
            <button class="origin disable-button" onclick="confirm()" disabled>确认</button>
            <button class="recovery" onclick="toSub()">全选</button>
            <button class="kill" onclick="toUnsub()">全不选</button-->
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-body" id="sub" style="padding-left: 30px">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript">
        var subList = [];

        Win10_child._ajax("php/appSub.php?action=get", {}).then(res => {
            subList = res;
            constructPage(res);
        }).catch(err => {
            Win10_child.childLayer(err.message, err.message);
        })

        function constructPage(apps)
        {
            var str = '';
            var i = 0;
            apps.forEach(function(item) {
                 if (i == 0)
                     str += '<div class="row">';
                 str += '<div class="appselect" id="' + item.name +
                        '" onclick="flipselect(\'' + item.name + '\')">' +
                        '<input type="checkbox" id="check-' + item.name +
                        '" ' + (item.sub? 'checked':'') + 
                        ' onclick="flipselect(\'' + item.name + '\')"><img style="vertical-align:middle" src="data:' +
                        item.icon + '" height="30" width="30"/> ' + item.name +
                        (item.yamlPath.includes('.batch')?'命令行':'') +
                        '</div>';
                 i++;
                 if (i == 6) {
                     str += '</div>';
                     i = 0;
                 }
            });
            $('#sub').html(str);
        }
        function flipselect(id) {
            checkid = '#check-' + id;
            if ($(checkid).is(':checked'))
                 $(checkid).prop('checked', false);
            else
                 $(checkid).prop('checked', true);
            $('.origin').removeClass('disable-button');
            $('.origin').prop('disabled', false);
        }

        function toUnsub() {
            $('.origin').removeClass('disable-button');
            $('.origin').prop('disabled', false);
            subList.forEach(function(item) {
                $('#check-' + item.name).prop('checked', false);
            }); 
        }

        function toSub() {
            $('.origin').removeClass('disable-button');
            $('.origin').prop('disabled', false);
            subList.forEach(function(item) {
                $('#check-' + item.name).prop('checked', true);
            });
        }

        function confirm() {
            var applist = [];
            subList.forEach(function(item) {
                if ($('#check-' + item.name).is(':checked'))
                    applist.push(item.name);
            });
            Win10_child._ajax("php/appSub.php?action=sub",
                 {apps:applist}).then(res=> {
                Win10_child.getIndex();
                Win10_child.close();
            }).catch(err=> {
                Win10_child.childLayer(err.message, err.message);
            });
        }
    </script>

</body>

</html>

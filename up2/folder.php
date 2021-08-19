<?php
include 'header.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="renderer" content="webkit">
    <title>elFinder 2.0</title>

    <link href="/css/jquery-ui.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen" href="/css/elfinder.min.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/theme.css" />
    <link rel="stylesheet" href="css/fonts.css">

</head>

<body style="margin:0; padding:0;">
    <div id="finder"></div>

    <script type="text/javascript" src="/plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="/plugins/jQueryUI/jquery-ui.min.js"></script>

    <script type="text/javascript" src="/js/elfinder.min.js"></script>
    <script type="text/javascript" src="js/child.js"></script>
    <script type="text/javascript" charset="utf-8">
        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, 
                function(m,key,value) {
                    vars[key] = value;
                });
            return vars;
        }
        function explorer(path) {
            if (path == '') {
                Win10_child.childLayer('尚无数据文件', 'No data found');
                return;
            }
            $('#finder').elfinder({
                url: '/php/connector.minimal.php?path=' + path,
                uploadMaxChunkSize: 10737418240,
                height: '100%',
                resizable: false,
                defaultView: 'list',
                lang: 'zh_CN',
                ui: ['toolbar', 'tree', 'path', 'stat'],
                uiOptions: {
                   toolbar:  [['back','forward','up'],
                    ['mkdir','mkfile','upload'],
                    ['open','downoad'],
                    ['undo','redo'],
                    ['copy', 'cut', 'paste', 'rm', 'hide'],
                    ['duplicate', 'rename', 'edit', 'resize', 'chmod'],
                    ['selectall', 'selectnone', 'selectinvert'],
                    ['quicklook', 'info'],
                    ['extract', 'archive'],
                    ['search'],
                    ['view', 'sort']
                  ]},
                contextmenu: {
                    files: ['quicklook','download','edit','|','mkdirin','|',
                      'copy','cut','duplicate','|','rm','rename','|',
                      'archive','extract','|','selectinvert','|','info']
                },
            });
        }
        $(document).ready(function() {
            var parin = getUrlVars();
            if (parin.dir && !parin.dir.includes('.yaml'))
                explorer(parin.dir);
            else
                $.ajax({
                    type: "post",
                    url: "php/getProjectCWD.php",
                    asynnc: false,
                    data: JSON.stringify(parin),
                    dataType: "json",
                    success: function(r) {
                        var path = r.data;
                        explorer(path); 
                    },
                    error: function(e) {
                        Win10.toLogin(e)
                    }
                });
        });
    </script>
</body>

</html>

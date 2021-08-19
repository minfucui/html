<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>作业列表</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/datatables.min.css">
    <link rel="stylesheet" href="css/jobs.css">

    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script type="text/javascript" src="./component/layer-v3.0.3/layer/layer.js"></script>
    <script src="./js/datatables.min.js"></script>
    <script src="./js/jobs.js"></script>
    <script src="./js/vnc.js"></script>
</head>

<body>
    <div class="content-wrapper">

        <section class="operation buttonGroup">
            <!--button class="stop disable-button" onclick="toStop()">停止</button>
            <button class="recovery disable-button" onclick="toRecovery()">恢复</button-->
            <!-- <button class="restart disable-button" onclick="toRestart()">重运行</button> -->
            <button class="kill disable-button" onclick="toKill()">杀掉</button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <table class="cell-boader compact stripe" style="text-align: center;" id="jobDataTables">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="select_all" value="1" id="example-select-all" /></th>
                                        <th>作业</th>
                                        <th>状态</th>
                                        <th>应用/实例名</th>
                                        <th>数据文件</th>
                                        <th>访问</th>
                                        <th>递交时间</th>
                                        <th>开始时间</th>
                                        <th>结束时间</th>
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

    <script type="text/javascript">
        var selectedJobList = [];
        var selectedJobListStatus = [];
        var nWait = 0;
        var nStop = 0;
        var nFinish = 0;
        var nRun = 0;
        var nOther = 0;
        var tablejson = [];
        var ajData;
        var ajUrl;
        var isCp = localStorage.getItem("getWorksOfProject");
        var cp = (isCp ? JSON.parse(localStorage.getItem("cp")) : "");
        getJobs();

        function getJobs() {
            if (isCp == "project") {
                ajUrl = "queryJob";
                ajData = {
                    "project": cp
                }
            } else if (isCp == "app") {
                ajUrl = "queryJob";
                ajData = {
                    "app": cp
                }
            } else {
                ajUrl = "queryAllJob";
                ajData = {
                    "username": "cadmin"
                }
            }
            ajRequest(ajUrl, ajData)
            setInterval("ajRequest(ajUrl, ajData)", 3000)
        }

        function createTable(tablejson) {
            var n = " - ";
            if (selectedJobList.length == 0) {
                $(".stop").addClass('disable-button'); $(".stop").prop('disabled', true);
                $(".recovery").addClass('disable-button'); $(".recovery").prop('disabled', true);
                // $(".restart").addClass('disable-button'); $(".restart").prop('disabled', true);
                $(".kill").addClass('disable-button'); $(".kill").prop('disabled', true);
            }
            $(document).ready(function() {
                var table = $('#jobDataTables').DataTable({
                    "data": tablejson,
                    "destroy": true,
                    "columns": [{
                        "orderable": false,
                        "render": function(data, type, row, meta) {
                            var cstr = '<input type="checkbox" class="checkbox" name="checklist" value="' + row.jobID.jobID + '" jstatus="' + row.statusString + '"'
                            if (selectedJobList.indexOf(row.jobID.jobID + "") < 0) {
                                return cstr + ' />'
                            } else {
                                return cstr + ' checked />'
                            }
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return '<span class="view" style="color:rgb(0, 0, 238);text-decoration:underline;cursor:pointer;" id="' +
                                  row.jobID.jobID + '" status="' + row.statusString + '" onclick="toWorkDetails(this)">' + row.jobID.jobID +
                                  '</span>'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            if (!row.statusString)
                                return n;
                            var bgcolor = statusClass(row.statusString);
                            var status = statusStrZh(row.statusString);
                            return '<span class="status" style="color:' + bgcolor + '">' + status + '</span>'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            if (row.jobSpec.jobDescription) {
                                d = row.jobSpec.jobDescription.slice(-14);
                                d2 = row.jobSpec.jobDescription.slice(-15,-14);
                                if (isNaN(d) && d2 != '-')
                                    return row.jobSpec.jobDescription;
                                else
                                    return row.jobSpec.application;
                            } else
                                return n;
                        }
                    }, {
                        "render": function(dart, type, row, meta) {
                            return '<a style="color:rgb(0, 0, 238);text-decoration:underline;cursor:pointer;" ' +
                                   '" onclick="toFiles(\'' + row.jobSpec.cwd + '\')">打开目录</a>'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            if ((row.jobSpec.jobName == "cubevnc" ||
                                    row.jobSpec.jobName == "dcv" ||
                                    row.jobSpec.jobName == "jupyter") &&
                                row.statusString == "RUN" && row.msg) {
                                if (row.jobSpec.application &&
                                    row.jobSpec.jobDescription.includes(row.jobSpec.application + '-'))
                                    title = row.jobSpec.application;
                                else
                                    title = row.jobSpec.jobDescription;
                                return '<span class="vnc" style="color:rgb(0, 0, 238);text-decoration:underline;cursor:pointer;" id="' + 
                                    row.jobID.jobID + '" pro="' + title + '" msg="' + row.msg.content +
                                    '" onclick="toVnc(this)">访问</span>'
                            } else {
                                return n
                            }
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.submitTime ? Win10_child.timestampToTime(row.submitTime) : n
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.startTime ? Win10_child.timestampToTime(row.startTime) : n
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.endTime ? Win10_child.timestampToTime(row.endTime) : n
                        }
                    }],
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": true,
                    "pagingType": "simple_numbers",
                    "order": [
                        [1, "desc"]
                    ],
                    "stateSave": true,
                    "lengthMenu": [50, 100, 500],
                    "autoWidth": false,
                    "language": {
                        "sDecimal": "",
                        "sEmptyTable": "表中数据为空",
                        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                        "sInfoPostFix": "",
                        "sThousands": ",",
                        "sLengthMenu": "显示 _MENU_ 项结果",
                        "sLoadingRecords": "载入中...",
                        "sProcessing": "处理中...",
                        "sSearch": "搜索:",
                        "sZeroRecords": "没有匹配结果",
                        "oPaginate": {
                            "sFirst": "首页",
                            "sPrevious": "上一页",
                            "sNext": "下一页",
                            "sLast": "尾页"
                        },
                        "oAria": {
                            "sSortAscending": ": 以升序排列此列",
                            "sSortDescending": ": 以降序排列此列"
                        }
                    },

                });

                $('#example-select-all').change(function() {
                    var rows = table.rows({
                        'search': 'applied'
                    }).nodes();
                    if (this.checked)
                        $('.checkbox').each(function() {
                            this.checked=true;
                            for (i = 0; i < rows.length; i++) {
                                var n = $(".view").eq(i).attr("id")
                                if (selectedJobList.indexOf(n) < 0) {
                                    selectedJobList.push(n);
                                    selectedJobListStatus.push($(".view").eq(i).attr("status"));
                                }
                            }
                        });
                    else
                        $('.checkbox').each(function() {
                            this.checked=false;
                            selectedJobList = [];
                            selectedJobListStatus = [];
                        });
                    updateOp();
                });

                $('.checkbox').click(function() {
                    jobid = $(this).val();
                    status = $(this).attr('jstatus');
                    if ($(this).is(":checked")) {
                        if (selectedJobList.indexOf(jobid) < 0) {
                            selectedJobList.push(jobid);
                            selectedJobListStatus.push(status);
                        }

                        var isAllChecked = 0;
                        $('.checkbox').each(function() {
                            if (!this.checked)
                                isAllChecked = 1;
                        });
                        if (isAllChecked == 0) {
                            $('#checklist').prop("checked", true);
                        }
                    } else {
                        $('#checklist').prop("checked", false);
                        idx = selectedJobList.indexOf(jobid);
                        selectedJobList.splice(idx, 1);
                        selectedJobListStatus.splice(idx, 1);
                    }
                    updateOp();
                });
            });
        }

        function updateOp() {
            nWait = nFinish = nRun = nOther = nStop = 0;
            for (let i = 0; i < selectedJobListStatus.length; i++) {
                switch (selectedJobListStatus[i]) {
                    case "WAIT":
                        nWait++;
                        break;
                    case "SYSSTOP":
                    case "USRSTOP":
                    case "WSTOP":
                        nStop++;
                        break;
                    case "FINISH":
                    case "EXIT":
                    case "ZOMBIE":
                        nFinish++;
                        break;
                    case "RUN":
                        nRun++;
                        break;
                    default:
                        nOther++;
                }
            }
            /* console.log(selectedJobList)
            console.log("nWait=" + nWait + "; nStop=" + nStop +
                        "; nFinish=" + nFinish + "; nRun=" + nRun +
                        "; nOther=" + nOther); */
            if (selectedJobList.length == 0) {
                $(".stop").addClass('disable-button'); $(".stop").prop('disabled',true);
                $(".recovery").addClass('disable-button'); $(".recovery").prop('disabled',true);
                // $(".restart").addClass('disable-button'); $(".restart").prop('disabled',true);
                $(".kill").addClass('disable-button'); $(".kill").prop('disabled',true);
            } else {
                if (nOther > 0 || nFinish > 0) {
                    $(".stop").addClass('disable-button'); $(".stop").prop('disabled',true);
                    $(".recovery").addClass('disable-button'); $(".recovery").prop('disabled',true);
                    // $(".restart").addClass('disable-button'); $(".restart").prop('disabled',true);
                    $(".kill").addClass('disable-button'); $(".kill").prop('disabled',true);
                } else {
                    $(".kill").removeClass('disable-button'); $(".kill").prop('disabled',false);
                    if (nWait > 0) {
                        // $(".restart").addClass('disable-button'); $(".restart").prop('disabled',true);
                    } else {
                        // $(".restart").removeClass('disable-button'); $(".restart").prop('disabled',false);
                    }
                    if (nStop == 0 || nRun > 0 || nWait > 0) {
                        $(".recovery").addClass('disable-button'); $(".recovery").prop('disabled',true);
                    }  else {
                        $(".recovery").removeClass('disable-button'); $(".recovery").prop('disabled',false);
                    }
                    if ((nRun == 0 && nWait == 0) || nStop > 0) {
                        $(".stop").addClass('disable-button'); $(".stop").prop('disabled',true);
                    } else {
                        $(".stop").removeClass('disable-button'); $(".stop").prop('disabled',false);
                    }
                }
            }
        }

        function ajRequest(url, data) {
            $.ajax({
                type: "post",
                url: "php/aip.php?action=" + url,
                contentType: "application/json",
                data: JSON.stringify(data),
                dataType: "json",
                success: function(d) {
                    if (url == "queryJob" || url == "queryAllJob") {
                        tablejson = d.data;
                        createTable(tablejson);
                    } else {
                        selectedJobList = []
                        // location.href = location.href;
                    }
                    if (d.code == "2005") {
                        console.log(d)
                        var href = {};
                        href.url = "./detail.php?id=error&emsg=" + d.message;
                        href.title = "作业错误";
                        console.log(href)
                        Win10_child.open_new_windows(href, true);
                    }
                },
                error: function(e) {
                    // Win10_child.close()
                    // Win10_child.toLogin(e)
                }

            });
        }

        function toStop() {
            url = "stopJob";
            data = {
                "jobId": selectedJobList.join(" ")
            }
            ajRequest(url, data);
        }

        function toRecovery() {
            url = "resumeJob";
            data = {
                "jobId": selectedJobList.join(" ")
            }
            ajRequest(url, data);
        }

        /* function toRestart() {
            url = "reRunJob";
            data = {
                "jobId": selectedJobList.join(" ")
            }
            ajRequest(url, data);
        } */

        function toKill() {
            url = "killJob";
            data = {
                "jobId": selectedJobList.join(" ")
            }
            layer.confirm('确认杀掉作业' + data.jobId.replace(' ', ',') + '?', {icon: 3, title: '提示'}, function(index){
                ajRequest(url, data);
                layer.close(index);
            });
        }

        function toVnc(par) {
            var jobid = par.id;
            var project = $(par).attr('pro');
            var vncUrl = $(par).attr('msg');
            url = vncurlConvert(vncUrl);
            if (url == 'no vnc url' || url == 'url wait') {

            } else if (url.indexOf("https") != -1) {
                window.open(url)
            } else {
                Win10_child.openUrl(url, project + ' ' + jobid, [
                    ['95%', '95%'],
                    ['10px', '10px']
                ]);
            }
        }

        function toWorkDetails(par) {
            params = par.id;
            //console.log(par.id)
            if (params) {
                Win10_child.openUrl('./detail.php?id=' + params, "作业 " + params);
            }
        }

        function toFiles(path) {
            Win10_child.openUrl('./folder.php?dir=' + encodeURI(path),
                   '文件: ' + path.substring(path.lastIndexOf('/') + 1),
                  [['80%','80%'], ['100px','100px']])
        }
    </script>

</body>

</html>

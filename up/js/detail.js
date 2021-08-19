/*
 *  By Skycloud Software. Copyright 2020
 *
 */
window.alert = function() {
    return false;
}
// 作业详情界面方法js
var cwd;
var jobid = Win10_child.GetQueryString('id');  // 获取作业号
$(".jobid").html(jobid)
var appName = '';
var jobData;

if (jobid == "error") {
    $(".stop").addClass('disable-button');
    $(".recovery").addClass('disable-button');
    // $(".restart").addClass('disable-button');
    $(".kill").addClass('disable-button');
    $(".origin").addClass('red');
    $(".origin").addClass('disable-button');

    $(".workstate").css('background', '#f3f3f3');
    $(".details").css('background', '#fff');

    var s = Win10_child.GetQueryString('emsg');
    if (s == "Low account balance")
        $("#jobid_title").html('账户余额不足。请充值');
    else
        $("#jobid_title").html(decodeURIComponent(s))
}
var project = "";
var data = {
    "username": "cadmin",
    "jobId": jobid
}

var intervald;
var intervalv;
var intervall;

if (!isNaN(jobid)) {  // 不断迭代的查询，输出日志，更新作业状态
    requestajax('queryJobDetail')
    requestajax('queryJobVncUrl')
    requestajax('queryJobLog')

    intervald = setInterval("requestajax('queryJobDetail')", 1000)
    intervalv = setInterval("requestajax('queryJobVncUrl')", 1000)
    intervall = setInterval("requestajax('queryJobLog')", 3000)
}

var str = "";

function create(dt) {  // data，根据作业状态定义控件
    if (dt.statusString == "WAIT") {
        $(".stop").prop('disabled', false); $(".stop").removeClass('disable-button');
        $(".recovery").prop('disabled', true); $(".recovery").addClass('disable-button');
        // $(".restart").prop('disabled', true); $(".restart").addClass('disable-button');
        $(".kill").prop('disabled', false); $(".kill").removeClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    } else if (dt.statusString == "WSTOP") {
        $(".stop").prop('disabled', true); $(".stop").addClass('disable-button');
        $(".recovery").prop('disabled', false); $(".recovery").removeClass('disable-button');
        // $(".restart").prop('disabled', true); $(".restart").addClass('disable-button');
        $(".kill").prop('disabled', false); $(".kill").removeClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    } else if (dt.statusString == "SYSSTOP") {
        $(".stop").prop('disabled', true); $(".stop").addClass('disable-button');
        $(".recovery").prop('disabled', true); $(".recovery").addClass('disable-button');
        // $(".restart").prop('disabled', false); $(".restart").removeClass('disable-button');
        $(".kill").prop('disabled', false); $(".kill").removeClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    } else if (dt.statusString == "USRSTOP") {
        $(".stop").prop('disabled', true); $(".stop").addClass('disable-button');
        $(".recovery").prop('disabled', false); $(".recovery").removeClass('disable-button');
        // $(".restart").prop('disabled', false); $(".restart").removeClass('disable-button');
        $(".kill").prop('disabled', false); $(".kill").removeClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    } else if (dt.statusString == "FINISH") {
        $(".stop").prop('disabled', true); $(".stop").addClass('disable-button');
        $(".recovery").prop('disabled', true); $(".recovery").addClass('disable-button');
        // $(".restart").prop('disabled', true); $(".restart").addClass('disable-button');
        $(".kill").prop('disabled', true); $(".kill").addClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    } else if (dt.statusString == "RUN") {
        $(".stop").prop('disabled', false); $(".stop").removeClass('disable-button');
        $(".recovery").prop('disabled', true); $(".recovery").addClass('disable-button');
        // $(".restart").prop('disabled', false); $(".restart").removeClass('disable-button');
        $(".kill").prop('disabled', false); $(".kill").removeClass('disable-button');
        if (dt.jobSpec.jobName != "cubevnc" &&
            dt.jobSpec.jobName != "dcv" &&
            dt.jobSpec.jobName != "jupyter" &&
            dt.jobSpec.jobType != 'vmware') {
            $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');
        } else {
            if (vncUrl != 'no vnc url' && vncUrl != 'url wait' && vncUrl != '') {
                $(".origin").prop('disabled', false); $(".origin").removeClass('disable-button');
            } else {
                $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');
            }
        }
    } else {
        $(".stop").prop('disabled', true); $(".stop").addClass('disable-button');
        $(".recovery").prop('disabled', true); $(".recovery").addClass('disable-button');
        // $(".restart").prop('disabled', true); $(".restart").addClass('disable-button');
        $(".kill").prop('disabled', true); $(".kill").addClass('disable-button');
        $(".origin").prop('disabled', true); $(".origin").addClass('disable-button');

    }

    $(".jobstatus").html('状态: ' + statusStrZh(dt.statusString));
    $(".jobstatus").css('color', statusClass(dt.statusString));
}

function details(dt) {  // 详情页面
    var n = " - "
    if (dt.jobSpec.application)
        appName = dt.jobSpec.application;
    remainStr = '';
    if (dt.runTime > 0) {
        runtime = convertToHMS(dt.runTime);
        if (dt.jobSpec.maxRunTime > 0) {
            left = dt.jobSpec.maxRunTime * 60 - dt.runTime;
            if (left < 900)
                remainStr = '<span style="color:red">剩余' + 
                          convertToHMS(left) + '强制结束</span>';
            else
                remainStr = '<span>剩余' + convertToHMS(left) + '强制结束</span>';
        }
    } else
        runtime = '-';
    str = '<p></p><p>作业详细信息</p>' +
        '<span>作业号: </span><span>' + (dt.jobID.jobID ? dt.jobID.jobID : n) + '</span>' +
        '<span>递交时间: </span><span>' + (dt.submitTime ? Win10_child.timestampToTime(dt.submitTime) : n) + '</span>' +
        '<span>应用名: </span><span>' + (dt.jobSpec.application ? dt.jobSpec.application : n) + '</span>' +
        '<span>开始运行: </span><span>' + (dt.startTime ? Win10_child.timestampToTime(dt.startTime) : n) + '</span>' +
        '<span>结束时间: </span><span>' + (dt.endTime ? Win10_child.timestampToTime(dt.endTime) : n) + '</span>' +
        '<span>应用实例名: </span><span>' + (dt.jobSpec.jobDescription ? dt.jobSpec.jobDescription : n) + '</span>' +
        // '<span>运行路径: </span><span>' + (dt.jobSpec.cwd ? dt.jobSpec.cwd : n) + '</span>' +
        // '<span>等待原因: </span><span>' + (dt.waitReason ? dt.waitReason : n) + '</span>' +
        // '<span>停止原因: </span><span>' + (dt.stopReason ? dt.stopReason : n) + '</span>' +
        '<span>运行时长: </span><span>' + runtime + '</span>' + 
        // '<span>运行时限: </span><span>' + (dt.jobSpec.maxRunTime > 0 ? dt.jobSpec.maxRunTime + '分' :n) + '</span>' +
        '<p></p><p>递交参数</p>' +
        // '<span>命令行: </span><span>' + (dt.jobSpec.command ? jobCmd(dt) : n) + '</span>' +
        '<span>队列: </span><span>' + (dt.jobSpec.queue ? dt.jobSpec.queue : n) + '</span>' +
        '<span>递交用户: </span><span>' + (dt.user ? dt.user : n) + '</span>' +
        // '<span>请求的资源: </span><span>' + n + '</span>' +
        '<span>请求的cpu数: </span><span>' + (dt.jobSpec.minNumSlots ? dt.jobSpec.minNumSlots : n) + '</span>' +
        // '<span>输入文件: </span><span>' + (dt.jobSpec.inFile ? dt.jobSpec.inFile : n) + '</span>' +
        '<span>输出文件: </span><span>' +
           ((dt.jobSpec.outFile && dt.jobSpec.outFile.name != '/dev/null') ?
            dt.jobSpec.outFile.name : n) + '</span>' ;
        // '<span>项目: </span><span>' + dt.jobSpec.project + '</span>' +
        // '<p></p><p>状态</p>' +
        // '<span>请求的主机: </span><span>' + (dt.submitHost ? dt.submitHost : n) + '</span>' +
        // '<span>运行主机: </span><span>' + (dt.execHosts ? dt.execHosts : n) + '</span>' +
        // '<span>资源使用: </span><span>' + ((dt.resource && dt.resource.mem > 0) ? resUsage(dt) : n) + '</span>' +
        // '<span>进程组: </span><span>' + ((dt.resource && dt.resource.pids) ? dt.resource.pids[0].pgid : n) + '</span>' +
        // '<span>进程号: </span><span>' + ((dt.resource && dt.resource.pids) ? dt.resource.pids[0].pid : n) + '</span>' +
        // '<span>线程数: </span><span>' + ((dt.resource && dt.resource.nthreads && (dt.resource.nthreads > 0)) ? dt.resource.nthreads : n) + '</span>' +
        // '<span>结束时间: </span><span>' + (dt.endTime ? Win10_child.timestampToTime(dt.endTime) : n) + '</span>' +
        // '<span>结束状态码: </span><span>' + exitStr(dt) + '</span>' + remainStr;
    $(".details").html(str)
    if (dt.jobSpec.cwd) {
        $("#file").prop('disabled', false);
        $("#file").removeClass('disable-button');
    }
}

function exitStr(dt) {  // 退出码
    var str = "-";
    if (dt.endTime && dt.endTime > 0) {
        if (dt.exitCode) {
            var code = (dt.exitCode >> 8);
            if (code > 128) {
                str = 'By Signal ' + (code & 0x7F);
            } else if (dt.execHosts) {
                str = 'Exit Code ' + code;
            } else {
                str = 'Killed While Pending';
            }
        } else
            str = '0';
    }
    return str;
}

function jobCmd(dt) {  // 作业命令
    var cn = dt.jobSpec.jobName;
    if (cn == 'cubevnc' || cn == 'dcv' || cn == 'jupyter') {
        return dt.jobSpec.jobName;
    } else {
        return dt.jobSpec.command;
    }
}

function resUsage(dt) {  // 资源使用情况
    var mem = dt.resource.mem;
    var swap = dt.resource.swap;
    var str = "";

    if (mem > 1024 * 1024) {
        str = 'MEM: ' + String((mem / (1024 * 1024)).toFixed(1) + 'GBytes');
    } else if (mem > 1024) {
        str = 'MEM: ' + String((mem / (1024)).toFixed(1) + 'MBytes');
    } else {
        str = 'MEM: ' + String((mem).toFixed(1) + 'KBytes');
    }

    if (swap > 1024 * 1024) {
        str += ', SWAP: ' + String((swap / (1024 * 1024)).toFixed(1) + 'GBytes');
    } else if (mem > 1024) {
        str += ', SWAP: ' + String((swap / (1024)).toFixed(1) + 'MBytes');
    } else {
        str += ', SWAP: ' + String((swap).toFixed(1) + 'KBytes');
    }
    return str;
}

var vncUrl;

function toVnc() {
    url = vncurlConvert(vncUrl);
    if (url == 'no vnc url' || url == 'url wait') {

    } else if (url.indexOf("https") != -1) {
        window.open(url)
    } else {
        if (project.includes(appName + '-'))
            project = appName;
        Win10_child.openUrl(url, project + ' ' + jobid, [
            ['95%', '95%'],
            ['10px', '10px']
        ]);
    }
}

function toStop() {
    requestajax('stopJob');
}

function toRecovery() {
    requestajax('resumeJob');
}

/* function toRestart() {
    requestajax('reRunJob');
} */

function toKill() {
    layer.confirm('确认杀掉作业' + jobid + '?', {icon: 3, title: '提示'}, function(index){
        requestajax('killJob');
    });
}

function modRunLimit() {  // 修改运行时间
    layer.prompt({
        formType: 0,
        title: '修改作业' + jobid + '的运行时限(分钟)，0表示无限',
        value: (jobData.jobSpec.maxRunTime > 0 ? jobData.jobSpec.maxRunTime :''),
        },
        function(value, index, elem) {
            if (parseInt(value) > 0 && jobData.runTime >= value * 60) {
                layer.alert('运行时限必须大于作业已经运行的时间：' +
                             parseInt(jobData.runTime / 60 + 1) + '分');
                return;
            }
            $.ajax({
                type: 'post',
                url: 'php/aip.php?action=setRunLimit',  // 作业aip命令接口，aip的核心也即作业
                contentType: "application/json",
                dataType: 'json',
                data: JSON.stringify({jobId: jobid, runLimit: parseInt(value)}),
                success: function(d) {
                    if (d.code == 0)
                        layer.close(index);
                    else
                        layer.alert(d.message, {icon: 2});
                },
                error: function(e) {
                    layer.alert('后台错误', {icon: 5});
                }
            });
    });
}

function openFile() {  // 日志文件
    Win10_child.openUrl('./folder.php?dir=' + encodeURI(cwd),
           '文件: ' + cwd.substring(cwd.lastIndexOf('/') + 1),
          [['80%','80%'], ['100px','100px']])
}

function convertToHMS(s) {
    hour = parseInt(s / 3600);
    remain = s % 3600;
    min = parseInt (remain / 60);
    minSt = min < 10 ? '0' + min.toString() : min.toString();
    sec = remain % 60;
    secSt = sec < 10 ? '0' + sec.toString() : sec.toString();
    return hour.toString() + ':' + minSt + ':' + secSt;
}

function requestajax(url) {
    $.ajax({
        type: "post",
        url: "php/aip.php?action=" + url,
        contentType: "application/json",
        data: JSON.stringify({
            "jobId": jobid
        }),
        dataType: "json",
        success: function(d) {
            if (url == 'queryJobDetail')
                jobData = d.data;
            if (url == 'queryJobDetail') {
                var status = d.data.statusString;
                // rerun job status: run/stop -> exit -> wait -> run
                if (status == "FINISH" || status == "ZOMBIE" || status == "EXIT") {  // 僵尸作业
                    clearInterval(intervald)
                    clearInterval(intervalv)
                    clearInterval(intervall)
                }
                create(d.data);  // 对作业查询或操作后，显示状态表单信息
                if (d.data.jobSpec)
                    cwd = d.data.jobSpec.cwd;
                else
                    cwd = '';
                details(d.data);  // 展示详情
                if (d.data.hasOwnProperty("jobSpec"))  // 检测一个属性是否是对象的自有属性
                project = d.data.jobSpec.jobDescription;
            } else if (url == 'queryJobVncUrl') {
                vncUrl = d.data;
            } else if (url == 'queryJobLog') {
                if (d.data) {
                    /* translation */
                    if (d.data.slice(0,7) == "Job has")
                        d.data = "";
                    var s = '<pre>' + d.data + '</pre>';
                    $(".logs").html(s)
                }

            } else {
                location.href = location.href;
            }
        },
        error: function(e) {
            Win10_child.close()
            Win10_child.toLogin(e)
        }
    });
}

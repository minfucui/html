/*
 *  By Skycloud Software. Copyright 2020
 *
 */
var disa = true;
//qiu
var fileLists = []
var fileString 
$(document).ready(function() {  // 转移到form_copy2.0.js

    $("#win10-form").bind('input propertychange', function() {
        $(".buttoncreate").removeClass('disable-button');
        /* $("#runbutton").addClass('disable-button');
        $("#runbutton").prop('disabled', true); */
        disa = false;
    });
    $("#win10-form").on("change", "select", function() {
        $(".buttoncreate").removeClass('disable-button');
        /* $("#runbutton").addClass('disable-button');
        $("#runbutton").prop('disabled', false); */
        disa = false;
    });
});

var queueList = [];
$.ajax({
    type: "post",
    url: "php/listQueue.php",
    async: false,
    contentType: "application/json",
    data: JSON.stringify(''),
    dataType: "json",
    success: function(d) {
        if (d.code == "0") {
            d.data.forEach(queue => {
                queueList.push(queue)
            })
        } else {
            Win10_child.childLayer(d.message, d.message);
        }

    },
    error: function(e) {
        Win10_child.close()
        Win10_child.toLogin(e)
    }
});
var aname = "";
var uiParamList = [];
var clusterParamMap = {};

var pPath = JSON.parse(localStorage.getItem("currentpro")).projectPath;
var cp = JSON.parse(localStorage.getItem("cp"));
var gdata;
var gurl;
var ct;
var queues = {options: [], value: ''};
var runlimit = {options: [], value: ''};
var guiApp = 'false';
var vncid;
var interval;
var project;
var appData;
// console.log(localStorage.getItem("currentpro"));
// console.log(cp);

localStorage.removeItem('fileId')
if (pPath && !pPath.includes('/apps/')) {
    gdata = {
        "projectPath": pPath
    }
    ct = "project";
    getForm("php/getProject.php", gdata);

} else {
    gdata = {
        "appName": cp
    }
    if (pPath && pPath.includes('/apps'))
        gdata.appPath = pPath;
    ct = "app";
    getForm("php/getApp.php", gdata);
    pPath = 'apps/' + cp + '.yaml';
}

function getForm(url, data) {
    Win10_child._ajax(url, data).then(res => {
        var d = res;
        if (url == "php/getProject.php") {
            $('#projectname').val(d.projectName);
            if (d.appName2)
                $('#appName').val(d.appName2);
            else
                $('#appName').val(d.appName);
            localStorage.setItem("cp", d.appName);
            $('#applabel').show();
            fillForm(d);
        } else { /* application */
            if (d.dir) { /* multiple apps */
                $('#applabel').show();
                str = '<select id="appName">';
                appData = d.apps;
                for(i = 0; i < d.apps.length; i++)
                    str += '<option value="' + d.apps[i].appName+ '"' + 
                           (i== 0?' selected>':'>') + d.apps[i].appName + '</options>';
                str += '</select>';
                $('#appName').replaceWith(str);
                fillForm(d.apps[0]);
                pPath = cp + '-' + d.apps[0].appName + '.yaml';
                $('#appName').on('change', function() {
                    for(i = 0; i < appData.length; i++)
                        if (this.value == appData[i].appName) {
                            fillForm(appData[i]);
                            pPath = cp + '-' + appData[i].appName + '.yaml';
                            break;
                        }
                });
            } else {  /* single app */
                $('#appName').val(cp);
                if (fillForm(d) == 0)  /* no seletable parameters, submit app directly */
                    createOrUpdate(2);
            }
        }
        getQSlots();
    }).catch(err => {
        Win10_child.childLayer(err.message, err.message);
    })
}

function fillForm(d) {
    guiApp = d.gui;
    if (d.cluster_params)
        clusterParamMap = new Map(Object.entries(d.cluster_params));
    uiParamList = d.uiParamList;
    return analyticData(uiParamList, clusterParamMap, "projectName" in d);
}

function getQSlots() {
    var qname = $('#queue').val();
    if (qname == null)
        qname = queueList[0].Name;
    for (i = 0; i < queueList.length; i++)
        if (qname == queueList[i].Name) {
            if (queueList[i].AvailSlots == '0')
                h = '<a style="color:red">0</a>';
            else
                h = '<a style="color:green">' + queueList[i].AvailSlots + '</a>';
            $('#cpun').html(h);
            break;
        }
}

function analyticData(uiParamList, clusterParams, isProject) {
    var str = "";
    var i;
    var numSelParams = 0;
    if (clusterParams.size > 0) {
        str += '<h3>集群资源参数</h3>';
        for (var [key, value] of clusterParams) {
            if (key == "queue") {
                str += '<label><span >队列:</span><select id="queue" onchange="getQSlots()">';
                if (value == "auto_list" || value.options == "auto_list") {
                    numSelParams++;
                    for (i = 0; i < queueList.length; i++) {
                        queues.options.push(queueList[i].Name);
                        str += '<option value="' + queueList[i].Name + '" ' +
                            (value.value == queueList[i].Name ? 'selected': '') +'>'
                            + queueList[i].Name + '</option>';
                    }
                } else if (value.options) {
                    if (value.options.length > 1)
                        numSelParams++;
                    if (value.value)
                        str += '<option value"' + value.value + '" selected>' + value.value +
                               '</option>';
                    for (i = 0; i < value.options.length; i++) {
                        queues.options.push(value.options[i]);
                        if (value.value && value.options[i] == value.value)
                            continue;
                        str += '<option value="' + value.options[i] + '">' +
                               value.options[i] + '</option>';
                    }
                } else {
                    if (queueList.length > 1)
                        numSelParams++;
                    str += '<option value="' + value + '" selected>' + value + '</option>'
                    for (i = 0; i < queueList.length; i++) {
                        queues.options.push(queueList[i].Name);
                        if (queueList[i].Name != value) {
                            str += '<option value="' + queueList[i].Name + '">' +
                                queueList[i].Name + '</option>';
                        }
                    }
                }
                str += '</select><b class="visible">*</b></label>';
            } else if (key == "distribution") {
                numSelParams++;
                str += '<label><span >内存架构:</span><select id="distribution">';
                var words = value.split("|")
                if (words.length == 1) {
                    str += '<option value="smp" ' + (value == 'smp' ? 'selected' : '') + '">smp</option>' +
                           '<option value="dmp" ' + (value == 'dmp' ? 'selected' : '') + '">dmp</option>';
                } else {
                    for (i = 0; i < words.length; i++) {
                        var dis;
                        dis = words[i].trim().toLowerCase();
                        if (dis == "dmp") {
                            str += '<option value="' + dis + '" selected>' + dis + '</option>';
                        } else {
                            str += '<option value="' + dis + '">' + dis + '</option>';
                        }
                    }
                }
                str += '</select><b class="visible">*</b></label>';
            } else if (key == "mincpu") {
                numSelParams++;
                str += '<label><span >CPU:</span><select id="mincpu">';
                if (value.options) {
                    for (i = 0; i < value.options.length; i++) {
                        str += '<option value="' + value.options[i] + '"';
                        if (value.value && (value.options[i] == value.value))
                            str += ' selected';
                        str += '>' + value.options[i] + '</option>';
                    }
                } else
                    if (typeof value === 'string' && value.includes(',')) {
                        str += '<option value="1" selected>1</option>';
                        for (i = 2; i <= 64; i++) {
                            str += '<option value="' + i + '">' + i + '</option>';
                        }
                    } else {
                        for (i = 1; i <= 64; i++) {
                            if (i != value) {
                                str += '<option value="' + i + '">' + i + '</option>';
                            } else {
                                str += '<option value="' + value + '" selected>' + value + '</option>';
                            }
                        }
                    }
                str += '</select><b class="visible">*</b></label>';
            } else if (key == "cwd" && value != "") {
                numSelParams++;
                str += '<label><span>数据路径:</span>' +
                    '<input type="button" id="cwd" onclick="modalopen(\'cwd\',\'/dir/*\')" class="button" value="' +
                    value + '" required title="' + value + '"/input><b class="visible">*</b></label>';
            } else if (key == "runlimit") {
                numSelParams++;
                str += '<label><span>运行时限(分):</span><select id="runlimit">';
                if (value.options) {
                    runlimit.options = value.options;
                    for (i = 0; i < value.options.length; i++) {
                        str += '<option value="' + value.options[i] + '"';
                        if (value.value && (value.options[i] == value.value))
                            str += ' selected';
                        str += '>' + value.options[i] + '</option>';
                    }
                } else {
                    str += '<option value="' + value + '">' + value + '</options>';
                }
                str += '<select><b class="visible">*<b></label>';
            }
        }
    }
    if (uiParamList && uiParamList.length > 0)
        numSelParams++;
    uiParamList.forEach(param => {
        str += '<h3>' + param.name + '</h3>';
        param.params.forEach(ele => {
            var disabled = "";
            var required = "";
            var visible = "invisible";
            if (ele.readonly) {
                disabled = "disabled"
            }
            if (ele.required) {
                required = "required"
                visible = "visible"
            }
            if (ele.filed_type == "input" && (ele.value_type == "string" || ele.value_type == '字串行')) {
                if (!ele.value)
                    ele.value = '';
                str += '<label><span >' + ele.filed_label + ': </span>' +
                    '<input type="text" id="' + ele.id + '" value="' + ele.value + '"' + disabled + ' ' + required + '><b class="' + visible + '">*</b></label>';
            } else if (ele.filed_type == "input" && ele.value_type == "number") {
                var vArr = ele.value_range.split('to');
                var vMin = vArr[0];
                var vMax = vArr[1];
                str += '<label><span >' + ele.filed_label + ': </span>' +
                    '<input type="number" id="' + ele.id + '" min="' + vMin + '" max="' + vMax + '" value="' + ele.value + '"' + disabled + ' ' + required + '><b class="' + visible + '">*</b></label>';
            } else if (ele.filed_type == "file-remote") {
                var fn;
                var fd;
                if (ele.value_range.includes('/dir/')) {
                    fn = ele.value ? ele.value : "$HOME";
                    // fd = "dir";
                } else {
                    fn = ele.value ? ele.value : "选择文件";
                    // fd = "file";
                }
                fd = ele.value_range;
                str += '<label><span >' + ele.filed_label + ': </span>' +
                    '<input type="button" id="' + ele.id + '" onclick="modalopen(' + ele.id + ',\'' + fd + '\')" class="button" value="' +
                    fn + '"' + disabled + ' ' + required + ' title="' + fn + '"><b class="' + visible + '">*</b></label>';
            } else if (ele.filed_type == "select-single") {
                var vArr = ele.value_range.split(',');
                var substr = "";
                vArr.forEach(temp => {
                    if (temp == ele.value) {
                        substr += '<option value="' + temp + '" selected>' + temp + '</option>';
                    } else {
                        substr += '<option value="' + temp + '">' + temp + '</option>';
                    }
                });
                str += '<label><span >' + ele.filed_label + ': </span>' +
                    '<select id="' + ele.id + '" ' + '" value="' + ele.value + '"' + disabled + ' ' + required + '>' + substr + '</select><b class="' + visible + '">*</b></label>';
            } else if (ele.filed_type == "switch") {
                str += '<label><span >' + ele.filed_label + ': </span>' +
                    '<input type="checkbox" class="switch switch-anim" id="' + ele.id + '" ' + '" value="' + ele.value + '"' + disabled + ' ' + required + '><b class="' + visible + '">*</b></label>';
            }
        });
    });
    if (ct == "app") {
        str += '<h1></h1><input type="button" onclick="projectFiles()" class="stop buttoncreate" value="数据文件" />' +
            '<input type="button" onclick="createOrUpdate(2)" class="recovery buttoncreate" style="margin-left:30px" value="运行" />' +
            '<input type="button" onclick="createOrUpdate(1)" class="restart buttoncreate" style="margin-left:7%" value="保存实例" />' +
            '<input type="button" onclick="cancel()" class="kill buttoncreate" style="margin-left:30px" value="取消" />'
    } else {
        str += '<h1></h1><input type="button" onclick="projectFiles()" class="stop buttoncreate" value="数据文件" />' +
            '<input type="button" onclick="createOrUpdate(2)" class="recovery buttoncreate" style="margin-left:30px" id="runbutton" value="运行" />' +
            '<input type="button" onclick="createOrUpdate(1)" class="restart buttoncreate disable-button" style="margin-left:7%" value="保存" />' +
            '<input type="button" onclick="cancel()" class="kill buttoncreate" value="取消" />'
    }
    $("#app-form").html(str);
    return numSelParams;
}

function getpar(clusterParams) {
    var ret = true;
    if (document.getElementById('mincpu') != null) {
        clusterParams['mincpu'] = document.getElementById('mincpu').value
    }
    if (document.getElementById('distribution') != null) {
        clusterParams['distribution'] = document.getElementById('distribution').value
    }
    if (document.getElementById('queue') != null) {
        clusterParams['queue'] = {options: queues.options,
                          value:document.getElementById('queue').value};
    }
    if (document.getElementById('cwd') != null) {
        clusterParams['cwd'] = document.getElementById('cwd').value
    }
    if (document.getElementById('runlimit') != null)
        clusterParams.runlimit = {options: runlimit.options,
                                  value:$('#runlimit').val()};
    if (uiParamList != null) {
        uiParamList.forEach(param => {
            param.params.forEach(ele => {
                value = document.getElementById(ele.id).value;
                if (ele.required && 
                    (value == "" || value == "选择文件")) {
                    ret = false;
                }
                ele.value = value;
                if (ele.value == '选择文件')
                    ele.value = '';
            })
        })
    }
    return ret;
}

function cancel() {
    Win10_child.close()
}

function checkFileSelected() {
    var fileid = localStorage.getItem('fileId')
    if (fileid != null &&
        (document.getElementById(fileid).value == null ||
            document.getElementById(fileid).value == "选择文件")) {
        Win10_child.childLayer('选择文件不能为空!', 'The selected file cannot be empty!');
        return false;
    }
    return true;
}

function projectFiles() {
    var projPath = pPath;
    var base = projPath.substring(projPath.lastIndexOf('/') + 1);
    base = base.substring(0, base.lastIndexOf('.'));
    Win10_child.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                   [['80%','80%'], ['100px','100px']])
}

/* p = 1: create project
 * p = 2: run project
 */
function createOrUpdate(p) {
    var proname = document.getElementById('projectname').value;
    if (p == 1 && (!proname || proname == '')) {
        Win10_child.childLayer('实例名不能为空!', "This project name can't be blank!");
        return false;
    }
    if (checkFileSelected() == false) {
        return false;
    }
    var clusterParams = {};
    if (!getpar(clusterParams)) {
        Win10_child.childLayer('必填项不能为空!', "Required fields can't be blank!");
        return false;
    }
    var par = {
        project: proname,
        uiParamList: uiParamList,
        clusterParams: clusterParams,
    }
    if (p == 1) {
        if (ct == "app") {
            par.appName = JSON.parse(localStorage.getItem("cp"));
            par.appName2 = $('#appName').val();
            createUpdate("php/createProject.php", par)
        } else {
            if (disa == true) {
                return;
            }
            par.projectPath = pPath;
            par.appName = localStorage.getItem("cp");
            par.appName2 = $('#appName').val();
            createUpdate("php/updateProject.php", par)
            $("#runbutton").removeClass('disable-button');
            $("#runbutton").prop('disabled', false);
        }
    } else if (p == 2) {
        if (ct == "app") {
            par.appName = JSON.parse(localStorage.getItem("cp"));
            par.appName2 = $('#appName').val();
            createOrUpdateAndRun("php/directRunProject.php", par)
        } else {
            par.projectPath = pPath;
            par.appName = localStorage.getItem("cp");
            par.appName2 = $('#appName').val();
            createOrUpdateAndRun("php/directRunProject.php", par)
        }
    }
}

function createUpdate(url, par) {
    Win10_child._ajax(url, par).then(res => {
        var d = res;
        if (url == "php/createProject.php") {
            Win10_child.childLayer('创建成功!', 'Created!');
            Win10_child.getIndex();
            Win10_child.close();
        } else if (url == "php/updateProject.php") {
            Win10_child.childLayer('修改成功!', 'Modified!');
            Win10_child.getIndex();
            // Win10_child.close();
        }
    }).catch(err => {
        var em = err.message.split('|');
        Win10_child.childLayer(em[0], em[1]);
    })
}

function getProPath(projectName) {
    var folders = pPath.split("/")
    var parent = folders.slice(0, folders.length - 1).join("/")
    return parent + "/" + projectName + ".yaml"
}

function createAndRun(params) {
    if (checkFileSelected() == false) {
        return false;
    }
    var clusterParams = {};
    if (!getpar(clusterParams)) {
        Win10_child.childLayer('必填项不能为空!', "Required fields can't be blank!"); 
        return false;
    }
    var par = {
        project: document.getElementById('projectname').value,
        uiParamList: uiParamList,
        clusterParams: clusterParams,
        appName: JSON.parse(localStorage.getItem("cp")),
    }
    if (!par.project || par.project == '') {
        Win10_child.childLayer('实例名不能为空!', "This project name can't be blank!");
        return;
    }
    var datas = {
        "appName": par.appName
    }

    Win10_child._ajax("php/getProjectByApp.php", datas).then(res => {
        var d = res;
        var names = [];
        d.forEach(ele => {
            names.push(ele.name)
        });
        if (names.indexOf(par.project) < 0) {
            createpro();
        } else {
            Win10_child.childLayer('实例名已存在!', 'This project name already exists!');
            return;
        }
    }).catch(err => {
        Win10_child.childLayer(err.message, err.message);
    })

    function createpro() {
        Win10_child._ajax("php/createProject.php", par).then(res => {
            var d = res;
            runpro(d);
        }).catch(err => {
            var em = err.message.split('|');
            Win10_child.childLayer(em[0], em[1]);
        })
    }
}

function runProject() {
    Win10_child.childLayer('正在运行,请稍后!', 'Running, please wait!');
    runpro(pPath);
}

function createOrUpdateAndRun(url, par) {
    project = (par.project == '' ? par.appName : par.project);
    $.ajax({
        type: "post",
        url: url,
        data: JSON.stringify(par),
        contentType: "application/json",
        dataType: "json",
        success: function(r) {
            Win10_child.childLayer('正在运行,请稍后!', 'Running, please wait!');
            if (r.code == "0") {
                vncid = r.data;
                var href = {};
                href.url = "./detail.php?id=" + r.data;
                href.title = "作业 " + r.data;
                Win10_child.open_new_windows(href, true);
                if (guiApp == 'true' || guiApp == true) {
                    interval = setInterval(function() {
                        openVnc();
                    }, 3000);
                } else {
                    Win10_child.getIndex();
                    Win10_child.close();
                }
            }

            if (r.code == "2005") {
                console.log(r)
                var href = {};
                href.url = "./detail.php?id=error&emsg=" +
                          encodeURIComponent(r.message);
                href.title = "作业递交错误";
                console.log(href)
                Win10_child.open_new_windows(href, true);
                Win10_child.close();
            }
        },
        error: function (err) {
            Win10_child.childLayer(err.message, err.message);
        }
    })
}

function runpro(params) {
    var parin = {
        "projectPath": params,
        "geometry": String(Math.floor(window.innerWidth * 19 / 20)) + "x" +
            String(Math.floor(window.innerHeight * 19 / 20) - 65)
    };
    Win10_child.open_new_windows(parin, false);
    var project = params.split(/\//).pop();
    project = project.split('.')[0];
    $.ajax({
        type: "post",
        url: "php/getProject.php",
        contentType: "application/json",
        data: JSON.stringify(parin),
        dataType: "json",
        success: function(d) {
            runp(d.data.gui);
        },
        error: function(e) {
            Win10_child.toLogin(e)
        }
    });

    function runp(gui) {
        $.ajax({
            type: "post",
            url: "php/runProject.php",
            data: JSON.stringify(parin),
            dataType: "json",
            success: function(r) {
                console.log(r)
                if (r.code == "0") {
                    vncid = r.data;
                    var href = {};
                    href.url = "./detail.php?id=" + r.data;
                    href.title = "作业 " + r.data;
                    Win10_child.open_new_windows(href, true);
                    if (gui) {
                        interval = setInterval(function() {
                            openVnc();
                        }, 3000);
                    } else if (gui == 'false') {
                        Win10_child.getIndex();
                        Win10_child.close();
                    }
                }

                if (r.code == "2005") {
                    console.log(r)
                    var href = {};
                    href.url = "./detail.php?id=error&emsg=" +
                      encodeURIComponent(r.message);
                    href.title = "作业递交错误";
                    console.log(href)
                    Win10_child.open_new_windows(href, true);
                    Win10_child.close();
                }
            },
            error: function(err) {
                Win10_child.toLogin(e)
            }
        });
    }
}
    function openVnc() {
        $.ajax({
            type: "post",
            url: "php/aip.php?action=queryJobVncUrl",
            data: JSON.stringify({
                "jobId": vncid
            }),
            dataType: "json",
            success: function(r) {
                urlc = vncurlConvert(r.data);
                if (urlc == 'no vnc url' || urlc == 'url wait') {
                    console.log(r.data);
                    if (urlc == 'no vnc url')
                        clearInterval(interval);
                } else if (urlc.indexOf("https") != -1) {
                    window.open(urlc)
                    clearInterval(interval);
                    Win10_child.close();
                } else {
                    Win10_child.openUrl(urlc, project + ' ' + vncid, [
                        ['95%', '95%'],
                        ['10px', '10px']
                    ]);
                    clearInterval(interval);
                    Win10_child.close();
                }
            },
            error: function(e) {
                Win10_child.toLogin(e)
            }
        });
    }
// }

// 弹窗
var back = document.getElementById("back");
var login = document.getElementById("login");

var numPerPage = 15;
var valPage = 1
var pagesize = numPerPage;
var pageNum = ""
var currentpage = 1
var files = [];
var currentFolder;
var forwardFolder;
var filedir;

function modalopen(id, fd) {
    //qiu
    fileLists= []
    localStorage.setItem('fileId', id);
    filedir = fd;
    getData();
    back.style.display = "block";
    login.style.display = "block";
}
//qiu
function modalbackANdDisplay() {
    
    displayfiles()
    back.style.display = "none";
    login.style.display = "none";
    valPage = 1
    pagesize = numPerPage;
    pageNum = ""
    currentpage = 1
    files = [];
    currentFolder = "";
    forwardFolder = "";
}
function displayfiles(){
    // var message = confirm('您选择了以下文件：'+fileLists)
    // if (message == true) return true
    // if (message == false) {
    //     localStorage.setItem('fileId', id);
    //     filedir = fd;
    //     getData();
    //     back.style.display = "block";
    //     login.style.display = "block";
    // }
    var modal = document.getElementById('myModal');
    var ok=document.getElementsByClassName("ok")[0];
    var no=document.getElementsByClassName("no")[0];
    modal.style.display = "block";
    ok.onclick=function(){
        modal.style.display = "none";
        fileString = String(fileLists)
        alert(fileString)
        return true
    }
    no.onclick=function(){
        fileLists = []
        getData();
        back.style.display = "block";
        login.style.display = "block";
        modal.style.display = "none";
    }
// 在用户点击其他地方时，重选
    window.onclick = function(event) {

        if (event.target == modal){
            fileLists = []
            getData();
            back.style.display = "block";
            login.style.display = "block";
            modal.style.display = "none";
        }
    }
    filename = document.getElementById('filelist');
            filename.innerHTML= ""
            fileLists.forEach(function(val){
                dlist = document.createElement("dt")
                dtext = document.createTextNode(val.substring(val.length-37,val.length))
                dlist.appendChild(dtext)
                filename.appendChild(dlist)
            })
 }

function modalback() {
    back.style.display = "none";
    login.style.display = "none";
    valPage = 1
    pagesize = numPerPage;
    pageNum = ""
    currentpage = 1
    files = [];
    currentFolder = "";
    forwardFolder = "";
}

function getData() {
    var par = {
        currentFolder: currentFolder,
        forwardFolder: forwardFolder,
        filter: filedir
    };

    Win10_child._ajax("php/getFiles.php", par).then(res => {
        var d = res;
        files = d.list;
        if (d.list.length % pagesize == 0) {
            pageNum = parseInt(d.list.length / pagesize);
        } else {
            pageNum = parseInt(d.list.length / pagesize) + 1;
        }
        currentFolder = d.currentFolder;
        dataForFile();
    }).catch(err => {
        Win10_child.childLayer(err.message, err.message);
    })
}

function dataForFile() {
    currentpage = '  ' + valPage + ' / ' + pageNum + '  ';
    if ((titlelen = currentFolder.length) > 50)
        title_name = '...' + currentFolder.substring(titlelen - 50, titlelen);
    else
        title_name = currentFolder;
    var str = '<span id="close_all" onclick="modalback()">×</span>' +
        '<span title="' + currentFolder + '">' + title_name + '</span>' +
        '<hr/>';
    str += '<table cellspacing=0 class="" style="bordercolor:#C0C0C0;" width="100%">'
    str += '<tr class="workbg">' +
        '<td width="10%">选择</td>' +
        '<td width="30%">文件名</td>' +
        '<td width="40%">修改时间</td>' +
        '<td width="20%">文件大小</td>' +
        ' </tr>';
    files.forEach((ele, i) => {
        var min = valPage * pagesize
        var max = valPage == 1 ? -1 : ((valPage - 1) * pagesize - 1)

        if (filedir.includes('/dir/') && ele.type != 'folder')
            return;
        var dn = ""
        if (filedir.includes('/file/') && ele.type == 'folder') {
            dn = 'display:none'
        }
        if (min > i && i > max) {
            namelen = ele.name.length;
            if (namelen > 45)
                display_name = ele.name.substring(0, 15) + '...' +
                       ele.name.substring(namelen - 25, namelen);
            else
                display_name = ele.name;
            str +=
                '<tr align="center">' +
                //qiu
                '<td ><input  style="' + dn + '" name="chooseFile" type="checkbox" onclick="checkit(this.checked,' + i + ', \'radio\')" value="" /></td>' +
                '<td class="filename"><a onclick="choosethis(' + i + ', \'path\')" class="' + ele.type + '" title="' +
                ele.name + '">' + display_name + '</a></td>' +
                '<td>' + ele.lastUpdateTime + '</td>' +
                '<td>' + ele.size + '</td>' +
                '</tr>'
        } else {

            return;
        }
    });
    str += '</table><hr/>' +
        '<div class="pagebutton">' +
        '<button class="ppage" onclick="pPageBtn()">上一页</button>' +
        '<span class="currentpage">' + currentpage + '</span>' +
        '<button class="npage" onclick="nPageBtn()">下一页</button>' +
        //qiu
        '<span class="currentpage">&nbsp&nbsp&nbsp</span>'+
        '<button class="npage" onclick="modalbackANdDisplay()">确定</button>' +
        '</div>'
    $(".fileList").html(str);
}

//qiu
function checkit(isChecked,i,act){
    if(isChecked){
        choosethis(i, act)
    }
    else
        notchoosethis(i, act)

}
function notchoosethis(i, act) {
    
    $(".buttoncreate").removeClass('disable-button');
    $(".buttoncreate").prop('disabled', false);
    disa = false;
    var fileid = localStorage.getItem('fileId');
    files.forEach((ele, index) => {
        if (i == index) {
            if (act == 'path' && ele.type == 'folder') {
                forwardFolder = ele.name;
                valPage = 1
                currentpage = 1
                getData();
            } else {

                fileLists.forEach(function(item,index,arr){
                    if(item === currentFolder + '/' + ele.name){
                        arr.splice(index, 1);
                    }
                })
            }
        }
    });
}
function choosethis(i, act) {
    $(".buttoncreate").removeClass('disable-button');
    $(".buttoncreate").prop('disabled', false);
    disa = false;
    var fileid = localStorage.getItem('fileId');
    files.forEach((ele, index) => {
        if (i == index) {
            if (act == 'path' && ele.type == 'folder') {
                forwardFolder = ele.name;
                valPage = 1
                currentpage = 1
                getData();
            } else {
                //qiu
                fileLists.push(currentFolder + '/' + ele.name)
                document.getElementById(fileid).value =
                    currentFolder + '/' + ele.name;
                document.getElementById(fileid).title =
                    currentFolder + '/' + ele.name;
                // modalback();
            }
        }
    });
}

function pPageBtn() {
    if (valPage > 1) {
        valPage--
    }
    dataForFile();
}

function nPageBtn() {
    if (valPage < pageNum) {
        valPage++
    }
    dataForFile();
}

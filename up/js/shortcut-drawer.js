//该插件提供桌面图标二级分类的支持
function getoutFromFolder(index) {
    var currentfolder = JSON.parse(localStorage.getItem("currentfolder"));
    var content;
    $.ajax({
        type: "post",
        url: "php/queryProjectByCategoryId.php",  // 根据二级分类查询实例图标，文件夹的形式（如果有）
        async: false,
        data: JSON.stringify({ categoryId: currentfolder.id }),
        dataType: "json",
        success: function(r) {
            if (r.code == 0) {
                var d = r.data;
                if (d) {
                    var str = "";
                    for (var i = 0; i < d.length; i++) {
                        str +=
                            '<div class="shortcut-drawer win10-ui-project-in-folder win10-ui-ap" data-path="' + d[i].yamlPath + '" draggable="true">' +
                            '<img class="icon" id="' + d[i].name + '" src="data:' + d[i].icon + '" draggable="false" />' +
                            '<img class="icon_quick" src="./img/skyform.ico" draggable="false" />' +
                            '<div class="title">' + d[i].name + '</div></div>';
                    }
                    $(".win10-drawer-box").html(str);
                    if (index) {
                        $(".layui-layer-content").html(str);
                    }
                    Win10.renderShortcuts();
                }
            } else {
                $(".win10-drawer-box").html('');
                layer.msg(Win10.lang('加载失败!', '对不起, 加载失败!'), { time: 1500 });
            }

        },
        error: function(e) {
            Win10.toLogin(e)
        }
    });
}
$(document).on('contextmenu', '.drawer', function(e) {
    console.log(e)
    e.preventDefault();
    e.stopPropagation();
});
Win10.onReady(function() {  // 定义实例图标打开以及右键属性
    var d = new Date();
    var previousTime = d.getTime();
    $('body').on('click', '.win10-drawer', function() {
        getoutFromFolder();

        var content = $(this).find('.win10-drawer-box').html();
        // console.log(content)
        var title = $(this).children('.title').html();
        var index = layer.open({
            type: 1,
            shadeClose: true,
            skin: 'drawer',
            area: [Win10.isSmallScreen() ? "50%" : "30%", "50%"],
            closeBtn: 0,
            title: title,
            content: content,
        });

        $(".layui-layer-shade").attr({ 'ondragover': 'allowDrop(event)', 'ondrop': 'dropout(event)' });

        Win10.setContextMenu('.shortcut-drawer.win10-ui-project-in-folder', [
            ['<i class="fa fa-fw fa-wrench"></i> ' + Win10.lang('属性', 'Attribute'), function(par) {  // 实例类

                layer.close(index);
                console.log(par.data.dataset.path)
                var currentpro = {}

                currentpro.url = "./form.php?projectPath=" + par.data.dataset.path;
                console.log(currentpro)
                Win10._open_new_windows(currentpro, true);
            }],
            ['<i class="fa fa-fw fa-play"></i> ' + Win10.lang('运行', 'Run'), function() {
                layer.close(index);
                Win10.runProject();
            }],
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('数据文件', 'Related Files'), function(par) {
                layer.close(index);
                var projPath = par.data.dataset.path;
                var base = projPath.substring(projPath.lastIndexOf('/') + 1);
                base = base.substring(0, base.lastIndexOf('.'));
                Win10.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                              [['80%','80%'], ['100px','100px']])
            }],
            ['<i class="fa fa-fw fa-list"></i> ' + Win10.lang('相关作业', 'Related Jobs'), function() {
                layer.close(index);
                localStorage.setItem("getWorksOfProject", "project");
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                Win10._open_new_windows(currentpro, false);
                Win10.openUrl('./works.php', '作业')
            }],
            ['<i class="fa fa-fw fa-times"></i>' + Win10.lang('杀掉相关作业','Kill Related Jobs'),
              function() {
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                kill_jobs(currentpro.projectPath, "project");
            }],
            ['<i class="fa fa-fw fa-trash"></i> ' + Win10.lang('删除', 'Delete project'), function() {
                layer.close(index);
                Win10.deletePtoject();
            }],
            '|', ['<i class="fa fa-fw fa-sign-out"></i> ' + Win10.lang('移出分组', 'Delete from folder'), function() {

                Win10.deleteFromFolder(index);
            }]
        ]);
        $(document).on('dblclick', '.win10-ui-project-in-folder', function(par) {
            var d1 = new Date();
            var currentTime = d1.getTime();
            layer.close(index);
            if (currentTime - previousTime < 500)
                return;
            var currentpro = JSON.parse(localStorage.getItem("currentpro"));
            currentpro.url = "./form.php?projectPath=" + currentpro.projectPat  // 双击进入详细页面
            Win10._open_new_windows(currentpro, true);
            // Win10.runProject();
            previousTime = currentTime;
        });
    })
});

function getoutFromAppCat(index) {  // 更多的桌面图标是从app.yaml配置里读取的
    var currentfolder = JSON.parse(localStorage.getItem("currentfolder"));
    var content;
    $.ajax({
        type: "post",
        url: "php/queryAppByCatId.php",
        async: false,
        data: JSON.stringify({ categoryId: currentfolder.id }),
        dataType: "json",
        success: function(r) {
            if (r.code == 0) {
                var d = r.data;
                console.log(d)
                if (d) {
                    var str = "";
                    for (var i = 0; i < d.length; i++) {
                        str +=
                            '<div class="shortcut-drawer win10-ui-app-in-cat win10-ui-ap" data-path="' + d[i].yamlPath + '" draggable="true">' +
                            '<img class="icon" id="' + d[i].name + '" src="data:' + d[i].icon + '" draggable="false" />' +
                            '<div class="title">' + d[i].name + '</div></div>';
                    }
                    $(".win10-drawer-box").html(str);
                    if (index) {
                        $(".layui-layer-content").html(str);
                    }
                    Win10.renderShortcuts();
                }
            } else {
                $(".win10-drawer-box").html('');
                layer.msg(Win10.lang('加载失败!', '对不起, 加载失败!'), { time: 1500 });
            }

        },
        error: function(e) {
            Win10.toLogin(e)
        }
    });
}

Win10.onReady(function() {  // 定义应用图标打开以及右键属性
    $('body').on('click', '.win10-appcat', function() {
        getoutFromAppCat();

        var content = $(this).find('.win10-drawer-box').html();
        // console.log(content)
        var title = $(this).children('.title').html();
        var index = layer.open({
            type: 1,
            shadeClose: true,
            skin: 'drawer',
            area: [Win10.isSmallScreen() ? "50%" : "30%", "50%"],
            closeBtn: 0,
            title: title,
            content: content,
        });

        $(".layui-layer-shade").attr({ 'ondragover': 'allowDrop(event)', 'ondrop': 'dropout(event)' });

        Win10.setContextMenu('.shortcut-drawer.win10-ui-app-in-cat', [
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('打开', 'New Instance'), function(par) {
                layer.close(index);
                var currentpro = {url: "./form.php"};
                Win10._open_new_windows(currentpro, true);
            }],
            ['<i class="fa fa-fw fa-list-ul"></i> ' + Win10.lang('相关实例', 'Related Instances'), function() {
                layer.close(index);
                localStorage.setItem("getProjectByApp", true);
                var currentpro = {
                    url: "./project.php"
                };
                Win10._open_new_windows(currentpro, true);
            }],
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('数据文件', 'Related Files'), function(par) {
                layer.close(index);
                var projPath = par.data.dataset.path;
                var base = projPath.substring(projPath.lastIndexOf('/') + 1);
                base = base.substring(0, base.lastIndexOf('.'));
                Win10.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                              [['80%','80%'], ['100px','100px']])
            }],
            ['<i class="fa fa-fw fa-list"></i> ' + Win10.lang('相关作业', 'Related Jobs'), function() {
                layer.close(index);
                localStorage.setItem("getWorksOfProject", "app");
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                Win10._open_new_windows(currentpro, false);
                Win10.openUrl('./works.php', '作业')
            }],
            ['<i class="fa fa-fw fa-times"></i>' + Win10.lang('杀掉相关作业','Kill Related Jobs'),
              function() {
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                kill_jobs(currentpro.projectPath, "app");
            }],
            ['<i class="fa fa-fw fa-trash"></i>' + Win10.lang('删除结束作业数据文件','Delete Related Job Data File'), function() {
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                delete_files(currentpro.projectPath, "app");
            }]
        ]);
        $(document).on('dblclick', '.win10-ui-app-in-cat', function(par) {  // 双击桌面应用图标，进入表单弹窗页面
            layer.close(index);
            var currentpro = {url: "./form.php"};  // 应用或实例表单接口
            Win10._open_new_windows(currentpro, true);
        });
    })
});

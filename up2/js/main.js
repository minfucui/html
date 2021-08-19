/*
 * By Skycloud Software. Copyright 2020
 *
 */
        Win10.getIndex();
        interval = setInterval("requestajax()", 5000);

        function requestajax() {
            $.ajax({
                type: "post",
                url: "php/queryEvent.php",
                contentType: "application/json",
                data: JSON.stringify({}),
                dataType: "json",
                success: function(d) {
                    if (d.data) {
                        d.data.forEach(ele => {
                            Win10.newMsg(ele.entity + ':' + ele.project + "  " + statusStrZh(ele.status), ele.time)

                        });

                    }

                },
                error: function(e) {
                    Win10.toLogin(e)
                }

            });
        }

        function statusStrZh(status) {
            var str;
            switch (status) {
                case 'NULL':
                    str = '无';
                    break;
                case 'WAIT':
                    str = '等待';
                    break;
                case 'WSTOP':
                    str = '等待时被停止';
                    break;
                case 'RUN':
                    str = '正在运行';
                    break;
                case 'SYSSTOP':
                    str = '被系统暂停';
                    break;
                case 'USRSTOP':
                    str = '运行中被停止';
                    break;
                case 'ZOMBIE':
                    str = '僵尸';
                    break;
                case 'EXIT':
                    str = '退出';
                    break;
                case 'FINISH':
                    str = '完成';
                    break;
                case 'UNKOWN':
                    str = '未知';
                    break;
                case 'ERROR':
                    str = '出错';
                    break;
                default:
                    str = '未知';
            }
            return str;
        }

        function toWorks() {
            // localStorage.setItem("getProjectByApp", false);
            localStorage.removeItem('getWorksOfProject');
            Win10.openUrl('./works.php', '作业');
        }

        function toProjects() {
            // localStorage.setItem("getWorksOfProject", false);
            localStorage.removeItem('getProjectByApp');
            Win10.openUrl('./project.php', '应用实例')
        }

        function addIntoFolder(id) {
            var currentpro = JSON.parse(localStorage.getItem("currentpro"));
            var par = {
                categoryId: id,
                projectPath: currentpro.projectPath
            };
            Win10._ajax("php/addProjectToProjectCategory.php", par).then(res => {
                Win10.getIndex();
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), {
                    time: 3000
                })
            })
        };

        function toLicenses() {
            Win10.openUrl('./licenses.html', '应用许可证')
        }

        function usage() {
            Win10.openUrl('./usage.html', '资源使用历史')
        }

        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drop(ev) {
            console.log(ev)
            console.log(ev.target.id)
            addIntoFolder(ev.target.id);
        }

        function dropout(ev) {
            Win10.deleteFromFolder();
        }

/*
 * By Skycloud Software. Copyright 2020
 *
 */
        var proList = [];
        var table;

        function createTable(tablejson) {
            var n = " - "
            $(document).ready(function() {
                table = $('#projectTables').DataTable({
                    "data": tablejson,
                    "columns": [{
                        "orderable": false,
                        "render": function(data, type, row, meta) {
                            return '<input type="checkbox" class="checkbox" name="checklist" value="' + row.yamlPath + '"/>'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return '<img class="iconimg" src="data:' + (row.icon ? row.icon : n) + '" />'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.name ? row.name : n
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return '<a style="color:rgb(0, 0, 238);text-decoration:underline;cursor:pointer;" rel="' + row.yamlPath + '" id="' + row.name + '" onclick="toProFiles(this)">打开目录</a>'
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return '<a style="color:rgb(0, 0, 238);text-decoration:underline;cursor:pointer;" rel="' + row.yamlPath + '" id="' + row.name + '" onclick="toProDetails(this)">查看</a>'
                        }

                    }],
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": true,
                    "pagingType": "simple_numbers",
                    "stateSave": true,
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
                    "fnDrawCallback": function(oSettings) {
                        $('input[type="checkbox"]').prop('checked', false);
                        $('#example-select-all').prop('checked', false);
                        proList = [];
                        delBtnStyle();
                        checkboxFn();
                    },
                });

                $('#example-select-all').change(function() {
                    var rows = table.rows({
                        'search': 'applied'
                    }).nodes();
                    if (this.checked) {
                        $('.checkbox').each(function() {
                            this.checked = true;
                            n = this.value;
                            if (proList.indexOf(n) < 0) {
                                proList.push(n)
                            }
                        });
                    } else {
                        $('.checkbox').each(function() {
                            this.checked = false;
                            proList = [];
                        });
                    }
                    delBtnStyle();
                });
            });
        }

        function checkboxFn() {
            $('.checkbox').click(function() {
                path = $(this).val();
                if ($(this).is(":checked")) {
                    if (proList.indexOf(path) < 0)
                        proList.push(path);

                    var isAllChecked = 0;
                    $('.checkbox').each(function() {
                        if (!this.checked)
                            isAllChecked = 1;
                    });
                    if (isAllChecked == 0)
                        $('#checklist').prop("checked", true);
                } else {
                    $('#checklist').prop("checked", false);
                    idx = proList.indexOf(path);
                    proList.splice(idx, 1);
                }
                delBtnStyle();
            });
        }

        function delBtnStyle() {
            if (proList.length == 0) {
                $(".delete").addClass('disable-button');
            } else {
                $(".delete").removeClass('disable-button');
            }
        }

        function toDel(params) {
            var parin = {
                "projectPath": proList.join(" ")
            };
            Win10_child.deletePtoject(parin);
        }

        var tablejson = [];

        var isCp = localStorage.getItem("getProjectByApp");
        var cp = isCp ? JSON.parse(localStorage.getItem("cp")) : '';
        var datas;
        if (isCp) {
            datas = {
                "appName": cp
            }
            ajRequest(datas);
        } else {
            datas = {};
            ajRequest(datas);
        }

        function ajRequest(datas) {
            Win10_child._ajax("php/getProjectByApp.php", datas).then(res => {
                tablejson = res;
                createTable(tablejson);
            }).catch(err => {
                Win10_child.childLayer(err.message, err.message);
            })
        }

        function toProFiles(par) {
            params = par.id;
            var projPath = document.getElementById(params).rel;
            if (params) {
                var base = projPath.substring(projPath.lastIndexOf('/') + 1);
                base = base.substring(0, base.lastIndexOf('.'));
                Win10_child.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                              [['80%','80%'], ['100px','100px']])
            }
        }

        function toProDetails(par) {
            params = par.id;
            var path = document.getElementById(params).rel;
            if (params) {
                var currentpro = {projectPath: path}; 
                localStorage.setItem("currentpro", JSON.stringify(currentpro));
                localStorage.setItem("cp", JSON.stringify(par.id));
                Win10_child.openUrl("./form.php?projectPath=" + $("#" + params).attr("name"), params);
            }
        }

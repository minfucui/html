/*
 * By Skycloud Software. Copyright 2020
 *
 */

        ajRequest()

        currency = '￥';
        function createSummaryTable(tablejson) {
            var n = " - "
            $(document).ready(function() {
                table = $('#summaryTable').DataTable({
                    "data": tablejson,
                    "columns": [{
                        "render": function(data, type, row, meta) {
                            return row.resource
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.unit
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return currency + row.unitPrice.toFixed(2)
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.amount.toFixed(4)
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return currency + row.cost.toFixed(2)
                        }
                    }],
                    "paging": false,
                    "ordering": false,
                    "info": false,
                    "searching": false,
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
            });
        }
        function createDetailTable(tablejson) {
            $(document).ready(function() {
                table = $('#detailTable').DataTable({
                    "data": tablejson,
                    "columns": [{
                        "render": function(data, type, row, meta) {
                            if (row.StartDate)
                                return row.StartDate;
                            else
                                return row.EndDate;
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.User
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.App
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return '<a href="javascript:Win10_child.openUrl(\'./hist.html?id=' +
                              row.JobId + '\',\'作业 ' + row.JobId +
                              '\');">' + row.JobId + '</a>';
                        }
                    }, {
                    /*    "render": function(data, type, row, meta) {
                            if (row.runTime)
                                return row.runTime.toFixed(4);
                            else
                                return row.Runtime.toFixed(4)
                        }
                    }, { */
                        "render": function(data, type, row, meta) {
                            return row.CPU_Hours.toFixed(4)
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.Mem_GB_Hours.toFixed(4)
                        }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.GPU_Hours.toFixed(4)
                       }
                    }, {
                        "render": function(data, type, row, meta) {
                            return row.Queue
                       }
                    }, {
                        "render": function(data, type, row, meta) {
                            return currency + row.App_Cost.toFixed(2)
                       }
                    }, {
                        "render": function(data, type, row, meta) {
                            totalCost = row.GPU_Cost + row.CPU_Cost + row.Mem_Cost + row.App_Cost;
                            return currency + totalCost.toFixed(2)
                       }
                    }],
                    "paging": true,
                    "ordering": true,
                    "order": [[0, "desc"],[3, "desc"]],
                    "info": true,
                    "searching": true,
                    "pagingType": "simple_numbers",
                    "stateSave": true,
                    "lengthChange": true,
                    "autoWidth": false,
                    "language": {
                        "sDecimal": "",
                        "sEmptyTable": "表中数据为空",
                        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项"
,
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
            });
        }


        function ajRequest() {
            var par = JSON.parse(localStorage.getItem("currentpro"));
            Win10_child._ajax("php/getDetailUsage.php", par).then(res => {
                createSummaryTable(res.summary);
                createDetailTable(res.detail);
                $('#total').html(res.Total_Cost.toFixed(2));
            }).catch(err => {
                Win10_child.childLayer(err.message, err.message);
            })
        }

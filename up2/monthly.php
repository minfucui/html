<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>月资源使用详单</title>

    <link href="./css/main.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/datatables.min.css">
    <script type="text/javascript" src="./js/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="./js/child.js"></script>
    <script src="./js/datatables.min.js"></script>
</head>

<body>

    <div class="content-wrapper">
        <section class="content">
            <div class="row">
                <h3>月用量总和</h3>
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-body">
                            
                            <table class="cell-boader compact stripe" style="text-align: center;" id="summaryTable">
                                <thead>
                                    <tr>
                                        <th>资源</th>
                                        <th>单位</th>
                                        <th>单价</th>
                                        <th>数量</th>
                                        <th>小计</th>
                                    </tr>
                                </thead>
                                <tbody id="list">
                                </tbody>
                            </table>
                            月总额：￥<span id="total">
                        </div>
                    </div>
                </div>
                <h3>详单</h3>
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-body">

                            <table class="cell-boader compact stripe" style="text-align: center;" id="detailTable">
                                <thead>
                                    <tr>
                                        <th>作业开始</th>
                                        <th>用户</th>
                                        <th>应用</th>
                                        <th>作业号</th>
                                        <th>CPU核小时</th>
                                        <th>内存GB小时</th>
                                        <th>GPU小时</th>
                                        <th>队列</th>
                                        <th>应用价格</th>
                                        <th>小计</th>
                                    </tr>
                                </thead>
                                <tbody id="list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript" src="./js/monthly.js?filever=<?=filesize('js/monthly.js')?>"></script>
</body>

</html>

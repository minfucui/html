<?php
include 'check_session.php';
include 'filefunctions.php';

if (!isset($_POST['month'])) {
    echo $error_return;
    die();
}

$billfile = $cmdpath."/bills/".$uname."/bill-".$_POST['month'];  // 查询指定月份的详细费用，用于资源使用历史点击月份的跳转查询接口
$ret = [];
$ret['summary'] = [];
$ret['detail'] = [];

if (file_exists($billfile)) {
    $out = rFile_Get_Contents($billfile);
    $bill = json_decode($out, true);
    if (!isset($bill['CPU_Cost']))
        $bill['CPU_Cost'] = 0;
    if (!isset($bill['Mem_Cost']))
        $bill['Mem_Cost'] = 0;
    if (!isset($bill['GPU_Cost']))
        $bill['GPU_Cost'] = 0;
    $up = $bill['CPU_Hours'] == 0 ? 0 : ($bill['CPU_Cost'] / $bill['CPU_Hours']);
    $ret['summary'][] = ["resource"=>"CPU",
                         "unit"=>"核小时",
                         "amount"=>$bill['CPU_Hours'],
                         "unitPrice"=>$up,
                         "cost"=>(isset($bill['CPU_Cost']) ? $bill['CPU_Cost'] : 0)];
    $up = $bill['Mem_GB_Hours'] == 0 ? 0 : ($bill['Mem_Cost'] / $bill['Mem_GB_Hours']);
    $ret['summary'][] = ["resource"=>"内存",
                         "unit"=>"GB小时",
                         "amount"=>$bill['Mem_GB_Hours'],
                         "unitPrice"=>$up,
                         "cost"=>(isset($bill['Mem_Cost']) ? $bill['Mem_Cost'] : 0)];
    $up = $bill['GPU_Hours'] == 0 ? 0 : ($bill['GPU_Cost'] / $bill['GPU_Hours']);
    $ret['summary'][] = ["resource"=>"GPU",
                         "unit"=>"GPU小时",
                         "amount"=>$bill['GPU_Hours'],
                         "unitPrice"=>$up,
                         "cost"=>(isset($bill['GPU_Cost']) ? $bill['GPU_Cost'] : 0)];
    foreach ($bill['App_Hours'] as $key=>$app) {
        if (isset($app['Hours']))
            $ret['summary'][] = ["resource"=>$key,
                         "unit"=>"核小时",
                         "amount"=>$app['Hours'],
                         "unitPrice"=>(isset($app['UnitPrice']) ? $app['UnitPrice'] : 0),
                         "cost"=>$app['Cost']];
        else
            $ret['summary'][] = ["resource"=>$key,
                         "unit"=>"核小时",
                         "amount"=>$app, "unitPrice"=>0, "cost"=>0];
    }
    for ($i = 0; $i < sizeof($bill['Details']); $i++) {
         if (!isset($bill['Details'][$i]['CPU_Cost']))
             $bill['Details'][$i]['CPU_Cost'] = $bill['Details'][$i]['GPU_Cost'] =
                 $bill['Details'][$i]['Mem_Cost'] = $bill['Details'][$i]['App_Cost'] = 0;
         if (!isset($bill['Details'][$i]['App']))
             $bill['Details'][$i]['App'] = '';
    }
    $ret['detail'] = $bill['Details'];
    if (!isset($bill['Total_Cost']))
        $ret['Total_Cost'] = 0;
    else
        $ret['Total_Cost'] = $bill['Total_Cost'];
}

echo '{"code":"0","message":"call service success","data":'.json_encode($ret).'}';
?>

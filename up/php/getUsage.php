<?php
include 'check_session.php';
include 'db.php';  // 数据库操作接口
include 'filefunctions.php';

setTimeZone();
$currentmonth = date("m");
$currentyear = date("Y");
$updated = FALSE;

$user = " -u ".$uname;
$billpath = $cmdpath."/bills/".$uname;

$o = shell_exec($cmdPrefix." cube");
$cluster = json_decode($o, TRUE);
if ($cluster === FALSE || sizeof($cluster) == 0) {
    echo $error_return;
    die();
}

$cmdPrefix = 'export OLWD='.$pword.';source ../../env.sh;../../cmd/runas '.$uname;


function price($name, $timestamp) {
    if (!isset($_SESSION['op_control']))
        return 0;
    $db = searchRowsOrder('prices', ['last_update'=>'DESC'], ['chargename'=>$name]);  // 从价格表中查询该用户的折扣价格
    if (isset($db['error'])) {
        error_log($db['error']);
        return 0;
    }
    $price = 0;
    foreach ($db as $p) {
        if ($p['last_update'] <= $timestamp) {
            $price = $p['unitprice'];  // 单位价格（核时）
            break;
        }
    }
    return $price;
}

function genbill($y, $m, $uname, $user) {  // 生成指定年月的费用

    global $cmdPrefix, $billpath, $updated;
    global $currentmonth, $currentyear, $_SESSION;

    $ymformat = sprintf("%4d-%02d", $y, $m);

    $billfilename = $billpath."/bill-".$ymformat;

    $now = time();

    $starttime = sprintf("%4d-%02d-01 00:00:00", $y, $m);

    if (file_exists($billfilename) === FALSE ||
        ($currentmonth == $m && $currentyear == $y &&
         $now - filemtime($billfilename) > 3600)) {
        $out = shell_exec($cmdPrefix.
                  " aipbills ".$user." -j -m ".$ymformat);
        $output = json_decode($out, TRUE);
        $appprices = [];        
        if (isset($_SESSION['op_control'])) {
            $userdb = searchRows('users', ['username'=>$user]);
            if (sizeof($userdb) == 0 || isset($userdb['error']) || $userdb[0]['discount'] == NULL)
                $discount = 1;
            else
                $discount = $userdb[0]['discount'];
        }
        else
            $discount = 0;
        $cpuprice = price('CPU', $starttime) * $discount;
        $memprice = price('内存', $starttime) * $discount;
        $gpuprice = price('GPU', $starttime) * $discount;
        $appCost = 0;
        if (sizeof($output['App_Hours']) > 0)
            foreach ($output['App_Hours'] as $k=>$a) {
                $unitprice = price($k, $starttime) * $discount;
                $appprices[$k] = ['Hours'=>$a, 'UnitPrice'=>$unitprice,
                                  'Cost'=>$a * $unitprice];
                $appCost += $appprices[$k]['Cost'];
            }
        $output['App_Hours'] = $appprices;
        $output['App_Cost'] = $appCost;
        $output['CPU_Cost'] = $cpuprice * $output['CPU_Hours'];
        $output['Mem_Cost'] = $memprice * $output['Mem_GB_Hours'];
        $output['GPU_Cost'] = $gpuprice * $output['GPU_Hours'];
        $output['Total_Cost'] = $output['App_Cost'] + $output['CPU_Cost'] +
                                $output['Mem_Cost'] + $output['GPU_Cost'];
        for ($i = 0; $i < sizeof($output['Details']); $i++) {
            $output['Details'][$i]['GPU_Cost'] =
                 $output['Details'][$i]['GPU_Hours'] * $gpuprice;
            $output['Details'][$i]['CPU_Cost'] =
                 $output['Details'][$i]['CPU_Hours'] * $cpuprice;
            $output['Details'][$i]['Mem_Cost'] =
                 $output['Details'][$i]['Mem_GB_Hours'] * $memprice;
            if (isset($output['App_Hours'][$output['Details'][$i]['App']]))
                $appP = $output['App_Hours'][$output['Details'][$i]['App']]['UnitPrice'];
            else
                $appP = 0;
            $output['Details'][$i]['App_Cost'] =
                 $output['Details'][$i]['CPU_Hours'] * $appP;
        }

        file_put_contents($billfilename, json_encode($output));
        $updated = TRUE;
    }
}

function previousmonth($y, $m, $prev)
{
    $l = $y * 12 + $m - 1;
    $l -= $prev;
    return [intval($l / 12), $l % 12 + 1];
}

if (!file_exists($billpath))
    mkdir($billpath);

/* for ($i = 0; $i < 6; $i++) {
    $m = previousmonth ($currentyear, $currentmonth, $i);
    if (!isset($_SESSION['op_control']))
        genbill($m[0], $m[1], $uname, $user);
} 
if ($updated) {
    flush();
    usleep(500000);
} */
$usages = [];
if (($files = scandir($billpath)) != FALSE) {
    foreach ($files as $file) {
        if (strpos($file, "bill") === FALSE)
            continue;
        
        $billcontent = rFile_Get_Contents($billpath.'/'.$file);
        $bill = json_decode($billcontent, TRUE);
        if ($bill !== FALSE && sizeof($bill) > 0) {
            unset($bill['Details']);
            unset($bill['App_Hours']);
            if (!isset($bill['CPU_Cost']))
                $bill['CPU_Cost'] = $bill['GPU_Cost'] = $bill['Mem_Cost'] =
                    $bill['App_Cost'] = $bill['Total_Cost'] = 0;
            $usages[] = $bill;
        }
    }
}
echo '{"code":"0","message":"call service success","data":'.json_encode($usages).'}';
?>

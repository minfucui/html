<?php
include 'common1.php';
include 'billfunc.php';

$lang = $_SESSION['lang'];

function updateData($uname)
{
    global $cmdpath;
    $billpath = $cmdpath.'/bills/'.$uname;
    if (!file_exists($billpath)) {
        mkdir($billpath, 0700);
    }
    $billsummary = [];
    $files = scandir($cmdpath.'/bills/'.$uname);
    if ($files === FALSE)
        fail('Cannot access bill directory');
    foreach ($files as $file) {
        if (strpos($file, "bill") === FALSE) {
            continue;
        }
        $billcontent = file_get_contents($billpath.'/'.$file);
        if ($billcontent === FALSE)
            fail("Cannot read file ".$billpath.'/'.$file);
        $bill = json_decode($billcontent, TRUE);
        $apphours = 0;
        if (sizeof($bill['App_Hours']) > 0)
            foreach ($bill['App_Hours'] as $key=>$a) {
                if (isset($a['Hours']))
                    $apphours = $apphours + $a['Hours'];
                else
                    $apphours = $apphours + $a;
            }
        $billsummary[] = ['Month'=>$bill['Month'].' php/mbill.php?file='.
                          urlencode($billpath.'/'.$file),
                         'username'=>$uname,
                         'CPU_Hours'=>number_format($bill['CPU_Hours'], 4),
                         'CPU_Cost'=>'￥'.(isset($bill['CPU_Cost']) ? number_format($bill['CPU_Cost'], 2):'0.00'),
                         'GPU_Hours'=>number_format($bill['GPU_Hours'], 4),
                         'GPU_Cost'=>'￥'.(isset($bill['GPU_Cost'])?number_format($bill['GPU_Cost'], 2):'0.00'),
                         'Mem_GB_Hours'=>number_format($bill['Mem_GB_Hours'], 4),
                         'Mem_Cost'=>'￥'.(isset($bill['Mem_Cost'])?number_format($bill['Mem_Cost'], 2):'0.00'),
                         'App_Hours'=>number_format($apphours, 4),
                         'App_Cost'=>'￥'.(isset($bill['App_Cost'])?number_format($bill['App_Cost'], 2):'0.00'),
                         'Total_Cost'=>'￥'.(isset($bill['Total_Cost'])?number_format($bill['Total_Cost'], 2):'0.00')];
    }
    return $billsummary;
}

function updateDataAll()
{
    global $cmdpath, $uname;
    $billpath = $cmdpath.'/bills';
    if (!file_exists($billpath.'/'.$uname))
        mkdir($billpath.'/'.$uname, 0700);
    $billsummary = [];
    $files = scandir($billpath);
    foreach($files as $file)
        if ($file[0] != '.') {
            $res = updateData($file);
            $billsummary = array_merge($billsummary, $res);
        }
    return $billsummary;
}

function genbill($y, $m, $user, $regen)
{
    global $cmdpath, $cmdPrefix, $uname;
    $ymformat = sprintf("%4d-%02d", $y, $m);
    $filepath = $cmdpath.'/bills/'.$user."/bill-".$ymformat;
    if (!file_exists($cmdpath.'/bills/'.$user))
        mkdir($cmdpath.'/bills/'.$user);

    $now = time();
    $starttime = sprintf("%4d-%02d-01 00:00:00", $y, $m);

    if ($regen || !file_exists($filepath)) {
        $out = shell_exec('export OLWD=A1uy3LpGhy;source '.
               $cmdpath.'/env.sh;'.$cmdpath.
               '/cmd/runas root aipbills -u '.$user." -j -m ".$ymformat);
        $output = json_decode($out, TRUE);
        $bill = calcCost($output['Details'], $y, $m);
        $bill['User'] = $user;

        file_put_contents($filepath, json_encode($bill));
    }
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./bills.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $lang = $_SESSION['lang'];
        if ($_SESSION['roles'][0] == $lang['USER'] ||
            $_SESSION['roles'][0] == $lang['GROUP_ADMIN'])
            $result = updateData($uname);
        else {
            $result = updateDataAll();
        }
        $page['rows'][0]['table']['data'] = $result;
        $ret['data'] = $page;
        break;

    case 'runbills':
        $currentmonth = date("m");
        $currentyear = date("Y");
        $users = [$uname];
        foreach ($_SESSION['roles'] as $r)
            if ($r == $lang['ADMIN']) {
                $uindb = searchRows('users');
                if (isset($uindb['error']) || sizeof($uindb) == 0)
                    break;
                $users = [];
                foreach ($uindb as $u)
                    $users[] = $u['username'];
                break;
            }
        for ($i = 0; $i < 6; $i++) {
            $m = previousmonth ($currentyear, $currentmonth, $i);
            foreach ($users as $u)
                genbill($m[0], $m[1], $u, $i == 0);
        }
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

<?php
include 'check_session.php';
include 'subfunc.php';  // 没用到

exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $er);  // 执行命令
$aipenv = $r[0];
$crondconf = $aipenv.'/cbcrond.yaml';  // 调度配置文件

if (!file_exists($crondconf))
    fail("cbcrond.yaml does not exist.\n");

$conf = yaml_parse_file($crondconf);
if ($conf === FALSE)
    fail("Invalid cbcrond.yaml.\n");
$qprices = [];
foreach ($conf['charge']['standard']['cpuperhour'] as $q)
    $qprices[$q['queue']] = $q['price'];

$cpu = $qprices['all'];

if (isset($_SESSION['config']['app_access']))
    $app_access = $_SESSION['config']['app_access'];

$appprices = [];
foreach ($conf['charge']['standard']['appperhour'] as $a) {
    if (isset($app_access) && isset($app_access[$a['app']])) {
        if (!in_array($_SESSION['uname'], $app_access[$a['app']]))
            continue;
    }
    $appprices[$a['app']] = $a['price'];
}

$qprices = [];
foreach ($conf['charge']['standard']['cpuperhour'] as $q)
    $qprices[$q['queue']] = $q['price'];

$cpu = $qprices['all'];
$gpu = $conf['charge']['standard']['gpuperhour'][0]['price'];
$mem = $conf['charge']['standard']['mempergbhour'][0]['price'];

$myd = [];
foreach ($conf['charge']['userdiscounts'] as $u) {
    if ($u['user'] == $uname) {
        $myd['cpu'] = isset($u['cpu']) ? $u['cpu'] : 1;
        $myd['gpu'] = isset($u['gpu']) ? $u['gpu'] : 1;
        $myd['mem'] = isset($u['mem']) ? $u['mem'] : 1;
        if (sizeof($u['apps']) > 0)
            foreach ($u['apps'] as $a)
               $myd[$a['app']] = $a['rate'];
        break;
    }
}

$p = [];
$p[] = ['product'=>'CPU','type'=>'硬件','unit'=>'核小时','sPrice'=>number_format($cpu, 2),
        'mPrice'=>number_format(isset($myd['cpu']) ? $cpu * $myd['cpu'] : $cpu, 2)];
$p[] = ['product'=>'内存','type'=>'硬件','unit'=>'GB小时','sPrice'=>number_format($mem, 2),
        'mPrice'=>number_format(isset($myd['mem']) ? ($mem * $myd['mem']) : $mem, 2)];
$p[] = ['product'=>'GPU','type'=>'硬件','unit'=>'个小时','sPrice'=>number_format($gpu, 2),
        'mPrice'=>number_format(isset($myd['gpu']) ? ($gpu * $myd['gpu']) : $gpu, 2)];
foreach ($qprices as $q=>$price) {
    if ($q != 'all') {
        $qp = $price - $cpu;
        $p[] = ['product'=>$q,'type'=>'队列附加','unit'=>'核小时','sPrice'=>number_format($qp, 2),
                'mPrice'=>number_format(isset($myd['cpu']) ? ($qp * $myd['cpu']) : $qp, 2)];
    }
}
foreach ($appprices as $a=>$price)
    $p[] = ['product'=>$a,'type'=>'应用许可','unit'=>'核小时','sPrice'=>number_format($price, 2),
            'mPrice'=>number_format(isset($myd[$a]) ? ($price * $myd[$a]) : $price, 2)];

echo '{"code":"0","message":"call service success","data":'.json_encode($p).'}';
?>

<?php
include 'check_session.php';
$out = shell_exec($cmdPrefix.' aip q i -l');  // 查找所有队列
exec($cmdPrefix." cparams | head -1 | awk '{print $3}'", $r, $errno);  // 查找限制使用的队列
if ($errno == 0) {
    $defq = $r[0];
    $qs = [['Name'=>$defq]];  // 数组赋值
} else {
    $defq = '';
    $qs = [];
}
$queues = json_decode($out, TRUE);
foreach ($queues as $queue) {  // 先筛查一遍，保留可选队列
    if (strpos($queue['Status'], 'Closed') === FALSE &&
        $queue['Name'] != $defq) {
        $q['Name'] = $queue['Name'];
        $qs[] = $q;
    }
}

for ($i = 0; $i < sizeof($qs); $i++) {  // 然后挨个查询可用核数
    foreach ($queues as $q)
        if ($q['Name'] == $qs[$i]['Name']) {
            $qs[$i]['AvailSlots'] = $q['AvailSlots'];
            break;
        }
}

header('Content-Type: application/json');
echo '{"code":0,"message":"call service success","data":'.json_encode($qs).'}';
?>

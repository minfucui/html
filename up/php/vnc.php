<?php
include 'check_session.php';
include 'filefunctions.php';  // 主要是一些命令

if (!isset($_GET['jobid']) || !isset($_GET['pw'])) {
    echo $error_return;
    error_log("Did not get job id or pw");
    die();
}

$jobid = $_GET['jobid'];
$password = $_GET['pw'];

#$r = vncurl($jobid, $password, $uname);
exec($cmdPrefix.' vncurl '.$jobid.' '.$password.' '.$uname, $r, $er);  // 显示作业的gui界面，返回界面url地址，$cmdPrefix全局变量配置在check_session.php里

# error_log("====".implode(";", $r));

if ($er != 0)
    echo '{"code":'.$er.',"message":"vncurl execution error:'.$r[0].'"}';
else {
    $ret = json_decode($r[0], true);
    $ret['data'] = $ret['url'];
    unset($ret['url']);
    echo json_encode($ret);
}
?>

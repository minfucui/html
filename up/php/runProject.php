<?php
include 'check_session.php';
include 'jobsdata.php';
include 'filefunctions.php';
include 'accountbalance.php';  // 账户余额接口

if (!isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}

if (isset($_SESSION['op_control']) && !balanceOK()) {  // 是否有条件控制，即运行作业的条件
    echo '{"code":"2005","message":"Low account balance"}'."\n";
    die();
}

if ($_SESSION['gui_limit'] == 1) {  // 判断有没有正在运行的gui界面的作业，有的话返回作业号，不提交新的作业
    $projData = myFile_Get_Contents($_POST['projectPath']);
    if ($projData === FALSE) {
        echo $error_return;
        die();
    }
    $projSpec = yaml_parse($projData);
    $projName = $projSpec['cluster_params']['instance'];
    if ($projSpec['gui'] === TRUE) {
        $jobId = runningProject($projName, $uname);  // 查找作业号，而不是生成作业号
        if ($jobId !== FALSE) {
            echo '{"code":"0","message":"call service success","data":'.$jobId.'}';
            die();
        }
    }
}

exec($cmdPrefix.' cbtool a c -f '.$_POST['projectPath'], $r, $errno);
if ($errno != 0)
{
    echo '{"code":"2005","message":"cbtool fails - '.$r[0].'"}';
    die();
}

if ($r[0][0] == '{')  // JSON submission
    $cmd = $cmdPrefix." aip j r '".$r[0]."'";
else {
    if (strpos($r[0], 'vncsub') !== FALSE &&
        isset($_POST['geometry'])) {
        $r[0] = str_replace('vncsub',
                            'vncsub -geometry '.$_POST['geometry'],
                            $r[0]);
    }
    $r[0] = str_replace('$HOME', $_SESSION['home'], $r[0]);
    $cmd = $cmdPrefix.' '.$r[0];
}

$r = [];
// trigger_error('########'.$cmd);
exec($cmd, $r, $errno);  // 提交作业

header('Content-Type: application/json');
if ($errno != 0)
    echo '{"code":"2005","message":"'.implode(';',$r).'"}';
else {
    $ids = explode(' ', $r[0]);
    if (!is_numeric($ids[1]))
        echo '{"code":"2005","message":"'.implode(';',$r).'"}';
    else
        echo '{"code":"0","message":"call service success","data":'.$ids[1].'}';
}
?>

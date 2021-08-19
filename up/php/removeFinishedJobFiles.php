<?php
include 'check_session.php';
include 'filefunctions.php';
include 'jobsdata.php';

if (!isset($_POST['app'])) {
    echo $error_return;
    die();
}
// 删除实例或应用的已完成的作业接口
function delete_directory($dirname) {
    $files = myScandir($dirname);
    if (!$files)
        return false;
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (!myIs_Dir($dirname."/".$file))
                myUnlink($dirname."/".$file);
            else
                delete_directory($dirname.'/'.$file);
        }
    }
    myRmdir($dirname);
    return true;
}

$aj = allJobData($uname);

$nd = [];

foreach ($aj as $j) {
    if ($j['statusString'] == 'FINISH' ||
        $j['statusString'] == 'EXIT')
        continue;
    if (!isset($j['jobSpec']['application']) ||
        $j['jobSpec']['application'] != $_POST['app'])
        continue;
    if (isset($j['jobSpec']['cwd'])) {
        if (strpos($j['jobSpec']['cwd'], '/') !== 0)
            $j['jobSpec']['cwd'] = $_SESSION['home'].'/'.$j['jobSpec']['cwd'];
        $nd[] = $j['jobSpec']['cwd'];
    }
}

$path = $_SESSION['data_home'].'/jobdata/default';

if (($files = myScandir($path)) === FALSE)
    die('{"code":0,"message":"successful","data":"success"}');


foreach ($files as $d) {
    if (strpos($d, $_POST['app']/*.'-'*/) !== 0)
        continue;
    if (in_array($path.'/'.$d, $nd))
        continue;
    delete_directory($path.'/'.$d);
}
header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":"success"}';
?>

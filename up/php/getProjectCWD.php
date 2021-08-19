<?php
include 'check_session.php';
include 'filefunctions.php';
include 'projectFunctions.php';

if (isset($_POST['dir'])) {  // 获取用户个人的数据地址
    $cwd = getProjectCWD(urldecode($_POST['dir']));
    if ($cwd === FALSE)
        $cwd = $_SESSION['data_home'];
} else
    $cwd = $_SESSION['data_home'];

header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":"'.$cwd.'"}';
?>

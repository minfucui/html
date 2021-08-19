<?php
include 'check_session.php';
include 'folders.php';
if (!isset($_POST['name'])) {
    echo $error_return;
    die();
}
header('Content-Type: application/json');
if (newFolder($_POST['name'], $uname) === FALSE) {  // 用文件管理系统，新建一个分组文件夹
    echo '{"code":401,"message":"failed"}';
} else {
    echo '{"code":0,"message":"successful","data":"success"}';
}
?>

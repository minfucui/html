<?php
include 'check_session.php';
include 'folders.php';
if (!isset($_POST['ids'])) {
    echo $error_return;
    die();
}

header('Content-Type: application/json');
if (deleteFolder($_POST['ids'], $uname) === FALSE) {  // 删除实例分组文件夹
    echo '{"code":401,"message":"failed"}';
} else {
    echo '{"code":0,"message":"successful","data":"success"}';
}
?>

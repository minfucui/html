<?php
include 'check_session.php';
include 'folders.php';
if (!isset($_POST['name']) || !isset($_POST['id'])) {
    echo $error_return;
    die();
}
header('Content-Type: application/json');
if (renameFolder($_POST['id'], $_POST['name'], $uname) === FALSE) {  // 重命名文件夹
    echo '{"code":401,"message":"failed"}';
} else {
    echo '{"code":0,"message":"successful","data":"success"}';
}
?>

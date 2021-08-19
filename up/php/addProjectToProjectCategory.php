<?php
include 'check_session.php';
include 'folders.php';
if (!isset($_POST['categoryId']) || !isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}

header('Content-Type: application/json');
if (addProjToFolder($_POST['categoryId'], $uname, $_POST['projectPath']) === FALSE) {  // 文件接口里的方法，添加实例到目录中，写入folder.json
    echo '{"code":401,"message":"failed"}';
} else {
    echo '{"code":0,"message":"successful","data":"success"}';
}
?>

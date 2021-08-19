<?php
include 'check_session.php';
include 'folders.php';
if (!isset($_POST['categoryId']) || !isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}
header('Content-Type: application/json');
if (deleteProjFromFolder($_POST['categoryId'], $uname, $_POST['projectPath']) === FALSE) {  // 从实例分组文件夹中删除实例，好像没有删除实例的相关作业，待测
    echo '{"code":401,"message":"failed"}';
} else {
    echo '{"code":0,"message":"successful","data":"success"}';
}
?>

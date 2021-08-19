<?php
include 'check_session.php';
if (!isset($_POST['appName']) || $_POST['appName'] == '')
    $_GET['appName'] = 'all';
else
    $_GET['appName'] = $_POST['appName'];
include 'queryAppAndProject.php';  // 真正的接口php，应用和实例一起查询
?>

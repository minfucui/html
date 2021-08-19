<?php
session_start();
include 'db.php';
include 'log.php';
skylog_activity($_SESSION['uname'], WEB_ACCESS, LOGOUT, ''); 
echo '{"code":0, "message": "succeful"}';
session_destroy();
session_start();
?>

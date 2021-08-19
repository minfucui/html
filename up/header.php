<?php
session_start();  // 启动seesion，并判断是否有登录信息，没有就重定向到index登录界面
if (!isset($_SESSION['login']) || $_SESSION['login'] == '') {
    header ("Location: index.php");
    die();
}
?>

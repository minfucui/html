<?php
include 'check_session.php';

setTimeZone();
if (!isset($_POST['action'])) {
    fail('no account info or bad request');
}
$ret = ['code'=>0, "message"=>"success"];

switch($_POST['action']) {
case 'support':  // 请求平台技术支持
    if (!isset($_POST['data']) || $_POST['data']['creator'] != $uname ||
        !isset($_SESSION['op_control']))
        fail('wrong request');
    include 'db.php';
    $_POST['data']['create_time'] = date('Y-m-d H:i:s', time());
    $res = insertARow('supportorders', $_POST['data']);  // 插入技术支持请求表中
    if (isset($res['error']))
        fail($res['error']);
    break;
default:
    fail('wrong request');
}
echo json_encode($ret);
?>

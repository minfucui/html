<?php
include 'check_session.php';

setTimeZone();
if (!isset($_POST['action'])) {
    fail('no account info or bad request');
}
$ret = ['code'=>0, "message"=>"success"];
$data = ['username'=>$uname];

switch($_POST['action']) {  // 账户信息管理，不同的动作代表不同的请求
case 'info':
    if (isset($_SESSION['op_control'])) {
        $data['op_control'] = 1;  // 权限控制
        include 'db.php';
        $users = searchRows('users', ['username'=>$uname]);  // 查看我的账户信息，这些是从数据库中读出来的，包括余额
        if (sizeof($users) != 0 && !isset($users['error'])) {
           $data['name'] = $users[0]['name'];
           $data['phone'] = $users[0]['phone'];
           $data['email'] = $users[0]['email'];
           $data['balance'] = number_format($users[0]['balance'], 2);
        }
    } else
        $data['op_control'] = 0;
    $ret['data'] = $data;
    break;
case 'change':  // 变更
    if (!isset($_POST['data']) || $_POST['data']['username'] != $uname ||
        !isset($_SESSION['op_control']))
        fail('wrong request');
    unset($_POST['data']['username']);
    include 'db.php';
    $res = modifyARow('users', ['username'=>$uname], $_POST['data']);
    if (isset($res['error']))
        fail($res['error']);
    break;
case 'charge':  // 充值、缴费
    if (!isset($_POST['data']) || $_POST['data']['username'] != $uname ||
        !isset($_SESSION['op_control']))
        fail('wrong request');
    if (!isset($_POST['data']['pay']) || floatval($_POST['data']['pay']) < 100)  // 充值金额不能小于100
        fail('wrong request'); 
    include 'db.php';
    $_POST['data']['req_time'] = date('Y-m-d H:i:s', time());
    $_POST['data']['pay'] = number_format(floatval($_POST['data']['pay']), 2, '.', '');
    $res = insertARow('chargeorders', $_POST['data']);  // 插入充值请求顺序表中
    if (isset($res['error']))
        fail($res['error']);
    break;
default:
    fail('wrong request');
}
echo json_encode($ret);
?>

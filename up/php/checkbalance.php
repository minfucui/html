<?php
include 'check_session.php';
include 'accountbalance.php';
if (!balanceOK()) {
    echo $error_return;  // 提示余额不足
    die();
}
echo '{"code":"0","message":"call service success","data":""}';
?>

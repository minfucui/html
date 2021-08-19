<?php
include 'db.php';
function balanceOK()  // 余额是否充足
{
    global $_SESSION;
    $uname = $_SESSION['uname'];
    if (!isset($_SESSION['op_control']))
        return TRUE;
    $u = searchRows('users', ['username'=>$uname]);  // 连接数据库
    if (sizeof($u) == 0 || isset($u['error']))
        return FALSE;
    if (floatval($u[0]['balance']) < 100)  // 小于100块，提示余额不足
        return FALSE;
    else
        return TRUE;
}
?>

<?php
include 'check_session.php';  // 检查session状态
$p = dirname($_SERVER['PHP_SELF']);

if (!isset($_POST['action'])) {
    fail('bad request');
}
$ret = ['code'=>0, "message"=>"success"];

$oldp = urldecode($_POST['data']['pw']);
$np = urldecode($_POST['data']['npw1']);

/* check old password */
exec('export OLWD='.$oldp.';source /var/www/html/env.sh;/var/www/html/cmd/runas '.$uname,
     $r, $errno);
if ($errno != '0')
    fail('目前密码不正确');

if (preg_match("/[\'$*()\\\[\]?\`}{]/", $np))
    fail('新密码含有不支持的字符');

exec('export OLWD='.$oldp.';source /var/www/html/env.sh;/var/www/html/cmd/runas '.$uname.  // 执行修改密码的命令文件chpwd
     ' chpwd \''.$oldp.'\' \''.$np.'\'', $r1, $errno);
if ($errno != '0')
    fail(implode('; ', $r1));
echo json_encode($ret);
?>

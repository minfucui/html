<?php
include 'check_session.php';
// refresh session
if (isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['id'];
    $_SESSION['login'] = $_SESSION['login'];
    $_SESSION['uname'] = $_SESSION['uname'];
    $_SESSION['password'] = $_SESSION['password'];
    $_SESSION['home'] = $_SESSION['home'];
    if (isset($_SESSION['data_home']))
        $_SESSION['data_home'] = $_SESSION['data_home'];
    if (isset($_SESSION['licenses'])) {
        $_SESSION['licenses'] = $_SESSION['licenses'];
        $_SESSION['lmstat'] = $_SESSION['lmstat'];
    }
}

$interval = 1;
include 'jobsdata.php';  // 查询作业
$lock_file = '../data/lock';
if (!file_exists($lock_file)) {
    $now = time();
    $file_time = filemtime('../data/jobs.json');  // 作业处理相关
    if ($file_time === FALSE)
        $file_time = 0;
    if ($now - $file_time >= $interval) {
        touch($lock_file);  // 设置指定文件的访问和修改时间，如果文件不存在，则会被创建，这里相当于加了一把互斥锁，防止多个用户同时写入jobs.json文件
        getJobData();
        unlink($lock_file);  // 删除文件，解锁
    }
}
$change = jobStatusChange($uname);  // 获取作业信息，包括作业号、实例名、状态、时间
header('Content-Type: application/json');
echo '{"code":0,"data":'.json_encode($change).'}';
?>

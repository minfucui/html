<?php
include 'common.php';
include 'filefunctions.php';
include '../../php/vncfunc.php';

if (!isset($_GET['jobid']) || !isset($_GET['pw'])) {
    fail('wrong parameter');
}

$jobid = $_GET['jobid'];
$password = $_GET['pw'];
$session = ['host'=>$_GET['host'], 'sid'=>$_GET['sid']];

$ret = vncurl($jobid, $password, $uname, $session);

if ($ret['code'] != 0) {
    echo $ret['message'];
    die();
}

echo $ret['url'];
?>

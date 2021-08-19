<?php
include '../header.php';
$uname = $_SESSION['uname'];
$pword = $_SESSION['password'];

include 'functions.php';
include 'vncfunc.php';

if (!isset($_GET['jobid']) || !isset($_GET['pw'])) {
    echo '';
    die();
}

$jobid = $_GET['jobid'];
$password = $_GET['pw'];

$ret = vncurl($jobid, $password, $uname);

if ($ret['code'] != 0) {
    echo $ret['message'];
    die();
}

echo $ret['url'];
?>

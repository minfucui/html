<?php
session_start();
if (!isset($_SESSION['uname']) || !isset($_SESSION['password'])
    || !isset($_SESSION['login']) || $_SESSION['login'] != 1) {
    echo '{"code":404, "message":"Page not found"}';
    die();
}

if (sizeof($_POST) == 0) {
    $str = file_get_contents("php://input");
    $_POST = json_decode($str, TRUE);
}

$uname = $_SESSION['uname'];
$pword = $_SESSION['password'];

$cmdPrefix = "export OLWD=".$pword.";source /var/www/html/env.sh;/var/www/html/cmd/runas ".$uname." ";
$datapath = '..';
$cmdpath = '/var/www/html';
$apppath = '../apps';

$shortName = exec('date +%Z');
$offset = exec('date +%::z');
$off = explode (":", $offset);
$offsetSeconds = $off[0][0] . abs($off[0])*3600 + $off[1]*60 + $off[2];
$longName = timezone_name_from_abbr($shortName, $offsetSeconds);
date_default_timezone_set($longName);

$ret = ['code'=>0, 'message'=>'successful','data'=>[]];

function fail($message)
{
    $ret = ['code'=>500, 'message'=>$message,'data'=>[]];
    echo json_encode($ret);
    die();
}
include 'db.php';
include 'log.php';
?>

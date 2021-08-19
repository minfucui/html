<?php
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];

if (!isset($uname)) {
    header('Location: index.php');
    die();
}

$timezone = shell_exec("cmd/timezone");
date_default_timezone_set($timezone);
$currentmonth = date("m");
$currentyear = date("Y");

if ($uname == $admin)
    $user = "";
else
    $user = " -u ".$uname;

function genbill($y, $m, $uname, $user) {

    $ymformat = sprintf("%4d-%02d", $y, $m);

    $output = shell_exec("source ./env.sh;export OLWD=".
              $GLOBALS['pword'].";cmd/runas ".
              $uname." aipbills ".$user." -j -m ".$ymformat);

    file_put_contents("bills/".$uname."/bill-".$ymformat, $output);
}

function previousmonth($y, $m, $prev)
{
    $l = $y * 12 + $m - 1;
    $l -= $prev;
    return [$l / 12, $l % 12 + 1];
}

for ($i = 0; $i < 6; $i++) {
    $m = previousmonth ($currentyear, $currentmonth, $i);
    genbill($m[0], $m[1], $uname, $user);
}
sleep(1);
echo "OK";
?>

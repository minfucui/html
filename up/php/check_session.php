<?php

function setTimeZone()
{
    $shortName = exec('date +%Z');
    $offset = exec('date +%::z');
    $off = explode (":", $offset);
    $offsetSeconds = $off[0][0] . abs($off[0])*3600 + $off[1]*60 + $off[2];
    $longName = timezone_name_from_abbr($shortName, $offsetSeconds);
    date_default_timezone_set($longName);
}

function fail($message)
{
    $ret['code'] = 201;
    $ret['message'] = $message;
    echo json_encode($ret);
    die();
}

if (!isset($session_start)) {
    session_start();
    $error_return = '{"code":201,"message":"Wrong call"}';
    if (!isset($_SESSION['login']) || $_SESSION['login'] == '') {
        echo $error_return;
        die();
    }
    $uname = $_SESSION['uname'];
    $pword = $_SESSION['password'];
    $cmdPrefix = 'export OLWD='.$pword.';source ../../env.sh;../../cmd/runas '.
              $uname;
    $datapath = '..';
    $cmdpath = '../..';
    $apppath = '../apps';
    if (sizeof($_POST) == 0) {
        $str = file_get_contents("php://input");
        $_POST = json_decode($str, TRUE);
    }
    $session_start = 1;
}
?>


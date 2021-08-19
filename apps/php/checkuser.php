<?php
session_start();
include 'db.php';
include 'user_data.php';

if (sizeof($_POST) == 0) {
    $str = file_get_contents("php://input");
    $_POST = json_decode($str, TRUE);
}

$ret = ['code'=>0, 'message'=>'successful','data'=>[]];

if (!isset($_POST['username'])) {
    $ret['code'] = 404;
    $ret['message'] = 'Error';
} else {
    $user = get_user_data($_POST['username']);
    if (sizeof($user) > 0 && isset($user['error'])) {
        $ret['code'] = 500;
        $ret['message'] = $user['error'];
    } else {
        $reguser = searchRows("regusers", $_POST);
        if (sizeof($reguser) > 0 && isset($reguser['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $reguser['error'];
        } else {
            exec("id ".$_POST['username']." 2>/dev/null", $r, $err);
            if (sizeof($user) != 0 || sizeof($reguser) != 0 || $err == 0) {
                $ret['code'] = 500;
                $ret['message'] = 'USERNAME_EXISTS';
            }
        }
    }
}
echo json_encode($ret);
?>

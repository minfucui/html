<?php
session_start();
include 'db.php';
include 'user_data.php';


function validemail($email)
{
    if (strpos($email, '@') === FALSE ||
        strpos($email, '@126.com') !== FALSE ||
        strpos($email, '@163.com') !== FALSE ||
        strpos($email, '@qq.com') !== FALSE ||
        strpos($email, '@gmail.com') !== FALSE ||
        strpos($email, '@outlook.com') !== FALSE ||
        strpos($email, '@yahoo.com') !== FALSE ||
        strpos($email, '@hotmail.com') !== FALSE)
        return FALSE;
    return TRUE;
}

if (sizeof($_POST) == 0) {
    $str = file_get_contents("php://input");
    $_POST = json_decode($str, TRUE);
}

$ret = ['code'=>0, 'message'=>'successful','data'=>[]];
if (!isset($_POST['organization']) || !isset($_POST['name'])
   || !isset($_POST['phone']) || !isset($_POST['email'])
   || !isset($_POST['address']) || !isset($_POST['username'])) {
    $ret['code'] = 404;
    $ret['message'] = 'Missing parameters';
} else if (!validemail($_POST['email'])) {
    $ret['code'] = 500;
    $ret['message'] = 'INVALID_EMAIL';
} else {
    $_POST['acctstatus'] = "2activate";
    $r = insertARow("regusers", $_POST);
    if (isset($r['error'])) {
        $ret['code'] = 500;
        $ret['message'] = $r['error'];
    }
}
echo json_encode($ret);
?>

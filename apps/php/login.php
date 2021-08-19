<?php
session_start();
include 'language.php';
include 'db.php';
include 'user_data.php';
include 'log.php';

$lang = $_SESSION['lang'];

if (sizeof($_POST) == 0) {
    $str = file_get_contents("php://input");
    $_POST = json_decode($str, TRUE);
}

$ret = ['code'=>0, 'message'=>'successful','data'=>[]];

if (!isset($_POST['username']) || !isset($_POST['password']) ||
     $_POST['username'] == 'root') {
    $ret['code'] = 201;
    $ret['message'] = 'INVALID_USERNAME_PASSWORD';
    echo json_encode($ret);
    die();
}
$uname = $_POST['username'];
$pword = $_POST['password'];

/* Run authentication */
exec("export OLWD=".$pword.";source ../../env.sh;../../cmd/runas ".$uname,
     $out, $errno);

$user = get_user_data($uname);

if ($errno == '0') {
    /* User authenticated */
    $_SESSION['login'] = "1";
    $_SESSION['uname'] = $uname;
    $_SESSION['password'] = $pword;
    /* look for database for role and the first page,
       then add $_SESSION['role'] */
    if (sizeof($user) == 0) {
        $user['username'] = $uname;
        $user['roles'] = $lang['USER'];
        $user['acctstatus'] = "normal";
        $r = add_user_data($user);
        if (isset($r['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $r['error'];
        } else {
            $_SESSION['roles'] = explode(" ", $user['roles']);
            $ret['data']['url'] = "main.html";
            exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);
            $_SESSION['home'] = $r[0];
            skylog_activity($uname, WEB_ACCESS, LOGIN, 'from: '.$_SERVER['REMOTE_ADDR']);
        }
    } else if (isset($user['error'])) {
        $ret['code'] = 500;
        $ret['message'] = 'DB_ERROR';
    } else if ($user['acctstatus'] != "normal") {
        $ret['code'] = 500;
        switch ($user['acctstatus']) {
        case 'inactivated':
            $ret['message'] = 'NEED_ACTIVATE';
            break;
        case 'activated':
            $ret['message'] = 'NEED_APPROVAL';
            break;
        case 'suspended':
            $ret['message'] = 'ACCT_SUSPENDED';
            break;
        default:
            $ret['message'] = 'DB_ERROR';
            break;
        }
    } else {
        $_SESSION['roles'] = explode(" ", $user['roles']);
        exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);
        if ($errno == 0) {
            $_SESSION['home'] = $r[0];
            $ret['data']['url'] = "main.html";
            skylog_activity($uname, WEB_ACCESS, LOGIN, 'from: '.$_SERVER['REMOTE_ADDR']);
        }  else {
            $ret['code'] = 200;
            $ret['message'] = "User home directory does not exist";
        }
    }
 
} else if ($errno == '255') {
    /* No valid license found */
    $ret['code'] = 301;
    $ret['message'] = 'INVALID_LICENSE';
} else {
    if (sizeof($user) == 0 || $errno != 0) {
        /* User not authenticated */
        $ret['code'] = 401;
        $ret['message'] = 'INVALID_USERNAME_PASSWORD';
    } else {
        $ret['code'] = 500;
        switch ($user['acctstatus']) {
        case '2activate':
            $ret['message'] = 'NEED_ACTIVATE';
            break;
        case 'activated':
            $ret['message'] = 'NEED_APPROVAL';
            break;
        case 'suspended':
            $ret['message'] = 'ACCT_SUSPENDED';
            break;
        default:
            $ret['message'] = 'DB_ERROR';
            break;
        }
    }
}

/* scan directory and add missing apps */
$result = searchRows('applications');
$apppath = '../../up/apps';
if (is_dir($apppath) && ($dir = scandir($apppath)) !== FALSE) {
    foreach($dir as $f) {
        if (($f[0] == '.' || strpos($f, '.yaml') === FALSE ||
            strpos($f, '.yaml.bak') !== FALSE) && $f != '.batch')
            continue;
        if (is_dir($apppath.'/'.$f)) {
            $dir = scandir($apppath.'/'.$f);
            foreach ($dir as $f) {
                if ($f[0] == '.' || strpos($f, '.yaml') === FALSE ||
                    strpos($f, '.yaml.bak') !== FALSE)
                    continue;
                insertARow('applications',['appname'=>basename($f, '.yaml'),
                                           'confpath'=>$f, 'published'=>0]);
            }
            continue;
        }
        $found = FALSE;
        $name = basename($f, '.yaml');
        for ($i = 0; $i < sizeof($result); $i++)
            if ($result[$i]['appname'] == $name) {
                $found = TRUE;
                break;
            }
        if (!$found) {
            $c = yaml_parse_file($apppath.'/'.$f);
            $icon = $c['icon'];
            $rec = ['appname'=>$name, 'confpath'=>$f,
                    'published'=>0, 'icon'=>$c['icon']];
            $res = insertARow('applications', $rec);
            if (isset($res['error']))
                error_log($res['error']);
        }
        if (is_dir($apppath.'/'.$f)) {
            $dir = scandir($apppath.'/'.$f);
            foreach ($dir as $f) {
                if ($f[0] == '.' || strpos($f, '.yaml') === FALSE ||
                    strpos($f, '.yaml.bak') !== FALSE)
                    continue;
                insertARow('applications',['appname'=>myBasename($f, '.yaml'),
                                           'confpath'=>f, 'published'=>0]);
            }
        }
    }
}

echo json_encode($ret);
?>

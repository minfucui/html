<?php
include 'common.php';

if (!isset($_GET['user'])) // || !in_array($_SESSION['lang']['ADMIN'], $_SESSION['roles']))
    fail('Invalid parameter');

$user = $_GET['user'];

exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $er);
$aipenv = $r[0];
$crondconf = $aipenv.'/cbcrond.yaml';

if (!file_exists($crondconf))
    fail("cbcrond.yaml does not exist.\n");

$conf = yaml_parse_file($crondconf);
if ($conf === FALSE)
    fail("Invalid cbcrond.yaml.\n");

/* $apps = searchRows('applications'); */
$apps = [];
foreach ($conf['charge']['standard']['appperhour'] as $a)
    $apps[] = ['appname'=>$a['app']];

$d = ['user'=>$user, 'apps'=>[]];
foreach ($conf['charge']['userdiscounts'] as $u) {
    if ($u['user'] == $user) {
        $d = $u;
        break;
    }
}

if (!isset($d['cpu'])) $d['cpu'] = 1;
if (!isset($d['mem'])) $d['mem'] = 1;
if (!isset($d['gpu'])) $d['gpu'] = 1;
foreach ($apps as $a) {
    $foundapp = false;
    if (sizeof($d['apps']) > 0)
        foreach ($d['apps'] as $da) {
            if ($da['app'] == $a['appname']) {
                $foundapp = true;
                break;
            }
        }
    if (!$foundapp)
        $d['apps'][] = ['app'=>$a['appname'], 'rate'=>1];
}
$ret['data'] = $d;
echo json_encode($ret);
?>

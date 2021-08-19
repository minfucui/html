<?php
include 'common.php';
include 'user_data.php';

$tablename = 'applications';

$apps = searchRows($tablename);
$perm = searchRows('permissions');
$appcats = searchRows('appcat');
$orders = searchRows('apporders', ['ugname'=>$uname]);
$apporders = [];
if (sizeof ($orders) > 0)
    foreach ($orders as $a)
        $apporders[] = $a['appname'];
$menu = [];
foreach ($appcats as $cat) {
    $menu[] = ['catname'=>$cat['catname'], 'applications'=>[]];
}
if (isset($apps['error']) || isset($perm['error'])) {
    $ret['code'] = 500;
    if (isset($apps['error']))
        $ret['message'] = $apps['error'];
    else
        $ret['message'] = $perm['error'];
} else {
    for($i = 0; $i < sizeof($apps); $i++) {
        if ($apps[$i]['published'] == 0
            || !in_array($apps[$i]['appname'], $apporders)
            )
            continue;
        if ($_SESSION['app_sec_control'] == 'yes' &&
            !permit($perm, $_SESSION['roles'],
               'app' + $apps[$i]['appname']))
            continue;
        for($j = 0; $j < sizeof($menu); $j++) {
            if ($menu[$j]['catname'] == $apps[$i]['catname']) {
                $menu[$j]['applications'][] =
                    ['appname'=>$apps[$i]['appname'],
                     'icon'=>$apps[$i]['icon']];
                break;
            }
        }
    }
    $n = sizeof($menu);
    for ($i = 0; $i < $n; $i++)
        if (sizeof($menu[$i]['applications']) == 0)
            unset($menu[$i]);
    $menu = array_values($menu);
    $ret['data'] = $menu;
}

echo json_encode($ret);
?>

<?php
include 'common.php';
include 'user_data.php';

$menu_file = "../menu.yaml";

if (! file_exists($menu_file) || ($menu = yaml_parse_file($menu_file)) === FALSE) {
    $ret['code'] = 500;
    $ret['message'] = "menu.yaml error";
    echo json_encode($ret);
    die();
}
$orig_menu = $menu;
$roles = $_SESSION['roles'];
$perm = searchRows("permissions");
foreach ($menu as $key => $mitem) { /* top level */
    $sn = sizeof ($mitem);
    for ($j = 0; $j < $sn; $j++) {
       $permitID = $key.$menu[$key][$j]['title'];
       if (!permit($perm, $roles, $permitID)) {
           unset($menu[$key][$j]);
           continue;
       }
       if (isset($menu[$key][$j]['submenu'])) {
           $ssn = sizeof($menu[$key][$j]['submenu']);
           for ($k = 0; $k < $ssn; $k++) {
                $permitID = $key.$menu[$key][$j]['title'].
                    '-'.$menu[$key][$j]['submenu'][$k]['title'];
                if (!permit($perm, $roles, $permitID))
                    unset($menu[$key][$j]['submenu'][$k]);
           }
           $menu[$key][$j]['submenu'] = array_values($menu[$key][$j]['submenu']);
       }
   }
   if ($sn > 0)
       $menu[$key] = array_values($menu[$key]);
}
/* get role based menu from configuration */
$ret['data']['menu'] = $menu;
$ret['data']['uname'] = $uname;
switch($roles[0]) {
    case '保密员':
    case 'confidential':
    case 'Confidential':
        $ret['data']['avatar'] = 'confidential';
        break;
    case '审计员':
    case 'auditor':
    case 'Auditor':
        $ret['data']['avatar'] = 'auditor';
        break;
    case '管理员':
    case 'admin':
    case 'Admin':
    case 'adminitrator':
    case 'Adminitrator':
        $ret['data']['avatar'] = 'admin';
        break;
    case '用户':
    case 'user':
    case 'User':
        $ret['data']['avatar'] = 'user';
        break;
    case '组管理员':
    case 'groupadmin':
    case 'Groupadmin':
        $ret['data']['avatar'] = 'groupadmin';
        break;
    default:
        $ret['data']['avatar'] = 'other';
        break;
}
if (file_exists("../config.yaml")) {
    $conf = yaml_parse_file("../config.yaml");
    if (isset($conf['forum']) && $conf['forum'] =='yes')
        $ret['data']['forum'] = 'yes';
    else
        $ret['data']['forum'] = 'no';
    $ret['data']['app_sec_control'] = isset($conf['app_sec_control'])?
          $conf['app_sec_control'] : 'yes';
} else {
    $ret['data']['forum'] = 'no';
    $ret['data']['app_sec_control'] = 'yes';
}

$_SESSION['app_sec_control'] = $ret['data']['app_sec_control'];
if (isset($conf['admin_only']) && $conf['admin_only'] == 'yes'
    && ($ret['data']['avatar'] == 'user' || $ret['data']['avatar'] == 'groupadmin'))
    $ret['data']['logout'] = 'yes';
else
    $ret['data']['logout'] = 'no';

echo json_encode($ret);
?>

<?php
include_once 'check_session.php';
include 'filefunctions.php';
include 'subfunc.php';
$icons = [];
$appCat = [];

function add_to_array($type, $dir, $appName = '')  // 应用订阅，增加app
{
    global $icons;
    global $appCat;
    $sub = get_sub();
    if (($files =
         ($type == 'APP' ? scandir($dir) : myScandir($dir))) === FALSE)
        return FALSE;
    if (isset($_SESSION['config']['app_access']))
        $app_access = $_SESSION['config']['app_access'];
    foreach ($files as $app) {
        if ($app[0] == '.') // || $app == '..')
            continue;
        $path = $dir.'/'.$app;
        if (($type == 'APP' ? is_dir($path) : myIs_dir($path)) === TRUE) {
            add_to_array($type, $path, $appName);
            continue;
        }
        if (strpos($app, '.yaml') !== FALSE) {
            $icon = [];
            $read = $type == 'APP' ? file_get_contents($path) : myFile_Get_Contents($path);
            if ($read === FALSE)
                continue;
            $filedata = yaml_parse($read);
            if ($appName != '' && $filedata['appName'] != $appName)
                continue;
            $icon['type'] = $type;
            $icon['name'] = myBasename($app, '.yaml');
            $icon['icon'] = str_replace("'","",$filedata['icon']);
            if ($type != 'FOLDER') {
                $icon['yamlPath'] = $path;
            }
            if ($type == 'APP') {
                $apppath = dirname($path);
                $pathname = myBasename($apppath);
                if ($pathname == 'projects' || $pathname == 'apps')
                    $icon['appCat'] = '';
                else {
                    $icon['appCat'] = $pathname;
                    $appCat[$pathname] = 1;
                }
                if (isset($filedata['url']))
                    $icon['url'] = $filedata['url'];
            }
            if (isset($app_access) && isset($app_access[$icon['name']])) {
                if (!in_array($_SESSION['uname'], $app_access[$icon['name']]))
                    continue;
            }

            if (sizeof($sub) > 0 && !in_array($icon['name'], $sub))
                $icon['sub'] = FALSE;
            else
                $icon['sub'] = TRUE;
            $icons[] = $icon;
        }
    }
    return TRUE;
}

if (!isset($_GET['action']))
    fail('Wrong call');

switch($_GET['action']) {
    case 'get':  // 获取所有app
        if (add_to_array('APP', $apppath) === FALSE) {
            echo $error_return;
            die();
        }
        echo '{"code":0,"message":"call service success","data":'.
             json_encode($icons).'}';
        break;
    case 'sub':
        if (!isset($_POST['apps']))
            fail('wrong call');
        put_sub($_POST['apps']);  // 保存桌面app
        die('{"code":0,"message":"call service success"}');
        break;
    default:
        fail('wrong call');
}
?>

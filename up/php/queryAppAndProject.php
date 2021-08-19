<?php
include_once 'check_session.php';
include 'filefunctions.php';
include 'folders.php';
include 'subfunc.php';  // 存应用实例接口
$icons = [];
$appCat = [];
function add_to_array($type, $dir, $appName = '')  // 查询所有的应用以及实例列表
{
    global $icons;
    global $appCat;
    if (isset($_SESSION['config']['app_access']))
        $app_access = $_SESSION['config']['app_access'];
    if ($type == 'APP')
        $sub = get_sub();
    if (($files =
         ($type == 'APP' ? scandir($dir) : myScandir($dir))) === FALSE)
        return FALSE;
    foreach ($files as $app) {
        $path = $dir.'/'.$app;
        if (($type == 'APP' ? is_dir($path) : myIs_dir($path)) === TRUE && $app[0] != '.') {
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

                if (sizeof($sub) > 0 && !in_array($icon['name'], $sub))
                    continue;
                if (isset($app_access) && isset($app_access[$icon['name']])) {
                    if (!in_array($_SESSION['uname'], $app_access[$icon['name']]))
                        continue;
                }
            }
            $icons[] = $icon;
        }
    }
    return TRUE;
}

/* list applications */
$t1 = microtime(true);
if (!isset($_GET['appName'])) {
    if (add_to_array('APP', $apppath) === FALSE) {
        echo $error_return;
        die();
    }
}

$t2 = microtime(true);

if (!isset($_SESSION['config']) || 
    !isset($_SESSION['config']['instance_on_desk']) ||
    $_SESSION['config']['instance_on_desk'] == true ||
    isset($_GET['appName']))
    $projects = true;
else
    $projects = false;

/* projects */

$olpath = $_SESSION['home'].'/projects';

if (!isset($_GET['appName']) || $_GET['appName'] == 'all')
    $appName = '';
else
    $appName = $_GET['appName'];
if ($projects)
    add_to_array('PROJECT', $olpath, $appName);

$t3 = microtime(true);

/* folders */

if (!isset($_GET['appName'])) {
    /* user's project folders */
    $folders = folders($uname);
    if (sizeof($folders) > 0) {
        foreach ($folders as $key=>$f) {
            $icon = [];
            $icon['type'] = 'FOLDER';
            $icon['id'] = $icon['name'] = $key;
            $icon['subProjectIcons'] = [];
            for ($i = 0; $i < 4 && isset($f[$i]); $i++) {
                $cont = myFile_Get_Contents($f[$i]);
                $yaml = yaml_parse($cont);
                if ($yaml === FALSE)
                    continue;
                $icon['subProjectIcons'][] =
                     str_replace("'","",$yaml['icon']);
            }
            $icons[] = $icon;
        }
        $n = sizeof($icons);
        foreach ($folders as $key=>$f) {
            foreach ($f as $folded) {
                for ($i = 0; $i < $n; $i++) {
                    if (!isset($icons[$i]) ||
                        $icons[$i]['type'] != 'PROJECT')
                        continue;
                    if ($icons[$i]['yamlPath'] == $folded) {
                        unset($icons[$i]);
                        break;
                    }
                }
            }
        }
        $icons = array_values($icons);
    }
    /* app folders */
    if (sizeof($appCat) > 0) {
        foreach($appCat as $ac=>$value) {
            $icon = [];
            $icon['type'] = 'APPCAT';
            $icon['id'] = $icon['name'] = $ac;
            $icon['appIcons'] = [];
            $j = 0;
            $n = sizeof($icons);
            for($i = 0; $i < $n; $i++) {
                if ($icons[$i]['type'] != 'APP' || $icons[$i]['appCat'] != $ac)
                    continue;
                if ($j < 4)
                    $icon['appIcons'][] = $icons[$i]['icon'];
                $j++;
                unset($icons[$i]);
            }
            if (sizeof($icon['appIcons']) > 0)
                $icons[] = $icon;
            $icons = array_values($icons);
        }
    }
}

$t4 = microtime(true);

function acomp($a, $b)
{
    if ($a['type'] > $b['type'])
        return 1;
    if ($a['type'] == $b['type'])
        return 0;
    return -1;
}

usort($icons, "acomp");

$t5 = microtime(true);

/* error_log('-app:'.number_format($t2-$t1,3).
          ';proj:'.number_format($t3-$t2,3).
          ';fold:'.number_format($t4-$t3,3).
          ';sort:'.number_format($t5-$t4,3));
*/
if (isset($_SESSION['op_control'])) {
    $_SESSION['dbserver'] = $_SESSION['dbserver'];
    $_SESSION['dbuser'] = $_SESSION['dbuser'];
    $_SESSION['dbpassword'] = $_SESSION['dbpassword'];
}

header('Content-Type: application/json');
echo '{"code":0,"message":"call service success","data":'.
     json_encode($icons).'}';
?>

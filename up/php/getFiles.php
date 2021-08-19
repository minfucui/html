<?php
include 'check_session.php';
include 'filefunctions.php';
// 获取应用或实例的数据文件列表
setTimeZone();

if (!isset($_POST['filter']))
    fail('Wrong call');
$ft = explode('/', $_POST['filter']);
if ($ft[1] == 'file') {
    $filter = str_replace('.', '\\.', $ft[2]);
    $filter = str_replace('*', '.*', $filter);
}
else
    $filter = '.*';
// 从地址读取数据文件，包含文件名、更新时间、文件大小等
if (isset($_SESSION['data_home']))
    $userhome = str_replace('$USER', $uname, $_SESSION['data_home']);
else
    $userhome = $_SESSION['home'];

if (!isset($_POST['currentFolder']) || $_POST['currentFolder'] == "") {
    $topdir = $userhome;
} else {
    if ($_POST['forwardFolder'] == '..')
        $topdir = dirname($_POST['currentFolder']);
    else
        $topdir = $_POST['currentFolder'].'/'.$_POST['forwardFolder'];
}

header('Content-Type: application/json');
if (($files = myScandir($topdir)) === FALSE)
    echo $error_return;
else {
    $ret['currentFolder'] = $topdir;
    $ret['list'] = [];
    foreach ($files as $f) {
        if ($f == '.')
            continue;
        if ($f == '..' && $topdir == $userhome)
            continue;
        if ($f[0] == '.' && $f[1] != '.')
            continue;
        $entry['name'] = $f;
        $fullpath = $topdir.'/'.$f;
        $entry['size'] = myFilesize($fullpath);
        $time = myFilemtime($fullpath);
        $entry['lastUpdateTime'] = date("Y-m-d H:i", $time);
        if (myIs_Dir($fullpath)) {
            $entry['type'] = 'folder';
            $ret['list'][] = $entry;
        }
        else {
            if (preg_match('/'.$filter.'/', $f) === 1) {
                $entry['type'] = 'file';
                $ret['list'][] = $entry;
            }
        }
    }
}
 
echo '{"code":"0","message":"call service success","data":'.json_encode($ret).'}';
?>

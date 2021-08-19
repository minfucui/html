<?php
include 'common.php';
include 'filefunctions.php';

$selected = '';

function fsize($path)
{
    $size = intval(myFilesize($path));
    if ($size >= 1073741824)
        return strval(intval($size / 1073741824))."G";
    else if ($size >= 1048576)
        return strval(intval($size / 1048576))."M";
    else if ($size >= 1024)
        return strval(intval($size / 1024))."K";
    else
        return strval($size)."B";
}

if ($_POST['path'] == '选择文件')
    $path = $_SESSION['home'];
else if (myIs_Dir($_POST['path']))
    $path = $_POST['path'];
else if (myIs_File($_POST['path'])) {
    $path = dirname($_POST['path']);
    $selected = myBasename($_POST['path']);
} else {
    $ret['code'] = 500;
    $ret['message'] = 'Wrong path';
    die();
}

$retd = [];
$retd['path'] = $path;
$retd['selected'] = $selected;
$retd['files'] = [];
$d = myScanDir($path);
if ($d !== FALSE) {
    if ($path != $_SESSION['home']) {
        $fullp = myDirname($path);
        $retd['files'][] = ["path"=>$fullp,
                           "time"=>date("Y-m-d H:i", myFilemtime($fullp)),
                           "size"=>fsize($fullp),
                           "type"=>"d", "name"=>".."];
    }
    foreach($d as $f) {
        if ($f[0] == '.')
            continue;
        $fullp = $path.'/'.$f;
        if (myIs_Dir($fullp))
            $type = 'd';
        else
            $type = 'f';
        if ($type == 'f' && $_POST['type'] == 'dir')
            continue;
        $size = fsize($fullp);
        $timestr = date("Y-m-d H:i", myFilemtime($fullp));
        $retd['files'][] = ["path"=>$fullp, "time"=>$timestr,
                           "size"=>$size, "type"=>$type,
                           "name"=>$f]; 
    }
}
$ret['data'] = $retd;
echo json_encode($ret);
?>

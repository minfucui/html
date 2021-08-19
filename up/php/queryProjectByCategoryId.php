<?php
include 'check_session.php';
include 'folders.php';
include 'filefunctions.php';

if (!isset($_POST['categoryId'])) {  // 这里为什么可以通过类别id来查询实例呢，是因为文件系统保存实例在用户名folder.json里是下标对应着类别
    echo $error_return;
    die();
}

$folders = folders($uname);

if (isset($folders[$_POST['categoryId']]))
    $paths = $folders[$_POST['categoryId']];
else
    $paths = [];

$projects = [];
foreach ($paths as $path)
{
    $proj = [];
    $pdata = myFile_Get_Contents($path);
    $p = yaml_parse($pdata);
    $proj['name'] = myBasename($path, '.yaml');
    $proj['yamlPath'] = $path;
    $proj['icon'] = str_replace("'","",$p['icon']);
    $projects[] = $proj;
}
header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":'.json_encode($projects).'}';
?>

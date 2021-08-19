<?php
include 'check_session.php';
include 'filefunctions.php';
include 'subfunc.php';

if (!isset($_POST['categoryId'])) {  // 分类应该是分为仿真应用、预处理应用这种
    echo $error_return;
    die();
}

$path = $apppath.'/'.$_POST['categoryId'];
if (($files = scandir($path)) === FALSE)  // 列出path中的文件和目录，相当于ls，返回文件数组
    return [];

$subs = get_sub();
$apps = [];
if (isset($_SESSION['config']['app_access']))
    $app_access = $_SESSION['config']['app_access'];
foreach ($files as $f)
{
    if($f[0] == '.' || is_dir($f) || strpos($f, '.yaml') === FALSE)
        continue;
    $app = [];
    $adata = myFile_Get_Contents($path.'/'.$f);
    $a = yaml_parse($adata);
    $app['name'] = myBasename($f, '.yaml');
    if (sizeof($subs) > 0 && !in_array($app['name'], $subs))
        continue;
    if (isset($app_access) && isset($app_access[$app['name']])) {
        if (!in_array($_SESSION['uname'], $app_access[$app['name']]))
            continue;
    }

    $app['yamlPath'] = $path.'/'.$f;
    $app['icon'] = $a['icon'];
    $apps[] = $app;
}
header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":'.json_encode($apps).'}';
?>

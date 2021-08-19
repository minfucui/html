<?php
include 'check_session.php';
include 'filefunctions.php';
// 创建实例
if (!isset($_POST['project'])) {
    echo $error_return;
    die();
}

$spec = $_POST;

$spec['project'] = str_replace(' ','_',$spec['project']);

if (file_exists($apppath.'/'.$spec['appName'].'.yaml'))
    $read = file_get_contents($apppath.'/'.$spec['appName'].'.yaml');
else
    $read = FALSE;

if ($read === FALSE) {
    $files = scandir($apppath);
    if ($files && sizeof($files)) {
        foreach ($files as $f) {
            if ($f[0] == '.'
        || !file_exists($apppath.'/'.$f.'/'.$spec['appName'].'.yaml'))
                continue;
            $read = file_get_contents($apppath.'/'.$f.'/'.$spec['appName'].'.yaml');
            if ($read !== FALSE)
                break;
        }
    }
    if ($read === FALSE) {
        trigger_error("Cannot read the app file: ".$spec['appName'].".yaml");
        echo $error_return;
        die();
    }
}

$proj = yaml_parse($read);

$cats = [];
$spec['params'] = [];
for ($i = 0; $i < sizeof($spec['uiParamList']); $i++) {
    $cat['name'] = $spec['uiParamList'][$i]['name'];
    $cat['id'] = $spec['uiParamList'][$i]['id'];
    $cats[] = $cat;
    $spec['params'] = array_merge($spec['params'],
                      $spec['uiParamList'][$i]['params']);
}
$proj['params_category'] = $cats;
$proj['params'] = $spec['params'];
if (isset($proj['cluster_params']['jobtype']))
    $spec['clusterParams']['jobtype'] = $proj['cluster_params']['jobtype'];
$proj['cluster_params'] = $spec['clusterParams'];
if (isset($proj['cluster_params']['mincpu']))
    $proj['cluster_params']['mincpu'] =
         intval($proj['cluster_params']['mincpu']);
if (isset($proj['cluster_params']['maxcpu']))
    $proj['cluster_params']['maxcpu'] =
         intval($proj['cluster_params']['maxcpu']);
$proj['cluster_params']['instance'] = $spec['project'];
$proj['appName'] = $spec['appName'];
if (!isset($proj['cluster_params']['cwd'])) {
    if (isset($_SESSION['data_home']))
        $data_home = str_replace('$USER', $uname, $_SESSION['data_home']);
    else
        $data_home = $_SESSION['home'];
    $cwd = $data_home.'/jobdata/default/'.$spec['project'];
    if (!myIs_Dir($data_home.'/jobdata'))
        myMkdir($data_home.'/jobdata');
    if (!myIs_Dir($data_home.'/jobdata/default'))
        myMkdir($data_home.'/jobdata/default');
    if (!myIs_Dir($cwd))
        myMkdir ($cwd);
    $proj['cluster_params']['cwd'] = $cwd;
}

$homepath = $_SESSION['home'];

$path = $homepath.'/projects/default';
$projPath = $path.'/'.$spec['project'].'.yaml';
if (myIs_Dir($homepath.'/projects') === FALSE)
    myMkdir ($homepath.'/projects');
if (myIs_Dir($path) === FALSE)
    myMkdir ($path);

if (isset($spec['appName2']))
    $proj['appName2'] = $spec['appName2'];

header('Content-Type: application/json');
if (myFile_Exists($projPath))
    echo '{"code":"6002","message":"项目已存在|Project name exists"}';
else {
    $str = yaml_emit($proj, YAML_UTF8_ENCODING);
    myFile_Put_Contents($projPath, $str);

    echo '{"code":0,"message":"successful","data":"'.$projPath.'"}';
}
?>

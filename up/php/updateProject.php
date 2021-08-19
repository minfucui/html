<?php
include 'check_session.php';
include 'filefunctions.php';
// 更新实例接口
if (!isset($_POST['project']) && !isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}

$spec = $_POST;

$spec['project'] = str_replace(' ','_',$spec['project']);

$read = myFile_Get_Contents($_POST['projectPath']);

if ($read === FALSE) {
    trigger_error("Cannot read the original project file: ".
                   $_POST['projectPath']);
    echo $error_return;
    die();
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
/* for ($i = 0; $i < sizeof($proj['params']); $i++) {
    $str = str_replace('$value', $proj['params'][$i]['value'],
                       $proj['params'][$i]['app_cli_convert']);
    $proj['params'][$i]['app_cli_convert'] = $str;
} */
$proj['cluster_params'] = $spec['clusterParams'];
if (isset($proj['cluster_params']['mincpu']))
    $proj['cluster_params']['mincpu'] =
         intval($proj['cluster_params']['mincpu']);
if (isset($proj['cluster_params']['maxcpu']))
    $proj['cluster_params']['maxcpu'] =
         intval($proj['cluster_params']['maxcpu']);
$proj['cluster_params']['instance'] = $spec['project'];

$projPath = dirname($_POST['projectPath']).'/'.$spec['project'].'.yaml';

header('Content-Type: application/json');
// if (myFile_Exists($projPath))
//    echo '{"code":"6002","message":"实例已存在|Instance name exists"}';
//else {
    if (isset($_SESSION['data_home']))
        $data_home = str_replace('$USER', $uname, $_SESSION['data_home']);
    else
        $data_home = $_SESSION['home'];
    $cwd = $data_home.'/jobdata/default/'.$spec['project'];
    if (!myIs_Dir($cwd))
        myMkdir ($cwd);
    $proj['cluster_params']['cwd'] = $cwd;
    $str = yaml_emit($proj, YAML_UTF8_ENCODING);
    myFile_Put_Contents($projPath, $str);

    echo '{"code":0,"message":"successful","data":"'.$projPath.'"}';
//}
?>

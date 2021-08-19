<?php
include 'check_session.php';
include 'folders.php';
include 'filefunctions.php';
include 'projectData.php';
// 获得应用配置相关的基本信息，打开应用需要
function convertData($vars, $appName) {
    global $queues;
    $vars['appName'] = $appName;
    if (isset($vars['params_category'])) {
        for ($i = 0; $i < sizeof($vars['params_category']); $i++) {
            $vars['params_category'][$i]['params'] = [];
            foreach ($vars['params'] as $param) {
                if ($param['category_id'] == $vars['params_category'][$i]['id'])
                    $vars['params_category'][$i]['params'][] = $param;
            }
        }
        $vars['uiParamList'] = $vars['params_category'];
    }
    else
        $vars['uiParamList'] = [];
    unset($vars['params']);
    unset($vars['params_category']);
    if (isset($vars['cluster_params']['mincpu']) && isset($_SESSION['job_size_list'])) {
        $vars['cluster_params']['mincpu']=['options'=>$_SESSION['job_size_list']];
    }
    if (isset($vars['cluster_params']['queue']) && isset($queues)) {
        if (isset($vars['cluster_params']['queue']['options'])) {
            for($i = 0; $i < sizeof($vars['cluster_params']['queue']['options']); $i++)
                if (!in_array($vars['cluster_params']['queue']['options'][$i], $queues))
                    unset($vars['cluster_params']['queue']['options'][$i]);
            $vars['cluster_params']['queue']['options'] =
                  array_values($vars['cluster_params']['queue']['options']);
        } else {
            unset($vars['cluster_params']['queue']);
            $vars['cluster_params']['queue'] = ['options'=>$queues];
        }
    }
    return $vars;
}

if (file_exists('../queueCheck.php')) {
    include '../queueCheck.php';
    /* $queues list available queues for the user */
}

if (!isset($_POST['appName'])) {
    echo $error_return;
    die();
}

if (isset($_POST['appPath']))
    $read = file_get_contents($_POST['appPath']);
else
    $read = file_get_contents($apppath.'/'.$_POST['appName'].'.yaml');
header('Content-Type: application/json');
if ($read === FALSE) {
    echo '{"code":401,"message":"failed"}';
} else {
    $vars = yaml_parse($read);
    unset($vars['icon']);
    if (isset($vars['dir'])) {
        if (!is_dir($vars['dir']) || ($fs = scandir($vars['dir'])) === false)
            fail('Invalid app spec');
        $vars['apps'] = [];
        foreach ($fs as $f) {
            if (substr($f, -5) != '.yaml')
                continue;
            $path = $vars['dir'].'/'.$f;
            $read = file_get_contents($path);
            $v1 = yaml_parse($read);
            if ($v1 === FALSE)
                continue;
            $v1 = convertData($v1, myBasename($f, '.yaml'));
            $vars['apps'][] = $v1;
        }
        if (sizeof($vars['apps']) == 0)
            fail('No app found');
    } else {
        $vars = convertData($vars, $_POST['appName']);  // 对应用的参数信息进一步处理转化
    }
    $vars = loadProjData($vars, $_POST['appName']);
    echo '{"code":"0","message":"call service success","data":'.json_encode($vars).'}';
}
?>

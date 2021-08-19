<?php
include 'check_session.php';
include 'filefunctions.php';
if (file_exists('../queueCheck.php')) {
    include '../queueCheck.php';  // 检查队列接口，获取可用队列列表
    /* $queues list available queues for the user */
}

if (!isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}
// 获得实例的一些信息
$read = myFile_Get_Contents($_POST['projectPath']);
header('Content-Type: application/json');
if ($read === FALSE) {
    echo '{"code":401,"message":"failed"}';
} else {
    $vars = yaml_parse($read);
    unset($vars['icon']);
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
    $vars['projectName'] = myBasename($_POST['projectPath'], '.yaml');
    if (isset($vars['cluster_params']['mincpu']) && isset($_SESSION['job_size_list'])) {
         $v = $vars['cluster_params']['mincpu'];
         $vars['cluster_params']['mincpu'] =
           ['options'=>$_SESSION['job_size_list'], 'value'=>$v];  // =>数组赋值
    }
    if (isset($vars['cluster_params']['queue']) && isset($queues)) {
        if (isset($vars['cluster_params']['queue']['options'])) {
            for($i = 0; $i < sizeof($vars['cluster_params']['queue']['options']); $i++)
                if (!in_array($vars['cluster_params']['queue']['options'][$i], $queues))
                    unset($vars['cluster_params']['queue']['options'][$i]);
            $vars['cluster_params']['queue']['options'] =
                    array_values($vars['cluster_params']['queue']['options']);
        } else {
            if (!isset($vars['cluster_params']['queue']['value'])) {
                $v = $vars['cluster_params']['queue'];
                $vars['cluster_params']['queue'] = ['options'=>$queues, 'value'=>$v];
            } else
                $vars['cluster_params']['queue']['options'] = $queues;
        }
    }
    if (isset($vars['cluster_params']['cwd']) &&
        $vars['cluster_params']['cwd'] == $_SESSION['data_home'].
          '/jobdata/default/'.$vars['projectName'])  // cwd类似Linux里的pwd，绝对地址
        unset($vars['cluster_params']['cwd']);
    echo '{"code":"0","message":"call service success","data":'.json_encode($vars).'}';
}
?>

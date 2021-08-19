<?php
function consYaml($ins)
{
    global $apppath, $_SESSION;
    if (($app = yaml_parse_file($apppath.'/'.$ins['appName'].'.yaml'))
        === FALSE)
        return [];
    if (!isset($app['appName']))
        $app['appName'] = $ins['appName'];
    $app['cluster_params'] = $ins['cluster_params'];
    for ($i = 0; isset($app['params']) && $i < sizeof($app['params']); $i++) {
        $value = $ins['uiParamList'][$app['params'][$i]['id']];
        $app['params'][$i]['value'] = $value;
        $app['params'][$i]['app_cli_convert'] = str_replace(
             "\$value", $value, $app['params'][$i]['app_cli_convert']);
        if ($app['params'][$i]['value'] == '选择文件')
            fail('NEED_INPUTFILE');
    }

    if (!isset($app['cluster_params']['cwd'])) {
        $cwd = $_SESSION['home'].'/jobdata/'.$app['cluster_params']['project'];
        if ($app['cluster_params']['instance'] == '') {
            $cwd = $cwd.'/default';
            if (!myIs_Dir($cwd))
                myMkDir($cwd);
        } else
           $cwd = $cwd.'/'.$app['cluster_params']['instance'];
    }
    return $app;
}
?>

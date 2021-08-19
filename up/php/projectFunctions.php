<?php
function getProjectCWD($projPath)  // 获得实例保存的绝对地址
{
    $project = false;
    $f = myFile_Get_Contents($projPath);
    if ($f)
        $project = yaml_parse($f);
    if ($project) {
        $name = myBasename($projPath, '.yaml');
        if (isset($project['cluster_params']['cwd']) &&
            strpos($project['cluster_params']['cwd'], $name) !== false)
            return $project['cluster_params']['cwd'];
    }
    $proj = myBasename($projPath, '.yaml');
    $projPath = $_SESSION['data_home'].'/jobdata/default/'.$proj;
    if (!myIs_Dir($projPath))
        return '';
    return $projPath;
}
?>

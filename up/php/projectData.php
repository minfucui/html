<?php
function saveProjData($proj, $origA)  // 实例数据的一些操作，保存实例，添加参数值，加载实例信息
{
    // error_log('instance='.$proj['cluster_params']['instance'].';origA='.$origA.';app='.$proj['appName']);
    if (($origA != $proj['appName'] &&
        strpos($proj['cluster_params']['instance'], $origA.'-'.$proj['appName']/*.'-'*/) === FALSE) &&
        strpos($proj['cluster_params']['instance'], $proj['appName'].'-') === FALSE)
        return;
    $dataDir = $_SESSION['home'].'/projects/.params';
    if (! myIs_Dir($_SESSION['home'].'/projects'))
        myMkdir($_SESSION['home'].'/projects');

    if (! myIs_Dir($dataDir)) {
        myMkdir($dataDir);
    }
    $filename = $dataDir.'/'.$origA.'-'.$proj['appName'].'.yaml';
    $str = yaml_emit($proj); 
    if ($str !== FALSE)
        myFile_Put_Contents($filename, $str);
}

function addValue($proj, $value)
{
    for($i = 0; $i < sizeof($proj['uiParamList']); $i++)
        for($j = 0; $j < sizeof($proj['uiParamList'][$i]['params']); $j++) {
            if (isset($value['params']))
              foreach ($value['params'] as $param) {
                if ($param['id'] == $proj['uiParamList'][$i]['params'][$j]['id']) {
                    $proj['uiParamList'][$i]['params'][$j]['value'] = $param['value'];
                    break;
                }
            }
        }
    if (isset($value['cluster_params']['mincpu']) &&
        isset($proj['cluster_params']['mincpu'])) {
        if (isset($value['cluster_params']['mincpu']['value']))
            $proj['cluster_params']['mincpu']['value'] =
                  $value['cluster_params']['mincpu']['value'];
        else
            $proj['cluster_params']['mincpu']['value'] =
                 isset($value['cluster_params']['mincpu']['value']) ?
                       $value['cluster_params']['mincpu']['value'] :
                       $value['cluster_params']['mincpu'];
    }
    if (isset($value['cluster_params']['queue']['value']) &&
        isset($proj['cluster_params']['queue'])) {
        if (isset($proj['cluster_params']['queue']['options']))
            $proj['cluster_params']['queue']['value'] =
                $value['cluster_params']['queue']['value'];
        else
            $proj['cluster_params']['queue'] = ['options'=>'auto_list',
                     'value'=>$value['cluster_params']['queue']['value']];
    }
    if (isset($value['cluster_params']['cwd']) &&
        isset($proj['cluster_params']['cwd']))
        $proj['cluster_params']['cwd'] = $value['cluster_params']['cwd'];
    if (isset($value['cluster_params']['distribution']) &&
        isset($proj['cluster_params']['distribution']))
        $proj['cluster_params']['distribution'] =
              $value['cluster_params']['distribution'];
    if (isset($value['cluster_params']['runlimit']) &&
        isset($value['cluster_params']['runlimit']['value']) &&
        isset($proj['cluster_params']['runlimit'])) {
            $proj['cluster_params']['runlimit']['value'] =
                 $value['cluster_params']['runlimit']['value'];
    }
    return $proj;
}

function loadProjData($proj, $appName)
{
    // error_log('appName:'.$appName);
    $dataDir = $_SESSION['home'].'/projects/.params';
    if (! myIs_Dir($_SESSION['home'].'/projects'))
        myMkdir($_SESSION['home'].'/projects');
    if (! myIs_Dir($dataDir))
        return $proj;
    if (isset($proj['apps'])) {
        for($i = 0; $i < sizeof($proj['apps']); $i++) {
            $filename = $dataDir.'/'.$appName.'-'.
                        $proj['apps'][$i]['appName'].'.yaml';
            if (!myFile_Exists($filename))
                continue;
            $str = myFile_Get_Contents($filename);
            $value = yaml_parse($str);
            $proj['apps'][$i] = addValue($proj['apps'][$i], $value);
        }
    } else {
        $filename = $dataDir.'/'.$appName.'-'.$appName.'.yaml';
        if (myFile_Exists($filename)) {
            $str = myFile_Get_Contents($filename);
            $value = yaml_parse($str);
            $proj = addValue($proj, $value);
        }
    }
    return $proj;
}
?>

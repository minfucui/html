<?php
include 'common.php';
include 'filefunctions.php';
include 'aipfunctions.php';

if (sizeof($_GET) < 1) {
    fail('Wrong request');
}

if (isset($_GET['app'])) {
    $name = urldecode($_GET['app']);
    $name = str_replace(' ', '+', $name);
    $file = $apppath.'/'.$name.'.yaml';
    $read = file_get_contents($file);
    $project = '';
} else if (isset($_GET['instance']) && isset($_GET['project'])) {
    $project = $_GET['project'];
    $name = $_GET['instance'];
    $file = $_SESSION['home'].'/projects/'.$project.'/'.$name.'.yaml';
    $read = myFile_Get_Contents($file);
} else {
    fail('Wrong request');
}

if ($read === FALSE) {
    fail('Cannot find file for: '.$name);
}
$vars = yaml_parse($read);
if (!isset($vars['appName']))
    $vars['appName'] = $name;
$icon = $vars['icon'];
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

$page = yaml_parse_file('./appform.yaml');
if ($page === FALSE)
    fail("Internal error");

$page['page_title'] = '应用实例: '.$name.' <img src="data:'.$icon.'" height="32"/>';
if ($project != '')
    $page['page_title'] = $page['page_title'].' '.$vars['appName'];
$form = $page['rows'][0]['form'];
$projOptions = [];
$projOptions[] = 'default';
$myproj = searchRows('projects', ['username'=>$uname]);
foreach ($myproj as $p)
    if ($p['projname'] != 'default')
        $projOptions[] = $p['projname'];
if ($project != '') {
    $form[0]['items'][0]['value'] = $project;
    $form[0]['items'][1]['value'] = $name;
}
$form[0]['items'][2]['value'] = $vars['appName'];
$form[0]['items'][0]['options'] = $projOptions;
if (isset($vars['cluster_params'])) {
    $clusterSec = [];
    $clusterSec['title'] = 'CLUSTER_PARAMETERS';
    $clusterSec['items'] = [];
    foreach ($vars['cluster_params'] as $key=>$value) {
        $item = [];
        if ($key == 'project' || $key == 'instance')
            continue;
        $item['id'] = $key;
        switch ($key) {
        case 'queue':
            $item['label'] = 'QUEUE';
            $item['type'] = 'select';
            $item['options'] = queuelist();
            if ($project != '' && $value != 'auto_list')
                $item['value'] = $value;
            break;
        case 'distribution':
            $item['label'] = 'MEMORY_ARCHITECTURE';
            $item['type'] = 'select';
            $item['options'] = ['smp','dmp'];
            if ($project != '' && strpos($value, '|') === FALSE)
                $item['value'] = $value;
            break;
        case 'mincpu':
            $item['label'] = 'CPUS';
            $item['type'] = 'select';
            $item['options'] = ['1','2','3','4','6','8','10','12','16',
                                '20','24','28','32','40','48','64','128','256'];
            if ($value != '1' && $value != 1)
                $item['value'] = strval($value);
            break;
        case 'cwd':
            $item['label'] = 'CURRENT_WORKING_DIR';
            $item['type'] = 'dir';
            if ($value != '$HOME')
                $item['value'] = $value;
            else
                $item['value'] = $_SESSION['home'];
            break;
        case 'gpu':
            $item['label'] = 'GPU';
            $item['type'] = 'select';
            $item['options'] = ['1','2','3','4','5','6','7','8'];
            if ($value != '1' && $value != 1)
                $item['value'] = strval($value);
            break;
        default:
            break;
        }
        $clusterSec['items'][] = $item;
    }
    $form[] = $clusterSec;
}

if ($project == '')
    $filepath = $_SESSION['home'].'/jobdata';
else {
    $filepath = $_SESSION['home'].'/jobdata/'.$project.'/'.$name;
    if (!myIs_Dir($filepath))
        myMkDir($filepath);
}

if (isset($vars['uiParamList'])) {
    foreach ($vars['uiParamList'] as $param) {
        $paramSec = [];
        $paramSec['title'] = $param['name'];
        foreach ($param['params'] as $ele) {
            $item = [];
            $item['label'] = $ele['filed_label'];
            $item['id'] = $ele['id'];
            if ($project != '')
                $item['value'] = $ele['value'];
            switch ($ele['filed_type']) {
            case 'input':
                $item['type'] = 'text';
                break;
            case 'select-single':
                $item['options'] = explode(',', $ele['value_range']);
                $item['type'] = 'select';
                break;
            case 'file-remote':
                if (strpos($ele['value_range'], '/dir/') !== FALSE) {
                    $item['type'] = 'dir';
                    if (!isset($ele['value']) || $ele['value'] == '')
                        $item['value'] = '$HOME';
                    else
                        $filepath = $ele['value'];
                } else {
                    $item['type'] = 'file';
                    if (!isset($ele['value']) || $ele['value'] == '')
                        $item['value'] = '选择文件';
                }
                break;
            case 'checkbox':
                $item['type'] = 'checkbox';
                break;
            default:
                break;
            }
            $paramSec['items'][] = $item;
        }
        $form[] = $paramSec;
    }
}

$page['rows'][0]['form'] = $form;
$page['rows'][2]['file']['path'] = $filepath;
$ret['data'] = $page;
echo json_encode($ret);
?>

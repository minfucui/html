<?php
include 'check_session.php';
include 'jobsdata.php';
include 'filefunctions.php';  // 文件操作接口
include 'accountbalance.php';
include 'projectData.php';
// 运行实例接口
if (!isset($_POST['appName'])) {
    echo $error_return;
    die();
}

if (isset($_SESSION['op_control']) && !balanceOK()) {  // 首先判断余额
    echo '{"code":"2005","message":"Low account balance"}'."\n";
    die();
}

$spec = $_POST;

setTimeZone();
if (!isset($spec['project']) || $spec['project'] == '') {
    if (isset($spec['appName2']) && $spec['appName'] != $spec['appName2'])
        $spec['project'] = $spec['appName'].'-'.$spec['appName2']/*.
          '-'.strftime('%Y%m%d%H%M%S') */;
    else
        $spec['project'] = $spec['appName']/*.'-'.strftime('%Y%m%d%H%M%S') */;
} else
    $spec['project'] = str_replace(' ','_',$spec['project']);

if (file_exists($apppath.'/'.$spec['appName'].'.yaml'))
    $read = file_get_contents($apppath.'/'.$spec['appName'].'.yaml');
else
    $read = FALSE;

if ($read === FALSE) {
    $files = scandir($apppath);
    if ($files && sizeof($files)) {
        foreach ($files as $f) {
            if (($f[0] == '.'
        || !file_exists($apppath.'/'.$f.'/'.$spec['appName'].'.yaml')) &&
            $f != '.batch')
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
if (isset($proj['dir'])) {
    $file = $proj['dir'].'/'.$spec['appName2'].'.yaml';
    $read = file_get_contents($file);
    if ($read === FALSE)
        fail('Cannot find application');
    $proj = yaml_parse($read);
    $spec['appName'] = $spec['appName2'];
}

$numFiles = 0;
$inputFiles = [];
$indexNo = -1;
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
for ($i = 0; $i < sizeof($proj['params']); $i++) {
    if ($proj['params'][$i]['filed_type'] == 'file-remote') {
        $inputFiles = explode(',', $proj['params'][$i]['value']);
        $numFiles = sizeof($inputFiles);
        $indexNo = $i;
    }
}
if (isset($proj['cluster_params']['jobtype']))
    $spec['clusterParams']['jobtype'] = $proj['cluster_params']['jobtype'];

foreach ($spec['clusterParams'] as $key=>$value)
    $proj['cluster_params'][$key] = $value;

if (isset($proj['cluster_params']['mincpu']))
    $proj['cluster_params']['mincpu'] =
         strval($proj['cluster_params']['mincpu']);
if (isset($proj['cluster_params']['maxcpu']))
    $proj['cluster_params']['maxcpu'] =
         strval($proj['cluster_params']['maxcpu']);
$proj['cluster_params']['instance'] = $spec['project'];
$proj['appName'] = $spec['appName'];
if (!isset($proj['cluster_params']['cwd'])
    && strpos($proj['common_params'], '-cwd') === false) {
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
if (isset($proj['cluster_params']['runlimit'])) {
    if (isset($proj['cluster_params']['runlimit']['options'])) {
        $n = sizeof($proj['cluster_params']['runlimit']['options']);
        for ($i = 0; $i < $n; $i++)
            $proj['cluster_params']['runlimit']['options'][$i] =
                 strval($proj['cluster_params']['runlimit']['options'][$i]);
    }
    if (isset($proj['cluster_params']['runlimit']['value']))
        $proj['cluster_params']['runlimit']['value'] =
            strval($proj['cluster_params']['runlimit']['value']);
}

unset($proj['icon']);

saveProjData($proj, $_POST['appName']);

function submitJob($proj) {
    global $cmdPrefix, $_POST, $_SESSION;
    $str = json_encode($proj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // error_log($str);

    exec($cmdPrefix.' cbtool a c \''.$str.'\'', $r, $errno);  // 提交作业准备
    if ($errno != 0)
        die('{"code":"2005","message":"cbtool fails - '.$r[0].'"}');

    // error_log('===cbtool:'.$r[0]);
    if ($r[0][0] == '{')  // JSON submission
        $cmd = $cmdPrefix." aip j r '".$r[0]."'";
    else {
        if (strpos($r[0], 'vncsub') !== FALSE &&
            isset($_POST['geometry'])) {
            $r[0] = str_replace('vncsub',
                                'vncsub -geometry '.$_POST['geometry'],
                                $r[0]);
        }
        $r[0] = str_replace('$HOME', $_SESSION['home'], $r[0]);
        $cmd = $cmdPrefix." '".$r[0]."'";
    }

    $r = [];
    // error_log('########'.$cmd);
    exec($cmd, $r, $errno);
    if ($errno != 0)
        return ['code'=>'2005', 'message'=>implode(';',$r), 'data'=>''];
    $ids = explode(' ', $r[0]);
    if (!is_numeric($ids[1]))
        return ['code'=>'2005', 'message'=>implode(';',$r), 'data'=>''];
    return ['code'=>'0', 'message'=>'call service success', 'data'=>$ids[1]];
}

if ($numFiles > 1) {  // 可以提交多个作业
    for ($i = 0; $i < $numFiles; $i++) {
       $proj['params'][$indexNo]['value'] = $inputFiles[$i];
       if ($i == 0)
           $ret = submitJob($proj);
       else
           submitJob($proj);
    }
} else
    $ret = submitJob($proj);

header('Content-Type: application/json');
echo json_encode($ret);
?>

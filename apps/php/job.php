<?php
include 'common.php';
include 'jobsdata.php';

function histJob($jobid)
{
    global $cmdPrefix;
    exec($cmdPrefix.'chist -n 0 -j '.$jobid, $cout, $errno);
    if ($errno != 0) {
        error_log("chist error:".implode(';'.$cout));
        return [];
    }
    for ($i = 0; $i < sizeof($cout); $i++) {
         $n = strpos($cout[$i], '"');
         if ($n !== FALSE) {
             $n++;
             $cout[$i][$n] = strtolower($cout[$i][$n]);
         }
    }
    $histdata = implode('',$cout);
    $jdata = json_decode($histdata, true);
    if ($jdata === FALSE)
        return [];
    // file_put_contents('../data/jobhist.json',$histdata);
    return $jdata[0];
}

function updateJob($jobid, $hist = false)
{
    global $uname, $_SESSION;
    if ($hist)
        $j = histJob($jobid);
    else
        $j = jobData($jobid, $uname);
    if (sizeof($j) == 0)
        return [];
    $job = [];
     
    $job['jobid'] = $j['jobID']['jobID'];
    switch ($j['statusString']) {
    case 'WAIT':
    case 'WSTOP':
        $style = '';
        break;
    case 'RUN':
        $style = ' class="text-primary"';
        break;
    case 'FINISH':
        $style = ' class="text-success"';
        break;
    case 'SYSSTOP':
    case 'USRSTOP':
        $style = ' class="text-warning"';
        break;
    default:
        $style = ' class="text-danger"';
        break;
    }
    $job['username'] = $j['user'];
    $job['status'] = '<span'.$style.'>'.$_SESSION['lang'][$j['statusString']].
                         '</span>';
    $job['ins_name'] = isset($j['jobSpec']['jobDescription']) ?
                       $j['jobSpec']['jobDescription'] : '-';
    $job['appname'] = isset($j['jobSpec']['application']) ?
                       $j['jobSpec']['application'] : '-';
    $job['project'] = $j['jobSpec']['project'];
    $job['submit_time'] = date("Y-m-d H:i:s", $j['submitTime']);
    $job['start_time'] = isset($j['startTime']) ?
                         date("Y-m-d H:i:s", $j['startTime']) : '-';
    $job['finish_time'] = isset($j['endTime']) ?
                         date("Y-m-d H:i:s", $j['endTime']) : '-';
    $job['queue'] = $j['jobSpec']['queue'];
    if (isset($j['jobSpec']['cwd'])) {
        $job['cwd'] = $j['jobSpec']['cwd'];
        if ($job['cwd'][0] != '/' && $job['cwd'] != '$HOME')
            $job['cwd'] = $_SESSION['home'].'/'.$job['cwd'];
    } else
        $job['cwd'] = $_SESSION['home'];
    $job['command'] = substr($j['jobSpec']['command'], 0, 50);
    if (isset($j['execHosts'])) {
        $job['ncpus'] = sizeof($j['execHosts']);
        if (isset($j['runTime'])) {
            $m = intval($j['runTime'] / 60);
            $s = $j['runTime'] % 60;
            $h = intval($m / 60);
            $m = $m % 60;
            $job['runtime'] = ($h < 10 ? '0':'').$h.':'.
                              ($m < 10 ? '0':'').$m.':'.
                              ($s < 10 ? '0':'').$s;
        } else
            $job['runtime'] = '0';
        if (isset($j['resource']['mem']))
            $job['memusage'] = number_format($j['resource']['mem']/1024/1024,
                             4).'GB';
        else
            $job['memusage'] = 0;
    }
    else {
        $job['ncpus'] = '-';
        $job['runtime'] = 0;
        $job['memusage'] = 0;
    }
    if (isset($j['jobSpec']['outFile']) &&
        $j['jobSpec']['outFile']['name'] != '/dev/null')
        $job['output'] = $j['jobSpec']['outFile']['name'];
    else
        $job['output'] = '-';
    if (isset($j['waitReason']))
        $job['pending_reason'] = $j['waitReason'];
    else
        $job['pending_reason'] = '-';
    if (isset($j['stopReason']))
        $job['susp_reason'] = $j['stopReason'];
    else
        $job['susp_reason'] = '-';
    if (isset($j['msg']) && isset($j['msg']['content']))
        $job['guiurl'] = $j['msg']['content'];
    else
        $job['guiurl'] = '';
    return $job;
}

if (!isset($_GET['jobid']) && !isset($_POST['action']))
    fail('Wrong request');

if (isset($_GET['jobid'])) {
    $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
    if ($page === FALSE)
        fail("Internal error");
    $job = updateJob($_GET['jobid']);
    $rows = $page['rows'][0]['description']['rows'];
    foreach($rows as $key=>$row)
       $rows[$key]['value'] = $job[$key];
    $page['rows'][0]['description']['rows'] = $rows;
    $page['rows'][0]['description']['title'] = $_GET['jobid'];
    if ($job['cwd'][0] != '/') {
        if (strpos($job['cwd'], '$HOME') !== FALSE)
            $job['cwd'] = str_replace('$HOME', $_SESSION['home'], $job['cwd']);
        else
            $job['cwd'] = $_SESSION['home'].'/'.$job['cwd'];
    }
    $page['rows'][1]['file']['path'] = $job['cwd'];
    $page['page_title'] = $page['page_title'].' '.$_GET['jobid'];
    if ($job['username'] != $uname) {
        unset($page['rows'][1]);
        unset($page['rows'][2]);
        $page['rows'][0]['width'] = 12;
    }
    $ret['data'] = $page;
} else if ($_POST['action'] == 'update') {
    $job = updateJob($_POST['id']);
    $description = [];
    foreach ($job as $key=>$value)
        $description[$key] = ['value'=>$value];
    $ret['data']['description'] = $description;
    $ret['data']['output'] = shell_exec($cmdPrefix.' aip j l '.$_POST['id']);
    if (isset($job['guiurl']))
        $ret['data']['guiurl'] = $job['guiurl'];
    else
        $ret['data']['guiurl'] = '';
} else if ($_POST['action'] == 'hist') {
    $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
    $job = updateJob($_POST['id'], true);
    $description = $page['rows'][0]['description'];
    foreach($description['rows'] as $key=>$row)
       $description['rows'][$key]['value'] = $job[$key];
    unset($description['update']);
    $ret['data']['description'] = $description;
}
echo json_encode($ret);
?>

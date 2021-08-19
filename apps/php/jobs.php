<?php
include 'common1.php';
include 'jobsdata.php';
include 'filefunctions.php';

function updateJobs($filter)
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $jobs = [];
    $jdata = allJobs();
    $queueF = '';
    $hostF = '';
    $userF = '';
    $appF = '';
    if ($filter != '') {
        $f = explode('=', $filter);
        switch($f[0]) {
        case 'queue':
            $queueF = $f[1];
            break;
        case 'host':
            $hostF = $f[1];
            break;
        case 'user':
            $userF = $f[1];
            break;
        case 'app':
            $appF = $f[1];
            break;
        default:
            break;
        }
    }
    foreach ($jdata as $j) {
        $job = [];
        if ($queueF != '' && $j['jobSpec']['queue'] != $queueF)
            continue;
        if ($userF != '' && $j['user'] != $userF)
            continue;
        if ($hostF != '' && !in_array($hostF, $j['execHosts']))
            continue;
        if ($appF != '' && (!isset($j['jobSpec']['application']) ||
            $j['jobSpec']['application'] != $appF))
            continue;

        $job['jobid'] = $j['jobID']['jobID'].' php/job.php?jobid='.
                        $j['jobID']['jobID'];
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
        $job['status'] = '<span'.$style.'>'.$_SESSION['lang'][$j['statusString']].
                         '</span>';
        $job['ins_name'] = isset($j['jobSpec']['jobDescription']) ?
                           $j['jobSpec']['jobDescription'] : '';
        $job['appname'] = isset($j['jobSpec']['application']) ?
                           $j['jobSpec']['application'] : '';
        $job['submit_time'] = date("Y-m-d H:i:s", $j['submitTime']);
        $job['start_time'] = isset($j['startTime']) ?
                             date("Y-m-d H:i:s", $j['startTime']) : '';
        $job['finish_time'] = isset($j['endTime']) ?
                             date("Y-m-d H:i:s", $j['endTime']) : '';
        $job['queue'] = $j['jobSpec']['queue'];
        $job['user'] = $j['user'];
        if (isset($j['msg']) && isset($j['msg']['content']) &&
            $j['statusString'] == 'RUN' && $j['user'] == $uname) {
            $s = $j['msg']['content'];
            if (strlen($s) == 32) {
                $session = myFile_Get_Contents($_SESSION['home'].'/.vnc/session.'.
                          $j['jobID']['jobID']);
                if ($session !== FALSE) {
                    $s = $s.' '.$session;
                }
            }
            $job['share'] = '<a href="javascript:new_file_share(\''.
                            $j['jobID']['jobID'].'\',\''.$s.'\');">'.$lang['SHARE'].'</a>';
        }
        else
            $job['share'] = '';
        $jobs[] = $job;
    }
    return $jobs;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        if (isset($_GET['host']))
            $page['page_title'] = '主机'.$_GET['host'].'上的作业';
        if (isset($_GET['queue']))
            $page['page_title'] = '队列'.$_GET['queue'].'里的作业';
        if (isset($_GET['user']))
            $page['page_title'] = '用户'.$_GET['user'].'递交的作业';
        if (isset($_GET['app']))
            $page['page_title'] = '应用'.$_GET['app'].'相关的作业';
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateJobs($_POST['filter']);
        break;
    case 'kill':
    case 'stop':
    case 'resume':
        $objects = $_POST['data'];
        if (($n = sizeof($objects)) < 1)
            break;
        for ($i = 0; $i < $n; $i++) {
            $job = explode(' ', urldecode($objects[$i]));
            $objects[$i] = $job[0];
        }
        exec($cmdPrefix.'c'.$_POST['action'].' '.implode(' ', $objects),
             $r, $eno);
        if ($eno != 0) {
            $ret['code'] = 200;
            $ret['message'] = implode('<p>', $r);
        }
        skylog_activity($uname, WORK, 'job '.$_POST['action'], implode(' ', $objects));
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

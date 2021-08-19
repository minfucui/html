<?php
include 'check_session.php';
include 'jobsdata.php';  // 查询作业信息接口（如详情、vnc地址、日志等）

function jobAction($jobId, $action, $param = '')  // 对作业执行的操作选项，包括查询作业详情，调用jobsdata.php里的方法
{
    global $cmdPrefix;
    if ($action == 's')
        return shell_exec($cmdPrefix.' cstop '.$jobId);
    else if ($action == 'rs')
        return shell_exec($cmdPrefix.' cresume '.$jobId);
    else if ($action == 'rr')
        return shell_exec($cmdPrefix.' crequeue '.$jobId);
    else if ($action == 'rl') {
        if (intval($param) > 0)
            return shell_exec($cmdPrefix.' cmod -W '.$param.' '.$jobId);
        else
            return shell_exec($cmdPrefix.' cmod -Wn '.$jobId);
    }
    else return shell_exec($cmdPrefix.' aip j '.$action.' '.$jobId);
}

function cwd($jobSpec)
{
    if (isset($jobSpec['cwd'])) {
        if (substr($jobSpec['cwd'], 0, 1) != '/')
            return $_SESSION['home'].'/'.$jobSpec['cwd'];
        return $jobSpec['cwd'];
    } else
        return $_SESSION['home'];
}

$ret['code'] = 0;
$ret['message'] = 'call service success';
$ret['data'] = [];

if (!isset($_GET['action'])) {
    echo $error_return;
    die();
}

if (isset($_POST['jobId']))
    $jobId = $_POST['jobId'];

switch($_GET['action']) {
    case 'queryJobDetail':
        $ret['data'] = jobData($jobId, $uname);
        if (isset($ret['data']['jobSpec']))  // 特殊作业
            $ret['data']['jobSpec']['cwd'] = cwd($ret['data']['jobSpec']);
        break;
    case 'queryJobHist':  // 查询作业历史
        $cmdLine = $cmdPrefix.' chist -j '.$jobId;
        exec($cmdLine, $r, $errno);
        for($i = 0; $i < sizeof($r); $i++) {
            $n = strpos($r[$i], '"');
            if ($n !== FALSE) {
                $n++;
                $r[$i][$n] = strtolower($r[$i][$n]);
            }
        }
        $jobs = json_decode(implode('', $r), true);
        $ret['data'] = $jobs[0];
        break;
    case 'queryJobVncUrl':
        $job = jobData($jobId, $uname);
        for ($i = 0; $i < 3 && sizeof($job) == 0; $i++) {
            usleep(300);
            $job = jobData($jobId, $uname);
        }
        if (!isset($job['msg'])) {
            if (isset($job['jobSpec'])) {
                if (!isset($job['jobSpec']['jobName']) || (
               $job['jobSpec']['jobName'] != 'cubevnc'
               && $job['jobSpec']['jobName'] != 'dcv'
               && $job['jobSpec']['jobName'] != 'jupyter'
               && (isset($job['jobSpec']['jobType']) && $job['jobSpec']['jobType'] != 'vmware')
               && strpos($job['jobSpec']['jobName'], 'GUI') === FALSE))
                $ret['data'] = 'no vnc url';
            } else
                $ret['data'] = 'url wait';
        } else {
            if (strpos($job['msg']['content'], 'http') === FALSE
                && strpos($job['msg']['content'], 'novnc') === FALSE
                && strlen($job['msg']['content']) != 32) {
                $ret['data'] = 'url wait';
            } else {
                if (strlen($job['msg']['content']) == 32)
                    $ret['data'] = 'php/vnc.php?jobid='.$jobId.'&pw='.$job['msg']['content'];
                else
                    $ret['data'] = $job['msg']['content'];
            }
        }
        break;
    case 'queryJobLog':
        $action = 'l';
        $ret['data'] = jobAction($jobId, $action);
        break;
    case 'stopJob':
        $action = 's';
        $ret['data'] = jobAction($jobId, $action);
        break;
    case 'resumeJob':
        $action = 'rs';
        $ret['data'] = jobAction($jobId, $action);
        break;
    case 'reRunJob':
        $action = 'rr';
        $ret['data'] = jobAction($jobId, $action);
        break;
    case 'killJob':
        $action = 'k';
        $ret['data'] = jobAction($jobId, $action);
        break;
    case 'queryJob':
    case 'queryAllJob':
        $ret['data'] = allJobData($uname);
        if (isset($_POST['project'])) {
            $n = sizeof($ret['data']);
            for ($i = 0; $i < $n; $i++) {
                if (!isset($ret['data'][$i]['jobSpec']['jobDescription']) ||
                    $ret['data'][$i]['jobSpec']['jobDescription'] !=
                    $_POST['project'])
                    unset($ret['data'][$i]);
            }
            $ret['data'] = array_values($ret['data']);
        }
        if (isset($_POST['app'])) {
            $n = sizeof($ret['data']);
            for ($i = 0; $i < $n; $i++) {
                if (!isset($ret['data'][$i]['jobSpec']['application']) ||
                    $ret['data'][$i]['jobSpec']['application'] !=
                    $_POST['app'])
                    unset($ret['data'][$i]);
                if (!isset($ret['data'][$i]['jobSpec']['jobDescription']) ||
                    strpos($ret['data'][$i]['jobSpec']['jobDescription'], $_POST['app']/*.'-'*/) === FALSE)
                    unset($ret['data'][$i]);
            }
            $ret['data'] = array_values($ret['data']);
        }
        if (isset($_POST['jobId'])) {
            $ids = explode(' ', $_POST['jobId']);
            $jobs = [];
            foreach ($ret['data'] as $job) {
                foreach ($ids as $id) {
                    if ($job['jobID']['jobID'] == $id) {
                         $jobs[] = $job;
                         break;
                    }
                }
            }
            $ret['data'] = $jobs;
        }
        for ($i = 0; $i < sizeof($ret['data']); $i++) {
            if (isset($ret['data'][$i]['msg']['content']) &&
                strlen($ret['data'][$i]['msg']['content']) == 32)
                $ret['data'][$i]['msg']['content'] = 'php/vnc.php?jobid='.
                     $ret['data'][$i]['jobID']['jobID'].'&pw='.
                     $ret['data'][$i]['msg']['content'];
            $ret['data'][$i]['jobSpec']['cwd'] = cwd($ret['data'][$i]['jobSpec']);
        }
        break;
    case 'setRunLimit':
        $ret['data'] = jobAction($jobId, 'rl', $_POST['runLimit']);
        break;
    default:
        $ret['code'] = 200;
        $ret['message'] = 'Wrong call';
        break;
}

echo json_encode($ret);
?>

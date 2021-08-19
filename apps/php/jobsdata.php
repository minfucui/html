<?php
function getJobData()
{
    global $datapath;
    global $cmdpath;
    $lock_file = '../data/lock';
    if (!file_exists($lock_file)) {
        $now = time();
        $file_time = filemtime('../data/jobs.json');
        if ($file_time === FALSE)
            $file_time = 0;
        if ($now - $file_time >= 1) {
            touch ($lock_file);
            exec('export OLWD=A1uy3LpGhy;source '.
               $cmdpath.'/env.sh;'.$cmdpath.
               '/cmd/runas root aip j i -u all -l -s a -m', $r, $errno);
            $jobdata = '';
            for($i = 0; $i < sizeof($r); $i++) {
                $n = strpos($r[$i], '"');
                if ($n !== FALSE) {
                    $n++;
                    $r[$i][$n] = strtolower($r[$i][$n]);
                }
                $jobdata = $jobdata.$r[$i]."\n";
            }
            file_put_contents($datapath.'/data/jobs.json', $jobdata);
            unlink($lock_file);
        }
    }
}

function jobStatusChange($user)
{
    global $datapath;
    global $cmdpath;
    $change = [];
    $status_file = $datapath.'/data/'.$user.'_status.json';
    getJobData();
    if (!file_exists($datapath.'/data/jobs.json'))
        return $change;
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata === FALSE)
        return $change;
    $jobs = json_decode($jobdata, TRUE);
    if ($jobs === FALSE || sizeof($jobs) == 0)
        return $change;
    $prev_stats = [];
    if (file_exists($status_file)) {
        $prev_status_data = file_get_contents($status_file);
        if ($prev_status_data !== FALSE)
            $prev_status = json_decode($prev_status_data, TRUE);
    }
    $new_status = [];
    $time = strftime("%m月%d日 %H:%M:%S");
    foreach ($jobs as $job) {
        if ($job['user'] != $user)
             continue;
        $jobID = $job['jobID']['jobID'];
        $new_status[$jobID] = $job['statusString'];
        if (!isset($prev_status[$jobID]) ||
            $prev_status[$jobID] != $new_status[$jobID]) {
            $entry['entity'] = $jobID;
            $entry['project'] = $job['jobSpec']['project'];
            $entry['instance'] = isset($job['jobSpec']['jobDescription']) ? 
                                 $job['jobSpec']['jobDescription'] : '';
            $entry['status'] = $job['statusString'];
            $entry['time'] = $time;
            $change[] = $entry;
        }
    }
    if (sizeof($change) != 0) {
        $new_status_data = json_encode($new_status);
        file_put_contents($status_file, $new_status_data);
    }
    return $change;
}

function returnJob($jobsdata, $jobId, $user)
{
    global $_SESSION;
    $ret = [];
    $jobs = json_decode($jobsdata, TRUE);
    if ($jobs === FALSE || sizeof($jobs) == 0)
        return $ret;
    foreach ($jobs as $job) {
        if ($_SESSION['roles'][0] != $_SESSION['lang']['ADMIN'] 
            && $job['user'] != $user)
            continue;
        if ($job['jobID']['jobID'] == $jobId) {
            $ret = $job;
            break;
        }
    }
    return $ret;
}

function runningProject($proj, $user)
{
    global $datapath;
    $jobids = [];
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata === FALSE || sizeof($jobdata) == 0)
        return $jobids;
    $jobs = json_decode($jobdata, TRUE);
    if ($jobs === FALSE || sizeof($jobs) == 0)
        return $jobids;
    foreach($jobs as $job) {
        if ($job['user'] == $user && $job['jobSpec']['project'] == $proj
            && $job['statusString'] != 'FINISH' && $job['statusString'] != 'EXIT' 
            && $job['statusString'] != 'UNKOWN')
            $jobids[] = $job['jobID']['jobID'];
    }
    return $jobids;
}

function jobData($jobId, $user)
{
    global $datapath;
    global $cmdpath;
    $lock_file = '../data/lock';
    $ret = [];
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata === FALSE || sizeof($jobdata) == 0)
        return $ret;
    $ret = returnJob($jobdata, $jobId, $user);
    if (sizeof($ret) == 0) {
        usleep(500);
        getJobData();
        $jobdata = file_get_contents($datapath.'/data/jobs.json');
        if ($jobdata === FALSE || sizeof($jobdata) == 0)
            return $ret;
        $ret = returnJob($jobdata, $jobId, $user);
    }
    return $ret;
}

function allJobData ($user)
{
    global $datapath;
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata == FALSE || sizeof($jobdata) == 0)
        return [];
    $ret = [];
    $jobs = json_decode($jobdata, TRUE);
    if ($jobs === FALSE or sizeof($jobs) == 0)
        return $ret;
    foreach ($jobs as $job) {
        if ($job['user'] != $user)
            continue;
        $ret[] = $job;
    }
    return $ret;
}

function activeJobData ($user)
{
    global $datapath;
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata == FALSE || sizeof($jobdata) == 0)
        return [];
    $ret = [];
    $jobs = json_decode($jobdata, TRUE);
    if ($jobs === FALSE or sizeof($jobs) == 0)
        return $ret;
    foreach ($jobs as $job) {
        if ($job['user'] != $user || $job['statusString'] == 'FINISH' ||
            $job['statusString'] == 'EXIT')
            continue;
        $ret[] = $job;
    }
    return $ret;
}

function allJobs()
{
    global $datapath, $_SESSION, $uname;
    $lang = $_SESSION['lang'];
    if (strpos(implode(' ', $_SESSION['roles']), $lang['ADMIN']) === FALSE ||
        strpos(implode(' ', $_SESSION['roles']), $lang['GROUP_ADMIN']) !== FALSE)
        return allJobData($uname);
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata == FALSE || sizeof($jobdata) == 0)
        return [];
    return json_decode($jobdata, TRUE);
}
?>

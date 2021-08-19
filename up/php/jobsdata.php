<?php
function getJobData()  // 作业信息的一些查询接口
{
    global $datapath;
    global $cmdpath;
    $lock_file = '../data/lock';  // 是否锁死数据，存在代表不能修改
    if (!file_exists($lock_file)) {
        $now = time();
        $file_time = filemtime('../data/jobs.json');
        if ($file_time === FALSE)
            $file_time = 0;
        if ($now - $file_time >= 1) {
            touch ($lock_file);  // 

            exec('export OLWD=A1uy3LpGhy;source '.
               $cmdpath.'/env.sh;'.$cmdpath.
               '/cmd/runas root aip j i -u all -l -s a -m', $r, $errno);  // 执行aip命令，查看所有作业信息
            $jobdata = '';
            for($i = 0; $i < sizeof($r); $i++) {
                $n = strpos($r[$i], '"');  // 查找字符串第一次出现的位置
                if ($n !== FALSE) {
                    $n++;
                    $r[$i][$n] = strtolower($r[$i][$n]);  // 转为小写
                }
                $jobdata = $jobdata.$r[$i]."\n";
            }
            file_put_contents($datapath.'/data/jobs.json', $jobdata);  // 保存作业数据
            unlink($lock_file);  // 删除文件，解锁
        }
    }
}

function jobStatusChange($user)  // 更新作业状态
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
    setTimeZone();
    $time = strftime("%m月%d日 %H:%M:%S");
    foreach ($jobs as $job) {
        if ($job['user'] != $user)  // 只要当前用户的作业，说明jobs.json里保存的确实是所有人的作业
             continue;
        $jobID = $job['jobID']['jobID'];
        $new_status[$jobID] = $job['statusString'];
        if (!isset($prev_status[$jobID]) ||
            $prev_status[$jobID] != $new_status[$jobID]) {
            $entry['entity'] = $jobID;
            $entry['project'] = isset($job['jobSpec']['jobDescription']) ?
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

function returnJob($jobsdata, $jobId, $user)  // 根据jobId查询作业信息
{
    $ret = [];
    $jobs = json_decode($jobsdata, TRUE);
    if ($jobs === FALSE || sizeof($jobs) == 0)
        return $ret;
    foreach ($jobs as $job) {
        if ($job['user'] != $user)
            continue;
        if ($job['jobID']['jobID'] == $jobId) {
            $ret = $job;
            break;
        }
    }
    return $ret;
}

function runningProject($proj, $user)  // 根据实例名查找运行中的实例作业
{
    global $datapath;
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata === FALSE || sizeof($jobdata) == 0)
        return FALSE;
    $jobs = json_decode($jobdata, TRUE);
    if ($jobs === FALSE || sizeof($jobs) == 0)
        return FALSE;
    foreach($jobs as $job) {
        if (isset($job['jobSpec']['jobDescription'])
            && $job['user'] == $user && $job['jobSpec']['jobDescription'] == $proj
            && $job['statusString'] != 'FINISH' && $job['statusString'] != 'EXIT' 
            && $job['statusString'] != 'UNKOWN')
            return $job['jobID']['jobID'];
    }
    return FALSE;
}

function jobData($jobId, $user)
{
    global $datapath;
    global $cmdpath;
    $lock_file = '../data/lock';
    $ret = [];
    getJobData();
    $jobdata = file_get_contents($datapath.'/data/jobs.json');
    if ($jobdata === FALSE || sizeof($jobdata) == 0) {
        return $ret;
    }
    $ret = returnJob($jobdata, $jobId, $user);
    if (sizeof($ret) == 0) {
        usleep(500);  // 延迟执行当前脚本500微秒
        getJobData();
        $jobdata = file_get_contents($datapath.'/data/jobs.json');
        if ($jobdata === FALSE || sizeof($jobdata) == 0)
            return $ret;
        $ret = returnJob($jobdata, $jobId, $user);
    }
    return $ret;
}

function allJobData ($user)  // 获得该用户所有作业的所有信息
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
?>

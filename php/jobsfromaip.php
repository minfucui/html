<?PHP
session_start();
include '../clusters.php';
$uname=$_SESSION['uname'];
$pword=$_SESSION['password'];

ini_set('memory_limit', '1G');
$TOP_MAX=5;

$output['numRunUsers'] = 0;
$output['numPendUsers'] = 0;
$output['numRunJobs'] = 0;
$output['numPendJobs'] = 0;
$output['topRunJobs'] = array();
$output['topPendJobs'] = array();

function exit_empty($exit_code) {
    echo json_encode($GLOBALS['output']);
    exit($exit_code);
}

$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ../env.sh;'.$setenvdir.'../cmd/runas '.$uname;
$esout = shell_exec($cmdPrefix.' aip j i -u all -l');
$jobs = json_decode($esout, true);

if (sizeof($jobs) == 0) {
    exit_empty(0);
}

$timenow = time();
$pendUsers = array();
$runUsers = array();

function pendTimeComp($j1, $j2)
{
    if ($j1['pendTime'] > $j2['pendTime'])
        return -1;
    if ($j1['pendTime'] < $j2['pendTime'])
        return 1;
    return 0;
}

function runTimeComp($j1, $j2)
{
    if ($j1['runTime'] > $j2['runTime'])
        return -1;
    if ($j1['runTime'] < $j2['runTime'])
        return 1;
    return 0;
}

$i = 0;
foreach($jobs as $job) {
    if ($job['StatusString'] == "WAIT" || $job['StatusString'] == "WSTOP") {
        $submitTime = $job['SubmitTime'];
        $pendtime = $timenow - $submitTime;
        $jobs[$i]['pendTime'] = $pendtime;
        $jobs[$i]['runTime'] = 0;
        if (!in_array($job['User'], $pendUsers))
            array_push($pendUsers, $job['User']);
        $output['numPendJobs']++;
    } else {
        $startTime = $job['StartTime'];
        $runtime = $timenow - $startTime;
        $jobs[$i]['pendTime'] = 0;
        $jobs[$i]['runTime'] = $runtime;
        if (!in_array($job['User'], $runUsers))
            array_push($runUsers, $job['User']);
        $output['numRunJobs']++;
    }
    $i++;
}

$output['numRunUsers'] = sizeof($runUsers);
$output['numPendUsers'] = sizeof($pendUsers);

function jobid($job) {
    if (!isset($job['JobID']['JobIndex']))
        $jid = strval($job['JobID']['JobID']);
    else
        $jid = strval($job['JobID']['JobID']).'['.
               strval($job['JobID']['JobIndex']).']';
    return $jid;
}

function joburl($job) {
    if (!isset($job['JobID']['JobIndex']))
        $idx = "0";
    else
        $idx = strval($job['JobID']['JobIndex']).']';
    return "job.php?jobid=".strval($job['JobID']['JobID']).
           "&idx=".$idx;
}

usort($jobs, "pendTimeComp");

for ($i = 0; $i < $TOP_MAX; $i++) {
    if (!isset($jobs[$i]))
        break;
    if ($jobs[$i]['pendTime'] == 0)
        continue;
    $j['jobId'] = jobid($jobs[$i]);
    $j['url'] = joburl($jobs[$i]);
    $j['jobName'] = $jobs[$i]['JobSpec']['JobName'];
    $j['userName'] = $jobs[$i]['User'];
    $j['pendTime'] = floatval($jobs[$i]['pendTime'])/3600.0;
    $j['pendReasons'] = $jobs[$i]['WaitReason'];
    $output['topPendJobs'][$i] = $j;
}

usort($jobs, "runTimeComp");

$j = array();
for ($i = 0; $i < $TOP_MAX; $i++) {
    if (!isset($jobs[$i]))
        break;
    if ($jobs[$i]['runTime'] == 0)
        continue;
    $j = array();
    $j['jobId'] = jobid($jobs[$i]);
    $j['url'] = joburl($jobs[$i]);
    $j['jobName'] = $jobs[$i]['JobSpec']['JobName'];
    $j['userName'] = $jobs[$i]['User'];
    $j['runTime'] = floatval($jobs[$i]['runTime'])/3600.0;
    $output['topRunJobs'][$i] = $j;
}

echo json_encode($output);

?>

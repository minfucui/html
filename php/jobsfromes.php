<?PHP
ini_set('memory_limit', '10G');
$MAX_SEARCH='40000';
$TOP_MAX=5;

$output['numRunUsers'] = 0;
$output['numPendUsers'] = 0;
$output['numRunJobs'] = 0;
$output['numPendJobs'] = 0;
$output['topRunJobs'] = array();
$output['topPendJobs'] = array();

function param($line, $key) {
    $subline = stristr($line, $key);
    if (!$subline)
        return false;
    $subline = strstr($subline, '=');
    if (!$subline)
        return false;
    $pos = strpos($subline, '#');
    if ($pos)
        $subline = substr($subline, 0, $pos);
    $subline = substr($subline, 1);
    return trim($subline);
}

function exit_empty($exit_code) {
    echo json_encode($GLOBALS['output']);
    exit($exit_code);
}

$envdir = shell_exec('source ../env.sh;echo $CB_ENVDIR');
$envdir = str_replace("\n","",$envdir);
$conf = $envdir.'/olmon.conf';
$cluster = shell_exec('source ../env.sh;lsid | grep -i "My cluster name" | awk \'{print $5}\'');
$cluster = str_replace("\n","", $cluster);
if ($cluster == '')
    exit_empty(1);

if (!file_exists($conf)) {
    exit_empty(0);
}

$fh = fopen($conf, "r");
if (! $fh) {
    exit_empty(1);
}

while ($line = fgets($fh, 1024)) {
    if (substr($line, 0, 1) == '#')
        continue;
    if ($param = param($line, "eshosts")) {
        $eshosts = explode(',', $param);
        continue;
    }
}
fclose($fh);
$curl = 'curl -s -XPOST -H "Content-Type: application/json" ';
$url1 = 'http://'.$eshosts[0].':9200/jobs/_search?size='.$MAX_SEARCH.' ';
if (isset($eshosts[1]))
    $url2 = 'http://'.$eshosts[1].':9200/jobs/_search?size='.$MAX_SEARCH.' ';
else
    $url2 = '';
$query = '{"query":
            {"bool":
               {"must":
                  [{"bool":{"should":[{"match":{"status":"RUN"}},
                                      {"match":{"status":"PEND"}},
                                      {"match":{"status":"PSUSP"}},
                                      {"match":{"status":"USUSP"}},
                                      {"match":{"status":"SSUSP"}},
                                      {"match":{"status":"UNKWN"}}]}},
                   {"bool":{"must":[{"match":{"cluster":"'.$cluster.'"}}]}}
                  ]
               }
            }
          }';
$esout = shell_exec($curl.$url1."-d '".$query."'");
$esoutStruct = json_decode($esout, true);
$jobs = $esoutStruct['hits']['hits'];

if (sizeof($jobs) == 0) {
    exit_empty(0);
}

$timenow = time();
exec("../cmd/timezone", $out, $exit_code);
$timezone=$out[0];
if ($exit_code == 1) {
    $timenow += 3600;
}
date_default_timezone_set($timezone);
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
foreach($jobs as $eachjob) {
    $job = $eachjob['_source'];
    if ($job['status'] == "PEND" || $job['status'] == "PSUSP") {
        $submitTime = strtotime($job['submitTime']);
        $pendtime = $timenow - $submitTime;
        $jobs[$i]['pendTime'] = $pendtime;
        $jobs[$i]['runTime'] = 0;
        if (!in_array($job['userName'], $pendUsers))
            array_push($pendUsers, $job['userName']);
        $output['numPendJobs']++;
    } else {
        $startTime = strtotime($job['startTime']);
        $runtime = $timenow - $startTime;
        $jobs[$i]['pendTime'] = 0;
        $jobs[$i]['runTime'] = $runtime;
        if (!in_array($job['userName'], $runUsers))
            array_push($runUsers, $job['userName']);
        $output['numRunJobs']++;
    }
    $i++;
}

$output['numRunUsers'] = sizeof($runUsers);
$output['numPendUsers'] = sizeof($pendUsers);

function jobid($job) {
    if ($job['_source']['idx'] == 0)
        $jid = strval($job['_source']['jobId']);
    else
        $jid = strval($job['_source']['jobId']).'['.
               strval($job['_source']['idx']).']';
    return $jid;
}

function joburl($job) {
    return "job.php?jobid=".strval($job['_source']['jobId']).
           "&idx=".strval($job['_source']['idx']);
}

usort($jobs, "pendTimeComp");

for ($i = 0; $i < $TOP_MAX; $i++) {
    if (!isset($jobs[$i]))
        break;
    if ($jobs[$i]['pendTime'] == 0)
        continue;
    $j['jobId'] = jobid($jobs[$i]);
    $j['url'] = joburl($jobs[$i]);
    $j['jobName'] = $jobs[$i]['_source']['jobName'];
    $j['userName'] = $jobs[$i]['_source']['userName'];
    $j['pendTime'] = floatval($jobs[$i]['pendTime'])/3600.0;
    $j['pendReasons'] = $jobs[$i]['_source']['pendReasons'];
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
    $j['jobName'] = $jobs[$i]['_source']['jobName'];
    $j['userName'] = $jobs[$i]['_source']['userName'];
    $j['runTime'] = floatval($jobs[$i]['runTime'])/3600.0;
    $output['topRunJobs'][$i] = $j;
}

echo json_encode($output);

?>

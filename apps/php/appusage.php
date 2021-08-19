<?php
include 'common1.php';
include 'jobsdata.php';

function updateData()
{
    global $_SESSION, $uname;
    $lang = $_SESSION['lang'];
    $appdata = searchRows('applications');

    $apps = [];
    if (isset($appdata['error']))
        fail($appdata['error']);

    foreach ($appdata as $a) {
        $app = [];
        $app['name'] = $a['appname'];
        $app['njobs'] = $app['npend'] = $app['nrun'] = $app['nstop'] = 0;
        $apps[] = $app;
    }
    
    $jobs = allJobs();
    $napps = sizeof($apps);
    foreach ($jobs as $j) {
        if (!isset($j['jobSpec']['application']))
            continue;
        // error_log('==job found:'.$j['jobID']['jobID']);
        for ($i = 0; $i < $napps; $i++) {
            if ($j['jobSpec']['application'] == $apps[$i]['name']) {
                switch ($j['statusString']) {
                case 'WAIT':
                case 'WSTOP':
                    $apps[$i]['npend'] += $j['jobSpec']['maxNumSlots'];
                    $apps[$i]['njobs'] += $j['jobSpec']['maxNumSlots'];
                    break;
                case 'RUN':
                    $apps[$i]['nrun'] += sizeof($j['execHosts']);
                    $apps[$i]['njobs'] += sizeof($j['execHosts']);
                    break;
                case 'SYSSTOP':
                case 'USRSTOP':
                    $apps[$i]['nstop'] += sizeof($j['execHosts']);
                    $apps[$i]['njobs'] += sizeof($j['execHosts']); 
                    break;
                default:
                    break;
                }
                break;
            }
        }
    }
    for ($i = 0; $i < $napps; $i++) {
        $name = $apps[$i]['name'];
        $apps[$i]['name'] = '<a href="javascript:openurl(\'php/jobs.php?app='.
                 $name.'\',\'作业\',\'\');">'.$name.'</a>';
    }

    return $apps;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateData();
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

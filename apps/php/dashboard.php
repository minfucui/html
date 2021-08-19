<?php
include 'common1.php';

$tablename = 'permissions';

function cmpuser($a, $b) {
    if ($a['ISGROUP'] == 'n' && $b['ISGROUP'] == 'y')
        return 1;
    if ($a['ISGROUP'] == 'y' && $b['ISGROUP'] == 'n')
        return -1;
    if ($a['ISGROUP'] == 'y' && $b['ISGROUP'] == 'y')
        return 0;
    return intval($a['NUM_JOBS']) - intval($b['NUM_JOBS']);
}

function cmpqueue($a, $b) {
    return $a['NJobs'] - $b['NJobs'];
}

function updateData()
{
    global $_SESSION, $cmdPrefix;
    $lang = $_SESSION['lang'];
    $data = [];
    $cout = shell_exec($cmdPrefix.'aip h i -l');
    if (($hdata = json_decode($cout, TRUE)) === FALSE)
        return $data;
    $cout = shell_exec($cmdPrefix.'aip q i -l');
    if (($qdata = json_decode($cout, TRUE)) === FALSE)
        return $data;
    $cout = shell_exec($cmdPrefix.'./uinfo');
    if (($udata = json_decode($cout, TRUE)) === FALSE)
        return $data;
    $hosts = [];
    $gpuAvail = $gpuUsed = 0;
    $usedSlots = $totalSlots = $numClosed = $numProblem =
        $numOk = $numFull = $numBusy = 0;
    $qNames = $qNumPend = $qNumRun = $qNumSusp = [];
    $uNames = $uNumPend = $uNumRun = $uNumSusp = [];
    $numPend = $numRun = $numSusp = 0;
    $numPendJobs = $numRunJobs = $numRunUsers = $numPendUsers = 0;
    $busySlots = $problemSlots = $closedSlots = $runSlots =
           $pendSlots = $suspSlots = 0;
    foreach($hdata as $h) {
        $hostChart = [];
        if ($h['GPU'] != '-') {
            $gpuUsed += intval($h['GPU']) - $h['Load'][11]['Value'];
            $gpuAvail += $h['Load'][11]['Value'];
        }
        $hUsedSlots = $h['NRun'] + $h['NUStop'] + $h['NRsv'];
        $usedSlots += $hUsedSlots;
        $totalSlots += $h['MaxJobs'];
        if ($h['CPU'] != '-') {
            $hostChart['ut'] = number_format($h['Load'][3]['Value'] * 100, 1).
                               '%';
            $hostChart['mem'] = number_format($h['Load'][10]['Value'],2).
                               'GB';
            $hostChart['io'] = number_format($h['Load'][5]['Value'],0).'KB/s';
            $hostChart['run'] = strval($hUsedSlots);
        } else {
            $hostChart['ut'] = $hostChart['mem'] = $hostChart['io'] = '-';
            $hostChart['run'] = '0';
        }
        $hostChart['host'] = $h['Name'];
        $hostChart['status'] = $h['Status'];
        $hostChart['mxj'] = $h['MaxJobs'];
        $hostChart['run'] = $hUsedSlots;
        $hosts[] = $hostChart;
        switch ($h['Status']) {
        case 'Ok':
            $numOk ++;
            break;
        case 'Unavail':
        case 'Unreach':
        case 'Closed-LS':
            $numProblem++;
            $problemSlots += $h['MaxJobs'];
            break;
        case 'Closed-Admin':
        case 'Closed-Excl':
        case 'Closed-Lock':
        case 'Closed-LockM':
        case 'Closed-Wind':
            $closedSlots += $h['MaxJobs'];
            $numClosed++;
            break;
        case 'Closed-Full':
            $numFull++;
            break;
        case 'Closed-Busy':
            $numBusy++;
            $busySlots += $h['MaxJobs'];
            break;
        default:
            $numProblem++;
            $problemSlots += $h['MaxJobs'];
            break;
        }
    }
    uasort($udata, 'cmpuser');
    $i = 0;
    foreach ($udata as $u) {
        if ($u['ISGROUP'] == 'y')
            continue;
        $uNames[$i] = [$i, $u['USER']];
        $uNumRun[$i] = [intval($u['NUM_RUNNING_JOBS']), $i];
        $uNumPend[$i] = [intval($u['NUM_PENDING_JOBS']), $i];
        $uNumSusp[$i] = [intval($u['NUM_SUSP_JOBS']), $i];
        if (intval($u['NUM_RUNNING_JOBS']) > 0) {
            $numRunUsers ++;
            $numRunJobs += intval($u['NUM_RUNNING_JOBS']);
        }
        if (intval($u['NUM_PENDING_JOBS']) > 0) {
            $numPendUsers ++;
            $numPendJobs += intval($u['NUM_PENDING_JOBS']);
        }
        $i++;
    }
    uasort($qdata, 'cmpqueue');
    $i = 0;
    foreach ($qdata as $q) {
        $qNames[$i] = [$i, $q['Name']];
        $qNumPend[$i] = [$q['NPend'], $i];
        $qNumRun[$i] = [$q['NRun'], $i];
        $qNumSusp[$i] = [$q['NStop'], $i];
        $numPend += $q['NPend'];
        $numRun += $q['NRun'];
        $numSusp += $q['NStop'];
        $i++;
    }

    $data[0] = $numRunJobs;
    $data[1] = $numPendJobs;
    $data[2] = $numRunUsers;
    $data[3] = $numPendJobs;
    $data[4] = [$numOk, $numFull, $numBusy, $numClosed, $numProblem];
    $data[5] = [$totalSlots - $busySlots - $closedSlots - $problemSlots - $usedSlots,
                $usedSlots, $busySlots, $closedSlots, $problemSlots];
    $data[6] = ['ydata'=>array_slice($uNames, 0, 10),
                'xdata'=>[array_slice($uNumPend, 0, 10),
                          array_slice($uNumRun, 0, 10),
                          array_slice($uNumSusp, 0, 10)]];
    $data[7] = [$gpuAvail, $gpuUsed];
    $data[8] = [$numPend, $numRun, $numSusp];
    $data[9] = ['ydata'=>array_slice($qNames, 0, 10),
                'xdata'=>[array_slice($qNumPend, 0, 10),
                          array_slice($qNumRun, 0, 10),
                          array_slice($qNumSusp, 0, 10)]];
    $data[10] = $hosts;
    
    return $data;
}

switch($_POST['action']) {
    case 'load':
    case 'update':
        $page = yaml_parse_file('./dashboard.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        $page['rows'][0]['infobox']['data'] = $result[0];
        $page['rows'][1]['infobox']['data'] = $result[1];
        $page['rows'][2]['infobox']['data'] = $result[2];
        $page['rows'][3]['infobox']['data'] = $result[3];
        for ($i = 0; $i < 5; $i++)
            $page['rows'][4]['donutchart']['data'][$i]['data'] =
                 $result[4][$i];
        for ($i = 0; $i < 5; $i++)
            $page['rows'][5]['donutchart']['data'][$i]['data'] =
                 $result[5][$i];
        $page['rows'][6]['hor_barchart']['ydata'] =  $result[6]['ydata'];
        $page['rows'][6]['hor_barchart']['xdata'] =  $result[6]['xdata'];
        for ($i = 0; $i < 2; $i++)
            $page['rows'][7]['donutchart']['data'][$i]['data'] =
                 $result[7][$i];
        for ($i = 0; $i < 3; $i++)
            $page['rows'][8]['donutchart']['data'][$i]['data'] =
                 $result[8][$i];
        $page['rows'][9]['hor_barchart']['ydata'] =  $result[9]['ydata'];
        $page['rows'][9]['hor_barchart']['xdata'] =  $result[9]['xdata'];
        $page['rows'][10]['hostlist']['data'] = $result[10];
        $ret['data'] = $page;
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

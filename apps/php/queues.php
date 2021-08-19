<?php
include 'common1.php';
include 'progress.php';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $queues = [];
    $cout = shell_exec($cmdPrefix.'aip q i -l');
    if (($qdata = json_decode($cout, TRUE)) === FALSE)
        return $queues;

    $cout = shell_exec($cmdPrefix.'aip h i -l');
    $hdata = json_decode($cout, TRUE);
    $totalslots = 0;
    foreach ($hdata as $h)
        $totalslots += $h['MaxJobs'];
    
    foreach($qdata as $q) {
        $queue = [];
        $queue['name'] = '<a href="javascript:openurl(\'php/jobs.php?queue='.$q['Name'].'\',\'作业\',\'\');">'.
                         $q['Name'].'</a>';
        $queue['priority'] = $q['Priority'];
        switch ($q['Status']){
        case 'OK':
            $queue['status'] = '<span class="text-success">'.$lang['OK_CHART'].'</span>';
            break;
        case 'Open:Inactive':
            $queue['status'] = '<span class="text-info">'.$lang['OPEND'].'-'.
                               $lang['INACTIVE'].'</span>';
            break;
        case 'Closed:Active':
            $queue['status'] = '<span class="text-warning">'.$lang['CLOSED'].'-'.
                               $lang['ACTIVE'].'</span>';
            break;
        case 'Closed:Inactive':
        default:
            $queue['status'] = '<span class="text-danger">'.$lang['CLOSED'].'-'.
                               $lang['ACTIVE'].'</span>';
            break;
        }
        $queue['njobs'] = $q['NJobs'];
        $queue['nrun'] = $q['NRun'];
        $queue['npend'] = $q['NPend'];
        $queue['nstop'] = $q['NStop'];
        if ($q['MaxJ'] != '-') {
            $queue['utilization'] = progress($q['NRun']/floatval($q['MaxJ']));
        }
        else if ($q['Hosts'] == 'all hosts')
            $queue['utilization'] = progress($q['NRun']/floatval($totalslots));
        else {
            $qhosts = explode(' ', $q['Hosts']);
            $qslots = 0;
            foreach ($hdata as $h)
                if (in_array($h['Name'], $qhosts))
                    $qslots += $h['MaxJobs'];
            $queue['utilization'] = progress($q['NRun']/floatval($qslots));
        }
        $queue['maxj'] = $q['MaxJ'];
        $queues[] = $queue;
    }      
    return $queues;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./queues.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateData();
        break;
    case 'open':
    case 'close':
        $queues = [];
        foreach ($_POST['data'] as $q) {
            $st = explode('>', urldecode($q));
            $qq = explode('<', $st[1]);
            $queues[] = $qq[0];
        }
        shell_exec($cmdPrefix.'csadmin q'.$_POST['action'].' '.
                   implode(' ', $queues));
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

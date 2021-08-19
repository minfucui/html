<?php
include 'common1.php';

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
        $queue['name'] = $q['Name'];
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
        $queue['maxj'] = $q['MaxJ'];
        $queue['userjlimit'] = $q['UserJLimit'];
        $queue['fairshare'] = ($q['Fairshare']['Shares'] == NULL) ? '无':'均匀分享';
        $queue['resreq'] = $q['ResReq'];
        $queue['hosts'] = $q['Hosts'] == 'all hosts' ? '所有主机' : $q['Hosts'];
        $queue['users'] = $q['Users'] == 'all users' ? '所有用户' : $q['Users'];
        $queue['jobstarter'] = $q['JobStarter'];
        $queues[] = $queue;
    }      
    return $queues;
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
    case 'modify':
    case 'new':
    case 'delete':
        $ret['code'] = 200;
        $ret['message'] = 'Not implemented yet';
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

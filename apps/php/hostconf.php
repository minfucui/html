<?php
include 'common1.php';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $hosts = [];
    $cout = shell_exec($cmdPrefix.'aip h i -l');
    if (($hdata = json_decode($cout, TRUE)) === FALSE)
        return $hosts;
    
    foreach($hdata as $h) {
        $host = [];
        $host['name'] = $h['Name'];
        switch ($h['Status']){
        case 'Ok':
            $host['status'] = '<span class="text-success">'.$lang['OK_CHART'].'</span>';
            break;
        case 'Closed-Admin':
        case 'Closed-Excl':
        case 'Closed-Lock':
        case 'Closed-LockM':
        case 'Closed-Wind':
            $host['status'] = '<span class="text-secondary">'.$lang['CLOSED'].'</span>';
            break;
        case 'Closed-Full':
            $host['status'] = '<span class="text-primary">'.$lang['FULL'].'</span>';
            break;
        case 'Closed-Busy':
            $host['status'] = '<span class="text-info">'.$lang['BUSY'].'</span>';
            break;
        case 'Unavail':
        case 'Unreach':
        case 'Closed-LS':
        default:
            $host['status'] = '<span class="text-danger">'.$lang['PROBLEM'].'</span>';
            break;
        }
        $host['cpus'] = $h['CPU'];
        $host['gpus'] = intval($h['Load'][11]['Value']);
        $host['maxmem'] = $h['MaxMem'].'GB';
        $host['maxswap'] = $h['MaxSwap'].'GB';
        $host['maxjobs'] = $h['MaxJobs'];
        $host['load_threshold'] = '';
        $hosts[] = $host;
    }      
    return $hosts;
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
    case 'add':
    case 'remove':
    case 'shutdown':
    case 'powerup':
    case 'modify':
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

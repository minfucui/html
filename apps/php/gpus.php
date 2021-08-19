<?php
include 'common1.php';
include 'progress.php';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $hosts = [];
    $cout = shell_exec($cmdPrefix.'aip h i -l');
    if (($hdata = json_decode($cout, TRUE)) === FALSE)
        return $hosts;
    
    foreach($hdata as $h) {
        if (sizeof($h['GPUInfo']) < 1)
            continue;
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
        $i = 0;
        foreach($h['GPUInfo'] as $g) {
            if ($i != 0) {
                $host['name'] = ' ';
                $host['status'] = ' ';
            }
            $host['id'] = $g['ID'];
            $host['model'] = $g['Model'];
            $host['tmem'] = $g['TotalMemory'];
            $host['fmem'] = $g['FreeMemory'];
            $host['temp'] = $g['Temperature'];
            $host['gut'] = progress($g['GpuUt']);
            $host['gmut'] = progress($g['MemUt']);
            $host['gpuut'] = progress($g['Usage']);
            $hosts[] = $host;
            $i++;
        }
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
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

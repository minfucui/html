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
        $host = [];
        $host['name'] = '<a href="javascript:hostdetail(\''.
            $h['Name'].'\');">'.$h['Name'].'</a>';
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
        if ($h['CPU'] != '-')
            $host['utilization'] = progress($h['NJobs']/$h['MaxJobs']);
        else
            $host['utilization'] = progress(0);
        $host['maxjobs'] = $h['MaxJobs'];
        $host['nrun'] = '<a href="javascript:openurl(\'php/jobs.php?host='.
             $h['Name'].'\',\'作业\',\'\');">'.$h['NJobs'].'</a>';
        $host['nstop'] = $h['NUStop'] + $h['NSStop'];
        $host['nrsv'] = $h['NRsv'];
        $host['ut'] = progress($h['Load'][3]['Value']);
        $host['memut'] = progress($h['MemUt']);
        $host['netio'] = $h['Load'][5]['Value'];
        if ($h['GPU'] != '-' && intval($h['GPU']) > 0)
            $host['gpu'] = progress((intval($h['GPU']) - $h['Load'][11]['Value'])/intval($h['GPU']));
        else
            $host['gpu'] = progress(0);
        $hosts[] = $host;
    }      
    return $hosts;
}

function hostStatic($hostname)
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $host = [];
    $cout = shell_exec($cmdPrefix.'aip h i -l');
    if (($hdata = json_decode($cout, TRUE)) === FALSE)
        return $host;
    foreach ($hdata as $h) {
        if ($h['Name'] == $hostname) {
            $host['name']['title'] = 'HOST';
            $host['name']['value'] = $h['Name'];
            $host['cpu_model']['title'] = 'CPU_MODEL';
            $host['cpu_model']['value'] = $h['CPUInfo']['Model'];
            $host['sockets']['title'] = 'SOCKETS';
            $host['sockets']['value'] = $h['CPUInfo']['Sockets'];
            $host['cores_per_socket']['title'] = 'CORES_PER_SOCKET';
            $host['cores_per_socket']['value'] = $h['CPUInfo']['Cores_per_socket'];
            $host['maxmem']['title'] = 'MAXIMUM_MEMORY';
            $host['maxmem']['value'] = $h['MaxMem'].'GB';
            $host['maxswap']['title'] = 'MAXIMUM_SWAP';
            $host['maxswap']['value'] = $h['MaxSwap'].'GB';
            $host['uptime']['title'] = 'UPTIME';
            $host['uptime']['value'] = number_format($h['Load'][6]['Value']/60, 3).'小时';
            if (isset($h['GPUInfo']) && sizeof($h['GPUInfo']) > 0) {
                 $i = 0;
                 foreach ($h['GPUInfo'] as $g) {
                     $host['gpu'.strval($i)]['title'] = 'GPU #'.strval($i);
                     $host['gpu'.strval($i)]['value'] = '型号：'.$g['Model'].' 总显存：'.
                           number_format(floatval($g['TotalMemory'])/1024, 3).'GB'.
                           ' 使用率：'.strval($g['Usage']*100).'%';
                     $i++;
                 }
            }
            break;
        }
    }
    return $host;
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
    case 'detail':
        if (!isset($_POST['name']))
            failt("Wrong request");
        $host = hostStatic($_POST['name']);
        $ret['data']['description']['title'] = $_POST['name'];
        $ret['data']['description']['rows'] = $host;
        break;
    case 'open':
    case 'close':
        $hosts = [];
        foreach ($_POST['data'] as $q) {
            $st = explode('>', urldecode($q));
            $qq = explode('<', $st[1]);
            $hosts[] = $qq[0];
        }
        shell_exec($cmdPrefix.'csadmin h'.$_POST['action'].' '.
                   implode(' ', $hosts));
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

<?php
include 'common1.php';

function orgHosts($hosts)
{
    $h = '';
    $n = 0;
    $prev = '';
    $started = 0;
    foreach ($hosts as $host) {
        if ($host != $prev) {
            if ($started != 0)
                $h = $h.($n > 1 ? '*'.strval($n) : '').' '.$host;
            else {
                $h = $host;
                $started = 1;
            }
            $n = 0;
            $prev = $host;
        }
        $n++;  
    }
    if ($n > 1)
       $h = $h.'*'.strval($n);
    return $h;
}


$lang = $_SESSION['lang'];

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $qdata = json_decode(shell_exec($cmdPrefix.'aip q i -l'), true);
        if ($qdata === FALSE)
            fail("AIP is down");
        $queues = ['ALL'];
        foreach ($qdata as $q)
            $queues[] = $q['Name'];
        $hdata = json_decode(shell_exec($cmdPrefix.'aip h i -l'), true);
        if ($hdata === FALSE)
            fail("AIP is down");
        $hosts = ['ALL'];
        foreach ($hdata as $h)
            $hosts[] = $h['Name'];
        
        $udata = searchRows('users');
        if ($udata === FALSE)
            fail("DB error");
        $users = ['ALL'];
        foreach ($udata as $u)
            $users[] = $u['username'];
        $page['rows'][0]['form'][0]['items'][4]['options'] = $queues;
        $page['rows'][0]['form'][0]['items'][5]['options'] = $hosts;
        $page['rows'][0]['form'][0]['items'][6]['options'] = $users;
        $ret['data'] = $page;
        break;
    case 'search':
        if (!isset($_POST['data']) || sizeof($_POST['data']) == 0)
             fail('Wrong request');
        $par = $_POST['data'];
        
        switch ($par['type']) {
        case $lang['ALL']:
            $stat = '-a';
            break;
        case $lang['FINISH']:
            $stat = '-d';
            break;
        case $lang['EXIT']:
            $stat = '-e';
            break;
        default:
            $stat = '';
            break;
        }
        $h = ($par['host'] != $lang['ALL'])? ' -m '.$par['host'] : '';
        $q = ($par['queue'] != $lang['ALL'])? ' -q '.$par['queue'] : '';
        $u = ($par['user'] != $lang['ALL'])? ' -u '.$par['user'] : ' -u all';
        $t = '';
        if ($par['timep'] != $lang['ALL']) {
            switch ($par['timep']) {
            case $lang['LAST_24_HOURS']:
                $t = ' -C .-1,';
                break;
            case $lang['LAST_7_DAYS']:
                $t = ' -C .-7, -n 10';
                break;
            case $lang['LAST_MONTH']:
                $t = ' -C .-1/, -n 0';
                break;
            case $lang['LAST_QUARTER']:
                $t = ' -C .-3/, -n 0';
                break;
            default:
                if ($par['from'] != '')
                    $t =' -C '.str_replace(' ','/',$par['from']).',';
                if ($par['to'] != '') {
                    if ($t == '')
                        $t = ' -C ,';
                    $t = $t.str_replace(' ','/'.$par['to']);
                }
                $t = $t.' -n 0';
                break;
            }
        }
        $cmd = $cmdPrefix.'chist -j '.$stat.' '.$h.$q.$u.$t;
        $out = shell_exec($cmd.' 2>&1');
        $jobs = json_decode($out, true);
        $data = [];
        if (sizeof($jobs) > 0)
            foreach($jobs as $j) {
                if ($stat == '-d' && $j['StatusString'] != 'FINISH')
                    continue;
                $d = [];
                $jid = strval($j['JobID']['JobID']);
                $d['jobid'] = '<a href="javascript:jobhist(\''.$jid.'\');">'.$jid.'</a>';
                $d['user'] =  $j['User'];
                $d['queue'] = $j['JobSpec']['Queue'];
                $d['exec_hosts'] = orgHosts($j['ExecHosts']);
                $d['status'] = $lang[$j['StatusString']];
                $d['submit_time'] = date("Y-m-d H:i", $j['SubmitTime']);
                $d['start_time'] = isset($j['StartTime']) ? date("Y-m-d H:i", $j['StartTime'])
                                    : '-';
                $d['finish_time'] = isset($j['EndTime']) ? date("Y-m-d H:i",$j['EndTime']) : '-';
                $data[] = $d;
           }
        $ret['data']['table'] = $data;
        break;
    default:
        fail('Wrong request');
        break;
}

echo json_encode($ret);
?>

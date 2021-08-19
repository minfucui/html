<?php
include 'common1.php';

$imgfile = '../data/rp.png';
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
        if (isset($udata['error']))
            fail($udata['error']);
        $users = ['ALL'];
        foreach ($udata as $u)
            $users[] = $u['username'];
        $page['rows'][0]['form'][0]['items'][5]['options'] = $queues;
        $page['rows'][0]['form'][0]['items'][6]['options'] = $hosts;
        $page['rows'][0]['form'][0]['items'][7]['options'] = $users;
        if (file_exists($imgfile) &&
            ($img = file_get_contents($imgfile)) !== FALSE) {
             $enc = base64_encode($img);
             $page['rows'][1]['report']['data'] = '<img src="data:image/png;base64,'.
                    $enc.'" style="height:100%;width:100%;object-fit:contain"/>';
        }

        $ret['data'] = $page;
        break;
    case 'run':
        if (!isset($_POST['data']) || sizeof($_POST['data']) == 0)
             fail('Wrong request');
        $par = $_POST['data'];
        switch ($par['cat']) {
        case $lang['BYUSER']:
            $cat = 'user';
            break;
        case $lang['BYQUEUE']:
            $cat = 'queue';
            break;
        case $lang['BYUSERGROUP']:
            $cat = 'ugroup';
            break;
        case $lang['BYPROJECT']:
            $cat = 'project';
            break;
        case $lang['BYAPP'];
            $cat = 'app';
            break;
        default:
            $cat = '';
            break;
        }
        switch ($par['type']) {
        case $lang['JOB_SLOT_USAGE']:
            $stat = 'run';
            break;
        case $lang['JOB_THROUGHPUT']:
            $stat = 'completed';
            break;
        case $lang['PENDING_JOBS']:
            $stat = 'pending';
            break;
        case $lang['SUSPEND_JOB']:
            $stat = 'susp';
            break;
        case $lang['DONE_JOBS']:
            $stat = 'done';
            break;
        case $lang['EXITED_JOBS']:
            $stat = 'exited';
            break;
        case $lang['SUBMITTED_JOBS']:
            $stat = "submit";
            break;
        default:
            $stat = '';
            break;
        }
        $h = ($par['host'] != $lang['ALL'])? ' -m '.$par['host'] : '';
        $q = ($par['queue'] != $lang['ALL'])? ' -q '.$par['queue'] : '';
        $u = ($par['user'] != $lang['ALL'])? ' -u '.$par['user'] : '';
        $t = '';
        if ($par['timep'] != $lang['ALL']) {
            switch ($par['timep']) {
            case $lang['LAST_24_HOURS']:
                $t = ' -C .-1,';
                break;
            case $lang['LAST_7_DAYS']:
                $t = ' -C .-7,';
                break;
            case $lang['LAST_MONTH']:
                $t = ' -C .-1/,';
                break;
            case $lang['LAST_QUARTER']:
                $t = ' -C .-3/,';
                break;
            default:
                if ($par['from'] != '')
                    $t =' -C '.str_replace(' ','/',$par['from']).',';
                if ($par['to'] != '') {
                    if ($t == '')
                        $t = ' -C ,';
                    $t = $t.str_replace(' ','/'.$par['to']);
                }
                break;
            }
        }
        $cmd = 'source /var/www/html/env.sh;creport -r '.$stat.':'.$cat.$h.$q.$u.$t.' -p '.$imgfile;
        $out = shell_exec($cmd.' 2>&1');
        file_put_contents('../data/rp.log', $cmd."\n".$out);
        $img = file_get_contents($imgfile);
        $enc = base64_encode($img);
        $ret['data'] = '<img src="data:image/png;base64,'.
                    $enc.'" style="height:100%;width:100%;object-fit:contain"/>';
        break;
    default:
        fail('Wrong request');
        break;
}

echo json_encode($ret);
?>

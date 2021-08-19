<?php
include 'common1.php';

$lang = $_SESSION['lang'];
exec('source '.$cmdpath.'/env.sh;echo $CB_ENVDIR/cb.yaml 2>&1', $r, $errno);
if ($errno != 0)
    fail (implode('<br>', $r));

$conf = yaml_parse_file($r[0]);
if ($conf === NULL)
    fail ('Cannot read AIP configuration file');

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $qdata = json_decode(shell_exec($cmdPrefix.'aip q i -l'), true);
        if ($qdata === FALSE)
            fail("AIP is down");
        $queues = [];
        foreach ($qdata as $q)
            $queues[] = $q['Name'];
        $param = $page['rows'][0]['form'][0];

        $param['items'][0]['options'] = $queues;
        $gen = $conf['general'];
        $param['items'][0]['value'] = $gen['default_queue'];
        $param['items'][1]['value'] = isset($gen['memperiod']) ? $gen['memperiod'] : '3600';
        if (isset($gen['cgroup']))
            $param['items'][2]['value'] = $gen['cgroup'];
        $param['items'][3]['value'] = (isset($gen['rootcanrun']) && $gen['rootcanrun'] == 'no')?
                 '否':'是';
        $param['items'][4]['value'] = isset($gen['load_interval']) ?
                 $gen['load_interval'] : '5';

        if (isset($conf['power'])) {
            $power = $conf['power'];
        } else
            $power = [];

        $page['rows'][0]['form'][0] = $param;
        $page['rows'][1]['form'][0]['items'][0]['value'] = 
                 isset($power['idle_time']) ? $power['idle_time'] : '';
        $page['rows'][1]['form'][0]['items'][1]['value'] =
                 isset($power['pend_time']) ? $power['pend_time'] : '';
        $page['rows'][1]['form'][0]['items'][2]['value'] =
                 isset($power['cycle_time']) ? $power['cycle_time'] : '';
        $page['rows'][1]['form'][0]['items'][3]['value'] =
                 isset($power['suspend_rate']) ? $power['suspend_rate'] : '';
        $page['rows'][1]['form'][0]['items'][4]['value'] =
                 isset($power['resume_rate']) ? $power['resume_rate'] : '';
        $page['rows'][1]['form'][0]['items'][5]['value'] =
                 isset($power['exclude_hosts']) ? $power['exclude_hosts'] : '';
        $page['rows'][1]['form'][0]['items'][6]['value'] =
                 isset($power['power_down_cmd']) ? $power['power_down_cmd'] : '';
        $page['rows'][1]['form'][0]['items'][7]['value'] =
                 isset($power['power_up_cmd']) ? $power['power_up_cmd'] : '';
        $page['rows'][1]['form'][0]['items'][8]['value'] =
                 isset($power['power_restart_cmd']) ? $power['power_restart_cmd'] : '';
        $ret['data'] = $page;
        break;
    default:
        fail('Wrong request');
        break;
}

echo json_encode($ret);
?>

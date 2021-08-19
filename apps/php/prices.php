<?php
include 'common1.php';
include 'filefunctions.php';

/* read existing cbcrond.yaml */
exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $er);
$aipenv = $r[0];
$crondconf = $aipenv.'/cbcrond.yaml';

if (!file_exists($crondconf))
    fail("cbcrond.yaml does not exist.\n");

$conf = yaml_parse_file($crondconf);
if ($conf === FALSE)
    fail("Invalid cbcrond.yaml.\n");
$qprices = [];
foreach ($conf['charge']['standard']['cpuperhour'] as $q)
    $qprices[$q['queue']] = $q['price'];

$cpu = $qprices['all'];

$appprices = [];
foreach ($conf['charge']['standard']['appperhour'] as $a)
    $appprices[$a['app']] = $a['price'];

$shortName = exec('date +%Z');
$offset = exec('date +%::z');
$off = explode (":", $offset);
$offsetSeconds = $off[0][0] . abs($off[0])*3600 + $off[1]*60 + $off[2];
$longName = timezone_name_from_abbr($shortName, $offsetSeconds);
date_default_timezone_set($longName);

$update = strftime("%Y-%m-%d %H:%M:%S", filemtime($crondconf));

function refresh_apps_queues()
{
    global $cmdPrefix, $conf, $qprices, $appprices;

    /* $apps = searchRows('applications');
    if (sizeof($apps) > 0 && !isset($apps['error']))
        foreach($apps as $app) {
            if (!isset($appprices[$app['appname']]))
                $conf['charge']['standard']['appperhour'][] =
                     ['app'=>$app['appname'], 'price'=>0];
        } */
    /* add queues */
    $cmdout = shell_exec($cmdPrefix.'aip q i -l');
    if (($qs = json_decode($cmdout, TRUE)) === FALSE)
        return;
    foreach ($qs as $q) {
        if (!isset($qprices[$q['Name']]))
             $conf['charge']['standard']['cpuperhour'][] =
                  ['queue'=>$q['Name'], $qprices['all']];
    }
}

function write_yaml()
{
    global $conf, $crondconf;
    $st = yaml_emit($conf, YAML_UTF8_ENCODING);
    if ($st === FALSE)
        return 'Invalid configuration';

    if (myFile_Put_Contents($crondconf, $st) === FALSE)
        return 'Cannot write to '.$crondconf;
    return 'ok';
}

function updateData($update_apps)
{
    global $conf, $qprices, $appprices, $update;
    if ($update_apps)
        refresh_apps_queues(); 
    $ret = [];
    $cpu = $qprices['all'];
    $ret[] = ['chargename'=>'CPU', 'unit'=>'核小时',
              'unitprice'=>$qprices['all'], 'last_update'=>$update];
    $ret[] = ['chargename'=>'内存', 'unit'=>'GB小时',
              'unitprice'=>$conf['charge']['standard']['mempergbhour'][0]['price'],
              'last_update'=>$update];
    $ret[] = ['chargename'=>'GPU', 'unit'=>'个小时',
              'unitprice'=>$conf['charge']['standard']['gpuperhour'][0]['price'],
              'last_update'=>$update];
    foreach ($qprices as $q=>$p)
        if ($q != 'all')
            $ret[] = ['chargename'=>'queue-'.$q, 'unit'=>'核小时',
                      'unitprice'=>$p - $cpu, 'last_update'=>$update];
    foreach ($appprices as $a=>$p)
        $ret[] = ['chargename'=>$a, 'unit'=>'核小时',
                  'unitprice'=>$p, 'last_update'=>$update];
    return $ret;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $data = updateData(TRUE);
        if (isset($data['error']))
            fail($data['error']);
        $page['rows'][0]['table']['data'] = $data;
        $ret['data'] = $page;
        break;

    case 'update':
        $data = updateData(FALSE);
        if (isset($data['error']))
            fail($data['error']);
        $ret['data']['table'] = $data;
        break;
    case 'new':
        $rec = $_POST['data'];
        if ($rec['chargename'] == '' || $rec['unitprice'] == '')
            fail('ALL_REQUIRED');
        if ($rec['chargename'] == 'CPU' || $rec['chargename'] == '内存'
            || $rec['chargename'] == 'GPU')
            fail('产品已存在');
        if (substr($rec['chargename'], 0, 6) == 'queue-' &&
            isset($qprices[substr($rec['chargename'], 6)]))
            fail('产品已存在');
        if (isset($appprices[$rec['chargename']]))
            fail('产品已存在');
        $rec['unitprice'] = floatval($rec['unitprice']);
        if (substr($rec['chargename'], 0, 6) == 'queue-') {
            $queue = substr($rec['chargename'], 6);
            $price = $rec['unitprice'] + $cpu;
            $conf['charge']['standard']['cpuperhour'][] =
                 ['queue'=>$queue, 'price'=>$price];
        } else {
            $conf['charge']['standard']['appperhour'][] =
                 ['app'=>$rec['chargename'],
                  'price'=>$rec['unitprice']];
        }
        $r = write_yaml();
        if ($r != 'ok')
            fail($r);
        break;
    case 'modify':
        $rec = $_POST['data'];
        if ($rec['unitprice'] == '')
            fail('ALL_REQUIRED');
        $rec['unitprice'] = floatval($rec['unitprice']);
        if ($rec['chargename'] == 'CPU') {
            $diff = $rec['unitprice'] - $cpu;
            $n = sizeof($conf['charge']['standard']['cpuperhour']);
            for ($i = 0; $i < $n; $i++)
                 $conf['charge']['standard']['cpuperhour'][$i]['price'] +=
                      $diff;
        } else if ($rec['chargename'] == '内存') {
            $conf['charge']['standard']['mempergbhour'][0]['price'] =
                 $rec['unitprice'];
        } else if ($rec['chargename'] == 'GPU') {
            $conf['charge']['standard']['gpuperhour'][0]['price'] =
                 $rec['unitprice'];
        } else if (substr($rec['chargename'], 0, 6) == 'queue-') {
            $queue = substr($rec['chargename'], 6);
            $price = $rec['unitprice'] + $cpu;
            $n = sizeof($conf['charge']['standard']['cpuperhour']);
            for ($i = 0; $i < $n; $i++)
                 if ($conf['charge']['standard']['cpuperhour'][$i]['queue']
                     == $queue) {
                     $conf['charge']['standard']['cpuperhour'][$i]['price']
                          = $price;
                     break;
                 }
        } else {
            $n = sizeof($conf['charge']['standard']['appperhour']);
            for ($i = 0; $i < $n; $i++)
                 if ($conf['charge']['standard']['appperhour'][$i]['app']
                     == $rec['chargename']) {
                     $conf['charge']['standard']['appperhour'][$i]['price']
                          = $rec['unitprice'];
                     break;
                 }
        }

        $r = write_yaml();
        if ($r != 'ok')
            fail($r);
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

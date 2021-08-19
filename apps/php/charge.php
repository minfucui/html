<?php
include 'db.php';
#include 'billfunc.php';

$aiptop = dirname(getenv('CB_ENVDIR'));
$usagedir = $aiptop.'/work/usages';
if (!is_dir($usagedir)) {
    die('Cannot access '.$usagedir."\n");
}

$shortName = exec('date +%Z');
$offset = exec('date +%::z');
$off = explode (":", $offset);
$offsetSeconds = $off[0][0] . abs($off[0])*3600 + $off[1]*60 + $off[2];
$longName = timezone_name_from_abbr($shortName, $offsetSeconds);
date_default_timezone_set($longName);

$tnow = time();
$now = localtime($tnow, true);

$remainder = $now['tm_min'] % 15;

$filep = sprintf('%s/%02d%02d%02d.json', $usagedir,
                 $now['tm_mday'], $now['tm_hour'],
                 $now['tm_min'] - $remainder);

if (!is_file($filep) || $tnow - filectime($filep) > 3600)
    die('Cannot find the correct usage file '.$filep."\n");

if (($str = file_get_contents($filep)) === FALSE)
    die('Cannot read the correct usage file '.$filep."\n");

$jinfo = json_decode($str, true);

$out = $jinfo['Costs'];

$users = searchRows('users');
if (($numusers = sizeof($users)) == 0 || isset($users['error']))
    die("Cannot find users in DB\n");

$currentime = sprintf("%4d-%02d-%02d %02d:%02d:%02d",
                      $now['tm_year'] + 1900, $now['tm_mon'] + 1,
                      $now['tm_mday'], $now['tm_hour'],
                      $now['tm_min'], $now['tm_sec']);
printf ("currenttime=%s\n", $currentime);

if (sizeof($out) > 0)
    foreach($out as $unit) {
        $u = $unit['User'];
        $totalcost = $unit['Cost'];
        for ($i = 0; $i < $numusers; $i++)
            if ($users[$i]['username'] == $u)
                break;
        if ($i == $numusers)
            continue;
        $users[$i]['balance'] = $users[$i]['balance'] - $totalcost;
        printf ("User: %s balance reduced by %.2f\n", $u, $totalcost);
        modifyARow('users', ['username'=>$u],
                  ['balance'=>$users[$i]['balance']]);
    }

$files = scandir('jobout');
$now = time();
foreach($files as $f) {
    if ($f != '.' && $f != '..' &&
        $now - filemtime('jobout/'.$f) > 604800)  // older than 1 wk
        unlink('jobout/'.$f);
}
?>

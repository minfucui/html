#!/usr/bin/php
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

if ($argc < 3)
   die('Usage: '.$argv[0].' ddhhmm ddhhmm(inclusion)'."\n");

$startd = substr($argv[1], 0, 2);
$starth = substr($argv[1], 2, 2);
$startm = substr($argv[1], 4, 2);
$stopd = substr($argv[2], 0, 2);
$stoph = substr($argv[2], 2, 2);
$stopm = substr($argv[2], 4, 2);

if (intval($startd) < 1 || intval($startd > 31) ||
    intval($starth) < 0 || intval($starth > 23) ||
    intval($startm) < 0 || intval($startm > 45) ||
    intval($stopd) < 1 || intval($stopd > 31) ||
    intval($stoph) < 0 || intval($stoph > 23) ||
    intval($stopm) < 0 || intval($stopm > 45) ||
    (intval($startm) % 15) != 0 || (intval($stopm) % 15) != 0 ||
    intval($argv[2]) <= intval($argv[1]))
    die("error in time format\n");

$files = [];

while (true) {
    $files[] = $usagedir.'/'.$startd.$starth.$startm.'.json';
    if ($startd == $stopd && $starth == $stoph && $startm == $stopm)
        break;
    $m = intval($startm) + 15;
    $h = intval($starth);
    $d = intval($startd);
    if ($m == 60) {
        $m = 0;
        $h ++;
        if ($h == 24) {
           $h = 0;
           $d ++;
        }
    }
    $startd = sprintf("%02d", $d);
    $starth = sprintf("%02d", $h);
    $startm = sprintf("%02d", $m);
}

foreach ($files as $filep) {
    if (!is_file($filep)) {
        error_log('====Cannot find the correct usage file '.$filep);
        continue;
    }

    printf("-- Processing %s --\n", $filep);
    if (($str = file_get_contents($filep)) === FALSE) {
        error_log('====Cannot read the correct usage file '.$filep);
        continue;
    }

    $jinfo = json_decode($str, true);
    if ($jinfo === FALSE) {
        error_log('====Cannot decode the correct usage file '.$filep);
        continue;
    }

    if (isset($jinfo['Costs']))
        $out = $jinfo['Costs'];
    else
        $out = [];

    $users = searchRows('users');
    if (($numusers = sizeof($users)) == 0 || isset($users['error']))
        die("Cannot find users in DB\n");

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
            $ret = modifyARow('users', ['username'=>$u],
                      ['balance'=>$users[$i]['balance']]);
            if (isset($ret['error']))
                printf("error: %s\n", $ret['error']);
        }
}
?>

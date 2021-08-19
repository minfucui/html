<?php
include 'db.php';
include 'billfunc.php';

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

$filep = sprintf('%s/usage.%1d%02d00', $usagedir,
                 $now['tm_wday'], $now['tm_hour']);

if (!is_file($filep) || $tnow - filectime($filep) > 3600)
    die('Cannot find the correct usage file '.$filep."\n");

if (($str = file_get_contents($filep)) === FALSE)
    die('Cannot read the correct usage file '.$filep."\n");

$jinfo = json_decode($str, true);

$jobs = $jinfo['Jobs'];

$out = calcCost($jobs);
echo json_encode($out, JSON_PRETTY_PRINT);

?>

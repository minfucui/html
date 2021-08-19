<?php
include 'common.php';
include 'filefunctions.php';
include 'formfunction.php';

if (!isset($_POST['appName']))
    fail('Wrong request');

$app = consYaml($_POST);
if (sizeof($app) == 0)
    fail('Cannot find the app definition');

if (!isset($app['cluster_params']['output']))
    $app['cluster_params']['output'] = '%J.out.txt';

if (!isset($app['cluster_params']['cwd'])) {
    $cwd = $_SESSION['home'].'/jobdata';
    if (isset($app['cluster_params']['project']))
        $projname = $app['cluster_params']['project'];
    else
        $projname = 'default';
    if (!myIs_Dir($cwd)) myMkdir($cwd);
    $cwd = $cwd.'/'.$projname;
    if (!myIs_Dir($cwd)) myMkdir($cwd);
    if (isset($app['cluster_params']['instance'])) {
        $cwd = $cwd.'/'.$app['cluster_params']['instance'];
        if (!myIs_Dir($cwd)) myMkdir($cwd);
    }
    $app['cluster_params']['cwd'] = $cwd;
}

$tmpdir = $_SESSION['home'].'/.cbsched';
if (!myIs_Dir($tmpdir))
    myMkDir($tmpdir);

unset($app['icon']);
$c = yaml_emit($app);
$tmpf = $tmpdir.'/job.yaml';
if (myFile_Put_Contents($tmpf, $c) === FALSE)
    fail('Cannot create '.$tmpf);

exec($cmdPrefix.'cbtool a c -f '.$tmpf, $r, $eno);
if ($eno != 0)
    fail('Failed to run cbtool');
if ($r[0][0] == '{')
    $submitCmd = "aip j r '".$r[0]."'";
else
    $submitCmd = $r[0];
$r = [];
exec($cmdPrefix.$submitCmd, $r, $eno);
if ($eno != 0)
    fail(implode('<p>', $r));
else
    error_log(implode(';', $r));
$jobid = preg_replace('/[^0-9]/', '', $r[0]);
if ($jobid == '')
    fail(implode('<p>', $r));
$ret['data']['jobid'] = $jobid;
skylog_activity($uname, WORK, 'submit job', $jobid);
echo json_encode($ret);
?>

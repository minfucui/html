<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ./env.sh;'.$setenvdir.'cmd/runas '.$uname;
$cmdExt = $_SESSION['ext'];
$app= isset($_GET['app']) ? $_GET['app'] : '';
if ($app == '')
    header ("Location: login.php");
if (isset($_SESSION['geometry']))
   $geometry = ' -geometry '.$_SESSION['geometry'];
else
   $geometry = '';
include('apps/'.$app.'_sub.php');
$ret = sscanf($res[0], "Job %d has been submitted");
if (!is_int($ret[0]))
    $ret = sscanf($res[0], "Job <%d> is submitted");
if (is_int($ret[0])) {
    $jobid=$ret[0];
    if (strpos ($cmd, "vnc") !== false
        || strpos ($cmd, "dcv") != false
        || strpos ($cmd, "jupyter") != false)
        $_SESSION['j'.strval($jobid)] = 'y';
    header("Location: job.php?jobid=".$jobid."&idx=0");
    die();
}
include('submitted.php');
?>

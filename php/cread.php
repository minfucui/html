<?PHP
include '../header.php';
include '../clusters.php';
if (!isset($_GET['jobid']))
   header("Location: ../jobs.php");
$pword=$_SESSION['password'];
$uname=$_SESSION['uname'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ../env.sh;'.$setenvdir.'../cmd/runas '.$uname;
$cmdExt = $_SESSION['ext'];

$cmd=$cmdPrefix." cread".$cmdExt." ".$_GET['jobid']." | grep MESSAGE | /bin/awk '{print $6}'";
exec($cmd, $output, $exit_code);
if (isset($output[0])) {
    if(isset($_SESSION['j'.$_GET['jobid']])) {
        unset($_SESSION['j'.$_GET['jobid']]);
        echo '/'.$output[0];
    } else
        echo $output[0];
}
?>

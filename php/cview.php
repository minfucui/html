<?PHP
include '../header.php';
include '../language.php';
include '../clusters.php';
if (!isset($_GET['jobid']) || !isset($_GET['idx']))
   header("Location: ../jobs.php");

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
$jobid = $_GET['jobid'];
$idx=$_GET['idx'];

if ($idx !='0')
    $jobidcomplex=$jobid.'['.$idx.']';
else
    $jobidcomplex=$jobid;

$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ../env.sh;'.$setenvdir.'../cmd/runas '.$uname;
$cmdExt = $_SESSION['ext'];
exec($cmdPrefix.' cview'.$cmdExt.' '.$jobidcomplex, $cpeekout, $errno);
$nlen=sizeof($cpeekout);
for ($i=0; $i<sizeof($cpeekout); $i++)
    echo $cpeekout[$i]."\r";
?>

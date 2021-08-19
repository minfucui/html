<?PHP
include '../header.php';
include '../language.php';
$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
$olv=$_SESSION['version'];
$jobid=$_GET['jobid'];
$idx=$_GET['idx'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';

exec('export OLWD='.$pword.';source ../env.sh;'.$setenvdir.
     './runas '.$uname.' ./jinfo'.$olv.' -j '
     .$jobid.'['.$idx.']', $res,$exit_code);

if ($exit_code != 0)
    exit (0);
if (isset($res[0]))
    echo $res[0];
?>

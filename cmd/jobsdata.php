<?PHP
include '../header.php';
$pword=$_SESSION['password'];
$uname=$_SESSION['uname'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';

$params = isset($_GET['params']) ? urldecode($_GET['params']) : '';
$cmd="export OLWD=".$pword.";source ../env.sh;".$setenvdir."./runas ".$uname.
     " ./jinfo".$params;
$res = shell_exec($cmd);
if (isset($res))
    echo $res;
?>

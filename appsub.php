<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'source ./env.sh;'.$setenvdir;
$cmdExt = $_SESSION['ext'];
$app=isset($_GET['app']) ? $_GET['app'] : '';
if ($app == '')
    header ("Location: login.php");
exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);
if ($errno==0)
    $olpath=$r[0];
else
    $olpath='/tmp';
?>

<!DOCTYPE html>
<html>
<?php include('header.html');?>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
  <?PHP include 'apps/'.$app.'.php'; ?>
</body>

</html>

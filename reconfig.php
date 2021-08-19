<?PHP
include 'header.php';
include 'language.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
if ($uname!=$admin) {
     header ("Location: dashboard.php");
}
$ckres=shell_exec('export OLWD='.$pword.';source ./env.sh;echo y | cmd/runas '.$admin.' csadmin ckconfig');
?>

<!DOCTYPE html>
<html>
<?PHP include('header.html');?>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?PHP echo $lang['RECONFIGURING_THE_SCHEDULER'];?></h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <pre>
		<?PHP if (strpos($ckres, "No errors found")!=FALSE) {
			$res=shell_exec('export OLWD='.$pword.';source ./env.sh;cmd/runas '.$admin.' csadmin reconfig');
			echo $res;
			$type="btn btn-success";
			$label=$lang['OK'];
		      } else {
			echo $ckres;
			$type="btn btn-warning";
			$label=$lang['BACK'];
		      }
		?>
              </pre>
              <p></p>
              <a href="dashboard.php"><button type="button" class="<?PHP echo $type; ?>"><?PHP echo $label; ?></button></a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
</body>

</html>

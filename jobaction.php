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
$jobid=$_GET['jobid'];
$idx=$_GET['idx'];
$action=$_GET['action'];
if ($jobid=='')
   header("Location: jobs.php");
if ($idx!='0')
   $jobidaction=$jobid.'['.$idx.']';
else
   $jobidaction=$jobid;
switch ($action) {
    case "suspend":
        exec($cmdPrefix.' cstop'.$cmdExt.' '.$jobidaction, $res, $exit_code);
        break;
    case "resume":
        exec($cmdPrefix.' cresume'.$cmdExt.' '.$jobidaction, $res, $exit_code);
	break;
    case "rerun":
	exec($cmdPrefix.' crequeue'.$cmdExt.' '.$jobidaction, $res, $exit_code);
	break;
    case "kill":
	exec($cmdPrefix.' ckill'.$cmdExt.' '.$jobidaction, $res, $exit_code);
	break;
    default;
	header("Location: jobs.php");
}
?>

<!DOCTYPE html>
<html>
<?php include('header.html');?>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1 class="page-header">
        <?PHP echo $lang['JOB_ACTION'];?>
      </h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <?PHP echo $res[0]; ?>
              </p></p>
              <a href="job.php?jobid=<?PHP echo $jobid.'&idx='.$idx; ?>"><button type="button" class="btn btn-warning"><?PHP echo $lang['OK'];?></button></a>
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

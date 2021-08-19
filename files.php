<?PHP
include 'header.php';
include 'language.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);
if ($errno==0)
    $olpath=$r[0];
else
    $olpath='/tmp';
exec("ps -ef |grep webssh | grep -v grep", $r, $errno);
if ( $errno == 0)
    $ssh = true;
else
    $ssh = false;
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
        <?PHP echo $lang['MY_FILES'];?>
      </h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <?php
                if ($ssh) {
                  echo '<a target="_blank" href="http://'.$_SERVER['SERVER_ADDR'].':2222/ssh/host/'.$_SERVER['SERVER_ADDR'].'">';
                  echo '<button type="button" class="btn btn-info">ssh</button></a>';
                }
              ?>
            </div>
            <div class="box-body">
              <div id="elfinder"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
    <?PHP include('js.html');?>

    <!-- elFinder JS (REQUIRED) -->
    <?PHP include 'elFinderInit.php';?>

</body>

</html>

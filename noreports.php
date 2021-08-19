<?PHP
include 'header.php';
include 'language.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
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
        <?PHP echo $lang['NOT_AUTHORIZED_RUN_REPORTS'];?>
      </h1>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
</body>

</html>

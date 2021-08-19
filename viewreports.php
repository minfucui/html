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
        <?PHP echo $lang['REPORTS'];?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <div id="report-images"></div>
            </div>
	  </div>
        </div>
      </div>
    </section>
  </div>
</div>
    <?PHP include('js.html');?>
    <!-- Viewing Reports JavaScript -->
    <script src="viewreports.js"></script>

</body>

</html>

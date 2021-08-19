<?PHP
include 'header.php';
include 'language.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
if (!isset($_GET['id']))
    header ("Location: dashboard.php");
$dbid=$_GET['id'];
?>

<!DOCTYPE html>
<html>
<?php include('header.html');?>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content">
	<iframe id="gframe" height="900"  frameBorder="0"  scrolling="no"  width="100%" ></iframe>
    </section>
  </div>
</div>
<?PHP include('js.html');?>
</body>

<script type="text/javascript" charset="utf-8">

$(document).ready(function() { 	
	var src="http://<?PHP echo $_SESSION['grafana'].':3000/dashboard/'.$dbid.'?kiosk=tv';?>";
	$("#gframe",parent.document.body).attr("src",src); 
});




</script>


 

</html>





<?PHP
include '../header.php';
include '../language.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];

if (!isset($uname) || !isset($pword)) {
    header("Location: ../index.php");
    die();
}
?>
<!DOCTYPE html>
<html>
<?PHP include('header.html');?>
<body class="login-transition login-page">
  <div class="login-box">
    <div class="login-box-body">
      <form name="openfoam" method="post" action="http://192.168.212.77:8081/swopenfoam/login">
      <?PHP echo '<input class="form-control" value="'.$uname.'" name="username" type="hidden">';
            echo '<input class="form-control" value="'.$pword.'" name="password" type="hidden">';
      ?>
         <h2>加载中，请稍候...</h2>
      </form>
    </div>
  </div>

  <?PHP include('js.html');?>
<script>

window.onload=function() {
    document.forms['openfoam'].submit();
}
</script>

</body>
</html>


<?PHP
session_start();
$uname=$_SESSION['uname'];
$pword=$_SESSION['password'];

if (!isset($uname) || !isset($pword)) {
    header("Location: ../index.php");
    die();
}
?>
<!DOCTYPE html>
<html>
<body class="login-transition login-page">
  <div class="login-box">
    <div class="login-box-body">
      <form name="openfoam" method="post" action="http://192.168.212.77:8081/swopenfoam/login">
        <!-- <form name="openfoam" method="post" action="http://192.168.212.77:9999"> -->
      <?PHP echo '<input class="form-control" value="'.$uname.'" name="username" type="hidden">';
            echo '<input class="form-control" value="'.$pword.'" name="password" type="hidden">';
      ?>
         <h2>加载中，请稍候...</h2>
      </form>
    </div>
  </div>

<script>

window.onload=function() {
    document.forms['openfoam'].submit();
}
</script>

</body>
</html>


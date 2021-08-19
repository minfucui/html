<?PHP
session_start();
include 'language.php';

$uname = "";
$pword = "";
$errorMessage = "";

function clusters() {
    $clusters = [];
    if (!file_exists("/etc/profile.d/aip.sh"))
        return $clusters;
    exec("source /etc/profile.d/aip.sh;echo \$CB_ENVDIR", $ret, $err);
    if (!isset($ret[0]))
        return $clusters;
    $envdir = $ret[0];
    $top = dirname($envdir);
    $ret = [];
    $clusterstop = $top."/clusters";
    if (!file_exists($clusterstop)) {
        exec("source /etc/profile.d/aip.sh;lsid 2>/dev/null|grep 'cluster name'|cut -d' ' -f5", $ret, $err);
        if (!isset($ret[0]))
            return $clusters;
        return [["cluster"=>$ret[0],"env"=>$envdir]];
    }
    else {
        foreach(scandir($clusterstop) as $file) {
            if (strpos($file, ".") === 0 || strpos($file, "default") === 0)
                continue;
            $clusters[] = ["cluster"=>$file,"env"=>$clusterstop."/".$file."/etc"];
        }
    }
    return $clusters;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    if (!isset($_POST['username']) || $_POST['username'] == '') {
        header ("Location: login.php");
        die();
    }
    $uname = $_POST['username'];
    if (isset($_POST['password']))
        $pword = $_POST['password'];
    else
        $pword = 'xxx';

    $uname = htmlspecialchars($uname);
    $pword = htmlspecialchars($pword);
    
    if (strcmp($uname, "root") == 0) {
	$errorMessage=$lang['USER_ROOT_IS_NOT_ALLOWED_TO_LOGIN_HERE'];
    } else {
        if ($pword != 'xxx')
	    exec("export OLWD=".$pword.";source ./env.sh;cmd/runas ".$uname, $out, $result);
        else
            $result = '0';
	if ($result=="0") { 
	    $_SESSION['login'] = "1";
	    $_SESSION['uname'] = $uname;
	    $_SESSION['password'] = $pword;
            $clusters = clusters();
            if (sizeof($clusters) == 0)
                $errorMessage=$lang['INVALID_ENVIRONMENT'];
            else {
                $_SESSION['clusters'] = $clusters;
                $_SESSION['mycluster'] = $clusters[0];
                if (sizeof($clusters) == 1)
                    $_SESSION['ext'] = '';
                else
                    $_SESSION['ext'] = '.orig';
	        header ("Location: dashboard.php");
                die();
            }
	} else if ($result == "255") {
	    $errorMessage=$lang['INVALID_LICENSE'];
	} else {
	    $errorMessage=$lang['INVALID_USERNAME_PASSWORD'];
	}
    }
}
?>
<!DOCTYPE html>
<html>
<?PHP include('header.html');?>

<body class="login-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <b>神工仿真云</b><br><font size="4">合作伙伴</font><img src="imgs/logo.png" width="150">
    </div>
    <div class="login-box-body">
      <form name="login_form" method="post" action="login.php">
        <div class="form-group has-feedback">
          <input class="form-control" placeholder="<?PHP echo $lang['USERNAME'];?>" name="username" type="text" autofocus>
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input class="form-control" placeholder="<?PHP echo $lang['PASSWORD'];?>" name="password" type="password" value="">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-horizontal">
          <div class="form-group">
            <label class="control-label col-xs-3" style="text-align:left"><?PHP echo $lang['LANGUAGE'];?>:</label>
            <div class="col-xs-9">
              <select class="form-control" name="language">
                <?PHP
                  $languages = getLanguages();
                  if (!isset($_SESSION['language']))
                      $langCode = 'en';
                  else
                      $langCode = $_SESSION['language'];
                  foreach ($languages as $key => $value) {
                    echo '<option class="lang" value="'.$key.'"';
                    if ($key == $langCode)
                      echo ' selected';
                      echo '>'.$value.'</option>';
                  }
                ?>
              </select>
            </div>
          </div>
        </div>
        <!-- Change this to a button or input when using this as a form -->
        <div class="form-group">
          <button class="btn btn-primary btn-block btn-flat" type="Submit" Name="Submit1"><?PHP echo $lang['LOGIN'];?></button>
        </div>
      </form>
      <font color="red">
        <?PHP print $errorMessage;?></font>
    </div>
  </div>

  <?php include('js.html');?>

<!-- change language script -->
<script>
$(function(){
    $('select.form-control').on('change', function() {
	var val = $('option:selected', this).attr('value');
	if (val != '') {
	    $.ajax({
		type: 'POST',
		data: {'language': val},
		success: function() {
		    window.location = window.location;
		}
	    });
	}
    });
});

$(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
});
</script>

</body>
</html>

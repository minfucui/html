<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

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
        <?PHP
          echo $lang['APPLICATIONS'];
        ?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <?PHP
                $f = scandir("apps");
                foreach($f as $file) {
                  if ($file != '.' && $file != '..' && strpos($file, '_sub.php') == false && !strpos($file, ".png")) {
                    $menuapp = basename($file, '.php');
                    echo '<div class="col-md-2">';
                    echo '<a href="appsub.php?app='.urlencode($menuapp).'">';
                    if (file_exists('apps/'.$menuapp.'.png'))
                       $imgf = 'apps/'.$menuapp.'.png';
                    else
                       $imgf = 'imgs/'.substr($menuapp, 0, 1).'.png';
                    echo '<center>';
                    echo '<img src="'.$imgf.'" height="80" width="80"><br>';
                    echo '<h4>'.(array_key_exists($menuapp, $lang)?$lang[$menuapp]:$menuapp).'</h4>';
                    echo '</center><br></a>';
                    echo '</div>';
                    echo "\n";
                  }
                }
             ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
<script type="text/javascript">
$(function() {
    var height = window.innerHeight;
    var width = window.innerWidth;
    var screensize = (width - 16).toString() + "x" +
          (height - 24).toString();
    // console.log(screensize);
    $.get("php/screensize.php?screen="+screensize, function (data) {
       console.log(data);
    });
});
</script>
</body>

</html>

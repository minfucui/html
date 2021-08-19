<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];

$billpath = "bills/".$uname;
if (!file_exists($billpath)) {
    mkdir($billpath, 0700);
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
        <?PHP
          echo $lang['BILLS'];
        ?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <table class="table table-condensed table-striped" id="billDataTables">
                <thead>
                <tr>
                  <th><?PHP echo $lang['MONTH'];?></th>
                  <th>CPU(Hours)</th>
                  <th>GPU(Hours)</th>
                  <th><?PHP echo $lang['MEMORY_USE'];?>(GB-Hours)</th>
                  <th><?PHP echo $lang['APPLICATIONS'];?>(Hours)</th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                  if (($files = scandir($billpath)) != FALSE) {
                       foreach ($files as $file) {
                          if (strpos($file, "bill") === FALSE)
                              continue;
                          $billcontent = file_get_contents($billpath.'/'.$file);
                          $bill = json_decode($billcontent, TRUE);
                          echo '<tr>';
                          printf ('<td><a href="billdetail.php?file=%s">%s</a></td>',
                               urlencode($billpath.'/'.$file), $bill['Month']);
                          printf ('<td>%.4f</td>', $bill['CPU_Hours']);
                          printf ('<td>%.4f</td>', $bill['GPU_Hours']);
                          printf ('<td>%.4f</td>', $bill['Mem_GB_Hours']);
                          $app = 0;
                          if (sizeof($bill['App_Hours']) > 0)
                              foreach ($bill['App_Hours'] as $key=>$a) {
                                  $app += $a;
                              }
                          printf ('<td>%.4f</td>', $app);
                          echo '</tr>';
                      }  
                  }
                  ?>
                </tbody>
              </table>
              <div id="loading-image"></div>
            </div>
            <div class="box-body">
              <div class="col-md-10">
                <button type="button" class="btn btn-success" onclick="genbills()">
                   <?PHP echo $lang['RUN_BILLS'];?>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div> 
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
<script type="text/javascript">
function genbills() {
    $('#loading-image').html('<img src="img/loading.gif" style="padding:25px 50px 50px">');
    $.get("billgen.php", function(result) {});
    location.replace("bills.php");
}

$(function() {
    $('#billDataTables').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "order": [[0,"desc"]],
        "columnDefs": [{"targets":[0],"orderable":false}],
        "info": true,
        "lengthMenu": [50, 100, 500],
        "autoWidth": true,
        "stateSave": true,
        "language":
        <?PHP
            $languages = getAnglicizedLanguages();
            echo file_get_contents('plugins/datatables-plugins/i18n/'.$languages
[$language].'.lang');
        ?>
    });
});
</script>
</body>

</html>

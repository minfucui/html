<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$filepath=$_GET['file'];
if (!isset($uname) || !isset($filepath)) {
    header("Location: index.php");
    die();
}
$filepath = urldecode($filepath);
$out = file_get_contents($filepath);
if ($out == '') {
    header("Location: bills.php");
    die();
}
$bill = json_decode($out, true);
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
        <?PHP echo $lang['BILL']; ?>
      </h1>
    </section>

    <section class="invoice">
      <div class="row">
        <div class="col-xs-12">
          <h2 class="page-header">
            <?php echo $lang['USER'].': '.($bill['User'] == 'all'?$lang['ALL']:$bill['User']);?>
            <span class="pull-right"><?php echo $lang['MONTH'].': '.substr(basename($filepath), 5);?></span>
          </h2>
        <div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table table-condensed table-striped">
            <thead>
            <tr>
              <th><?PHP echo $lang['RESOURCE'];?></th>
              <th><?PHP echo $lang['UNIT'];?></th>
              <th><?PHP echo $lang['QUANTITY'];?></th>
            </tr>
            </thead>
            <tbody>
            <?PHP
              echo '<tr>';
              echo ' <td>CPU</td>';
              echo ' <td>'.$lang['CORE'].$lang['HOURS'].'</td>';
              printf (" <td>%.3f</td>\n", $bill['CPU_Hours']);
              echo '</tr>';
              echo '<tr>';
              echo '<td>'.$lang['MEMORY_USE'].'</td>';
              echo ' <td>GB'.$lang['HOURS'].'</td>';
              printf (" <td>%.3f</td>\n", $bill['Mem_GB_Hours']);
              echo '</tr>';
              echo '<tr>';
              echo ' <td>GPU</td>';
              echo ' <td>GPU'.$lang['HOURS'].'</td>';
              printf (" <td>%.3f</td>\n", $bill['GPU_Hours']);
              echo '</tr>';
              foreach ($bill['App_Hours'] as $key=>$app) {
                  echo '<tr>';
                  echo '<td>'.$key.'</td>';
                  echo ' <td>'.$lang['CORE'].$lang['HOURS'].'</td>';
                  printf (" <td>%.3f</td>\n", $app);
                  echo '</tr>';
              }
            ?>
            </tbody>
          </table>
        </div>
      </div> 
      <div class="row">
        <div class="col-xs-12">
          <h3 class="page-header">
            <?php echo $lang['BILLDETAIL'];?>
          </h3>
        <div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table table-condensed table-striped" id="billDataTable">
            <thead>
             <tr>
             <?PHP
              echo '<th>'.$lang['ENDDATE'].'</th>';
              echo '<th>'.$lang['USER'].'</th>';
              echo '<th>'.$lang['APP_NAME'].'</th>';
              echo '<th>'.$lang['JOB_ID'].'</th>';
              echo '<th>'.$lang['RUN_TIME'].'('.$lang['HOURS'].')</th>';
              echo '<th>CPU'.$lang['CORE'].$lang['HOURS'].'</th>';
              echo '<th>'.$lang['MEMORY_USE'].' GB'.$lang['HOURS'].'</th>';
              echo '<th>GPU'.$lang['HOURS'].'</th>';
              echo '<th>'.$lang['CLUSTER'].'</th>';
              echo '<th>'.$lang['QUEUE'].'</th>';
             ?>
            </tr>
            </thead>
            <tbody>
            <?PHP
              foreach ($bill['Details'] as $j) {
                echo "<tr>";
                echo '<td>'.$j['EndDate'].'</td>';
                echo '<td>'.$j['User'].'</td>';
                echo '<td>'.$j['App'].'</td>';
                echo '<td>'.$j['JobId'].'</td>';
                printf ('<td>%.4f</td>', $j['runTime']);
                printf ('<td>%.4f</td>', $j['CPU_Hours']);
                printf ('<td>%.4f</td>', $j['Mem_GB_Hours']);
                printf ('<td>%.4f</td>', $j['GPU_Hours']);
                printf ('<td>%s</td>', $j['Cluster']);
                printf ('<td>%s</td>', $j['Queue']);
                echo "</tr>\n";
              }
            ?>
            </tbody>
          </table>
        </div>
      </row>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
<script type="text/javascript">
$(function() {
    $('#billDataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "order": [],
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

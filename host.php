<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$host=$_GET['host'];
$olv=$_SESSION['version'];
if ($host=='')
   header("Location: hosts.php");

$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'source ./env.sh;'.$setenvdir;

exec($cmdPrefix.'cmd/hinfo'.$olv.' -m '.$host, $res, $exit_code);
$hinfo = json_decode($res[0], true);
if (isset($hinfo[0]['Error'])) {
    header("Location: hosts.php");
    die();
}
$utilization = $hinfo['UTILIZATION'];
$host_status_alert = $hinfo['HOST_STATUS_ALERT'];
$loadindex = $hinfo['LOAD_INDEX'];
$resource = $hinfo ['RESOURCE'];
$threshold = $hinfo['THRESHOLD'];
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
        <?PHP echo $lang['HOST'].': '.$hinfo['HOST']; ?>
      </h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <div class="col-md-10">
                <button type="button" class="btn btn-success"
                  <?PHP
                    if (strcmp($uname, $admin)!=0)
                      echo 'disabled';
                  ?>
                  onclick="hopen()"><?PHP echo $lang['OPEN'];?></button>
                <button type="button" class="btn btn-warning"
                  <?PHP
                   if (strcmp($uname, $admin)!=0)
                     echo 'disabled';
                  ?>
                  onclick="hclose()"><?PHP echo $lang['CLOSE'];?></button>
              </div>
              <div class="col-md-1">
                <a href="jobs.php?host=<?PHP echo $host;?>"><button type="button" class="btn btn-default">
                <?PHP echo $lang['JOB_LIST'];?></button></a>
              </div>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-body">
              <?PHP echo '<div class="alert '.$hinfo['HOST_STATUS_ALERT'].'">'.$lang['HOST_STATUS'].': <b>'.$hinfo['STATUS'].'</b></div>';?>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title">
                <?PHP echo $lang['HOST_UTILIZATION_AND_SLOT_LIMITS'];?>
              </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th><?PHP echo $lang['TOTAL_NUM_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RUNNING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_SUSP_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RESERVED_JOB_SLOTS'];?></th>
                  <th><?PHP echo $lang['MAX_JOB_SLOTS'];?> </th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_USER'];?></th>
                  <th><?PHP echo $lang['DISPATCH_WINDOW'];?></th>
                  <th><?PHP echo $lang['CPU_FACTOR'];?></th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                    echo '<tr class="'.$host_status_alert.'">';
                    echo '<td><a href="'.$utilization['JOB_URL'].'">'.$utilization['TOTAL_NUM_JOBS'].'</font></a></td>';
                    echo '<td>'.$utilization['NUM_RUNNING_JOBS'].'</td>';
                    echo '<td>'.$utilization['NUM_SUSP_JOBS'].'</td>';
                    echo '<td>'.$utilization['NUM_RESERVED_JOBS'].'</td>';
                    echo '<td>'.(array_key_exists('MAX_JOB_SLOTS',$utilization)?$utilization['MAX_JOB_SLOTS']:'-').'</td>';
                    echo '<td>'.(array_key_exists('JOB_SLOT_LIMIT_PER_USER',$utilization)?$utilization['JOB_SLOT_LIMIT_PER_USER']:'-').'</td>';
                    echo '<td>'.(array_key_exists('DISPATCH_WINDOW',$utilization)?$utilization['DISPATCH_WINDOW']:'-').'</td>';
                    echo '<td>'.$utilization['CPU_FACTOR'].'</td>';
                    echo '</tr>';
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
     <?php
       if ($_SESSION['es']) {
          echo '<div class="col-md-6">'."\n";
          echo ' <div class="box box-solid">'."\n";
          echo '  <div class="box-header with-border">'."\n";
          echo '    <h3 class="box-title">'.$lang['UT'].'</h3>'."\n";
          echo '  </div>'."\n";
          echo '  <div class="box-body">'."\n";
          echo '    <div id="ut" style="height: 200px;"></div>'."\n";
          echo '  </div>'."\n";
          echo ' </div>'."\n";
          echo '</div>'."\n";
          echo '<div class="col-md-6">'."\n";
          echo ' <div class="box box-solid">'."\n";
          echo '  <div class="box-header with-border">'."\n";
          echo '    <h3 class="box-title">'.$lang['MEM_UT'].'</h3>'."\n";
          echo '  </div>'."\n";
          echo '  <div class="box-body">'."\n";
          echo '    <div id="memut" style="height: 200px;"></div>'."\n";
          echo '  </div>'."\n";
          echo ' </div>'."\n";
          echo '</div>'."\n";
          echo '<div class="col-md-6">'."\n";
          echo ' <div class="box box-solid">'."\n";
          echo '  <div class="box-header with-border">'."\n";
          echo '    <h3 class="box-title">'.$lang['NETIO'].'</h3>'."\n";
          echo '  </div>'."\n";
          echo '  <div class="box-body">'."\n";
          echo '    <div id="io" style="height: 200px;"></div>'."\n";
          echo '  </div>'."\n";
          echo ' </div>'."\n";
          echo '</div>'."\n";
          echo '<div class="col-md-6">'."\n";
          echo ' <div class="box box-solid">'."\n";
          echo '  <div class="box-header with-border">'."\n";
          echo '    <h3 class="box-title">'.$lang['JOB_SLOT_USAGE'].'</h3>'."\n";
          echo '  </div>'."\n";
          echo '  <div class="box-body">'."\n";
          echo '    <div id="slotut" style="height: 200px;"></div>'."\n";
          echo '  </div>'."\n";
          echo '</div></div>'."\n";
       }
     ?>
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title">
                <?PHP echo $lang['CURRENT_LOAD_USED_FOR_SCHEDULING'];?>
              </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <?PHP 
                  echo '<thead><tr><th>'.$lang['LOAD_INDEX'].'</th>';
                  foreach ($loadindex as $value)
                    echo '<th>'.$value['INDEX'].'</th>';
                  echo '</tr></thead>';
                  echo '<tbody><tr><td><b>'.$lang['TOTAL'].'</b></td>';
                  foreach ($loadindex as $value)
                    echo '<td>'.$value['TOTAL'].'</td>';
                  echo '</tr>';
                  echo '<tr><td><b>'.$lang['RESERVED'].'</b></td>';
                  foreach ($loadindex as $value)
                    echo '<td>'.$value['RESERVED'].'</td>';
                  echo '</tr></tbody>';
                ?>
              </table>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title">
                <?PHP echo $lang['SHARED_RESOURCES_USED_FOR_SCHEDULING'];?>
              </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <?PHP
                  echo '<thead><tr><th>'.$lang['RESOURCE'].'</th>';
                  foreach ($resource as $value)
                    echo '<th>'.$value['NAME'].'</th>';
                  echo '</tr></thead>';
                  echo '<tbody><tr><td><b>'.$lang['TOTAL'].'</b></td>';
                  foreach ($resource as $value)
                    echo '<td>'.$value['TOTAL'].'</td>';
                  echo '</tr>';
                  echo '<tr><td><b>'.$lang['RESERVED'].'</b></td>';
                  foreach ($resource as $value)
                    echo '<td>'.$value['RESERVED'].'</td>';
                  echo '</tr></tbody>';
                ?>
              </table>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title">
                <?PHP echo $lang['LOAD_THRESHOLD_USED_FOR_SCHEDULING'];?>
              </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <?PHP
                  echo '<thead><tr><th>'.$lang['THRESHOLD'].'</th>';
                  foreach ($threshold as $value)
                    echo '<th>'.$value['INDEX'].'</th>';
                  echo '</tr></thead>';
                  echo '<tbody><tr><td><b>'.$lang['SCHEDULE'].'</b></td>';
                  foreach ($threshold as $value)
                    echo '<td>'.$value['SCHEDULE'].'</td>';
                  echo '</tr>';
                  echo '<tr><td><b>'.$lang['STOP'].'</b></td>';
                  foreach ($threshold as $value)
                    echo '<td>'.$value['STOP'].'</td>';
                  echo '</tr></tbody>';
                ?>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title"><?PHP echo $lang['CONFIGURATION'];?></h3>
            </div>
            <div class="box-body">
              <table class="tab">
                <?PHP 
                  if (isset($hinfo['CONFIGURATION'])) {
                    $configuration = $hinfo['CONFIGURATION'];
                    echo '<tr><td class="celll">'.$lang['HOST_TYPE'].'</td><td><b>'.$configuration['HOST_TYPE'].'</b></td>';
                    echo '<tr><td class="celll">'.$lang['HOST_MODEL'].'</td><td><b>'.$configuration['HOST_MODEL'].'</b></td>';
                    echo '<tr><td class="celll">'.$lang['NUMBER_OF_CPUS'].'</td><td><b>'.(array_key_exists('NUMBER_OF_CPUS',$configuration)?$configuration['NUMBER_OF_CPUS']:'-').'</b></td>';
                    echo '<tr><td class="celll">'.$lang['MAXIMUM_MEMORY'].'</td><td><b>'.(array_key_exists('MAXIMUM_MEMORY',$configuration)?$configuration['MAXIMUM_MEMORY']:'-').'</b></td>';
                    echo '<tr><td class="celll">'.$lang['MAXIMUM_SWAP'].'</td><td><b>'.(array_key_exists('MAXIMUM_SWAP',$configuration)?$configuration['MAXIMUM_SWAP']:'-').'</b></td>';
                    echo '<tr><td class="celll">'.$lang['SERVER'].'</td><td><b>'.$configuration['SERVER'].'</b></td>';
                    echo '<tr><td class="celll">'.$lang['RESOURCES'].'</td><td><b>'.(array_key_exists('RESOURCES',$configuration)?$configuration['RESOURCES']:'-').'</b></td>';
                  }
                ?>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>
  <script type="text/javascript" src="plugins/fastclick/fastclick.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.min.js"></script>
  <script src="plugins/flot/jquery.flot.resize.min.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.time.js"></script>
  <script src="plugins/jQuery/hashtable.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.symbol.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.axislabels.js"></script>
  <script src="plugins/flot/jquery.flot.symbol.js"></script>
  <script src="plugins/jQuery/jquery.numberformatter-1.2.4.min.js"></script>
  <script src="plugins/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
  <script src="plugins/flot/jquery.flot.tickrotor.js"></script>
  <script>
    function hopen() {
       var host;
       host="<?PHP echo $host; ?>";
       if (confirm("<?PHP echo $lang['OPEN_HOST'];?> "+host+"?")==true) {
           window.location.assign("haction.php?action=hopen&host="+host);
       }
    };
    function hclose() {
       var host;
       host="<?PHP echo $host; ?>";
       if (confirm("<?PHP echo $lang['CLOSE_HOST'];?> "+host+"?")==true) {
           window.location.assign("haction.php?action=hclose&host="+host);
       }
    };
    $(function() {
      var es = <?PHP if ($_SESSION['es']) echo 1; else echo 0; ?>;

      if (es == 0)
          return;
      var options = {
        series: {
            lines: {
                show: true,
                fill: true,
                lineWidth: 1.2,
            }
        },
        colors: ["#00a65a"],
        xaxis: {
            mode: "time",
            timezone: "browser",
            timeformat: "%m/%d %H:%M",
            show: true
        },
        yaxis: {
            min: 0,
            max: 1,
            show: true
        },
        grid: {
            hoverable: true,
            borderWidth:1,
            axisMargin:50
        },
        tooltip: {
            show: true,
            content: "%x %y.2",
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        }
      };

      function displayCharts() {
        $.get("php/eshost.php?host=<?php echo $host;?>", function(data) {
            var plotdata = JSON.parse(data);
            var ut = [{data: plotdata['ut']}];
            var memut = [{data: plotdata['memUt']}];
            var io = [{data: plotdata['io']}];
            var slotut = [{data: plotdata['slotUt']}];
            $.plot("#ut", ut, options);
            $.plot("#memut", memut, options);
            $.plot("#slotut", slotut, options);
            options.yaxis.max = null;
            $.plot("#io", io, options);
        });
      }

      displayCharts();

    });

    </script>
</body>

</html>

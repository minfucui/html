<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$hosts=isset($_GET['hosts'])?$_GET['hosts']:'';
$olv=$_SESSION['version'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'source ./env.sh;'.$setenvdir;
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
        <?PHP echo $lang['HOSTS'];?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <table class="table table-condensed table-striped" id="jobDataTables">
                <thead>
                <tr>
                  <th><?PHP echo $lang['HOST_NAME'];?></th>
                  <th><?PHP echo $lang['STATUS'];?></th>
                  <th><?PHP echo $lang['TOTAL_NUM_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RUNNING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_SUSP_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RESERVED_JOB_SLOTS'];?></th>
                  <th><?PHP echo $lang['R1M'];?></th>
                  <th><?PHP echo $lang['UT'];?></th>
                  <th><?PHP echo $lang['MEM'];?></th>
                  <th><?PHP echo $lang['NETIO'];?></th>
                  <th><?PHP echo $lang['GPU'];?></th>
                  <th><?PHP echo $lang['MAX_JOB_SLOTS'];?></th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_USER'];?></th>
                  <th><?PHP echo $lang['DISPATCH_WINDOW'];?></th>
                  <th><?PHP echo $lang['CPU_FACTOR'];?></th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                    if ($hosts!='')
                      $hosts=' -g "'.$hosts.'"'; 
                    exec($cmdPrefix."cmd/hinfo".$olv.$hosts, $res, $exit_code);
                    foreach (json_decode($res[0], true) as $row) {
                      echo '<tr>';
                      echo '<td><a href="'.$row['HOST_URL'].'">'.$row['HOST'].'</font></a></td>';
                      echo '<td class="'.$row['CLASS'].'">'.$row['STATUS'].'</td>';
                      echo '<td><a href="'.$row['JOB_URL'].'">'.$row['TOTAL_NUM_JOBS'].'</font></a></td>';
                      echo '<td>'.$row['NUM_RUNNING_JOBS'].'</td>';
                      echo '<td>'.$row['NUM_SUSP_JOBS'].'</td>';
                      echo '<td>'.$row['NUM_RESERVED_JOBS'].'</td>';
                      echo '<td>'.$row['R1M'].'</td>';
                      echo '<td>'.$row['UT'].'</td>';
                      echo '<td>'.$row['MEM'].'</td>';
                      echo '<td>'.$row['NETIO'].'</td>';
                      echo '<td>'.$row['GPU'].'</td>';
                      echo '<td>'.(array_key_exists('MAX_JOB_SLOTS',$row)?$row['MAX_JOB_SLOTS']:'-').'</td>';
                      echo '<td>'.(array_key_exists('JOB_SLOT_LIMIT_PER_USER',$row)?$row['JOB_SLOT_LIMIT_PER_USER']:'-').'</td>';
                      echo '<td>'.(array_key_exists('DISPATCH_WINDOW',$row)?$row['DISPATCH_WINDOW']:'-').'</td>';
                      echo '<td>'.$row['CPU_FACTOR'].'</td>';
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
<?PHP include('js.html');?>

    <?PHP
    // datatables Initialization
    include 'datatablesInit.php';
    ?>

</body>

</html>

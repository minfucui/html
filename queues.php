<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$olv=$_SESSION['version'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'source ./env.sh;'.$setenvdir;
?>

<!DOCTYPE html>
<html>
<?PHP include('header.html');?>

<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1 class="page-header"><?PHP echo $lang['QUEUES'];?></h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <table class="table table-striped">
                <thead>
                <tr>
                  <th><?PHP echo $lang['QUEUE_NAME'];?></th>
                  <th><?PHP echo $lang['STATUS'];?></th>
                  <th><?PHP echo $lang['PRIORITY'];?></th>
                  <th><?PHP echo $lang['HOSTS'];?></th>
                  <th><?PHP echo $lang['TOTAL_NUM_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_PENDING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RUNNING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_SUSP_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['MAX_JOB_SLOTS'];?> </th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_USER'];?></th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_CORE'];?></th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_HOST'];?></th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                    if ($uname != $admin)
                      $qopt=" -u ".$uname;
                    else
                      $qopt="";
                    exec($cmdPrefix.'cmd/qinfo'.$olv.$qopt, $res, $exit_code);
                    foreach (json_decode($res[0], true) as $row) {
                      echo '<tr class="gradeA">';
                      echo '<td><a href="'.$row['QUEUE_URL'].'">'.$row['QUEUE'].'</font></a></td>';
                      echo '<td class="'.$row['QUEUE_STATUS_ALERT'].'">'.$row['STATUS'].'</td>';
                      echo '<td>'.$row['PRIORITY'].'</td>';
                      $hosts=rtrim($row['HOSTS']," ");
                      $hosts=str_replace("/", "", $hosts);
                      echo '<td><a href="hosts.php?hosts='.urlencode($hosts).'">'.$row['HOSTS'].'</font></a></td>';
                      echo '<td><a href="'.$row['JOB_URL'].'">'.$row['TOTAL_NUM_JOBS'].'</font></a></td>';
                      echo '<td>'.$row['NUM_PENDING_JOBS'].'</td>';
                      echo '<td>'.$row['NUM_RUNNING_JOBS'].'</td>';
                      echo '<td>'.$row['NUM_SUSP_JOBS'].'</td>';
                      echo '<td>'.(array_key_exists('MAX_JOB_SLOTS',$row)?$row['MAX_JOB_SLOTS']:'-').'</td>';
                      echo '<td>'.(array_key_exists('JOB_SLOT_LIMIT_PER_USER',$row)?$row['JOB_SLOT_LIMIT_PER_USER']:'-').'</td>';
                      echo '<td>'.(array_key_exists('JOB_SLOT_LIMIT_PER_CORE',$row)?$row['JOB_SLOT_LIMIT_PER_CORE']:'-').'</td>';
                      echo '<td>'.(array_key_exists('JOB_SLOT_LIMIT_PER_HOST',$row)?$row['JOB_SLOT_LIMIT_PER_HOST']:'-').'</td>';
                      echo '</tr>';
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
</body>

</html>

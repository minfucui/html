<?PHP
include 'header.php';
include 'language.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
$olv=$_SESSION['version'];
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
        <?PHP echo $lang['USERS'];?>
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
                  <th><?PHP echo $lang['USER_GROUP'];?> </th>
                  <th><?PHP echo $lang['TOTAL_NUM_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_PENDING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RUNNING_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_SUSP_JOBS'];?> (<?PHP echo $lang['SLOTS'];?>)</th>
                  <th><?PHP echo $lang['NUM_RESERVED_JOB_SLOTS'];?></th>
                  <th><?PHP echo $lang['MAX_JOB_SLOTS'];?> </th>
                  <th><?PHP echo $lang['JOB_SLOT_LIMIT_PER_CORE'];?></th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                    $users=shell_exec('source ./env.sh;export OLWD='.$pword.';cmd/runas '.$uname.' cmd/uinfo');
                    foreach(json_decode($users, true) as $user) {
                      echo '<tr>';
                      echo '<td>'.$user['USER'].'</td>';
                      echo '<td><a href="'.$user['JOB_URL'].'">'.$user['NUM_JOBS'].'</font></a></td>';
                      echo '<td>'.$user['NUM_PENDING_JOBS'].'</td>';
                      echo '<td>'.$user['NUM_RUNNING_JOBS'].'</td>';
                      echo '<td>'.$user['NUM_SUSP_JOBS'].'</td>';
                      echo '<td>'.$user['NUM_RESERVED_SLOTS'].'</td>';
                      echo '<td>'.$user['MAX_JOB_SLOTS'].'</td>';
                      echo '<td>'.$user['MAX_SLOTS_PER_PROCESSOR'].'</td>';
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
    <?PHP
    // datatables Initialization
    include 'datatablesInit.php';
    ?>

</body>

</html>

<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$olv=$_SESSION['version'];
$queue=$_GET['queue'];
if ($queue=='')
   header("Location: queues.php");
putenv("COLUMNS=4000");

$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'source ./env.sh;'.$setenvdir;

exec($cmdPrefix.'cmd/qinfo'.$olv.' -q '.$queue, $res, $exit_code);
$qinfo = json_decode($res[0], true);
if (isset($qinfo[0]['Error'])) {
    header("Location: queues.php");
    die();
}
$utiltable = $qinfo['UTILIZATION'];
$resourcelimit = $qinfo['RESOURCE_LIMIT'];
$threshold = $qinfo['THRESHOLD'];
$usershares = $qinfo['USER_SHARES'];
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
      <h1 class="page-header">
        <?PHP echo $lang['QUEUE'];?>: <?PHP echo $qinfo['QUEUE_NAME']; ?>
      </h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <div class="col-md-10">
                <button type="button" class="btn btn-info"
                  <?PHP
                    if (strcmp($uname, $admin)!=0)
                      echo 'disabled';
                  ?>
                  onclick="qactivate()"><?PHP echo $lang['ACTIVATE'];?></button>
                <button type="button" class="btn btn-warning"
                  <?PHP
                    if (strcmp($uname, $admin)!=0)
                      echo 'disabled';
                  ?>
                  onclick="qinactivate()"><?PHP echo $lang['INACTIVATE'];?></button>
                <button type="button" class="btn btn-success"
                  <?PHP
                    if (strcmp($uname, $admin)!=0)
                      echo 'disabled';
                  ?>
                  onclick="qopen()"><?PHP echo $lang['OPEN'];?></button>
                <button type="button" class="btn btn-danger"
                  <?PHP
                    if (strcmp($uname, $admin)!=0)
                      echo 'disabled';
                  ?>
                  onclick="qclose()"><?PHP echo $lang['CLOSE'];?></button>
              </div>
              <div class="col-lg-1">
                <a href="jobs.php?queue=<?PHP echo $queue;?>"><button type="button" class="btn btn-default">
                <?PHP echo $lang['JOB_LIST'];?></button></a>
              </div>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-body">
              <?PHP echo '<div class="alert '.$qinfo['QUEUE_STATUS_ALERT'].'">'.$lang['QUEUE_STATUS'].': <b>'.$qinfo['STATUS'].'</b></div>';?>
              <?PHP echo $qinfo['DESCRIPTION'];?>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">  
              <h3 class="box-title"><?PHP echo $lang['QUEUE_UTILIZATION_AND_SLOT_LIMITS'];?> </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <thead>
                <tr>
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
                  <tr>
                  <td><a href="<?PHP echo $utiltable['JOB_URL'];?>"><?PHP echo $utiltable['TOTAL_NUM_JOBS'];?></font></a></td>
                  <td><?PHP echo $utiltable['NUM_PENDING_JOBS'];?></td>
                  <td><?PHP echo $utiltable['NUM_RUNNING_JOBS'];?></td>
                  <td><?PHP echo $utiltable['NUM_SUSP_JOBS'];?></td>
                  <td><?PHP echo (array_key_exists('MAX_JOB_SLOTS',$utiltable)?$utiltable['MAX_JOB_SLOTS']:'-');?></td>
                  <td><?PHP echo (array_key_exists('JOB_SLOT_LIMIT_PER_USER',$utiltable)?$utiltable['JOB_SLOT_LIMIT_PER_USER']:'-');?></td>
                  <td><?PHP echo (array_key_exists('JOB_SLOT_LIMIT_PER_CORE',$utiltable)?$utiltable['JOB_SLOT_LIMIT_PER_CORE']:'-');?></td>
                  <td><?PHP echo (array_key_exists('JOB_SLOT_LIMIT_PER_HOST',$utiltable)?$utiltable['JOB_SLOT_LIMIT_PER_HOST']:'-');?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title"><?PHP echo $lang['RESOURCE_LIMITS'];?> </h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th><?PHP echo $lang['RESOURCE'];?></th>
                  <th><?PHP echo $lang['CPU_TIME'];?> (<?PHP echo $lang['SEC'];?>)</th>
                  <th><?PHP echo $lang['FILE_SIZE'];?> (KB)</th>
                  <th><?PHP echo $lang['DATA_SEGMENT_SIZE'];?> (KB)</th>
                  <th><?PHP echo $lang['STACK_SIZE'];?> (KB)</th>
                  <th>Core Size (KB)</th>
                  <th><?PHP echo $lang['MEMORY_SIZE'];?> (KB)</th>
                  <th><?PHP echo $lang['RUN_TIME'];?> (sec)</th>
                  <th><?PHP echo $lang['NUM_OF_PROCESSES'];?></th>
                  <th>Swap Size (KB)</th>
                </tr>
                </thead>
                <tbody>
                  <?PHP
                    function isunlimited($string) {
                      if (is_numeric($string) && intval($string) == -1)
                        return $lang['UNLIMITED'];
                      return $string;
                    }
                  ?>
                  <tr><td><b>Soft</b></td>
                    <?PHP
                      foreach ($resourcelimit as $limit) {
                        $value = $limit['SOFT'];
                        echo '<td>';
                        if (is_numeric($value) && intval($value) == -1)
                          echo $lang['UNLIMITED']; 
                        else
                          echo $value;
                        echo '</td>';
                      } 
                    ?>
                  </tr>
                  <tr><td><b>Hard</b></td>
                    <?PHP
                      foreach ($resourcelimit as $limit) {
                        $value = $limit['HARD'];
                        echo '<td>';
                        if (is_numeric($value) && intval($value) == -1)
                          echo $lang['UNLIMITED'];
                        else
                          echo $value;
                        echo '</td>';
                      }
                    ?>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title"><?PHP echo $lang['LOAD_THRESHOLD'];?></h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th><?PHP echo $lang['THRESHOLD'];?></th>
                  <?PHP foreach ($threshold as $value) echo '<th>'.$value['INDEX'].'</th>';?>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><b><?PHP echo $lang['SCHEDULE'];?></b></td>
                    <?PHP
                      foreach ($threshold as $value)
                        echo '<td>'.$value['SCHEDULE'].'</td>';
                    ?>
                  </tr>
                  <tr>
                    <td><b><?PHP echo $lang['STOP'];?></b></td>
                    <?PHP
                      foreach ($threshold as $value)
                        echo '<td>'.$value['STOP'].'</td>';
                    ?>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title"><?PHP echo $lang['CONFIGURED_PARAMETERS'];?></h3>
            </div>
            <div class="box-body">
              <table class="tab">
                <tr><td class="celll"><?PHP echo $lang['PRIORITY'];?></td><td><b><?PHP echo $qinfo['PRIORITY'];?></b></td></tr>
                <tr><td class="celll">NICE</td><td><b><?PHP echo $qinfo['NICE'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['DEFAULT_HOST'];?></td><td><b><?PHP echo $qinfo['DEFAULT_HOST'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['RUN_WINDOWS'];?></td><td><b><?PHP echo (array_key_exists('RUN_WINDOWS',$qinfo)?$qinfo['RUN_WINDOWS']:'-');?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['DISPATCH_WINDOWS'];?></td><td><b><?PHP echo $qinfo['DISPATCH_WINDOWS'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['USERS'];?></td><td><b><?PHP echo $qinfo['USERS'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['HOSTS'];?></td><td><b><?PHP echo $qinfo['HOSTS'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['ADMINISTRATORS'];?></td><td><b><?PHP echo $qinfo['ADMINISTRATORS'];?></b></td></tr>
                <tr><td class="celll">Pre-execution</td><td><b><?PHP echo $qinfo['PRE_EXECUTION'];?></b></td></tr>
                <tr><td class="celll">Post-execution</td><td><b><?PHP echo $qinfo['POST_EXECUTION'];?></b></td></tr>
                <tr><td class="celll">Requeue Exit Values</td><td><b><?PHP echo $qinfo['REQUEUE_EXIT_VALUE'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['JOB_ACCEPTANCE_INTERVAL'];?></td><td><b><?PHP
                  if (isset($qinfo['JOB_ACCEPTANCE_INTERVAL'])) echo $qinfo['JOB_ACCEPTANCE_INTERVAL'].' seconds'; else echo '-';?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['MIGRATION_THRESHOLD'];?></td><td><b><?PHP
                  if (isset($qinfo['MIGRATION_THRESHOLD'])) echo $qinfo['MIGRATION_THRESHOLD'].' minutes'; else echo '-';?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['NEW_JOB_SCHEDULE_DELAY'];?></td><td><b><?PHP
                  if (isset($qinfo['NEW_JOB_SCHEDULE_DELAY'])) echo $qinfo['NEW_JOB_SCHEDULE_DELAY'].' seconds'; else echo '-';?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['RESOURCE_REQUIREMENT'];?></td><td><b><?PHP echo $qinfo['RESOURCE_REQUIREMENT'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['MAX_SLOT_RESERVE_TIME'];?></td><td><b><?PHP
                  if (isset($qinfo['MAX_SLOT_RESERVE_TIME'])) echo $qinfo['MAX_SLOT_RESERVE_TIME'].' seconds'; else echo '-';?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['RESUME_CONDITION'];?></td><td><b><?PHP echo $qinfo['RESUME_CONDITION'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['STOP_CONDITION'];?></td><td><b><?PHP echo $qinfo['STOP_CONDITION'];?></b></td></tr>
                <tr><td class="celll">Job Starter</td><td><b><?PHP echo $qinfo['JOB_STARTER'];?></b></td></tr>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-titlt"><?PHP echo $lang['CONFIGURED_POLICIES'];?></h3>
            </div>
            <div class="box-body">
              <table class="tab">
                <tr><td class="celll"><?PHP echo $lang['POLICIES'];?></td><td><b><?PHP echo $qinfo['POLICIES'];?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['JOB_SUSPEND_CONTROLS'];?></td><td><b><?PHP echo (array_key_exists('JOB_SUSPEND_CONTROLS',$qinfo)?$qinfo['JOB_SUSPEND_CONTROLS']:'-');?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['JOB_RESUME_CONTROLS'];?></td><td><b><?PHP echo (array_key_exists('JOB_RESUME_CONTROLS',$qinfo)?$qinfo['JOB_RESUME_CONTROLS']:'-');?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['JOB_TERMINATE_CONTROLS'];?></td><td><b><?PHP echo (array_key_exists('JOB_TERMINATE_CONTROLS',$qinfo)?$qinfo['JOB_TERMINATE_CONTROLS']:'-');?></b></td></tr>
                <tr><td class="celll"><?PHP echo $lang['TERMINATE_INSTEAD_OF_SUSPEND_BY'];?></td><td><b><?PHP echo (array_key_exists('TERMINATE_INSTEAD_OF_SUSPEND_BY',$qinfo)?$qinfo['TERMINATE_INSTEAD_OF_SUSPEND_BY']:'-');?></b></td></tr>
              </table>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-header">
              <h3 class="box-title"><?PHP echo $lang['USER_SHARES'];?></h3>
            </div>
            <div class="box-body">
              <?PHP
                if (isset($usershares['TOTAL_SLOTS']) && isset($usershares['FREE_SLOTS']))
                  echo $lang['TOTAL_SLOTS'].': <b>'.$usershares['TOTAL_SLOTS'].'</b> '.$lang['FREE_SLOTS'].': <b>'.$usershares['FREE_SLOTS'].'</b>';
              ?>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                  <tr>
                    <th><?PHP echo $lang['USER_GROUP'];?></th>
                    <th><?PHP echo $lang['SHARES'];?></th>
                    <th><?PHP echo $lang['PRIORITY'];?></th>
                    <th><?PHP echo $lang['NUM_PENDING_JOBS'];?></th>
                    <th><?PHP echo $lang['NUM_RUNNING_JOBS'];?></th>
                  </tr>
                  </thead>
                  <tbody>
                    <?PHP 
                      if (array_key_exists('TABLE',$usershares)) {
                        foreach ($usershares['TABLE'] as $row) {
                          echo '<tr><td>'.$row['USER_GROUP'].'</td><td>'.$row['SHARES'].'</td><td>'.$row['PRIORITY'].'</td><td>'.$row['NUM_PENDING_JOBS'].'</td><td>'.$row['NUM_RUNNING_JOBS'].'</td></tr>';
                        }
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
  <?PHP include('js.html');?>

    <script>
    function qactivate() {
       var queue;
       queue="<?PHP echo $queue; ?>";
       if (confirm("<?PHP echo $lang['ACTIVATE_QUEUE'];?> "+queue+"?")==true) {
	   window.location.assign("qaction.php?action=qact&queue="+queue);
       }
    };

    function qinactivate() {
       var queue;
       queue="<?PHP echo $queue; ?>";
       if (confirm("<?PHP echo $lang['INACTIVATE_QUEUE'];?> "+queue+"?")==true) {
           window.location.assign("qaction.php?action=qinact&queue="+queue);
       }
    };
    function qopen() {
       var queue;
       queue="<?PHP echo $queue; ?>";
       if (confirm("<?PHP echo $lang['OPEN_QUEUE'];?> "+queue+"?")==true) {
           window.location.assign("qaction.php?action=qopen&queue="+queue);
       }
    };
    function qclose() {
       var queue;
       queue="<?PHP echo $queue; ?>";
       if (confirm("<?PHP echo $lang['CLOSE_QUEUE'];?> "+queue+"?")==true) {
           window.location.assign("qaction.php?action=qclose&queue="+queue);
       }
    };

    </script>
</body>
</html>

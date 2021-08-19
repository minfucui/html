<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];

include 'php/extlink.php';

$olv=$_SESSION['version'];
$jobid=$_GET['jobid'];
if (isset($_GET['idx']))
    $idx=$_GET['idx'];
else
    $idx='0';
if ($jobid=='')
   header("Location: jobs.php");

$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ./env.sh;'.$setenvdir.'cmd/runas '.$uname;
$cmdExt = $_SESSION['ext'];
exec($cmdPrefix.' cmd/jinfo'.$olv.' -j '.$jobid.'['.$idx.']',
      $res,$exit_code);

if ($exit_code != 0)
    header("Location: jobs.php");

if ($res[0] == '')
    header("Location: jobs.php");
$jinfo_data = json_decode($res[0], true);
$job_status = $jinfo_data['JOB_STATUS'];
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
        <?PHP echo $lang['JOB'];?>: <?PHP echo $jinfo_data['JOB_NAME'].' ID: '.$jinfo_data['JOB_ID'];?>
      </h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <div class="col-md-10">
                <button type="button" class="btn btn-warning" id="suspend_bt"
                <?PHP
                  if ((strstr($job_status,'Pend')==FALSE) &&
                    (strstr($job_status,'Runn')==FALSE))
                    echo 'disabled';
                ?>
                onclick="suspend()"><?PHP echo $lang['SUSPEND'];?></button>
                <button type="button" class="btn btn-info" id="resume_bt"
                <?PHP
                  if (strstr($job_status,"Suspend")==FALSE)
                    echo 'disabled';
                ?>
                onclick="resume()"><?PHP echo $lang['RESUME'];?></button>
                <button type="button" class="btn btn-success" id="rerun_bt"
                <?PHP
                  if ((strstr($job_status,"Suspend")==FALSE) &&
                    (strstr($job_status,"Runn")==FALSE))
                    echo 'disabled';
                ?>
                onclick="rerun()"><?PHP echo $lang['RERUN'];?></button>
                <button type="button" class="btn btn-danger" id="kill_bt"
                <?PHP
                  if ((strstr($job_status,"Done")!=FALSE) ||
                    (strstr($job_status,"Exit")!=FALSE))
                    echo 'disabled';
                ?>
                onclick="kill()"><?PHP echo $lang['KILL'];?></button>
              </div>
              <div class="col-md-2">
                <?PHP extLink($jinfo_data, $uname, $pword, $lang); ?>
                <a href="jobs.php"><button type="button" class="btn btn-default">
                <?PHP echo $lang['JOB_LIST'];?></button></a>
              </div>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-body">
              <div id="status_bar" class="alert label-<?php echo $jinfo_data['JOB_STATUS_ALERT'];?>">
                <?PHP echo $lang['JOB_STATUS'];?>: <b id="jobstatus"><?PHP echo $lang[keyify($job_status)];?></b>
              </div>
            </div>
             <?PHP
            if ($uname == $jinfo_data['USERNAME']) {
              echo '<div class="box box-solid">';
              echo '  <div class="box-header with-border">';
              echo '    <h3 class="box-title">'.$lang['JOB_OUTPUT'].'</h3>';
              echo '  </div>';
              echo '  <div class="box-body">';
              echo '  <pre>';
              if ($idx !='0')
                $jobidcomplex=$jobid.'['.$idx.']';
              else
                $jobidcomplex=$jobid;
              exec($cmdPrefix.' cview'.$cmdExt.' '.$jobidcomplex, $cpeekout, $errno);
              $nlen=sizeof($cpeekout);
              $h= 17*$nlen+20;
              if ($h > 550) $h=550;
              if ($h < 250) $h=250;
              printf('<div style="height:%dpx; overflow:auto;" id="joboutput">',$h);
              for ($i=0; $i<sizeof($cpeekout); $i++)
                echo $cpeekout[$i]."\r";
              echo '</div></pre>';
              echo '  </div>';
              echo '</div>';
            }
          ?>
          <a id="resusage"></a>
          <?PHP
            if (strstr($job_status,"Pending")!=TRUE && $uname==$jinfo_data['USERNAME']
                && $jinfo_data['JOB_NAME'] != 'cubevnc'
                && $jinfo_data['JOB_NAME'] != 'dcv') {
              echo '<div class="box box-solid">';
              echo '<div class="box-header with-border">';
              echo '<h3 class="box-title">'.$lang['JOB_FILE'].'</h3>';
              echo '</div>';
              echo '<div class="box-body">';
              echo '<div id="elfinder"></div>';
              echo '</div></div>';
            }
          ?>
          <div class="box box-solid">  
            <div class="col-md-11">
              <div class="col-md-5">
                <table class="tab">
                    <tr><td class="celll"><?PHP echo $lang['JOB_ID'];?></td><td><b id="jobid">
                        <?PHP echo $jinfo_data['JOB_ID'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['JOB_NAME_TITLE'];?></td><td><b id="jobname">
                        <?PHP echo $jinfo_data['JOB_NAME'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['PENDING_REASONS'];?></td><td><b id="pending_reasons">
                        <?PHP echo $jinfo_data['PENDING_REASONS'];?></b></td></tr>
                </table>
              </div>
              <div class="col-md-6">
                  <table class="tab">
                    <tr><td class="cellr"><?PHP echo $lang['SUBMITTED'];?></td><td><b id="submitted">
                        <?PHP echo $jinfo_data['SUBMITTED'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['STARTED'];?></td><td><b id="started">
                        <?PHP echo $jinfo_data['STARTED'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['SUSPENSION_REASONS'];?></td><td><b id="suspension_reasons">
                        <?PHP echo $jinfo_data['SUSPENSION_REASONS'];?></b></td></tr>
                  </table>
              </div>
              </div><div class="col-md-11">
              <div class="col-md-5">
                  <table class="tab">
                    <tr><td><b><?PHP echo $lang['SUBMISSION_PARAMETERS'];?></b></td><td></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['JOB_COMMAND'];?></td><td><b id="job_command">
                        <?PHP echo jobCmd($jinfo_data); ?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['PROJECT'];?></td><td><b id="project">
                        <?PHP echo $jinfo_data['PROJECT'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['JOB_QUEUE'];?></td><td><b id="job_queue">
                        <?PHP echo $jinfo_data['JOB_QUEUE'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['SUBMITTED_BY_THE_USER'];?></td><td><b id="submitted_by_the_user">
                        <?PHP echo $jinfo_data['SUBMITTED_BY_THE_USER'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['CURRENT_WORKING_DIR'];?></td><td><b id="current_working_dir">
                        <?PHP echo $jinfo_data['CURRENT_WORKING_DIR'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['RESOURCE_REQUIREMENT'];?></td><td><b id="resource_requirement">
                        <?PHP echo $jinfo_data['RESOURCE_REQUIREMENT'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['REQUIRED_PROCESSORS'];?></td><td><b id="required_processors">
                        <?PHP echo $jinfo_data['REQUIRED_PROCESSORS'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['INPUT_FILES'];?></td><td><b id="input_files">
                        <?PHP echo $jinfo_data['INPUT_FILES'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['OUTPUT_FILES'];?></td><td><b id="output_files">
                        <?PHP echo $jinfo_data['OUTPUT_FILES'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['JOB_DESCRIPTION'];?></td><td><b id="job_description">
                        <?PHP echo $jinfo_data['JOB_DESCRIPTION'];?></b></td></tr>
                  </table>
              </div>
              <div class="col-md-6">
                  <table class="tab">
                    <tr><td><b><?PHP echo $lang['STATUS'];?></b></td><td></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['REQUESTED_HOSTS'];?></td><td><b id="required_hosts">
                        <?PHP echo $jinfo_data['REQUESTED_HOSTS'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['JOB_EXECUTION_HOSTS'];?></td><td><b id="job_execution_hosts">
                        <?PHP echo $jinfo_data['JOB_EXECUTION_HOSTS'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['RESOURCE_USAGE'];?></td><td><b id="resource_usage">
                        <?PHP echo $jinfo_data['RESOURCE_USAGE'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['PROCESS_GROUP_ID'];?></td><td><b id="process_group_id">
                        <?PHP echo $jinfo_data['PROCESS_GROUP_ID'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['PROCESS_ID'];?></td><td><b id="process_id">
                        <?PHP echo $jinfo_data['PROCESS_ID'];?></b></td></tr>
                    <tr><td class="celll"><?PHP echo $lang['NTHREADS'];?></td><td><b id="nthreads">
                        <?PHP echo $jinfo_data['NTHREADS'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['ENDED'];?></td><td><b id="ended">
                        <?PHP echo $jinfo_data['ENDED'];?></b></td></tr>
                    <tr><td class="cellr"><?PHP echo $lang['EXIT_STATUS'];?></td><td><b id="exit_status">
                        <?PHP echo $jinfo_data['EXIT_STATUS'];?></b></td></tr>
                  </table>
              </div>
              </div>
              <div class="col-md-11">
                <div class="col-md-6">
                  <table class="tab" id="tasks">
                  <?PHP
                    if (isset($jinfo_data['taskInfo'])) {
                      echo '<tr><td><b>'.$lang['TASKS'].'</b></td><td></td></tr>'."\n";
                      foreach ($jinfo_data['taskInfo'] as $comp) {
                        echo '<tr><td>'.$lang['COMPONENT'].'</td>';
                        echo     '<td><b>'.$comp['Component'].'</b></td></tr>'."\n";
                        foreach ($comp['Tasks'] as $task) {
                          echo '<tr><td class="celll"><b>'.$lang['TASK'].'</b></td><td><b>'.$task['TaskId'].'</b></td></tr>'."\n";
                          echo '<tr><td class="celll">'.$lang['STATUS'].'</td>';
                          echo     '<td>'.$task['Status'].'</td></tr>'."\n";
                          echo '<tr><td class="celll">'.$lang['CPU_TIME'].'</td>';
                          echo     '<td>'.$task['CPUTime'].'</td></tr>'."\n";
                          echo '<tr><td class="celll">'.$lang['MEM_USAGE'].'</td>';
                          echo     '<td>'.$task['MemGB'].'</td></tr>'."\n";
                          echo '<tr><td class="celll">'.$lang['NTHREADS'].'</td>';
                          echo     '<td>'.$task['nThreads'].'</td></tr>'."\n";
                        }
                      }
                    }
                  ?>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>    <!-- /#page-wrapper -->
  </div>
</div>
    <!-- /#wrapper -->
  <?PHP include('js.html');?>
  <script type="text/javascript" src="plugins/fastclick/fastclick.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.min.js"></script>
  <script src="plugins/flot/jquery.flot.resize.min.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.time.js"></script>
  <script src="plugins/jQuery/hashtable.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.numberformatter.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.symbol.js"></script>
  <script type="text/javascript" src="plugins/flot/jquery.flot.axislabels.js"></script>

    <!-- elFinder JS (REQUIRED) -->
    <?PHP
    $olpath = $jinfo_data['ELFINDER_DIR'];
    include 'elFinderInit.php';
    ?>

    <script>
    function suspend() {
       var jobid;
       jobid="<?PHP if ($idx=='0') echo $jobid; else echo $jobid.'['.$idx.']'; ?>";
       if (confirm("<?PHP echo $lang['SUSPEND_JOB'];?> "+jobid+"?")==true) {
	   window.location.assign("jobaction.php?action=suspend&jobid=<?PHP echo $jobid; ?>&idx=<?PHP echo $idx; ?>");
       }
    };

    function resume() {
        var jobid;
        jobid="<?PHP if ($idx=='0') echo $jobid; else echo $jobid.'['.$idx.']'; ?>";
	if (confirm("<?PHP echo $lang['RESUME_JOB'];?> "+jobid+"?")==true) {
           window.location.assign("jobaction.php?action=resume&jobid=<?PHP echo $jobid; ?>&idx=<?PHP echo $idx; ?>");
       }
    };

    function rerun() {
        var jobid;
        jobid="<?PHP if ($idx=='0') echo $jobid; else echo $jobid.'['.$idx.']'; ?>";
        if (confirm("<?PHP echo $lang['RERUN_JOB'];?> "+jobid+"?")==true) {
           window.location.assign("jobaction.php?action=rerun&jobid=<?PHP echo $jobid; ?>&idx=<?PHP echo $idx; ?>");
       }
    };
    function kill() {
        var jobid;
        jobid="<?PHP if ($idx=='0') echo $jobid; else echo $jobid.'['.$idx.']'; ?>";
        if (confirm("<?PHP echo $lang['KILL_JOB'];?> "+jobid+"?")==true) {
           window.location.assign("jobaction.php?action=kill&jobid=<?PHP echo $jobid; ?>&idx=<?PHP echo $idx; ?>");
       }
    };
  $(function () {
    var es = <?PHP if ($_SESSION['es']) echo 1; else echo 0; ?>;
    var updateInterval = 2000;
    var dynamic = 1;
    var activeajax;
    var rusagehtmlplaced = 0;

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
            show: true
        },
        grid: {
            hoverable: true,
            borderWidth:1,
            axisMargin:50
        }
    };
    var rusagehtml = '<div class="box box-solid">' +
                     '  <div class="box-header with-border">' +
                     '    <h3 class="box-title"><?php echo $lang['CPU_TIME'];?></h3>' +
                     '  </div>' +
                     '  <div class="box-body">' +
                     '    <div id="cpuusage" style="height: 200px;"></div>' +
                     '  </div>' +
                     '  <div class="box-header with-border">' +
                     '    <h3 class="box-title"><?php echo $lang['MEM_USAGE'];?></h3>' +
                     '  </div>' +
                     '  <div class="box-body">' +
                     '    <div id="memusage" style="height: 200px;"></div>' +
                     '  </div>' +
                     '  <div class="box-header with-border">' +
                     '    <h3 class="box-title"><?php echo $lang['SWAP_USAGE'];?></h3>' +
                     '  </div>' +
                     '  <div class="box-body">' +
                     '    <div id="swapusage" style="height: 200px;"></div>' +
                     '  </div>' +
                     '</div>';

    function jobi18nStatus(status) {
        switch (status) {
            case "Pending":
                return "<?php echo $lang['PENDING'];?>";
            case "Suspended while pending":
                return "<?php echo $lang['SUSPENDED_WHILE_PENDING'];?>";
            case "Running":
                return "<?php echo $lang['RUNNING'];?>";
            case "Stopped by the scheduler":
                return "<?php echo $lang['SUSPENDED_BY_SKYFORM'];?>";
            case "Suspended while running":
                return "<?php echo $lang['SUSPENDED_WHILE_RUNNING'];?>";
            case "Exited":
                return "<?php echo $lang['EXITED'];?>";
            case "Done":
                return "<?php echo $lang['DONE'];?>";
            default:
                return "<?php echo $lang['UNKNOWN'];?>";
       }
    }

    function displayRusage(jobId, jobSubmitted) {
        if (rusagehtmlplaced == 0) {
            $("#resusage").html(rusagehtml);
            rusagehtmlplaced = 1;
        }
        $.get("php/esjob.php?jobid=" + jobId + "&submitted="
              + encodeURI(jobSubmitted), function(data) {
            var plotdata = JSON.parse(data);
            var cpu = [{data: plotdata['cpu']}];
            var mem = [{data: plotdata['mem']}];
            var swap = [{data: plotdata['swap']}];
            $.plot("#cpuusage", cpu, options);
            $.plot("#memusage", mem, options);
            $.plot("#swapusage", swap, options);
        });
    }
    function firstDisplay (jobId, jobSubmitted, jobStarted, jobEnded) {
        if (es == 1 && jobStarted != '-') {
            displayRusage(jobId, jobSubmitted);
        }
        if (jobEnded != '-')
            dynamic = 0;
    }

    function dynamicDataDisplay (jData) {
        var jobdata = JSON.parse(jData);
        var jobstatus = jobdata['JOB_STATUS'];
        var jobname = jobdata['JOB_NAME'];
        if (jobstatus != 'Pending' && jobstatus != 'Running')
            $("#suspend_bt").attr("disabled", true);
        else
            $("#suspend_bt").attr("disabled", false);
        if (!jobstatus.includes("Suspend"))
            $("#resume_bt").attr("disabled", true);
        else
            $("#resume_bt").attr("disabled", false);
        if (!jobstatus.includes("Suspend") && jobstatus != 'Running')
            $("#rerun_bt").attr("disabled", true);
        else
            $("#rerun_bt").attr("disabled", false);
        if (jobstatus == 'Done' || jobstatus == 'Exited')
            $("#kill_bt").attr("disabled", true);
        else
            $("#kill_bt").attr("disabled", false);
        if (jobname == 'cubevnc' || jobname == 'dcv' ||
            jobname == 'jupyter' || jobname.includes('GUI')) {
            if (jobstatus == 'Running')
                $.get("php/cread.php?jobid=" + jobdata['JOB_ID'], function(data) {
                    if (data.includes("/") == true) {
                        $("#extlink").attr("class", "btn btn-info");
                        $("#extlink").attr("disabled", false);
                        $("#extlink").attr("href", data);
                        if (data.substring(0,2) == '//' ||
                            data.substring(0,2) == '/h')
                            window.open((data.substring(1,5) != 'http' ? 
                                   window.location.origin : '') +
                                   data.substring(1,), "_blank");
                    }
                });
            if (jobdata['ENDED'] != '-') {
                $("#extlink").attr("class", "btn btn-default");
                $("#extlink").attr("disabled", true);
            }
        }
        $("#status_bar").attr("class", 'alert label-' + jobdata['JOB_STATUS_ALERT']);
        $("#jobstatus").html(jobi18nStatus(jobstatus));
        $("#started").html(jobdata['STARTED']);
        $("#pending_reasons").html(jobdata['PENDING_REASONS']);
        $("#suspension_reasons").html(jobdata['SUSPENSION_REASONS']);
        $("#job_execution_hosts").html(jobdata['JOB_EXECUTION_HOSTS']);
        $("#resource_usage").html(jobdata['RESOURCE_USAGE']);
        $("#process_group_id").html(jobdata['PROCESS_GROUP_ID']);
        $("#process_id").html(jobdata['PROCESS_ID']);
        $("#nthreads").html(jobdata['NTHREADS']);
        $("#ended").html(jobdata['ENDED']);
        $("#exit_status").html(jobdata['EXIT_STATUS']);
        if (jobdata['taskInfo'] != null) {
            htmlstr = "<tr><td><b><?php echo $lang['TASKS'];?></b></td></tr>";
            jobdata['taskInfo'].forEach(function(comp) {
                htmlstr += "<tr><td><?pho echo $lang['COMPONENT'];?></td>";
                htmlstr += "<td><b>" + comp['Component'] + "</b></td></tr>";
                comp['Tasks'].forEach(function(task) {
                    htmlstr += "<tr><td class=\"celll\"><b><?php echo $lang['TASK'];?></b></td><td><b>"
                            + task['TaskId'] + "</b></td></tr>";
                    htmlstr += "<tr><td class=\"celll\"><?php echo $lang['STATUS'];?></td>";
                    htmlstr += "<td>" +  task['Status'] + "</td></tr>";
                    htmlstr += "<tr><td class=\"celll\"><?php echo $lang['CPU_TIME'];?></td>";
                    htmlstr += "<td>" +  task['CPUTime'] + "</td></tr>";
                    htmlstr += "<tr><td class=\"celll\"><?php echo $lang['MEM_USAGE'];?></td>";
                    htmlstr += "<td>" +  task['MemGB'] + "</td></tr>";
                    htmlstr += "<tr><td class=\"celll\"><?php echo $lang['NTHREADS'];?></td>";
                    htmlstr += "<td>" +  task['nThreads'] + "</td></tr>";
                });
            });
            $("#tasks").html(htmlstr);
        }
        if (jobstatus == 'Running' && jobdata['USERNAME'] == "<?php echo $uname;?>")
            $.get("php/cview.php?jobid=<?php echo $jobid;?>&idx=<?php echo $idx;?>", function(data) {
                  $("#joboutput").html(data);
                  $("#joboutput").scrollTop($("#joboutput")[0].scrollHeight - $("#joboutput").height());
            });

        if (es == 1 && jobdata['STARTED'] != '-') {
            displayRusage(jobdata['JOB_ID'], jobdata['SUBMITTED']);
        }
        if (jobdata['ENDED'] != '-')
            dynamic = 0;
    }
 
    firstDisplay (<?PHP echo '"'.$jinfo_data['JOB_ID'].
                             '","'.$jinfo_data['SUBMITTED'].
                             '","'.$jinfo_data['STARTED'].
                             '","'.$jinfo_data['ENDED'].'"';?>
                  );

    if (dynamic == 1) {
        $.ajaxSetup({ cache: false });
        setInterval(function() {
            if (dynamic == 0)
                activeajax.abort();
            activeajax = $.ajax({
                url: "cmd/jobdata.php?jobid=<?php echo $jobid;?>&idx=<?php echo $idx;?>",
                type: "GET",
                datatType: "json",
                success: dynamicDataDisplay
            });
        },updateInterval);
    }
  });
    </script>
</body>

</html>

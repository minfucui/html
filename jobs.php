<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$pword=$_SESSION['password'];
$olv=$_SESSION['version'];

include 'php/extlink.php';

$queue=isset($_GET['queue'])?$_GET['queue']:'';
$host=isset($_GET['host'])?$_GET['host']:'';
$user=isset($_GET['user'])?$_GET['user']:'';
$jobname=isset($_GET['jobname'])?$_GET['jobname']:'';
$jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
if ($user != '')
    $users=' -u '.$user;
else
    $users=' -u all';
if ($queue !='')
    $queues=' -q '.$queue;
else
    $queues='';
if ($host !='')
    $hosts=' -m '.$host;
else
    $hosts='';
if ($jobname != '')
    $jobnames=' -J '.$jobname;
else
    $jobnames='';
$params = urlencode($users.$queues.$hosts.$jobnames);
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
          echo $lang['JOBS'];
          if ($user!='')
            echo $lang['BY_THE_USER'].' '.$user;
          if ($queue!='')
            echo $lang['IN_THE_QUEUE'].' '.$queue;
          if ($host!='')
            echo $lang['ON_THE_HOST'].' '.$host;
          if ($jobid!='')
            echo ' '.$jobid;
        ?>
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
                  <th><?PHP echo $lang['JOB_ID'];?></th>
                  <th><?PHP echo $lang['USER'];?></th>
                  <th><?PHP echo $lang['FROM_HOST'];?></th>
                  <th><?PHP echo $lang['JOB_NAME_TITLE'];?></th>
                  <th><?PHP echo $lang['QUEUE'];?></th>
                  <th><?PHP echo $lang['STATUS'];?></th>
                  <th><?PHP echo $lang['PROJECT'];?></th>
                  <th><?PHP echo $lang['SUBMIT_TIME'];?></th>
                  <th><?PHP echo $lang['START_TIME'];?></th>
                  <th><?PHP echo $lang['FINISH_TIME'];?></th>
                </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
    <!-- /#wrapper -->
    <?PHP include('js.html');?>
    <!-- Custom Theme JavaScript -->

    <script>
    $(function() {
        var updateInverval = 30000;
        var ajaxhandle;

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

        function filljobdata(jdata) {
            var jobdata = JSON.parse(jdata);
            $("#jobDataTables").DataTable({
                "data": jobdata.data,
                "columns":
                    [{"render": function(data, type, row, meta) {
                        return '<a href="'+row.URL+'">'+row.JOB_ID +'</a>';
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.USER;
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.HOST;
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.JOB_NAME;
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.QUEUE;
                     }},
                     {"render": function(data, type, row, meta) {
                        
                        return '<div class="label-'+row.STATUSCLASS+'">'+jobi18nStatus(row.STATUS) +
                        (row.PEND_REASON ? ': '+row.PEND_REASON : '') +
                        (row.EXEC_HOST ? '<?php echo $lang['SPACE_ON'];?> '+row.EXEC_HOST : '') +
                        (row.EXIT_STRING ? ': '+row.EXIT_STRING : '') + '</div>';
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.PROJECT;
                     }},
                     {"render": function(data, type, row, meta) {
                        return row.SUBMIT_TIME;
                     }},
                     {"render": function(data, type, row, meta) {
                        return (row.START_TIME ? row.START_TIME : '');
                     }},
                     {"render": function(data, type, row, meta) {
                        return (row.FINISH_TIME ? row.FINISH_TIME : '');
                     }}],
                 "paging": true,
                 "lengthChange": true,
                 "searching": true,
                 "ordering": true,
                 "order": [],
                 "info": true,
                 "lengthMenu": [50, 100, 500],
                 "autoWidth": false,
                 "destroy": true,
                 "stateSave": true,
                 "language":
                <?PHP
                    $languages = getAnglicizedLanguages();
                    echo file_get_contents('plugins/datatables-plugins/i18n/'.$languages[$language].'.lang');
                ?>

            });
        }
        $.ajaxSetup({cache: false});
        $.ajax({
            url: "cmd/jobsdata.php?params=<?php echo $params;?>",
            type: "GET",
            dataType: "text",
            success: filljobdata
        });
        
        setInterval(function() {
            $.ajax({
                url: "cmd/jobsdata.php?params=<?php echo $params;?>",
                type: "GET",
                dataType: "text",
                success: filljobdata
            });
        }, updateInverval);
    });
    </script>

</body>

</html>

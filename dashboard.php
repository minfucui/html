<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
// shell_exec("rm /tmp/ol".$uname."* > /dev/null 2>&1");

$olv='';
$_SESSION['version']='';
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';

$result=shell_exec("source ./env.sh;".$setenvdir."cmd/clusterinfo".$olv);
if ($result === NULL) {
        header ("Location: cluster_down.html");
}

if (strpos ($result, 'CBLS is down;') != false) {
	header ("Location: cluster_down.html");
}
$ress =json_decode($result, true);
$clustername=$ress['CLUSTER_NAME'];
$mastername=$ress['MASTER_HOST'];
$adminname=$ress['ADMINS'][0];
$_SESSION['admin']=$adminname;
exec("source ./env.sh; if [ -f \$CB_ENVDIR/olmon.conf ]; then grep kibana \$CB_ENVDIR/olmon.conf | cut -f2 -d'=';fi", $r, $err);
if (isset($r[0])) {
   $_SESSION['kibana'] = $r[0];
   unset($r);
   exec("source ./env.sh; grep grafana \$CB_ENVDIR/olmon.conf 2> /dev/null | cut -f2 -d'='", $r, $err);
   if (isset($r[0]))
      $_SESSION['grafana'] = $r[0];
}
if (isset($r[0]))
  $_SESSION['es'] = TRUE;
else
  $_SESSION['es'] = FALSE;
if ($uname != $adminname)
  header("Location: applications.php");
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
      <h1><?PHP echo $lang['DASHBOARD'];?></h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-3">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['HOST_STATUS'];?></h4>
            </div>
            <div class="box-body">
              <div id="host-pie-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['CPU_STATUS'];?></h4>
            </div>
            <div class="box-body">
              <div id="cpu-pie-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with border">
               <h4 class="box-title"><?PHP echo $lang['NUMBER_OF_JOBS_BY_USER'];?></h4>
            </div>
            <div class="box-body">
               <div id="user-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <div class="box box-solid">
            <div class="box-header with border">
               <h4 class="box-title"><?PHP echo $lang['LS_STATUS'];?></h4>
            </div>
            <div class="box-body">
              <div id="gpu-pie-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['JOB_STATUS'];?></h4>
            </div>
            <div class="box-body">
              <div id="job-pie-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-md-6">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['NUMBER_OF_JOBS_BY_QUEUE'];?></h4>
            </div>
            <div class="box-body">
                <div id="legendPlaceholder"></div>
                <div id="queue-stacked-chart" style="height: 200px;"></div>
            </div>
          </div>
        </div>
      </div>
<?PHP
# if ($_SESSION['es']) {
echo '<div class="row">';
echo '  <div class="col-md-3 col-sm-6 col-xs-12">';
echo '    <div class="info-box">';
echo '      <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>';
echo '      <div class="info-box-content">';
echo '        <span class="info-box-text">'.$lang['NUM_RUNNING_JOBS'].'</span>';
echo '        <span class="info-box-number" id="runjobs"></span>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '  <div class="col-md-3 col-sm-6 col-xs-12">';
echo '    <div class="info-box">';
echo '      <span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-1"></i></span>';
echo '      <div class="info-box-content">';
echo '        <span class="info-box-text">'.$lang['NUM_PENDING_JOBS'].'</span>';
echo '        <span class="info-box-number" id="pendjobs"></span>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '  <div class="col-md-3 col-sm-6 col-xs-12">';
echo '    <div class="info-box">';
echo '      <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>';
echo '      <div class="info-box-content">';
echo '        <span class="info-box-text">'.$lang['NUM_RUNNING_USERS'].'</span>';
echo '        <span class="info-box-number" id="runusers"></span>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '  <div class="col-md-3 col-sm-6 col-xs-12">';
echo '    <div class="info-box">';
echo '      <span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-1"></i></span>';
echo '      <div class="info-box-content">';
echo '        <span class="info-box-text">'.$lang['NUM_PENDING_USERS'].'</span>';
echo '        <span class="info-box-number" id="pendusers"></span>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '</div>';
echo '<div class="row">';
echo '  <div class="col-md-6">';
echo '    <div class="box box-solid">';
echo '      <div class="box-header with border">';
echo '        <h4 class="box-title">TOP 5 '.$lang['PENDING_JOBS'].'</h4>';
echo '      </div>';
echo '      <div class="box-body">';
echo '        <table class="table table-condensed table-striped" id="pendlist">';
echo '          <thead><tr>';
echo '            <th>'.$lang['JOB_ID'].'</th>';
echo '            <th>'.$lang['USER'].'</th>';
echo '            <th>'.$lang['JOB_NAME'].'</th>';
echo '            <th>'.$lang['PEND_HOUR'].'</th>';
echo '          </tr></thead>';
echo '          <tbody></tbody>';
echo '        </table>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '  <div class="col-md-6">';
echo '    <div class="box box-solid">';
echo '      <div class="box-header with border">';
echo '        <h4 class="box-title">TOP 5 '.$lang['RUNNING_JOBS'].'</h4>';
echo '      </div>';
echo '      <div class="box-body">';
echo '        <table class="table table-condensed table-striped" id="runlist">';
echo '          <thead><tr>';
echo '            <th>'.$lang['JOB_ID'].'</th>';
echo '            <th>'.$lang['USER'].'</th>';
echo '            <th>'.$lang['JOB_NAME'].'</th>';
echo '            <th>'.$lang['RUN_HOUR'].'</th>';
echo '          </tr></thead>';
echo '          <tbody></tbody>';
echo '        </table>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '</div>';
# }
?>
      <div class="row">
        <div class="col-lg-12">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['HOSTS'];?></h4>
            </div>
            <div class="box-body">
              <div id="hostbars"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="box box-solid">
            <div class="box-header with border">
              <h4 class="box-title"><?PHP echo $lang['RESOURCE'];?></h4>
            </div>
            <div class="box-body">
              <div id="res-charts-area"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
    <!-- /#wrapper -->
    <?PHP include('js.html');?>
    <!-- Sparkline -->
    <script src="plugins/sparkline/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="plugins/knob/jquery.knob.js"></script>
    <!-- daterangepicker -->
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- datepicker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>


    <!-- Flot Charts JavaScript -->
    <script src="plugins/flot/excanvas.min.js"></script>
    <script src="plugins/flot/jquery.flot.js"></script>
    <script src="plugins/flot/jquery.flot.stack.js"></script>
    <script src="plugins/flot/jquery.flot.pie.js"></script>
    <script src="plugins/flot/jquery.flot.resize.js"></script>
    <script src="plugins/flot/jquery.flot.time.js"></script>
    <script src="plugins/flot/jquery.flot.axislabels.js"></script>
    <script src="plugins/flot/jquery.flot.symbol.js"></script>
    <script src="plugins/jQuery/hashtable.js"></script>
    <script src="plugins/jQuery/jquery.numberformatter-1.2.4.min.js"></script>
    <script src="plugins/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
    <script src="plugins/flot/jquery.flot.tickrotor.js"></script>

<script type="text/javascript">

$(function() {
    var pieoptions={
        series: {
            pie: {
                innerRadius: 0.3,
                show: true,
                /* stroke: {
                   color: "#384246",
                   width: 2
                },*/
                label: {
                   show: true,
                   radius: 1,
                   formatter: function(label, series) {
                       var percent=Math.round(series.percent);
                       var number = series.data[0][1];
                       return ('<center><font color="black">' + 
                           number +  
                           '</font></center>');
                   }
                }
            }
        },
        grid: {
            hoverable: true
        },
        tooltip: {
            show: true,
            content: "%s:%p.2%",
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        },
        colors: ["#00a65a",    /* Ok */
                 "#0cc0ef",    /* Full */
                 "#f39c12",    /* Busy */
                 "#c0c0c0",    /* Closed */
                 "#dd4b39"     /* Problem */
                ],
        legend: {
            show: true,
            position: 'se',
            backgroundOpacity: 0 
        }
    };

    var moptions= {
        series: {
            stack: true,
            lines: {
                show: true,
                fill: true,
                lineWidth: 1.2,
                fillColor: {colors: [{opacity: 1}, {opacity: 0.5}]}
            }
        },

        colors: ["#0cc0ef", /* used */ 
                 "#00a65a" /* available */
                ],
        xaxis: {
            mode: "time",
            tickSize: [120, "second"],
            tickFormatter: function (v,axis) {
                var date =new Date(v);
                var hours = date.getHours() < 10 ?
                            "0" + date.getHours() :
                            date.getHours();
                var minutes = date.getMinutes() < 10 ?
                            "0" + date.getMinutes() :
                            date.getMinutes();
                return hours + ":" + minutes;
            },
            axisLabel: "<?PHP echo $lang['TIME'];?>",
            axisLabelUseCanvas: false,
            axisLabelPadding: 10
        },
        yaxis: {
            min: 0
        },
        legend: {
            position: "nw",
            show: true,
            backgroundOpacity: 0
        }
    };
    var qoptions = {
        series: {
            stack: true,
            bars: {
                 show: true,
                 barWidth: 0.5,
                 horizontal: true,
                 align: "center",
                 fillColor: {colors: [{opacity: 0.8}, {opacity: 0.8},
                                      {opacity: 0.8}]}
            }
        },
        colors: ["#f39c12",    /* Pending */
                 "#0cc0ef",    /* Running */
                 "#c0c0c0"     /* Suspended */
                ],
        grid: {
            margin: 5,
            hoverable: true
        },
        yaxis: {
        }, 
        xaxis: {
            min:0,
            autoscaleMargin: .02
        },
        tooltip: {
            show: true,
            content: "%s:%y %x",
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: false
        },
        legend: {
            position: 'se',
            backgroundOpacity: 0,
            show: true
        }

    };

    var pieoptions2 = pieoptions.constructor();
    for (var attr in pieoptions)
        if (pieoptions.hasOwnProperty(attr))
            pieoptions2[attr] = pieoptions[attr];
    pieoptions2.colors = qoptions.colors;

    var used = [], avail = [];
    var updateInterval = 15000;
    var totalPoints = 30 * 60 / (updateInterval / 1000);
    var now = new Date().getTime();

    function initData() {
        now -= updateInterval;
        now -= (updateInterval + 1) * totalPoints;
        for (var i = 0; i < totalPoints; i++) {
            var temp = [now += updateInterval, 0];
            used.push(temp);
            avail.push(temp);
        }
        $('.flot-tick-label').css('color','white');
    }

    function dataReceived(allData) {
        var dataset = JSON.parse(allData);
        var hosts = dataset.hosts;
        var queues = dataset.queues;
        var resources = dataset.resources;
        var users = dataset.users;

        var gpuAvail = 0, gpuUsed = 0;
        var usedSlots = 0, totalSlots = 0;
        var numBusy = 0, numClosed = 0, numProblem = 0;
        var numOk = 0, numFull = 0;
        var qNames = [], numPend = [], numRun = [];
        var numSusp = [];
        var uNames = [], uNumPend = [], uNumRun = [], uNumSusp = [];
        var hostsHTML = '';
        var busySlots = 0, problemSlots = 0, closedSlots = 0;
        var runSlots = 0, pendSlots = 0, suspSlots = 0;

        hosts.forEach(function(host) {
            var percent = 0;
            var barcolor = 'aqua';
            if (host.MAXGPU != '-' && host.GPU != '-')
                gpuUsed += (parseInt(host.MAXGPU) - parseInt(host.GPU));
            if (host.GPU != '-')
                gpuAvail += parseInt(host.GPU);
            usedSlots += parseInt(host.NUM_RUNNING_JOBS);
            if (host.MAX_JOB_SLOTS != '-') {
                totalSlots += parseInt(host.MAX_JOB_SLOTS) -
                              parseInt(host.NUM_RUNNING_JOBS);
                percent = host.NUM_RUNNING_JOBS * 100 / host.MAX_JOB_SLOTS;
            }
            tips = "<?php echo $lang['STATUS'];?>: " + host.STATUS + "\n"
                 + "CPU: " + host.UT + "\n"
                 + "MEM: " + (host.MEM == '-' ? '-' : String(parseInt(parseInt(host.MEM)/1024))) + "GB\n"
                 + "NET: " + (host.NETIO == '-' ? '-' : host.NETIO) + "KB/s\n"
                 + "<?php echo $lang['JOBS'];?>: " + host.NUM_RUNNING_JOBS;
            hostsHTML += '<div class="col-md-1" title="'
                         + tips
                         + '"><a href="' + host.HOST_URL 
                         +'"><div class="progress-group"><span class="progress-text">'
                         + host.HOST + '</span>';
            switch (host.STATUS) {
                case 'OK':
                    numOk++;
                    barcolor = 'green';
                    break;
                case 'Unavailable':
                case 'Unreachable':
                case 'CBLS is unreachable':
                    numProblem++;
                    barcolor = 'red';
                    percent = 100;
                    if (host.MAX_JOB_SLOTS != '-')
                        problemSlots += parseInt(host.MAX_JOB_SLOTS);
                    break;
                case 'Closed by Admin':
                    if (host.MAX_JOB_SLOTS != '-')
                        closedSlots += parseInt(host.MAX_JOB_SLOTS);
                    numClosed++;
                    barcolor = 'guan';
                    percent = 100;
                    break;
                case 'Closed by an Exclusive Job':
                case 'Full':
                    numFull++;
                    break;
                case 'Busy':
                    numBusy++;
                    barcolor = 'yellow';
                    percent = 100;
                    if (host.MAX_JOB_SLOTS != '-')
                        busySlots += parseInt(host.MAX_JOB_SLOTS);
                    break;
                default:
                    numClosed++;
                    barcolor = 'guan';
                    percent = 100;
                    break;
            }
            hostsHTML += '<div class="progress sm"><div class="progress-bar progress-bar-'
                         + barcolor + '" style="width: '+ percent + '%"'
                         + '></div></div></div></a></div>';
        });

        queues.sort(function(a,b) {
            if (a.TOTAL_NUM_JOBS < b.TOTAL_NUM_JOBS)
                return -1;
            if (a.TOTAL_NUM_JOBS > b.TOTAL_NUM_JOBS)
                return 1;
            return 0;
        });

        var i = 0;
        queues.forEach(function(queue) {
            pendSlots += parseInt(queue.NUM_PENDING_JOBS);
            runSlots += parseInt(queue.NUM_RUNNING_JOBS);
            suspSlots += parseInt(queue.NUM_SUSP_JOBS);
            if (i >= 10)
               return;
            qNames[i] = [i, queue.QUEUE];
            numPend[i] = [queue.NUM_PENDING_JOBS, i];
            numRun[i] = [queue.NUM_RUNNING_JOBS, i];
            numSusp[i] = [queue.NUM_SUSP_JOBS, i];
            i++;
        });

        users.sort(function(a,b) {
            if (a.NUM_JOBS < b.NUM_JOBS)
                return -1;
            if (a.NUM_JOBS > b.NUM_JOBS)
                return 1;
            return 0;
        });

        i = 0;
        users.forEach(function(user) {
            if (user.ISGROUP == "y")
                return;
            if (i >= 10)
                return;
            uNames[i] = [i, user.USER];
            uNumPend[i] = [user.NUM_PENDING_JOBS, i];
            uNumRun[i] = [user.NUM_RUNNING_JOBS, i];
            uNumSusp[i] = [user.NUM_SUSP_JOBS, i];
            i++;
        });

        var temp;
        used.shift();
        avail.shift();
        now +=updateInterval;
        temp = [now, usedSlots];
        used.push(temp);
        temp = [now, totalSlots];
        avail.push(temp);

        var slotdata = [
            {label: "<?PHP echo $lang['USED'];?>", data: used},
            {label: "<?PHP echo $lang['AVAILABLE'];?>", data: avail},
        ];
        // $.plot($("#slot-chart"), slotdata, moptions);

        var gpudata = [
            {label: "<?PHP echo $lang['AVAILABLE'];?>", data: gpuAvail},
            {label: "<?PHP echo $lang['USED'];?>", data: gpuUsed}
        ];

        $.plot($("#gpu-pie-chart"), gpudata, pieoptions);

        var hostdata = [
            {label: "<?PHP echo $lang['OK_CHART'];?>", data: numOk},
            {label: "<?PHP echo $lang['FULL'];?>", data: numFull},
            {label: "<?PHP echo $lang['BUSY'];?>", data: numBusy},
            {label: "<?PHP echo $lang['CLOSED'];?>", data: numClosed},
            {label: "<?PHP echo $lang['PROBLEM'];?>", data: numProblem}
        ];

        $.plot($("#host-pie-chart"), hostdata, pieoptions);

        var cpudata = [
            {label: "<?PHP echo $lang['AVAILABLE'];?>",
               data: totalSlots - busySlots - closedSlots - problemSlots},
            {label: "<?PHP echo $lang['USED'];?>", data: usedSlots},
            {label: "<?PHP echo $lang['BUSY'];?>", data: busySlots},
            {label: "<?PHP echo $lang['CLOSED'];?>", data: closedSlots},
            {label: "<?PHP echo $lang['PROBLEM'];?>", data: problemSlots}
        ];

        $.plot($("#cpu-pie-chart"), cpudata, pieoptions);

        var queuedata = [
            {label: "<?PHP echo $lang['PENDING'];?>", data: numPend},
            {label: "<?PHP echo $lang['RUNNING'];?>", data: numRun},
            {label: "<?PHP echo $lang['SUSPENDED'];?>", data: numSusp}
            ];
        qoptions.yaxis.ticks = qNames;
        $.plot($("#queue-stacked-chart"), queuedata, qoptions);

        var slotdata = [
            
            {label: "<?PHP echo $lang['PENDING'];?>", data: pendSlots},
            {label: "<?PHP echo $lang['RUNNING'];?>", data: runSlots},
            {label: "<?PHP echo $lang['SUSPENDED'];?>", data: suspSlots}
        ];            

        $.plot($("#job-pie-chart"), slotdata, pieoptions2);

        var userdata = [
            {label: "<?PHP echo $lang['PENDING'];?>", data: uNumPend},
            {label: "<?PHP echo $lang['RUNNING'];?>", data: uNumRun},
            {label: "<?PHP echo $lang['SUSPENDED'];?>", data: uNumSusp}
            ];
        qoptions.yaxis.ticks = uNames;
        $.plot($("#user-chart"), userdata, qoptions);

        $("#hostbars").html(hostsHTML);

        var resourcedata = [];
        var resHTML = '';
        i = 0;
        resources.forEach(function(resource) {
            if (resource.TOTAL != '-' && resource.TYPE == "NUMBER") {
                resourcedata[i] = [
                    {label: "<?PHP echo $lang['AVAILABLE'];?>",
                     data: resource.TOTAL - resource.RESERVED},
                    {label: "<?PHP echo $lang['USED'];?>",
                     data: resource.RESERVED}
                ];
                resHTML += '<div class="col-lg-2">'
                           + resource.RESOURCE + '<div id="res-pie-' + i
                           + '" style="height: 200px;"></div></div>';
                i++;
            }
        });

        $("#res-charts-area").html(resHTML);

        for (i = i - 1; i >= 0; i--) {
            $.plot($('#res-pie-' + i), resourcedata[i], pieoptions);
        }
    }

    function jobDataReceived(dataset) {
        $("#runjobs").html(dataset.numRunJobs.toString());
        $("#pendjobs").html(dataset.numPendJobs.toString());
        $("#runusers").html(dataset.numRunUsers.toString());
        $("#pendusers").html(dataset.numPendUsers.toString());
        $("#pendlist").DataTable({
            "data": dataset.topPendJobs,
            "columns": [{"render": function(data, type, row, meta) {
                           return '<a href="'+row.url+'">'+row.jobId+'</a>';
                        }},
                        {"render": function(data, type, row, meta) {
                           return row.userName;
                        }},
                        {"render": function(data, type, row, meta) {
                           return row.jobName;
                        }},
                        {"render": function(data, type, row, meta) {
                           pendTime = row.pendTime.toFixed(3);
                           return pendTime.toString();
                        }}],
            "searching": false,
            "paging": false,
            "ordering": false,
            "info": false,
            "destroy": true,
            "language":
        <?PHP
            $languages = getAnglicizedLanguages();
            echo file_get_contents('plugins/datatables-plugins/i18n/'.$languages
[$language].'.lang');
        ?>
        });
        $("#runlist").DataTable({
            "data": dataset.topRunJobs,
            "columns": [{"render": function(data, type, row, meta) {
                           return '<a href="'+row.url+'">'+row.jobId+'</a>';
                        }},
                        {"render": function(data, type, row, meta) {
                           return row.userName;
                        }},
                        {"render": function(data, type, row, meta) {
                           return row.jobName;
                        }},
                        {"render": function(data, type, row, meta) {
                           runTime = row.runTime.toFixed(3);
                           return runTime.toString();
                        }}],
            "searching": false,
            "paging": false,
            "ordering": false,
            "info": false,
            "destroy": true,
            "language":
        <?PHP
            $languages = getAnglicizedLanguages();
            echo file_get_contents('plugins/datatables-plugins/i18n/'.$languages
[$language].'.lang');
        ?>
        });
    } 

    initData();

    $.ajaxSetup({ cache: false });

    $.ajax({
        url: "cmd/dbdata.php",
        type: "GET",
        datatType: "json",
        success: dataReceived
    });

    setInterval(function() {
        $.ajax({
            url: "cmd/dbdata.php",
            type: "GET",
            datatType: "json",
            success: dataReceived
        });
      },updateInterval);

    $.ajax({
        url: "php/jobsfromaip.php",
        type: "GET",
        dataType: "json",
        success: jobDataReceived
    });

    setInterval(function() {
        $.ajax({
            url: "php/jobsfromaip.php",
            type: "GET",
            dataType: "json",
            success: jobDataReceived
        });
    },updateInterval * 2);

});
</script>

</body>

</html>

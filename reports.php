<?PHP
include 'header.php';
include 'language.php';
include 'clusters.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
$olv=$_SESSION['version'];
$setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
$cmdPrefix = 'export OLWD='.$pword.';source ./env.sh;'.$setenvdir.'cmd/runas '.$uname;
$cmdExt = $_SESSION['ext'];
if ($uname!=$admin) header("Location: viewreports.php");
$cmdPrefix = 'source ./env.sh;'.$setenvdir;
$filename=basename(__FILE__, '.php');
if (isset($_GET['new']) && ($new=$_GET['new'])=='1') {
    if (($files=scandir("./reports"))!=FALSE) {
	for ($i=2; $i< sizeof ($files); $i++) {
	    unlink("reports/".$files[$i]);
	}
    }
    $timeoption=$_POST['time-option'];
    $begin=$_POST['begin'];
    $end=$_POST['end'];
    $report=$_POST['report'];
    $queue=$_POST['queue'];
    $host=$_POST['host'];
    $user=$_POST['user'];
    $directory=$_POST['directory'];
    $time='';

    $timearray = array(				// predefined time periods
	'day' => '.-1,',
	'week' => '.-7,',
	'month' => '.-1/,',
	'quarter' => '.-3/,',
    );

    if ($timeoption=='custom') {
	if ($begin!='') {
	    $begin=str_replace(' ','/',$begin);
	    $time='-C '.$begin.',';
	}
	if ($end!='') {
	    $end=str_replace(' ','/',$end);
	    if ($time=='')
		$time='-C ,';
	    $time=$time.$end;
	}
    }
    else {					// it is assumed that there is only custom time and predefined time (day, week, month, quarter)
	$time='-C '.$timearray[$timeoption];
    }

    if ($directory!='') {
	$directory='-f '.$directory.'+';
    }
    else {
        $acctdir=shell_exec('source ./env.sh;if [ -f $CB_ENVDIR/cb.conf ]; then grep CB_SHAREDIR $CB_ENVDIR/cb.conf | cut -d= -f2; else echo `dirname $CB_ENVDIR`/work; fi');
        $acctdir=str_replace("\n",'',$acctdir);
        $directory='-f '.$acctdir.'/data';
    }
    $str=' '.$queue.' '.$user.' '.$host.' '.$time.' '.$directory;
    if (strcmp($report, 'throughput')==0) {
	$cmd=  array (
	    0 =>$cmdPrefix.'creport -i 1h -r comp:user -p reports/'.rand(1000,1999).'.png'.$str,
	    1 =>$cmdPrefix.'creport -i 1h -r comp:queue -p reports/'.rand(2000,2999).'.png'.$str,
	    2 =>$cmdPrefix.'creport -i 1h -r comp:project -p reports/'.rand(3000,3999).'.png'.$str,
	    3 =>$cmdPrefix.'creport -i 1h -r comp:ugroup -p reports/'.rand(4000,4999).'.png'.$str,
	    4 =>$cmdPrefix.'creport -i 1h -r comp:resreq -p reports/'.rand(5000,5999).'.png'.$str,
	);
    }
    else if (strcmp($report, 'exit')==0) {
	$cmd= array (
	    0 =>$cmdPrefix.'creport -r exit:user -p reports/'.rand(1000,1999).'.png'.$str,
	    1 =>$cmdPrefix.'creport -r exit:queue -p reports/'.rand(2000,2999).'.png'.$str,
	    2 =>$cmdPrefix.'creport -r exit:project -p reports/'.rand(3000,3999).'.png'.$str,
	    3 =>$cmdPrefix.'creport -r exit:ugroup -p reports/'.rand(4000,4999).'.png'.$str,
	    4 =>$cmdPrefix.'creport -r exit:resreq -p reports/'.rand(5000,5999).'.png'.$str,
	);
    }
    else if (strcmp($report, 'scheduling')==0) {
	$cmd= array (
	    0 =>$cmdPrefix.'creport -f + -r pend:resreq -p reports/'.rand(1000,1999).'.png'.$str,
	    1 =>$cmdPrefix.'creport -f + -r run:resreq -p reports/'.rand(2000,2999).'.png'.$str,
	);
    }
    else if (strcmp($report, 'chargeback')==0) {
	$cmd= array (
	    0 =>$cmdPrefix.'creport -i 1d -r run:user -p reports/'.rand(1000,1999).'.png'.$str,
	    1 =>$cmdPrefix.'creport -i 1d -r run:project -p reports/'.rand(2000,2999).'.png'.$str,
	    2 =>$cmdPrefix.'creport -i 1d -r run:ugroup -p reports/'.rand(2000,3999).'.png'.$str,
	);
    }
    else { // slot or any other
	$cmd= array (
	    0 =>$cmdPrefix.'creport -r run:user -p reports/'.rand(1000,1999).'.png'.$str,
	    1 =>$cmdPrefix.'creport -r run:queue -p reports/'.rand(2000,2999).'.png'.$str,
	    2 =>$cmdPrefix.'creport -r run:project -p reports/'.rand(3000,3999).'.png'.$str,
	    3 =>$cmdPrefix.'creport -r run:ugroup -p reports/'.rand(4000,4999).'.png'.$str,
	    4 =>$cmdPrefix.'creport -r run:resreq -p reports/'.rand(5000,5999).'.png'.$str,
	);
    }

    for ($i=0; $i<sizeof($cmd); $i++)
	shell_exec($cmd[$i].'> reports/'.$i.'.out 2>&1 &');
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
        <?PHP echo $lang['REPORTS'];?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <div id="loading-image"></div>
              <div id="report-images"></div>
            </div>
          </div>
          <div class="box box-solid">
            <div class="box-body">
              <form role="form" action="reports.php?new=1" method="post">
                <b><?PHP echo $lang['TIME_PERIOD'];?></b><p></p>
                <div class="form-group">
                  <div class="radio">
                    <label><input type="radio" name="time-option" value="day" class="predefined-time"><?PHP echo $lang['LAST_24_HOURS'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="time-option" value="week" class="predefined-time"><?PHP echo $lang['LAST_7_DAYS'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="time-option" value="month" class="predefined-time"><?PHP echo $lang['LAST_MONTH'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="time-option" value="quarter" class="predefined-time"><?PHP echo $lang['LAST_QUARTER'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="time-option" value="custom" class="custom-time" checked><?PHP echo $lang['CUSTOM'];?></label>
                  </div>
                  <div class="form-group" id="custom-time-picker">
                    <label><?PHP echo $lang['FROM_DATE'];?></label><input class="form-control" name="begin" id="datetimepicker1">
                    <label><?PHP echo $lang['TO'];?></label><input class="form-control" name="end" id="datetimepicker2">
                  </div>
                </div>
                <div class="form-group">
                  <label><?PHP echo $lang['REPORT_TYPE'];?></label>
                  <div class="radio" >
                    <label><input type="radio" name="report" value="slot" checked>
                    <?PHP echo $lang['JOB_SLOT_USAGE'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="report" value="throughput"><?PHP echo $lang['JOB_THROUGHPUT'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="report" value="exit"><?PHP echo $lang['EXITED_JOBS'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="report" value="scheduling"><?PHP echo $lang['SCHEDULING_ACTIVITIES'];?></label>
                  </div>
                  <div class="radio">
                    <label><input type="radio" name="report" value="chargeback"><?PHP echo $lang['CHARGE_BACK'];?></label>
                  </div>
                  <label><?PHP echo $lang['QUEUE'];?></label>
                  <select class="form-control" name="queue">
                    <option value=""><?PHP echo $lang['ALL'];?></option>
                    <?PHP
                      exec($cmdPrefix."aip queue info | awk 'NR>1 {print $1}'",$qout, $errorno);
                      for ($i=0; $i<sizeof($qout); $i++)
                        echo '<option value="-q '.$qout[$i].'">'.$qout[$i].'</option>';
                    ?>
                  </select>
                  <label><?PHP echo $lang['HOSTS'];?></label>
                  <select class="form-control" name="host">
                    <option value=""><?PHP echo $lang['ALL'];?></option>
                    <?PHP
                      exec($cmdPrefix."aip host info | awk 'NR>1 {print $1}'",$hout, $errorno);
                      for ($i=0; $i<sizeof($hout); $i++)
                        echo '<option value="-m '.$hout[$i].'">'.$hout[$i].'</option>';
                    ?>
                  </select>
                  <label><?PHP echo $lang['USER'];?></label>
                  <select class="form-control" name="user">
                    <option value=""><?PHP echo $lang['ALL'];?></option>
                    <?PHP
                      exec($cmdPrefix."cmd/uinfo".$olv." -l",$uout, $errorno);
                      for ($i=0; $i<sizeof($uout); $i++)
                        echo '<option value="-u '.$uout[$i].'">'.$uout[$i].'</option>';
                    ?>
                  </select><p></p>
                  <label><?PHP echo $lang['DATA_DIRECTORY'];?> (<?PHP echo $lang['LEAVE_BLANK_FOR_THE_CURRENT_SKYFORM_SYSTEM'];?>)</label>
                  <input class="form-control" name="directory">
                </div>
                <button class="btn btn-success" type="submit"><?PHP echo $lang['RUN_REPORTS'];?></button>
                <button class="btn btn-info" type="reset"><?PHP echo $lang['RESET'];?></button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

    <?PHP include('js.html');?>

    <script src="plugins/datetimepicker/jquery.datetimepicker.full.js"></script>
    <!-- Viewing Reports JavaScript -->
    <script src="viewreports.js"></script>

    <script>
$('#datetimepicker1').datetimepicker({
});

$('#datetimepicker2').datetimepicker({
});

    </script>

    <!-- Sliding Custom Time Period -->
    <script>
    $(function() {
	$('input.predefined-time').click(function() {
	    $('#custom-time-picker').slideUp();
	});
	$('input.custom-time').click(function() {
	    $('#custom-time-picker').slideDown();
	});
    });
    </script>

</body>

</html>

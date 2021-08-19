<?PHP
include '../header.php';
if (!isset($_GET['jobid']) || !isset($_GET['submitted']))
   header("Location: ../jobs.php");
$submittime = urldecode($_GET['submitted']);

$cmd='source ../env.sh;../cmd/esjob '.$_GET['jobid'].' "'.$submittime.'"';
exec($cmd, $output, $exit_code);
echo '{"cpu":'.$output[0].',';
echo '"mem":'.$output[1].',';
echo '"swap":'.$output[2].'}';
?>

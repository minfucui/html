<?PHP
include '../header.php';
if (!isset($_GET['host']))
   header("Location: ../hosts.php");

if (isset($_GET['num']))
   $num = ' '.$_GET['num'];
else
   $num = '';

$cmd='source ../env.sh;../cmd/eshost '.$_GET['host'].$num;
exec($cmd, $output, $exit_code);
echo '{"ut":'.$output[0].',';
echo '"memUt":'.$output[1].',';
echo '"io":'.$output[2].',';
echo '"slotUt":'.$output[3].'}';
?>

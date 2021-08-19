<?php
include 'common.php';

if (!isset($_GET['fname']))
    fail(implode('<br>',$_GET));

exec('export OLWD=A1uy3LpGhy;source '.
               $cmdpath.'/env.sh;cd /tmp;'.$cmdpath.
               '/cmd/runas root '.$_GET['fname'].' 2>&1', $r, $errno);
$ret['data'] = implode('<br>', $r);
$ret['message'] = $_POST['fname'];
echo json_encode($ret);
?>

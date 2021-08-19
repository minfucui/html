<?php
include 'common.php';
include 'jobsdata.php';
$change = jobStatusChange($uname);
header('Content-Type: application/json');
echo '{"code":0,"message":"success","data":'.json_encode($change).'}';
?>

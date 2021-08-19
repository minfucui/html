<?php
include 'common.php';
 
if (!isset($_POST['action'])) {
    $ret['code'] = 500;
    $ret['message'] = 'Error';
    echo json_encode($ret);
    die();
}

?>

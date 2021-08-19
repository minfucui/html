<?php
include 'check_session.php';
include 'jobsdata.php';

function readalljsons($folder)
{
    $allfiles = glob('$folder/*.json');
    $allres = array();
    foreach ($allfiles as &$file) {
        $string = file_get_contents($file);
        $json_a=json_decode($string,true);
        $string0 = json_encode($json_a);
        $allres[$file]=$string0
    }
    return $allres;
}

switch($_GET['action']) {
    case 'queryAllJson':
        $ret01=readalljsons('/home/export/online3/amd_app/.ICESIM')
        break;
    default:
        break;
}

// echo json_encode($ret01);
header('Content-Type: application/json');
echo '{"code":"0","message":"call service success","data":'.json_encode($ret01).'}';
?>

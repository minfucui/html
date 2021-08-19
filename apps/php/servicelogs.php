<?php
include 'common1.php';

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        exec('source '.$cmdpath.'/env.sh;echo $CB_ENVDIR', $r, $errno);
        $page['rows'][0]['file']['path'] = dirname($r[0]).'/log';
        $ret['data'] = $page;
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}


echo json_encode($ret);
?>

<?php
include 'common1.php';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $users = [];
    $cout = shell_exec($cmdPrefix.'aip li i -l');
    if (($udata = json_decode($cout, TRUE)) === FALSE)
        return $users;

    foreach($udata as $u) {
        $user = [];
        $user['server'] = $u['Server'];
        $user['vendor'] = $u['Vendor']; 
        $user['feature'] = $u['Feature'];
        $user['total'] = $u['Total'];
        $user['available'] = $u['Free'];
        $used = '';
        foreach ($u as $key=>$value) {
            if ($key == 'User')
                $used = $used.$value.':';
            if ($key == 'InUse')
                $used = $used.$value.' ';
        }
        $user['used'] = $used;
        $users[] = $user;
    }      
    return $users;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateData();
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

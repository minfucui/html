<?php
include 'common1.php';
include 'jobsdata.php';

$lang = $_SESSION['lang'];

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $data = [];
        if ($_SESSION['roles'][0] == $lang['GROUP_ADMIN']) {
            $users = searchRows('users', ['username'=>$uname]);
            if (isset($users['error']))
                fail($users['error']);
            $ugroup = $users[0]['groupname'];
            $users = searchRows('users', ['groupname'=>$ugroup]);
        } else
            $users = searchRows('users');
        if (isset($users['error']))
            fail($users['error']);
        foreach ($users as $u) {
            $order = searchRows('apporders', ['ugname'=>$u['username']]);
            if (isset($order['error']))
                fail($order['error']);
            $data[] = ['username'=>$u['username'].
                          ' php/appuse.php?username='.$u['username'],
                       'app_permitted'=>sizeof($order)];
        }
        $page['rows'][0]['table']['data'] = $data;
        $ret['data'] = $page;
        break;

    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

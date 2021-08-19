<?php
include 'common1.php';

$tablename = 'orders';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $orders = [];
    $rows = searchRows($tablename);
    if (($nrows = sizeof($rows)) == 0) {
        return [];
    }
    foreach($rows as $r) {
        if ($r['creator'] != $uname && $r['assigned'] != $uname)
            continue;
        $o = $r;
        $o['orderid'] = strval($r['orderid']);
        $orders[] = $o;
    }
    return $orders;
}

function userlist()
{
    global $_SESSION;
    $users = [];
    $dbout = searchRows('users');
    foreach ($dbout as $d) {
        $roles = explode(' ', $d['roles']);
        if (in_array($_SESSION['lang']['USER'], $roles))
            $users[] = $d['username'];
    }
    return $users;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        if (isset($result['error'])) {
            fail($result['error']);
            break;
        }
        $page['rows'][0]['table']['data'] = $result;
        $page['rows'][0]['table']['options'] = ['assigned'=>userlist()];
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateData();
        if (isset($result['error'])) {
            fail($result['error']);
        } else {
            $ret['data']['table'] = $result;
            $ret['data']['options'] = ['assigned'=>userlist()];
        }
        break;
    case 'new_order':
        $rec = $_POST['data'];
        if ($rec['assigned'] == '' || $rec['task'] == '' || $rec['finishby'] == '') {
            fail('ALL_REQUIRED');
        } else {
            $rec['creator'] = $uname;
            $rec['create_time'] = date("Y-m-d H:i:s", time());
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                fail($result['error']);
            }
        }
        break;
    case 'update_order':
        break;
    case 'complete_order':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        foreach($toDelete as $del) {
            $result = deleteARow($tablename, ["username"=>$del]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    default:
        fail('Wrong request');
        break;
}

echo json_encode($ret);
?>

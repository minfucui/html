<?php
include 'common1.php';

$tablename = 'regusers';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $rows = searchRows($tablename);
    if (($nrows = sizeof($rows)) == 0) {
        return [];
    }
    return $rows;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./it_orders.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
            break;
        }
        $page['rows'][0]['table']['data'] = $result;
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else {
            $ret['data']['table'] = $result;
        }
        break;
    case 'new_order':
        $rec = $_POST['data'];
        if ($rec['username'] == '' || $rec['name'] == '' || $rec['phone'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else {
            $exist = searchRows($tablename, ["username"=>$rec['username']]);
            if (sizeof($exist) > 0) {
                $ret['code'] = 200;
                $ret['message'] = 'NAME_EXISTS';
                break;
            }
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    case 'delete_order':
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
    case 'modify_order':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        if ($mod['name'] == '' || $mod['phone'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
            break;
        }
        $result = modifyARow($tablename, ["username"=>$mod['username']], $mod);
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        }
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

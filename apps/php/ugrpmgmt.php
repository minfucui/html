<?php
include 'common1.php';

$tablename = 'usergroups';
$generic = "通用";

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $rows = searchRows($tablename);
    if (sizeof($rows) == 0) {
        return [];
    }
    $subs = searchRows('users');
    $nrows = sizeof($rows);
    for ($i = 0; $i < $nrows; $i++) {
        $rows[$i]['num_users'] = 0;
    }
    $nsubs = sizeof($subs);
    for ($i = 0; $i < $nsubs; $i++) {
        for($j = 0; $j < $nrows; $j++) {
            if ($subs[$i]['groupname'] == $rows[$j]['groupname']) {
                 $rows[$j]['num_users']++;
                 break;
            }
        }
    }
    return $rows;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./ugrpmgmt.yaml');
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
        } else
            $ret['data']['table'] = $result;
        break;
    case 'new_ugrp':
        $rec = $_POST['data'];
        if ($rec['groupname'] == '' || $rec['contactname'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else {
            $exist = searchRows($tablename, ["groupname"=>$rec['groupname']]);
            if (sizeof($exist) > 0) {
                $ret['code'] = 200;
                $ret['message'] = 'NAME_EXISTS';
                break;
            }
            if ($rec['groupadmin'] != '') {
                $existuser = searchRows('users', ["username"=>$rec['groupadmin']]);
                if (sizeof($existuser) == 0) {
                    $ret['code'] = 200;
                    $ret['message'] = 'ADMINNAME_NOT_EXIST';
                    break;
                }
                if ($existuser['groupname'] != '' &&
                    $existuser['groupname'] != $rec['groupname']) {
                    $ret['code'] = 200;
                    $ret['message'] = '管理员属于另一个组';
                    break;
                }
            }
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
            if ($rec['groupadmin'] != '' && (!isset($existuser['groupname']) || 
                $existuser['groupname'] == '')) {
                $result = modifyArow('users', ["username"=>$rec['groupadmin']],
                                 ["groupname"=>$rec['groupname']]);
                if (isset($result['error'])) {
                    $ret['code'] = 500;
                    $ret['message'] = $result['error'];
                    break;
                }
            }
        }
        break;
    case 'delete_ugrps':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        foreach($toDelete as $del) {
            $result = deleteARow($tablename, ["groupname"=>$del]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
            $result = modifyARow('users', ["groupname"=>$del], ["groupname"=>""]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    case 'modify_ugrp':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        if ($mod['contactname'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
            break;
        }
        if ($mod['groupadmin'] != '') {
            $existuser = searchRows('users', ["username"=>$mod['groupadmin']]);
            if (sizeof($existuser) == 0) {
                $ret['code'] = 200;
                $ret['message'] = 'ADMINNAME_NOT_EXIST';
                break;
            }
            if (isset($existuser['groupname']) && $existuser['groupname'] != '' &&
                $existuser['groupname'] != $mod['groupname']) {
                $ret['code'] = 200;
                $ret['message'] = '管理员属于另一个组';
                break;
            }
        }
        $result = modifyARow($tablename, ["groupname"=>$mod['groupname']], $mod);
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        }
        if ($mod['groupadmin'] != '' &&
            (!isset($existuser['groupname']) || $existuser['groupname'] == '')) {
            $result = modifyArow('users', ["username"=>$mod['groupadmin']],
                             ["groupname"=>$mod['groupname']]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

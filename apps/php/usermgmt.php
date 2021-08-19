<?php
include 'common1.php';
include 'jobsdata.php';

$tablename = 'users';
$generic = "通用";

function roleoptions()
{
    global $_SESSION;
    $roles = [];
    foreach ($_SESSION['roleopt'] as $r)
        $roles[] = $r['name'];
    return $roles;
}

function groupoptions()
{
    $groups = [];
    $gdata = searchRows('usergroups');
    for ($i = 0; $i < sizeof($gdata); $i++)
        $groups[] = $gdata[$i]['groupname'];
    return $groups;
}

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $rows = searchRows($tablename);
    if (($nrows = sizeof($rows)) == 0) {
        return [];
    }
    for ($i = 0; $i < $nrows; $i++) {
       $rows[$i]['balance'] = '￥'.($rows[$i]['balance'] == '' ? 0 :
           $rows[$i]['balance']);
       $rows[$i]['last_pay'] = '￥'.($rows[$i]['last_pay'] == '' ? 0 :
           $rows[$i]['last_pay']);
       $rows[$i]['discount'] = '<button type="button" class="btn btn-success" onclick=setDiscount("'.
               $rows[$i]['username'].'")>设置</button>'; 
       #$rows[$i]['discount'] = ($rows[$i]['discount'] == '' ? 100 :
       #    strval($rows[$i]['discount'])*100).'%';
       $rows[$i]['last_pay_time'] = $rows[$i]['last_pay_time'] == '' ? '无' :
           $rows[$i]['last_pay_time'];
    }
    return $rows;
}

function addJobCounts($data)
{
    $n = sizeof($data);
    if ($n == 0)
        return [];
    for ($i = 0; $i < $n; $i++)
        $data[$i]['activejobs'] = 0;
    $jobs = allJobs();
    if (sizeof($jobs) == 0)
        return $data;
    foreach ($jobs as $job) {
        $n = sizeof($data);
        for ($i = 0; $i < $n; $i++) {
             if ($data[$i]['username'] == $job['user'] &&
                 $job['statusString'] != 'FINISH' &&
                 $job['statusString'] != 'EXIT' ) {
                 $data[$i]['activejobs'] ++;
                 break;
             }
        }
    }
    return $data;
}

function timenow()
{
    return date('Y-m-d H:i:s', time());
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./usermgmt.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
            break;
        }
        $page['rows'][0]['table']['data'] = addJobCounts($result);
        $page['rows'][0]['table']['options'] =
            ['groupname' => groupoptions(),
             'roles' => roleoptions()];
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else {
            $ret['data']['table'] = addJobCounts($result);
            $ret['data']['options'] =
                ['groupname' => groupoptions(),
                 'roles' => roleoptions()];
        }
        break;
    case 'new_user':
        $rec = $_POST['data'];
        if ($rec['username'] == '' || $rec['name'] == '' || $rec['phone'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else {
            $rec['last_pay'] = str_replace('￥', '', $rec['last_pay']);
            # $rec['discount'] = str_replace('%', '', $rec['discount']);
            if (!is_numeric($rec['last_pay'])) # || !is_numeric($rec['discount']))
                fail('充值必须为数值');
            $exist = searchRows($tablename, ["username"=>$rec['username']]);
            if (sizeof($exist) > 0) {
                $ret['code'] = 200;
                $ret['message'] = 'NAME_EXISTS';
                break;
            }
            if ($rec['groupname'] != '') {
                $existgrp = searchRows('usergroups', ["groupname"=>$rec['groupname']]);
                if (sizeof($existgrp) == 0) {
                    $ret['code'] = 200;
                    $ret['message'] = 'DEPT_NOT_EXIST';
                    break;
                }
            } else
                unset($rec['groupname']);
            $rec['last_pay'] = number_format(
                 floatval($rec['last_pay']), 2, '.', '');
            $rec['last_pay_time'] = $rec['bill_end_time'] = timenow();
            $rec['balance'] = $rec['bill_end_balance'] = $rec['last_pay'];
            #$rec['discount'] = number_format(
            #     floatval($rec['discount'])/100, 3, '.', '');
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    case 'delete_users':
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
    case 'modify_user':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        if ($mod['name'] == '' || $mod['phone'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
            break;
        }
        $mod['this_pay'] = str_replace('￥', '', $mod['this_pay']);
        # $mod['discount'] = str_replace('%', '', $mod['discount']);
        if (($mod['this_pay'] != '' && !is_numeric($mod['this_pay']))) # ||
            # !is_numeric($mod['discount']))
            fail('充值必须为数值');
        if ($mod['groupname'] != '') {
            $existuser = searchRows('usergroups', ["groupname"=>$mod['groupname']]);
            if (sizeof($existuser) == 0) {
                $ret['code'] = 200;
                $ret['message'] = '部门不存在';
                break;
            }
        } else
            unset($mod['groupname']);
        $exist = searchRows('users', ['username'=>$mod['username']]);
        $thispay = floatval($mod['this_pay']);
        $exist_balance = floatval($exist[0]['balance']);
        $exist_bill_end = floatval($exist[0]['bill_end_balance']);
        if ($thispay != 0) {
            $mod['last_pay_time'] = timenow();
            $mod['balance'] = number_format($exist_balance + $thispay,
                 2, '.', ''); 
            $mod['bill_end_balance'] = number_format($exist_bill_end +
                $thispay, 2, '.', '');
            $mod['last_pay'] = number_format($thispay, 2, '.', '');
        } else {
            unset($mod['balance']);
        }
        unset($mod['this_pay']);
        /* $mod['discount'] = number_format(
                 floatval($mod['discount'])/100, 3, '.', ''); */

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

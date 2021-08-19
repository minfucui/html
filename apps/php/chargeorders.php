<?php
include 'common1.php';

$tablename = 'chargeorders';

function updateData()
{
    global $_SESSION, $tablename;
    $rows = searchRows($tablename, ['approvedat'=>'NULL']);
    $existing = runSQL('select * from chargeorders where approvedat >= curdate()');
    if (sizeof($existing) > 0 && !isset($existing['error']))
        $rows = array_merge($rows, $existing);
    $nrows = sizeof($rows);
    for ($i = 0; $i < $nrows; $i++) {
        $rows[$i]['username'] = '<a href="javascript:userInfo(\''.
                      $rows[$i]['username'].'\');">'.
                      $rows[$i]['username'].'</a>';
    }
        
    return $rows;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
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
    case 'accept':
        foreach ($_POST['data'] as $item) {
            if ($item['1'] == '' || $item['2'] == '') {
                fail('Wrong request');
            }
            $rec = ['orderid'=>$item[0], 'username'=>$item[1], 'pay'=>$item[2]];
            $result = searchRows('chargeorders', ['orderid'=>$item[0]]);
            if ($result[0]['approvedat'] != NULL)
                continue;
            $u = explode('>', $rec['username']);
            $u1 = explode('<', $u[1]);
            $rec['username'] = $u1[0];
        
            $exist = searchRows('users', ['username'=>$rec['username']]);
            if (sizeof($exist) < 1 || isset($exist['error']))
                fail('DB error');
            $exist_balance = floatval($exist[0]['balance']);
            $exist_bill_end = floatval($exist[0]['bill_end_balance']);
            $thispay = floatval($rec['pay']);
            $rec['last_pay'] = number_format($thispay, 2, '.', '');
            $now = date('Y-m-d H:i:s', time());
            $rec['last_pay_time'] = $now;
            $rec['balance'] = number_format($exist_balance + $thispay,
                          2, '.', '');
            $mod['bill_end_balance'] = number_format($exist_bill_end +
                    $thispay, 2, '.', '');
            unset($rec['pay']);
            unset($rec['orderid']);
            $result = modifyARow('users', ["username"=>$rec['username']], $rec);
            if (isset($result['error']))
                fail($result['error']);
            $result = modifyARow('chargeorders', ['orderid'=>$item[0]],
                               ['approvedby'=>$uname, 'approvedat'=>$now]);
        }
        break;
    case 'userinfo':
        $rec = $_POST['username'];
        if ($rec == '')
            fail('Wrong request');
        $users = searchRows('users', ['username'=>$rec]);
        if (isset($users['error']) || sizeof($users) == 0) {
            fail('Cannot find user');
        }
        $u = [];
        foreach($users[0] as $key=>$value) {
            if ($key == 'last_update' || $key == 'activation' ||
                $key == 'acctstatus' || $key == 'bill_end_balance' ||
                $key == 'bill_end_time' || $key == 'groupname')
                continue;
            $u[$key] = ['title'=>strtoupper($key), 
                'value'=>($value == NULL? '-': $value)];
        }
        $ret['data']['description'] = ['id'=>$rec, 'rows'=>$u];
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

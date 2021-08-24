<?php
include 'common1.php';

$tablename = 'supportorders';

function updateData()
{
    global $_SESSION, $tablename;
    $rows = searchRows($tablename);
    $nrows = sizeof($rows);
    for ($i = 0; $i < $nrows; $i++) {
        $rows[$i]['creator'] = '<a href="javascript:creatorInfo(\''.
                      $rows[$i]['creator'].'\');">'.
                      $rows[$i]['creator'].'</a>';
        $rows[$i]['status'] = $rows[$i]['status']==1 ? "未处理" : "已处理";
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
    case 'finish':
        $toUpdate = $_POST['data'];
        if (sizeof($toUpdate) < 1)
            break;
        foreach($toUpdate as $id) {
            $result = modifyARow($tablename, ['orderid'=>$id], ['status'=>2]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    case 'creatorInfo':
        $rec = $_POST['creator'];
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
                $key == 'bill_end_time' || $key == 'groupname' ||
                $key == 'last_pay' || $key == 'last_pay_time' ||
                $key == 'balance' || $key == 'discount')
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

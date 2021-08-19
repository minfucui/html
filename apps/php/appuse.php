<?php
include 'common1.php';
include 'user_data.php';

$lang = $_SESSION['lang'];
$ordertable = 'apporders';

function updateData($user)
{
    global $ordertable, $lang;
    $orders = searchRows($ordertable, ['ugname'=>$user]); 
    if (isset($orders['error']))
        fail($orders['error']);
    $rows = searchRows('applications');
    $n = sizeof($rows);
    for ($i = 0; $i < $n; $i++) {
        unset($rows['last_update']);
        unset($rows['published']);
        unset($rows['confpath']);
        unset($rows['icon']);
        $rows[$i]['use'] = $lang['WORDNO']; 
        if (sizeof($orders) > 0)
            foreach($orders as $order) {
                if ($order['appname'] == $rows[$i]['appname']) {
                    $rows[$i]['use'] = $lang['WORDYES'];
                    break;
                }
            }
    }
    return $rows;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        if (!isset($_GET['username']))
            fail("Error request");
        $user = $_GET['username'];
        $result = updateData($user);
        if (isset($result['error']))
            fail ($result['error']);
        $page['rows'][0]['table']['data'] = $result;
        $page['page_title'] = $lang['APP_ORDER'].': '.$lang['USERNAME'].
                             ' '.$user;
        $page['rows'][0]['table']['update']['url'] =
              $page['rows'][0]['table']['update']['url'].'?username='.$user;
        $page['modals'][0]['buttons'][0]['url'] =
              $page['modals'][0]['buttons'][0]['url'].'?username='.$user;
        $page['modals'][0]['buttons'][0]['url'] =
              $page['modals'][1]['buttons'][0]['url'].'?username='.$user;
        $ret['data'] = $page;
        break;

    case 'update':
        if (!isset($_GET['username']))
            fail("Error request");
        $result = updateData($_GET['username']);
        if (isset($result['error']))
            fail ($result['error']);
        $ret['data']['table'] = $result;
        break;
    case 'add_orders':
        if (!isset($_POST['data']) || sizeof($_POST['data']) < 1 ||
            !isset($_GET['username']))
            fail("Error request");
        $user = $_GET['username'];
        foreach ($_POST['data'] as $order) {
            $res = insertARow($ordertable, ['ugname'=>$user,
                                            'appname'=>$order]);
            if (isset($res['error']))
                 fail($res['error']);
        }
        skylog_activity($uname, CONFIG, 'add app orders',
                        sizeof($_POST['data']).' apps for user: '.
                        $user);
        break;
    case 'del_orders':
        if (!isset($_POST['data']) || sizeof($_POST['data']) < 1 ||
            !isset($_GET['username']))
            fail("Error request");
        $user = $_GET['username'];
        foreach ($_POST['data'] as $order) {
            $res = deleteARow($ordertable, ['ugname'=>$user,
                                            'appname'=>$order]);   
            if (isset($res['error']))
                 fail($res['error']);
        }
        skylog_activity($uname, CONFIG, 'remove app orders',
                        sizeof($_POST['data']).' apps for user: '.
                        $user);
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

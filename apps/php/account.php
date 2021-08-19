<?php
include 'common1.php';

if ($_POST['action'] != 'load')
    fail("Wrong request");

$page = yaml_parse_file('./account.yaml');
if ($page === FALSE)
    fail("Internal error");

$row = searchRows('users', ['username'=>$uname]);
if (isset($row['error']) || sizeof($row) != 1)
    fail('DB_ERROR');

$items = $page['rows'][0]['form']['0']['items'];
foreach($row[0] as $key=>$value) {
    for ($i = 0; $i < sizeof($items); $i++)
        if ($items[$i]['id'] == $key) {
            $items[$i]['value'] = $value;
            break;
        }
}
$page['rows'][0]['form']['0']['items'] = $items;

$ret['data'] = $page;
echo json_encode($ret);
?>

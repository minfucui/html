<?php
include 'check_session.php';
include 'folders.php';

$folders = folders($uname);  // 在用户目录下查询实例分组类别接口，分组其实就相当于一个文件夹

$ret = [];

foreach ($folders as $key=>$value) {
    $itm = [];
    $itm['id'] = $itm['name'] = $key;
    $ret[] = $itm;
}
header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":'.json_encode($ret).'}';
?>

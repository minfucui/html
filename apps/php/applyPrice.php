<?php
include 'common.php';
if (!in_array($_SESSION['lang']['ADMIN'], $_SESSION['roles']))
    fail('Invalid parameter');
exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $er);
$confcmd = dirname($r[0]).'/sbin/cbcrond reconfig';
$r = [];
exec($cmdPrefix.$confcmd, $r, $er);
$ret['data'] = implode("<p>", $r);
echo json_encode($ret);
?>

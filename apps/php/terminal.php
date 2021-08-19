<?php
include 'common.php';

/* start the terminal service */

$h = substr(hash('md5', $uname), 0, 4);
$p = hexdec($h);
if ($p < 16000)
   $p += 16000;

$port = strval($p);
$cmd = 'export OLWD='.$pword.';source /var/www/html/env.sh'.
       ';export HOME='.$_SESSION['home'].';/var/www/html/cmd/runas '.$uname.
       ' $CB_ENVDIR/skyformvnc/bin/ttyd -p '.$port.' -o -m 1 bash > /dev/null &';
exec($cmd, $rout, $errno);
if ($errno != 0)
   error_log('ttyd error: '.implode($rout));
$ret['url'] = 'http://'.$_SERVER['SERVER_ADDR'].':'.$port;
skylog_activity($uname, WEB_ACCESS, 'open terminal', ' port:'.$port);
usleep(300000);
echo json_encode($ret);
?>

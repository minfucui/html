<?php
/* debugstart 
session_start();
*/
$cmdPrefix = 'export OLWD='.$_SESSION['password'].';source /var/www/html/env.sh;/var/www/html/cmd/runas '.
     $_SESSION['uname'].' ';
exec($cmdPrefix."/usr/sw-mpp/bin/qload -w | grep q_ | awk '{print $1}'", $qout, $r);
$queues = $qout;
foreach ($qout as $q)
{
    if (strpos($q, 'q_x86') !== FALSE)
        $queues[] = $q.'_plus';
}
$queues[] = 'local';
/* debugstart
var_dump($queues);
 debugstop */
?>

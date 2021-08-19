<?php
/* debugstart 
session_start();
*/
$cmdPrefix = 'export OLWD='.$_SESSION['password'].';source /var/www/html/env.sh;/var/www/html/cmd/runas '.
     $_SESSION['uname'].' ';
exec($cmdPrefix."/usr/sw-mpp/bin/qload -w | grep q_ | awk '{print $1}'", $qout, $r);
$queues = $qout;
foreach ($qout as $q)  // 检查队列类型，是否为本地队列
{
    if (strpos($q, 'q_x86') !== FALSE)
        $queues[] = $q.'_plus';  // 类似append，添加一个新的元素
}
$queues[] = 'local';
/* debugstart
var_dump($queues);
 debugstop */
?>

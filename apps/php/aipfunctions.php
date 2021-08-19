<?php

function queuelist() {
    global $cmdPrefix;
    $out = shell_exec($cmdPrefix.' aip q i -l');
    exec($cmdPrefix." cparams | head -1 | awk '{print $3}'", $r, $errno);
    $queues = json_decode($out, TRUE);
    if ($erro == 0) {
        $defq = $r[0];
        $qs = [$defq];
    } else {
        $defq = '';
        $qs = [];
    }
    foreach ($queues as $queue) {
        if (strpos($queue['Status'], 'Closed') === FALSE &&
            $queue['Name'] != $defq)
            $qs[] = $queue['Name'];
    }
    return $qs;
}

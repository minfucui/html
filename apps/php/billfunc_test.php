<?php
include 'common.php';
include 'billfunc.php';

$lang= $_SESSION['lang'];
function genbill($y, $m, $user, $regen)
{
    global $cmdpath, $cmdPrefix, $uname;
    $ymformat = sprintf("%4d-%02d", $y, $m);
    $filepath = 'bill/'.$user."-".$ymformat;

    $now = time();
    $starttime = sprintf("%4d-%02d-01 00:00:00", $y, $m);

    if ($regen || !file_exists($filepath)) {
        $out = shell_exec('export OLWD=A1uy3LpGhy;source '.
               $cmdpath.'/env.sh;'.$cmdpath.
               '/cmd/runas root aipbills -u '.$user." -j -m ".$ymformat);
        $output = json_decode($out, TRUE);
        $bill = calcCost($output['Details'], $y, $m);

        file_put_contents($filepath, json_encode($bill));
    }
}

        $currentmonth = date("m");
        $currentyear = date("Y");
        $users = [$uname];
        foreach ($_SESSION['roles'] as $r)
            if ($r == $lang['ADMIN']) {
                $uindb = searchRows('users');
                if (isset($uindb['error']) || sizeof($uindb) == 0)
                    break;
                $users = [];
                foreach ($uindb as $u)
                    $users[] = $u['username'];
                break;
            }
        for ($i = 0; $i < 1; $i++) {
            $m = previousmonth ($currentyear, $currentmonth, $i);
            foreach ($users as $u)
                genbill($m[0], $m[1], $u, $i == 0);
        }

echo json_encode($ret);
?>

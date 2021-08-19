<?php
include 'check_session.php';
$lics = $_SESSION['licenses'];

$ret = [];
if (sizeof($lics) > 0) {
    foreach ($lics as $lic) {
        $a =  shell_exec($cmdPrefix.' aip li i -s '.$lic['server'].' -lm '.  // 执行相应aip命令，获得许可证
              $_SESSION['lmstat'].' -l');
        $licj = json_decode($a, TRUE);
        if (isset($lic['features']) && $lic['features'][0] != 'all') {
            $n = sizeof($licj);
            for ($i = 0; $i < $n; $i++) {
                $shouldinclude = FALSE;
                foreach($lic['features'] as $feature) {
                    if ($feature == $licj[$i]['Feature']) {
                        $shouldinclude = TRUE;
                        break;
                    }
                }
                if ($shouldinclude === FALSE)
                    unset($licj[$i]);
            }
            $licj = array_values($licj);
        }
        $ret = array_merge($ret, $licj);
    }
    $a = json_encode($ret);
}
else
    $a = '[]';

echo '{"code":"0","message":"call service success","data":'.$a.'}';
?>

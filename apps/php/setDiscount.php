<?php
include 'common.php';
include 'filefunctions.php';

if (!isset($_POST['user']) || !in_array($_SESSION['lang']['ADMIN'], $_SESSION['roles']))
    fail('Invalid parameter');

$d = $_POST;

exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $er);
$aipenv = $r[0];
$crondconf = $aipenv.'/cbcrond.yaml';

if (!file_exists($crondconf))
    fail("cbcrond.yaml does not exist.\n");

$conf = yaml_parse_file($crondconf);
if ($conf === FALSE)
    fail("Invalid cbcrond.yaml.\n");

$n = sizeof($conf['charge']['userdiscounts']);
for($i = 0; $i < $n; $i++) {
    if ($conf['charge']['userdiscounts'][$i]['user'] == $d['user']) {
        $conf['charge']['userdiscounts'][$i] = $d;
        break;
    }
}

if ($i == $n)
    $conf['charge']['userdiscounts'][] = $d;
$st = yaml_emit($conf, YAML_UTF8_ENCODING);
if ($st === FALSE)
    fail('Invalid configuration');

if (myFile_Put_Contents($crondconf, $st) === FALSE)
    fail('Cannot write to '.$crondconf);

echo json_encode($ret);
?>

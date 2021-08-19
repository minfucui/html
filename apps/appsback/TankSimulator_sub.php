<?PHP
/*--------------- Please modify the following parameters for your environment --*/
$app_cmd="TankSimulator";
$app_name="TankSimulator";
/*--------------- End of custom parameters -------------------------------------*/

$cwd = $_POST['cwd'];
$app = ' -A '.$app_name;

$cmd=$cmdPrefix.
' vncsub -cwd '.$cwd.$geometry.$app.' '.$app_cmd.' 2>&1';
#exec('echo "'.$cmd.'"', $res, $exit_code);
exec($cmd, $res, $exit_code);
?>

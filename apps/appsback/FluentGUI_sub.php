<?PHP
/*--------------- Please modify the following parameters for your environment --*/
$app_cmd="fluent-job";
$app_name="Fluent";
/*--------------- End of custom parameters -------------------------------------*/

$ncpu=$_POST['nmin'];
$otherp='';
$queue='';

$solver = strtolower($_POST['dimension']);
if (isset($_POST['display_options']))
    $display_mesh = "";
else
    $display_mesh = " -nm";
if ($solver == "3d" && isset($_POST['meshing']))
    $meshing = " -meshing -tm ".$ncpu;
else
    $meshing = "";
if (isset($_POST['post']))
    $post = " -post";
else
    $post = "";
if (isset($_POST['double_precision']))
    $solver = " ".$solver."dp";
else
    $solver = " ".$solver;
$cwd = $_POST['cwd'];

$fluentoptions='';

$nproc = ' -n '.$ncpu;

$outfile = ''; // -o fluent.%J.txt -e fluent.%J.err';

$app = ' -A '.$app_name;

$cmd=$cmdPrefix.
' vncsub -vnc /usr/bin'.$queue.$nproc.$outfile.$otherp.' -cwd '.$cwd.$geometry.$app.
' '.$app_cmd.$solver.$fluentoptions.
$display_mesh.$meshing.$post.' 2>&1';
#exec('echo "'.$cmd.'"', $res, $exit_code);
exec($cmd, $res, $exit_code);
?>

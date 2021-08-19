<?PHP
session_start();
include '../langfunctions.php';
$language = $_SESSION['language'];
$lang = parse_ini_file('../i18n/'.$language.'.ini');

$uname=$_SESSION['uname'];
$pword=$_SESSION['password'];
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'functions.php';
$dir=$_GET['dir'];
$title=$_GET['title'];
if (isset($_GET['op']))
    $op = $_GET['op'];
else
    $op = 'file';
if ($dir=='') $dir='.';
$dir=myRealpath($dir);
$tablename=$title."DataTables";
$timezone=shell_exec("../cmd/timezone");
date_default_timezone_set($timezone);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<div class="dataTable_wrapper">
<table class="table table-bordered" id="<?php echo $tablename;?>">
<thead>
<tr>
    <th><?PHP echo $lang['SELECT'];?></th>
    <th><?PHP echo $lang['NAME'];?></th>
    <th><?PHP echo $lang['LAST_MODIFIED'];?></th>
    <th><?PHP echo $lang['SIZE'];?></th>
</tr>
</thead>
<tbody>
<?php
  $d[$title]=myScandir($dir);
  $n = 0;
  foreach ($d[$title] as $file) {
	if ($file=='.') continue;
	if (substr($file,0,1)==='.' && $file!='..') continue;
	$paths[$title][]=$path=$dir.'/'.$file;
	/* if (myIs_Readable($path)==TRUE) $readable="Readable";
	else $readable="Unreadable";
	if (myIs_Writable($path)==TRUE) $writable="Writable";
	else $writable="Unwritable"; */
	$type="File";
	if (myIs_Dir($path)) $type="Directory";
	if (myIs_Link($path)) $type="File";
	$types[$title][]=$type;
	$size=myFilesize($path);
	$time=myFilemtime($path);
	$timestr=date("Y/m/d H:i",$time);
        if ($op == 'file') {
            if ($type=="Directory") {
	        $hash[$title][]=$hashid=hash("md5",$path.$title);
                echo '<tr><td></td><td><a href="#" id="'.$hashid.'">'.$file."</a></td>";
	        $n++;
            }
            else {
	        echo '<tr><td><input class="checkbox" type="checkbox" name="'.$title.'" value="'.$path.'"></td><td>'.$file.'</td>';
                $hash[$title][] = 0;
            }
            echo     "<td>".$timestr."</td>";
            echo     "<td>".$size."</td></tr>"."\n";
        } else {
            if ($type == "Directory") {
                $hash[$title][]=$hashid=hash("md5",$path.$title);
                if ($file == '..')
                    echo '<tr><td></td>';
                else
                    echo '<tr><td><input class="checkbox" type="checkbox" name="'.$title.'" value="'.$path.'"></td>';
                echo '<td><a href="#" id="'.$hashid.'">'.$file."</a></td>";
                $n++;
            }
            else {
                echo '<tr><td></td><td>'.$file.'</td>';
                $hash[$title][] = 0;
            }
            echo     "<td>".$timestr."</td>";
            echo     "<td>".$size."</td></tr>"."\n";
        }
  }
?>
</tbody>
</table>
</div>
<!-- jQuery -->
<script type="text/javascript" charset="utf-8">
<?php
     for ($i=0; $i<sizeof($d[$title]); $i++) 
	if (isset($types[$title][$i]) && $types[$title][$i]=="Directory") {
	    printf('$(document).ready(function(){%s',"\n"); 
	    printf('  $("#%s").click(function(){%s',$hash[$title][$i], "\n");
	    printf('     $("#%s_explore").load("php/fileexplore.php?dir=%s&title=%s&op=%s");%s', $title,
			$paths[$title][$i],$title, $op, "\n");
            printf('  });%s',"\n");
	    printf('});%s',"\n");
        }
?>

$(document).ready(function() {
    $('#<?php echo $tablename;?>').DataTable({
	responsive: true,
	"order": [[ 1, "asc"]],
	"pageLength": 10,
	"language":
	<?PHP
	    $languages = getAnglicizedLanguages();
	    echo file_get_contents('../plugins/datatables-plugins/i18n/'.$languages[$language].'.lang');
	?>
    });
});

$(document).ready(function() {
     $("#<?php echo $title; ?>_title").text('<?php echo $dir; ?>');
});
</script>
</body>
</html>

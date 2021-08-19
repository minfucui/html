<?PHP
include 'header.php';
include 'language.php';
$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];

function description($file, $pat) {
    $f=fopen($file,"r");
    $name=0;
    while (($line=fgets($f))!=FALSE) {
        if ($name==0) {
            if (strncmp($line, "<h2>NAME", 8)==0)
                $name=1;
        }
        else {
            if (strstr($line, $pat)!=FALSE) {
                $s=strstr($line, "-");
                if ($s == FALSE) {
                    $s=strstr($line, "&minus;");
                    if ($s == FALSE)
                        return "";
                }
                return str_replace("</p>\n", "", $s);
            }
        }
        continue;
    }
    return "";
}
function listman($path) {
    $files=scandir($path);
    if ($files==FALSE)
        return;
    printf ("<div class=\"table-responsive\">\n<table class=\"table\">\n<tbody>\n");
    foreach ($files as $file) {
        if ($file == "." || $file == "..")
            continue;
        printf("<tr>");
        $name=preg_replace("/.html/", "", $file);
        $fullpath=$path.'/'.$file;
        printf("<td><a href=\"%s\">%s</a></td><td>%s</td>\n", $fullpath, $name,
            description($fullpath, $name));
        printf("</tr>");       
    }
    printf ("</tbody></table></div>\n");
}
?>

<!DOCTYPE html>
<html>
<?PHP include('header.html');?>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
  <div class="wrapper">
    <!-- Navigation -->
    <?PHP include 'navigation.php';?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1 class="page-header">Cube <?PHP echo $lang['MAN_PAGES'];?></h1>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box box-solid">
	      <div class="box-body">
                <h3><?PHP echo $lang['COMMAND'];?></h3>
                <?PHP listman("man1"); ?>
                <h3><?PHP echo $lang['CONFIG'];?></h3>
                <?PHP listman("man5"); ?>
                <h3><?PHP echo $lang['DAEMON'];?></h3>
                <?PHP listman("man8"); ?>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?PHP include('js.html');?>
</body>
</html>

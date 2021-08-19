<?PHP
/* this is all file operation functions that uses the session variable
   uname and password to call function on behalf of the user
*/
// 文件处理函数，全是以linux命令的形式
$runas='source /var/www/html/env.sh;export LANG=en_US.UTF-8;export OLWD='.
$pword.';/var/www/html/cmd/runas '.$uname.' ';
$file_exists='1 ';
$mkdir='2 ';
$chmod='3 ';
$is_dir='4 ';
$is_readable='6 ';
$is_writable='5 ';
$is_link='8 ';
$file='9 ';
$realpath='10 ';
$filemtime='11 ';
$readlink='12 ';
$lstat='13 ';
$stat='14 ';
$posix_getpwuid='15 ';
$posix_getgrgid='16 ';
$copy='17 ';
$rename='18 ';
$unlink='19 ';
$rmdir='20 ';
$file_put_contents='21 ';
$file_get_contents='22 ';
$scandir='23 ';
$filesize='24 ';
$is_file='25 ';
$posix_getuid='26 ';
$symlink='27 ';
$touch='28 ';
$archive='29 ';
$unpack='30 ';

$dirent=array();
$direntname=array();

function myFile_Exists($filename){
     exec($GLOBALS['runas'].$GLOBALS['file_exists'].$filename, $r, $eno);
     if ( $eno == 0 ) return TRUE;
     else return FALSE;
}

function myMkdir ($pathname, $mode="0755", $recursive = FALSE){
     if ( $recursive == TRUE) $rec=1;
     else $rec=0;
     exec($GLOBALS['runas'].$GLOBALS['mkdir'].$pathname.' '.$mode.' '.$rec, $r, $eno);
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myChmod ($filename, $mode) {
     exec($GLOBALS['runas'].$GLOBALS['chmod'].$filename.' '.$mode, $r, $eno);
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myIs_Dir($filename) {
     $r=array_search($filename, $GLOBALS['direntname']);
     if ($r==FALSE) 
         exec($GLOBALS['runas'].$GLOBALS['is_dir'].$filename, $r, $eno);
     else {
         $fileent=explode(" ", $GLOBALS['dirent'][$r]);
         $eno=$fileent[1];
     }
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myIs_Readable($filename) {
     $r=array_search($filename, $GLOBALS['direntname']);
     if ($r==FALSE)
         exec($GLOBALS['runas'].$GLOBALS['is_readable'].$filename, $r, $eno);
     else {
         $fileent=explode(" ", $GLOBALS['dirent'][$r]);
         $eno=$fileent[4];
     }
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myIs_Writable($filename) {
     $r=array_search($filename, $GLOBALS['direntname']);
     if ($r==FALSE)
         exec($GLOBALS['runas'].$GLOBALS['is_writable'].$filename, $r, $eno);
     else {
         $fileent=explode(" ", $GLOBALS['dirent'][$r]);
         $eno=$fileent[5];
     }
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myIs_Link($filename) {
     $r=array_search($filename, $GLOBALS['direntname']);
     if ($r==FALSE)
         exec($GLOBALS['runas'].$GLOBALS['is_link'].$filename, $r, $eno);
     else {
         $fileent=explode(" ", $GLOBALS['dirent'][$r]);
         $eno=$fileent[3];
     }
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myIs_File($filename) {
     $r=array_search($filename, $GLOBALS['direntname']);
     if ($r==FALSE)
         exec($GLOBALS['runas'].$GLOBALS['is_file'].$filename, $r, $eno);
     else {
         $fileent=explode(" ", $GLOBALS['dirent'][$r]);
         $eno=$fileent[2];
     }
     if ($eno==0) return TRUE;
     else return FALSE;
}

function myFile($filename) {
    exec($GLOBALS['runas'].$GLOBALS['file'].$filename, $r, $eno);
    if ($eno != 0 ) return FALSE;
    for ($i=0; $i<sizeof($r); $i++)
	$r[$i]=$r[$i]."\n";
    return $r;
}

function myRealpath($path) {
    exec($GLOBALS['runas'].$GLOBALS['realpath'].$path, $r, $eno);
    if ($eno != 0) FALSE;
    else return $r[0];
}

function myFilemtime($filename) {
    exec($GLOBALS['runas'].$GLOBALS['filemtime'].$filename, $r, $eno); 
    if ($eno !=0) return FALSE;
    else return $r[0];
}

function myReadlink($filename) {
    exec($GLOBALS['runas'].$GLOBALS['readlink'].$filename, $r, $eno);
    if ($eno!=0) return FALSE;
    else return $r[0];
}

function myLstat($filename) {
    exec($GLOBALS['runas'].$GLOBALS['lstat'].$filename, $r, $eno);
    if ($eno!=0) return FALSE;
    $res=array($r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8],$r[9],$r[10],$r[11],$r[12],
               'dev'=>$r[0], 'ino'=>$r[1], 'mode'=>$r[2], 'nlink'=>$r[3], 'uid'=>$r[4],
               'gid'=>$r[5], 'rdev'=>$r[6], 'size'=>$r[7], 'atime'=>$r[8], 'mtime'=>$r[9], 
	       'ctime'=>$r[10], 'blksize'=>$r[11], 'blocks'=>$r[12]);
    return $res;
}
function myStat($filename) {
    $key=array_search($filename, $GLOBALS['direntname']);
    if ($key==FALSE) {
        exec($GLOBALS['runas'].$GLOBALS['stat'].$filename, $r, $eno);
        if ($eno!=0) return FALSE;
    } else {
        $fileent=explode(" ", $GLOBALS['dirent'][$key]);
        $r=array_slice ($fileent, 7);
    }
    $res=array($r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8],$r[9],$r[10],$r[11],$r[12],
               'dev'=>$r[0], 'ino'=>$r[1], 'mode'=>$r[2], 'nlink'=>$r[3], 'uid'=>$r[4],
               'gid'=>$r[5], 'rdev'=>$r[6], 'size'=>$r[7], 'atime'=>$r[8], 'mtime'=>$r[9],
               'ctime'=>$r[10], 'blksize'=>$r[11], 'blocks'=>$r[12]);
    return $res;
}
function myPosix_Getpwuid($uid) {
    return posix_getpwuid($uid);
}
function myPosix_Getgrgid($gid) {
    return posix_getgrgid($gid);
}
function myPosix_Getuid() {
    $r=posix_getpwnam($GLOBALS['uname']);
    return $r['uid'];
}
function myCopy($src, $dest){
    // $f=str_replace(" ","_", $dest);
    exec($GLOBALS['runas'].$GLOBALS['copy'].$src.' '.$dest, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}
function myRename($old, $new){
    // $f=str_replace(" ","_", $new);
    exec($GLOBALS['runas'].$GLOBALS['rename'].$old.' '.$new, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}

function myUnlink($filename){
    exec($GLOBALS['runas'].$GLOBALS['unlink'].$filename, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}

function myRmdir($dirname){
    exec($GLOBALS['runas'].$GLOBALS['rmdir'].$dirname, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}

function mySymlink($old, $new){
    exec($GLOBALS['runas'].$GLOBALS['symlink'].$old.' '.$new, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}

function myFilesize($filename){
    $r=array_search($filename, $GLOBALS['direntname']);
    if ($r==FALSE)
        exec($GLOBALS['runas'].$GLOBALS['filesize'].$filename, $r, $eno);
    else {
        $fileent=explode(" ", $GLOBALS['dirent'][$r]);
        return $fileent[6];
    }    
    if ($eno!=0) return FALSE;
    else return $r[0];
}

function myBasename($str, $ext='')  // 取文件名，不要后缀
{
    $n = strripos($str, '/');
    if ($n !== FALSE)
        $str = substr($str, $n + 1);
    if ($ext != '') {
        $n = strripos($str, $ext);
        $str = substr($str, 0, $n);
    }
    return $str;
}

function myDirname($str)
{
    $n = strripos($str, '/');
    if ($n !== FALSE)
        return substr($str, 0, $n);
    else
        return '.';
}

function myScandir($dir, $order='0') {
    exec($GLOBALS['runas'].$GLOBALS['scandir'].$dir.' '.$order, $r, $eno);
    if ($eno!=0) return FALSE;
    $GLOBALS['dirent']=$r;
    $ret=array();
    $GLOBALS['direntname']=array();
    $i=0;
    foreach ($r as $ent) {
       $filestring=explode(' ', $ent);
       array_push($GLOBALS['direntname'], $filestring[0]);
       $ret[$i]=myBasename($filestring[0]);
       $i++;
    }
    return $ret;
}

function myFile_Get_Contents($filename) {
    exec($GLOBALS['runas'].$GLOBALS['file_get_contents'].$filename, $r, $eno);
    if ($eno!=0) return FALSE;
    $res=file_get_contents($r[0]);
    myUnlink($r[0]);
    return $res;
}

function rFile_Get_Contents($filename) {
    global $runas;
    $origr = $runas;
    $runas='source /var/www/html/env.sh;export OLWD=1As_ap_~;/var/www/html/cmd/runas root ';
    $res = myFile_Get_Contents($filename);
    $runas = $origr;
    return $res;
}

function myFile_Put_Contents($filename, $data, $flags=0) {
    $tmpfname=tempnam("/tmp", "OLGUI");
    $n=file_put_contents($tmpfname, $data, $flags);
    chmod ($tmpfname, 0644);
    // $f=str_replace(" ","_", $filename);
    $r=myCopy($tmpfname, $filename);
    unlink($tmpfname);
    if ($r==FALSE) return FALSE;
    else return $n;
}

function myTouch($filename) {
    exec($GLOBALS['runas'].$GLOBALS['touch'].$filename, $r, $eno);
    if ($eno!=0) return FALSE;
    else return TRUE;
}

/*
 * Alternative to makeArchive(), found in elFinderVolumeDriver.class.php
 * Supports the archiving formats .tar, .tgz, .tbz, .xz, and .zip
 * Called once in elFinderVolumeLocalFileSystem.class.php
 *
 * Arguments:
 *   $dir: directory to the archived files
 *   $files: array of the file names to be archived
 *   $name: name of the archive
 *   $arc: array storing information about the archiving type - the command, operations, and file extension
 * Returns: either the path if the archive was created successfully; otherwise FALSE 
 */
function myMakeArchive($dir, $files, $name, $arc) { 
    $argc = ($arc['cmd'] == 'zip') ? $arc['argc'] : substr($arc['argc'], 1);			// tar operations do not need to be preceded by the - symbol
    exec($GLOBALS['runas'].$GLOBALS['archive'].$dir.' "'.$arc['cmd'].' '.$argc.' '.$name.' '.implode(' ', $files).'"', $r, $eno);	// args: $dir, $cmd
    if($eno!=0) return FALSE;
    $path = $dir.DIRECTORY_SEPARATOR.$name;
    return myFile_Exists($path) ? $path : FALSE;
}

/*
 * Alternative to unpackArchive(), found in elFinderVolumeDriver.class.php
 * Supports the archiving formats .tar, .tz, .tbz, .xz, and .zip
 * Called once in elFinderVolumeLocalFileSystem.class.php
 *
 * Arguments:
 *   $path: path to the archive
 *   $arc: see definition in myMakeArchive()
 *   $remove: unused; kept for legacy purposes
 * Returns: void
 */
function myUnpackArchive($path, $arc, $remove = true) {
    $dir = dirname($path);
    $argc = ($arc['cmd'] == 'zip') ? $arc['argc'] : substr($arc['argc'], 1);			// tar operations do not need to be preceded by the - symbol
    exec($GLOBALS['runas'].$GLOBALS['unpack'].$dir.' "'.$arc['cmd'].' '.$argc.' '.escapeshellarg(basename($path)).'"', $r, $eno);	// args: $dir, $cmd
    myUnlink($path);			// removes the duplicated archive in the quarantined folder
}
?>

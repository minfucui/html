<?php
include 'check_session.php';
include 'filefunctions.php';
include 'folders.php';
include 'projectFunctions.php';  // 获取实例cwd绝对地址
include 'jobsdata.php';

if (!isset($_POST['projectPath'])) {
    echo $error_return;
    die();
}
// 删除实例并杀死删除相关作业
function delete_directory($dirname) {
    $files = myScandir($dirname);
    if (!$files)
        return false;
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (!myIs_Dir($dirname."/".$file))
                myUnlink($dirname."/".$file);
            else
                delete_directory($dirname.'/'.$file);
        }
    }
    myRmdir($dirname);
    return true;
}

foreach (explode(' ', $_POST['projectPath']) as $proj)
{
    global $_SESSION, $cmdPrefix;
    $projectName = myBasename($proj, '.yaml');
    $jobid = runningProject($projectName, $_SESSION['uname']);
    if ($jobid !== FALSE)
        shell_exec($cmdPrefix.' ckill '.$jobid);  // 杀死作业
    $cwd = getProjectCWD($proj);
    if ($cwd)
        delete_directory($cwd);
    myUnlink($proj);

    $folders = folders($uname);
    foreach ($folders as $key=>$value) {
        $n = sizeof($folders[$key]);
        for ($i = 0; $i < $n; $i++) {
            if ($value[$i] == $proj) {
                array_splice($folders[$key], $i, 1);
                break;
            }
        }
        if ($i < $n)  { // deleted
            writeToFolderFile($folders, $uname);
            break;
        }
    }
}
header('Content-Type: application/json');
echo '{"code":0,"message":"successful","data":"success"}';
?>

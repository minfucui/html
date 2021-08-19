<?php
define('WEB_ACCESS', 'webaccess');
define('LOGIN', 'login');
define('LOGOUT', 'logout');
define('CONFIG', 'config');
define('WORK', 'work');

function skylog_activity($user, $cat, $activity, $note = '')
{
    $ret = [];
    $conn = mysqli_connect($GLOBALS['_SESSION']['dbserver'],
                           $GLOBALS['_SESSION']['dbuser'],
                           $GLOBALS['_SESSION']['dbpassword'],
                           "aiphist");
    if ($conn === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }
    $sql = "insert into operation(username,category,notes) values(".
           sqlstr($user).','.sqlstr($cat).','.sqlstr($activity.':'.$note).
           ")";
    if (!mysqli_query($conn, $sql)) {
        $ret['error'] = mysqli_error($conn);
        error_log('logerror:'.$ret['error']);
    }
    mysqli_close($conn);
    return $ret;
}

function searchRecentActivity()
{
    $ret = [];
    $conn = mysqli_connect($GLOBALS['_SESSION']['dbserver'],
                           $GLOBALS['_SESSION']['dbuser'],
                           $GLOBALS['_SESSION']['dbpassword'],
                           "aiphist");
    if ($conn === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }
    $sql = "select * from operation order by id desc limit 1000";
    $res = mysqli_query($conn, $sql);
    if (!$res)
        $ret['error'] = mysqli_error($conn);
    if (mysqli_num_rows($res) > 0) {
        while ($record = mysqli_fetch_assoc($res)) {
            $ret[] = $record;
        }
    }
    return $ret;
}
?>

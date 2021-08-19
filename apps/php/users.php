<?php
include 'common1.php';

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname;
    $lang = $_SESSION['lang'];
    $users = [];
    $cout = shell_exec($cmdPrefix.'./uinfo');
    if (($udata = json_decode($cout, TRUE)) === FALSE)
        return $users;

    foreach($udata as $u) {
        $user = [];
        $user['name'] = '<a href="javascript:openurl(\'php/jobs.php?user='.$u['USER'].
                        '\',\'作业\',\'\');">'.$u['USER'].'</a>';
        $user['isgroup'] = $u['ISGROUP'] == 'y' ? $lang['WORDYES'] : $lang['WORDNO']; 
        $user['njobs'] = $u['NUM_JOBS'];
        $user['nrun'] = $u['NUM_RUNNING_JOBS'];
        $user['npend'] = $u['NUM_PENDING_JOBS'];
        $user['nstop'] = $u['NUM_SUSP_JOBS'];
        $user['nrsv'] = $u['NUM_RESERVED_SLOTS'];
        $user['maxj'] = $u['MAX_JOB_SLOTS'];
        if ($user['maxj'] == 'Unlimited')
            $user['maxj'] = $lang['UNLIMITED'];
        $users[] = $user;
    }      
    return $users;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./users.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateData();
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

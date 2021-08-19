<?php
include 'common1.php';
include 'filefunctions.php';
$lang = $_SESSION['lang'];

function updateShares()
{
    global $_SESSION, $cmdPrefix, $uname;
    $s = searchRows('shares', ['status'=>'open', 'type'=>'file']);
    
    if (sizeof($s) == 0 || isset($s['error']))
        return $s;
    $shares = [];
    foreach ($s as $share) {
        $users = explode(' ', $share['targets']);
        if (!in_array($uname, $users))
            continue;
        $ele = [];
        $ele['file'] = $share['pathjobid'];
        $ele['description'] = $share['description'];
        $ele['targets'] = $share['owner'];
        $ele['share'] = '<a href="javascript:extract_file(\''.$share['id'].
                '\');">'.$_SESSION['lang']['EXTRACT'].'</a>';
        $ele['id'] = $share['id'];
        $ele['last_update'] = $share['last_update'];
        $shares[] = $ele;
    }
    foreach ($s as $share) {
        if ($uname != $share['owner'])
            continue;
        $ele = [];
        $ele['file'] = $share['pathjobid'];
        $ele['description'] = $share['description'];
        $ele['targets'] = $share['targets'];
        $ele['share'] = strval($share['nshared']).'个用户已提取';
        $ele['id'] = $share['id'];
        $ele['last_update'] = $share['last_update'];
        $shares[] = $ele;
    }
    return $shares;
}

function validUsers()
{
    global $uname;
    $users = searchRows('users');
    $targets = [];
    if (sizeof($users) > 0 && !isset($users['error']))
        foreach($users as $u)
            if ($u['roles'] != '保密员' &&
                $u['roles'] != '审计员' &&
                $u['roles'] != '管理员' && $u['username'] != $uname)
                $targets[] = $u['username'];
    return $targets;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file(basename($_SERVER['PHP_SELF'], '.php').'.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $page['rows'][1]['file']['path'] = $_SESSION['home'];
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateShares();
        $ret['data']['options'] = validUsers();
        break;
    case 'new_share':
        if ($_POST['data']['share_path'] == '' ||
            $_POST['data']['targets'] == '') {
            fail('ALL_REQUIRED');
        }
        $vusers = validUsers();
        $tusers = explode(' ', $_POST['data']['targets']);
        foreach($tusers as $u)
            if (!in_array($u, $vusers))
                fail($u.'：用户不存在');
        insertARow('shares',['id'=>hexdec(uniqid()), 'owner'=>$uname,
                             'type'=>'file',
                             'targets'=>$_POST['data']['targets'],
                             'description'=>$_POST['data']['description'],
                             'pathjobid'=>$_POST['data']['share_path'],
                             'status'=>'open',
                             'ntarget'=>sizeof($tusers),
                             'nshared'=>0
                             ]);
        break;
    case 'extract':
        if ($_POST['data']['id'] == '' ||
            $_POST['data']['todir'] == '')
            fail('Wrong request');
        $e = searchRows('shares', ['id'=>$_POST['data']['id']]);
        if (sizeof($e) != 1 || isset($e['error']))
            fail('cannot find the share');
        $source = $e[0]['pathjobid'];
        $target = $_POST['data']['todir'];
        $cmd = 'source /var/www/html/env.sh;export OLWD=a^T21Op_8;/var/www/html/cmd/runas root cp -rf '.$source.' '.$target;
        exec($cmd, $r, $e);
        if ($e != 0 ) {
            fail('提取失败');
        }
        $cmd = 'source /var/www/html/env.sh;export OLWD=a^T21Op_8;/var/www/html/cmd/runas root chown -R '.$uname.' '.$target;
        error_log($cmd);
        exec($cmd, $r, $e);
        if ($e != 0 ) {
            error_log(implode(';', $r));
            fail('提取失败');
        }
        $e[0]['nshared'] = $e[0]['nshared'] + 1;
        if ($e[0]['nshared'] == $e[0]['ntarget'])
            modifyARow('shares', ['id'=>$_POST['data']['id']],
                       ['nshared'=>0, 'status'=>'close']);
        else
            modifyARow('shares', ['id'=>$_POST['data']['id']],
                       ['nshared'=>$e[0]['nshared']]);
        break;
    case 'delete':
        $del = $_POST['data'];
        if (sizeof($del) == 0)
            fail('Wrong request');
        $deleted = 0;
        foreach ($del as $d) {
            $r = searchRows('shares',['id'=>$d]);
            if (sizeof($r) == 0 || isset($r['error']))
                continue;
            if ($r[0]['owner'] != $uname)
                continue;
            deleteARow('shares', ['id'=>$d]);
            $deleted ++;
        }
        if ($deleted == 0)
            fail('只能删除自己的共享');
        break;
    default:
        fail('Wrong request');
        break;
}


echo json_encode($ret);
?>

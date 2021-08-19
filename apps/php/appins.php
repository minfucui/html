<?php
include 'common1.php';
include 'jobsdata.php';
include 'filefunctions.php';
include 'formfunction.php';

$table = 'instances';

function runningIns($insname, $uname)
{
    $myjobs = activeJobData($uname);
    for ($i = 0; $i < sizeof($myjobs); $i++) {
        if (!isset($myjobs[$i]['jobSpec']['jobDescription']) ||
             $myjobs[$i]['jobSpec']['jobDescription'] != $insname)
            unset($myjobs[$i]);
    }
    return array_values($myjobs);
}

function updateInstances($projname = '', $appname = '')
{
    global $_SESSION, $uname, $table;
    $keys = ['username'=>$uname];
    if ($projname != '')
        $keys[] = ['projname'=>$projname];
    if ($appname != '')
        $keys[] = ['appname'=>$appname];

    $ins = searchRows($table, $keys);
    if (sizeof($ins) == 0)
        return $ins;
    if (isset($ins['error'])) {
        error_log('search instance: '.$ins['error']);
        return [];
    }
    $jobs = activeJobData($uname);
    $n = sizeof($ins);
    for ($i = 0; $i < $n; $i++) {
        $ins[$i]['activejobs'] = 0;
    }
    $nj = sizeof($jobs);
    for ($i = 0; $i < $nj; $i++) {
        if (isset($jobs[$i]['jobSpec']['jobDescription']))
            for ($j = 0; $j < $n; $j++)
                if ($ins[$j]['ins_name'] ==
                    $jobs[$i]['jobSpec']['jobDescription'] &&
                    $ins[$j]['projname'] ==
                    $jobs[$i]['jobSpec']['project']) {
                    $ins[$j]['activejobs']++;
                    break;
                }
    }
    for ($i = 0; $i < $n; $i++) {
        $ins[$i]['ins_name'] = $ins[$i]['ins_name'].
              ' php/appform.php?instance='.
              $ins[$i]['ins_name'].'&project='.
              $ins[$i]['projname'];
    }
    return $ins;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./appins.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $page['rows'][1]['file']['path'] = $_SESSION['home'].'/jobdata';
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateInstances();
        break;
    case 'delete_ins':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        foreach($toDelete as $d) {
            /* format of input: xxx.php?instance=insname&project=projname */
            $t = explode('=', $d);
            $i = explode('&', $t[1]);
            $del = $i[0];
            $proj = $t[2];
            $jobs = runningIns($del, $uname);
            if (sizeof($jobs) > 0) {
                $ret['code'] = 200;
                $ret['message'] = '项目'.$del.'有作业在运行';
                break;
            }
            shell_exec($cmdPrefix.' rm -rf '.$_SESSION['home'].'/projects/'.$proj.'/'.$del.'.yaml');
            $res = deleteARow($table, ['username'=>$uname, 'ins_name'=>$del]);
            if (isset($res['error']))
                error_log('delete instance: '.$res['error']);
        }
        break;
    case 'save':
        $app = consYaml($_POST['data']);
        if (sizeof($app) == 0)
            fail('Wrong data in request');
        $filepath = $_SESSION['home'].'/projects/'.$app['cluster_params']['project'].
                    '/'.$app['cluster_params']['instance'].'.yaml';
        $c = yaml_emit($app);
        if (myFile_put_Contents($filepath, $c) === FALSE)
            fail('Cannot create '.$filepath);
        $res = searchRows('instances', ['username'=>$uname,
                  'projname'=>$app['cluster_params']['project'],
                  'ins_name'=>$app['cluster_params']['instance'],
                  'appname'=>$app['appName'],
                  'ins_path'=>$filepath]);
        if (sizeof($res) == 0)
            insertARow('instances', ['id'=>uniqid(), 'username'=>$uname,
                  'projname'=>$app['cluster_params']['project'],
                  'ins_name'=>$app['cluster_params']['instance'],
                  'appname'=>$app['appName'],
                  'ins_path'=>$filepath]);
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}


echo json_encode($ret);
?>

<?php
include 'common1.php';
include 'filefunctions.php';
include 'jobsdata.php';

function options()
{
    global $_SESSION;
    return $_SESSION['classopt'];
}

function updateProjects()
{
    global $_SESSION, $cmdPrefix, $uname;
    $projdir = $_SESSION['home'].'/projects';
    $insdir = $_SESSION['home'].'/jobdata';
    $projects = [];
    if (!myIs_Dir($projdir))
        myMkDir($projdir);
    if (!myIs_Dir($insdir))
        myMkDir($insdir);
    if (!myIs_Dir($insdir.'/default'))
        myMkDir($insdir.'/default');
    $defp = $projdir.'/default';
    if (!myIs_Dir($defp)) {
        myMkDir($defp);
        $c = yaml_emit(['confidential'=>'','description'=>''], YAML_UTF8_ENCODING);
        myFile_Put_Contents($defp.'/.cfg.y', $c);
    }
    
    insertARow('projects', ['id'=>$uname.'-default',
                            'projname'=>'default', 'username'=>$uname,
                            'confidential'=>'', 'description'=>''],
                TRUE);
    $files = myScandir($projdir);
    foreach ($files as $f) {
        $proj = [];
        if ($f[0] == '.')
            continue;
        $proj['name'] = $f;
        $projpath = $projdir.'/'.$f;
        $pinsdir = $insdir.'/'.$f;
        if (!myIs_Dir($pinsdir))
            myMkDir($pinsdir);
        $du = intval(shell_exec($cmdPrefix."/usr/bin/du -k -d 0 ".$pinsdir));
        $proj['storage'] = number_format(floatval($du) * 0.0000009537, 4);
        $conf = $projpath.'/.cfg.y';
        if (myFile_Exists($conf)) {
            $c = myFile_Get_Contents($conf);
            $projConf = yaml_parse($c);
            if ($c !== FALSE) {
                $proj['confidential'] = $projConf['confidential'];
                $proj['description'] = $projConf['description'];
            }
        } else {
            $c = yaml_emit(['confidential'=>'','description'=>''], YAML_UTF8_ENCODING);
            myFile_Put_Contents($conf, $c);
            $proj['confidential'] = '';
            $proj['description'] = '';
        }
        $res = insertARow('projects', ['id'=>$uname.'-'.$f,
                          'projname'=>$f, 'username'=>$uname,
                          'confidential'=>$proj['confidential'],
                          'description'=>$proj['description']],
                          TRUE);
        if (isset($res['error']))
            error_log('insert project: '.$res['error']);
        $proj['numjobs'] = 0;
        $n = 0;
        if (($d = myScandir($projpath)) !== FALSE) {
            foreach ($d as $subd) {
                if ($subd[0] == '.')
                    continue;
                $insname = myBasename($subd, '.yaml');
                $res = searchRows('instances',
                                 ['username'=>$uname, 'projname'=>$f,
                                  'ins_name'=>$insname]);
                if (isset($res['error']))
                    error_log('search instances: '.$res['error']);
                else {
                    $find = 0;
                    for ($k = 0; $k < sizeof($res); $k++)
                        if ($res[$k]['ins_name'] == $insname) {
                            $find = 1;
                            break;
                         }
                    if ($find == 0) {
                        $ins_path = $projpath.'/'.$subd;
                        $c = myFile_Get_Contents($ins_path);
                        $conf = yaml_parse($c);
                        $appname = $conf['appName'];
                        $res = insertARow('instances',
                               ['id'=>uniqid(), 'username'=>$uname,
                                'projname'=>$f, 'ins_name'=>$insname,
                                'appname'=>$appname, 'ins_path'=>$ins_path]);
                        if (isset($res['error']))
                            error_log('insert instances: '.$res['error']);
                    }
                }
                $n++;
            }
        }
        $proj['numinstances'] = $n;
        $updateTime = myFilemtime($projpath);
        $proj['lastmodtime'] = date("Y-m-d H:i:s", $updateTime);
        $projects[] = $proj;
    }
    $n = sizeof($projects);
    $jobs = activeJobData($uname);
    if (sizeof($jobs))
        foreach($jobs as $job) {
            for ($j = 0; $j < $n; $j++) {
                if ($projects[$j]['name'] == $job['jobSpec']['project']) {
                    $projects[$j]['numjobs']++;
                    break;
                }
            }
        }
    return $projects;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./files.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $page['rows'][1]['file']['path'] = $_SESSION['home'];
        $ret['data'] = $page;
        break;

    case 'update':
        $ret['data']['table'] = updateProjects();
        $ret['data']['options'] = ["confidential"=>options()];
        break;
    case 'new_project':
        if ($_POST['data']['name'] == '' || $_POST['data']['description'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else if (preg_match('/'.preg_quote('^\'£$%^&*()}{@#~?><,@|-=-+-¬','/').'/',
                   $_POST['data']['name']) ||
                   strpos($_POST['data']['name'], '/') !== FALSE) {
            $ret['code'] = 200;
            $ret['message'] = 'BAD_PROJECT_NAME';
        } else {
            $name = str_replace(' ', '_', $_POST['data']['name']); 
            if (myFile_Exists($_SESSION['home'].'/projects/'.$name)) {
                $ret['code'] = 200;
                $ret['message'] = 'PROJECT_EXISTS';
                break;
            }
            myMkDir($_SESSION['home'].'/projects/'.$name);
            myMkDir($_SESSION['home'].'/jobdata/'.$name);
            $c = yaml_emit(['confidential'=>$_POST['data']['confidential'],
                            'description'=>$_POST['data']['description']],
                            YAML_UTF8_ENCODING);
            myFile_Put_Contents($_SESSION['home'].'/projects/'.$name.'/.cfg.y', $c);
            $res = insertARow('projects', ['id'=>$uname.'-'.$name,
                            'projname'=>$name, 'username'=>$uname,
                            'confidential'=>$_POST['data']['confidential'],
                            'description'=>$_POST['data']['description']]);
            if (isset($res['error']))
                error_log('insert project: '.$res['error']);
        }
        break;
    case 'delete_projects':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        if (in_array("default", $toDelete))
            fail('default为系统项目，不能删');
        foreach($toDelete as $del) {
            $jobs = runningProject($del, $uname);
            if (sizeof($jobs) > 0) {
                $ret['code'] = 200;
                $ret['message'] = '项目'.$del.'有作业在运行';
                break;
            }
            shell_exec($cmdPrefix.' rm -rf '.$_SESSION['home'].'/projects/'.$del);
            $res = deleteARow('projects', ['id'=>$uname.'-'.$del]);
            if (isset($res['error']))
                error_log('delete project: '.$res['error']);
        }
        break;
    case 'modify_project':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        $file = $_SESSION['home'].'/projects/'.$mod['name'];
        $c = yaml_emit(['confidential'=>$mod['confidential'],
                        'description'=>$mod['description']],
                            YAML_UTF8_ENCODING);
        myFile_Put_Contents($file.'/.cfg.y', $c);
        $res = modifyARow('projects', ['id'=>$uname.'-'.$mod['name']],
                          ['confidential'=>$mod['confidential'],
                           'description'=>$mod['description']]);
        if (isset($res['error']))
            error_log('modify project: '.$res['error']);
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}


echo json_encode($ret);
?>

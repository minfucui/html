<?php
include 'common1.php';
include 'jobsdata.php';

$tablename = 'applications';
$generic = "通用";

function catoptions()
{
    $cats = [];
    $cdata = searchRows('appcat');
    for ($i = 0; $i < sizeof($cdata); $i++)
        $cats[] = $cdata[$i]['catname'];
    return $cats;
}

function puboptions()
{
    return ['否','是'];
}

function updateData()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $rows = searchRows($tablename);
    $n = sizeof($rows);
    for ($i = 0; $i < $n; $i++)
        if ($rows[$i]['published'] == 1)
            $rows[$i]['published'] = '是';
        else
            $rows[$i]['published'] = '否';
    return $rows;
}

function addJobCounts($data)
{
    $n = sizeof($data);
    if ($n == 0)
        return [];
    for ($i = 0; $i < $n; $i++)
        $data[$i]['activejobs'] = 0;
    $jobs = allJobs();
    if (sizeof($jobs) == 0)
        return $data;
    foreach ($jobs as $job) {
        $n = sizeof($data);
        for ($i = 0; $i < $n; $i++) {
             if (isset($job['jobSpec']['application']) &&
                 $data[$i]['appname'] == $job['jobSpec']['application'] &&
                 $job['statusString'] != 'FINISH' &&
                 $job['statusString'] != 'EXIT' ) {
                 $data[$i]['activejobs'] ++;
                 break;
             }
        }
    }
    return $data;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./appmgmt.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
            break;
        }
        $page['rows'][0]['table']['data'] = addJobCounts($result);
        $page['rows'][0]['table']['options'] = ['catname' => catoptions(),
                                 'published'=>puboptions()];
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else
            $ret['data']['table'] = addJobCounts($result);
            $ret['data']['options'] = ['catname' => catoptions(),
                                       'published'=>puboptions()];
        break;
    case 'new_app':
        $rec = $_POST['data'];
        if ($rec['appname'] == '' || $rec['confpath'] == '' ||
            yaml_parse_file($rec['confpath']) === FALSE) {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else {
            $exist = searchRows($tablename, ["appname"=>$rec['appname']]);
            if (sizeof($exist) > 0) {
                $ret['code'] = 200;
                $ret['message'] = 'NAME_EXISTS';
                break;
            }
            if ($rec['published'] = '是')
                $ret['published'] = 1;
            else
                $ret['published'] = 0;
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
            }
        }
        break;
    case 'delete_apps':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        foreach($toDelete as $del) {
            $rec = searchRows($tablename, ["appname"=>$del]);
            if ($rec['0']['published'] != 0) {
                $ret['code'] = 200;
                $ret['message'] = '不能删除已上线应用';
                break;
            }
            $result = deleteARow($tablename, ["appname"=>$del]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
            if (isset($rec['error'])) {
                error_log($rec['error']);
                break;
            } else {
                $file = $apppath.'/'.$rec[0]['confpath'];
                if (file_exists($file))
                    rename($file, $file.'.bak');
            }
        }
        break;
    case 'modify_app':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        if ($mod['published'] == '是')
            $mod['published'] = 1;
        else
            $mod['published'] = 0;
        $result = modifyARow($tablename, ["appname"=>$mod['appname']], $mod);
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        }
        break;
    case 'publish_apps':
        $toPub = $_POST['data'];
        if (sizeof($toPub) < 1)
            break;
        foreach($toPub as $item) {
            $result = modifyARow($tablename, ["appname"=>$item],
                                  ["published"=>1]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    case 'unpublish_apps':
        $toPub = $_POST['data'];
        if (sizeof($toPub) < 1)
            break;
        foreach($toPub as $item) {
            $result = modifyARow($tablename, ["appname"=>$item],
                                  ["published"=>0]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

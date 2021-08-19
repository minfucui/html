<?php
include 'common1.php';

$tablename = 'appcat';
$generic = "通用";

function updateAppCats()
{
    global $_SESSION, $cmdPrefix, $uname, $tablename, $generic;
    $appcats = searchRows($tablename);
    $apps = searchRows('applications');
    if (sizeof($appcats) == 0) {
        $appcats = [];
        $appcats[] = ["catname"=>$generic,"description"=>""];
        $result = insertARow($tablename, $appcats[0]);
        if (isset($result['error']))
            return $result;
    }
    $ncats = sizeof($appcats);
    for ($i = 0; $i < $ncats; $i++) {
        $appcats[$i]['numapps'] = 0;
    }
    $napps = sizeof($apps);
    for ($i = 0; $i < $napps; $i++) {
        for($j = 0; $j < $ncats; $j++) {
            if ($apps[$i]['catname'] == $appcats[$j]['catname']) {
                 $appcats[$j]['numapps']++;
                 break;
            }
        }
    }
    return $appcats;
}

$data = [];
switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./appcatmgmt.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateAppCats();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
            break;
        }
        $page['rows'][0]['table']['data'] = $result;
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateAppCats();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else
            $ret['data']['table'] = $result;
        break;
    case 'new_appcat':
        $rec = $_POST['data'];
        if ($rec['catname'] == '' || $rec['description'] == '') {
            $ret['code'] = 200;
            $ret['message'] = 'ALL_REQUIRED';
        } else {
            $exist = searchRows($tablename, ["catname"=>$rec['catname']]);
            if (sizeof($exist) > 0) {
                $ret['code'] = 200;
                $ret['message'] = 'NAME_EXISTS';
                break;
            }
            $result = insertARow($tablename, $rec);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
            } else
                skylog_activity($uname, CONFIG, 'add appcat', $rec['catname']);
        }
        break;
    case 'delete_appcats':
        $toDelete = $_POST['data'];
        if (sizeof($toDelete) < 1)
            break;
        foreach($toDelete as $del) {
            if ($del == $generic) {
                $ret['code'] = 200;
                $ret['message'] = 'UNDELETABLE';
                break;
            }
            $result = deleteARow($tablename, ["catname"=>$del]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
            $result = modifyARow('applications', ["catname"=>$del], ["catname"=>$generic]);
            if (isset($result['error'])) {
                $ret['code'] = 500;
                $ret['message'] = $result['error'];
                break;
            }
        }
        if ($ret['code'] == 0)
            skylog_activity($uname, CONFIG, 'del appcat', implode(' ', $toDelete));
        break;
    case 'modify_appcat':
        $mod = $_POST['data'];
        if (sizeof($mod) < 1)
            break;
        $result = modifyARow($tablename, ["catname"=>$mod['catname']], $mod);
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else
            skylog_activity($uname, CONFIG, 'mod appcat', $rec['catname']);
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

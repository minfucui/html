<?php
include 'common1.php';

$tablename = 'permissions';

function updateData()
{
    global $_SESSION, $tablename;
    $lang = $_SESSION['lang'];
    $permissions = [];
    

    $rows = searchRows($tablename);
    if (($nrows = sizeof($rows)) == 0) {
        return permissions;
    }
    if (isset($rows['error']))
        return $rows;
    $apps = searchRows('applications');
    foreach($apps as $app) {
        $scope = 'app'.$lang['APP_NAME'].'-'.$app['appname'];
        $p = searchRows($tablename, ['idvalue'=>$scope]);
        if (!isset($p['error']) && sizeof($p) == 0) {
            $r = insertARow($tablename, ['idvalue'=>$scope,
                        'roles_permitted'=>implode(' ', 
                             [$lang['ADMIN'], $lang['GROUP_ADMIN'],
                              $lang['USER']])
                        ]);
            if (isset($r['error']))
                fail($r['error']); 
        } else if (isset($p['error']))
            fail($p['error']);
    }

    $checkboxb = '<input class="checkbox" type="checkbox" id="';
    $checkboxe_checked = '" checked>';
    $checkboxe = '">';
    foreach($rows as $row) {
        $id = str_replace('+', '-', $row['idvalue']);
        $perm = [];
        if (($n = strpos($row['idvalue'], 'side_menu')) !== FALSE)
            $perm['scope'] = substr($row['idvalue'], 9);
        else if (($n = strpos($row['idvalue'], 'app')) !== FALSE)
            $perm['scope'] = substr($row['idvalue'], 3);
        else
            $perm['scope'] = $row['idvalue'];
        $permitted = explode(' ', $row['roles_permitted']);
        $perm['admin'] =
             $checkboxb.$id.'_admin'.$checkboxe;
        $perm['group_admin'] =
             $checkboxb.$id.'_group_admin'.$checkboxe;
        $perm['user'] =
             $checkboxb.$id.'_user'.$checkboxe;
        $perm['confidential_admin'] =
             $checkboxb.$id.'_confidential_admin'.$checkboxe;
        $perm['auditor'] =
             $checkboxb.$id.'_auditor'.$checkboxe;
        foreach ($permitted as $role) {
            switch ($role) {
            case $lang['ADMIN']:
                $perm['admin'] =
                   $checkboxb.$id.'_admin'.$checkboxe_checked;
                break;
            case $lang['GROUP_ADMIN']:
                $perm['group_admin'] =
                   $checkboxb.$id.'_group_admin'.$checkboxe_checked;
                break;
            case $lang['USER']:
                $perm['user'] =
                   $checkboxb.$id.'_user'.$checkboxe_checked;
                break;
            case $lang['CONFIDENTIAL_ADMIN']:
                $perm['confidential_admin'] =
                   $checkboxb.$id.'_confidential_admin'.$checkboxe_checked;
                break;
            case $lang['AUDITOR']:
                $perm['auditor'] =
                   $checkboxb.$id.'_auditor'.$checkboxe_checked;
                break;
            default:
                break;
            }
        }
        $perm['last_update'] = $row['last_update'];
        $permissions[] = $perm;
    }
    return $permissions;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./roles.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
            break;
        }
        $page['rows'][0]['table']['data'] = $result;
        $ret['data'] = $page;
        break;

    case 'update':
        $result = updateData();
        if (isset($result['error'])) {
            $ret['code'] = 500;
            $ret['message'] = $result['error'];
        } else {
            $ret['data']['table'] = $result;
        }
        break;
    case 'save':
        $rec = $_POST['data'];
        foreach ($rec as $row) {
            $res = modifyARow($tablename, ['idvalue'=>$row['idvalue']],
                                          ['roles_permitted'=>$row['roles_permitted']]);
            if (isset($res['error']))
                fail ($res['error']);
        }
        skylog_activity($uname, CONFIG, "update permissions", "");
        break;
    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>

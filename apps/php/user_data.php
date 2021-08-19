<?php

function get_user_data($user) {
    $userdata = searchRows("users", ["username"=>$user]);
    if (isset($userdata['error']))
        return $userdata;
    if (sizeof ($userdata))
        return $userdata[0];
    else
        return [];
}

function permit($perm, $roles, $permitID)
/* $perm: data from DB table permissions
   $roles: array of roles of the user
   $permitID: the ID of an iterm that the permission applies
   Return: if $permitID is permitted
 */
{
    $idx = -1;
    $n = sizeof($perm);
    for ($i = 0; $i < $n; $i++) {
        if ($permitID == $perm[$i]['idvalue']) {
            $idx = $i;
            break;
        }
    }          
    if ($idx == -1)
        return TRUE;
    $itemperm = $perm[$idx]['roles_permitted'];
    foreach ($roles as $role)
        if (strpos($itemperm, $role) !== FALSE)
             return TRUE;
    return FALSE;
}

function add_user_data($user)
{
    return insertARow("users", $user);
}

function ugname()
{
    global $uname;
    $user = searchRows("users", ["username"=>$uname]);
    $group = searchRows("usergroups", ["groupname"=>$user[0]['groupname']]);
    if (!isset($group['error']) && sizeof($group) > 0) {
        // I belong to a group
        if ($group[0]['groupadmin'] != $uname) {
            // but I am not the group admin
            return "";
        } else
            return $group[0]['groupname'];
    }
    return $uname;
}
?>

<?php
function folders($user)
{
    global $datapath;
    $ffile = $datapath.'/data/'.$user.'_folders.json';
    if (!file_exists($ffile) ||
       ($folders_data = file_get_contents($ffile)) === FALSE) {  // 把整个文件读入到一个字符串中
        return [];
    } else {
        return json_decode($folders_data, TRUE);
    }
}

function writeToFolderFile($folders, $user)
{
    global $datapath;
    $ffile = $datapath.'/data/'.$user.'_folders.json';
    file_put_contents($ffile, json_encode($folders));  // 写文件
}

function namePlusOne($a)
{
    $s=explode("(",$a);  // 把字符串打散为数组，以(为分隔符
    $name = $s[0];
    if (sizeof($s) == 1)
        return $name.'(1)';
    sscanf($s[1], "%d)", $num);  // 类似printf的输出形式，以第二个参数的形式解析第一个参数
    $num = $num + 1;
    return $name.'('.$num.')';
}

function newFolder($folderName, $user)
{
    $folders = folders($user);
    if (isset($folders[$folderName])) {
        $folderName = namePlusOne($folderName);
    }   
    $folders[$folderName] = [];
    writeToFolderFile($folders, $user);
    return TRUE;
}

function renameFolder($folderName, $newName, $user)
{
    $folders = folders($user);
    if (!isset($folders[$folderName]))
        return FALSE;
    $temp = $folders[$folderName];
    unset($folders[$folderName]);
    if ($newName !== FALSE)
        $folders[$newName] = $temp;
    writeToFolderFile($folders, $user);
    return TRUE;
}

function deleteFolder($folderName, $user)
{
    return renameFolder($folderName, FALSE, $user);
}

function addProjToFolder($folderName, $user, $projPath)
{
    $folders = folders($user);
    if (!isset($folders[$folderName]))
        return FALSE;
    $folders[$folderName][] = $projPath;
    writeToFolderFile($folders, $user);
    return TRUE;
}

function deleteProjFromFolder($folderName, $user, $projPath)
{
    $folders = folders($user);
    if (!isset($folders[$folderName]))
        return FALSE;
    for($i = 0; $i < sizeof($folders[$folderName]); $i++)
        if ($folders[$folderName][$i] == $projPath) {
            array_splice($folders[$folderName], $i, 1);  // 移除元素，并用新元素替代它（如果有），参数分别为数组、起始位置、长度、替代元素
            break;
        }
    writeToFolderFile($folders, $user);
    return TRUE;
}
?>

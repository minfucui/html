<?php
function get_sub()  // 从文件中读取app列表
{
    $filename = $_SESSION['home'].'/projects/.apps';
    if (!myFile_Exists($filename))
        return [];
    if (($cont = myFile_Get_Contents($filename)) == NULL)
        return [];
    return explode(',', $cont);  // 读取应用列表字符串，并以'，'为分隔符打散
}

function put_sub($apps)  // 存图标，这里应该是应用订阅里的保存功能，继续保存到.apps文件中
{
    $filename = $_SESSION['home'].'/projects/.apps';
    myFile_Put_Contents($filename, implode(',', $apps));  // 保存在隐藏文件中
}

?>

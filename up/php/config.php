<?php
$uname=$_SESSION['uname'];
exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);  // 获取该用户的home地址
if ($errno == 0)
    $_SESSION['home'] = $r[0];
else
    $_SESSION['home'] = '/tmp';

$config = FALSE;
if (file_exists('config.yaml'))
    $config = yaml_parse_file('config.yaml');
if ($config === FALSE || !isset($config['gui_project_job_limit'])
    || intval($config['gui_project_job_limit']) != 1)  // intval()函数用于获得参数变量的整数值
    $gui_limit = -1;
else
    $gui_limit = 1;  // 图形实例每个用户只能同时运行1个作业
$_SESSION['gui_limit'] = $gui_limit;
if (isset($config['data_home'])) {
    if (strpos($config['data_home'], '$USER') !== FALSE) // strpos()查找第二个参数在字符串中第一次出现的位置
        $_SESSION['data_home'] = str_replace('$USER', $uname, $config['data_home']);
    if (strpos($config['data_home'], '$HOME') !== FALSE)
        $_SESSION['data_home'] = str_replace('$HOME', 
                 $_SESSION['home'], $config['data_home']);  // 用到了开头的用户home地址
}
else
    $_SESSION['data_home'] = $_SESSION['home'];
if (isset($config['licenses']) && isset($config['lmstat'])) { 
    $_SESSION['licenses'] = $config['licenses'];  // 没用到
    $_SESSION['lmstat'] = $config['lmstat'];
}
else
    $_SESSION['licenses'] = [];

if (isset($config['download']) && sizeof($config['download']) > 0)
    $_SESSION['download'] = $config['download'];
else
    $_SESSION['download'] = [];
if (isset($config['title']))
    $_SESSION['title'] = $config['title'];
else
    $_SESSION['title'] = 'SkyForm工业仿真云';
if (isset($config['forum']))
    $_SESSION['forum'] = '1';
if (isset($config['op_control']))
   $_SESSION['op_control'] = $config['op_control'];
if (isset($config['job_size_list']))  // 作业大小
   $_SESSION['job_size_list'] = $config['job_size_list'];
$_SESSION['config'] = $config;
?>

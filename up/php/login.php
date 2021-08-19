<?php
session_start();
if (!isset($_GET['username']) || !isset($_GET['password'])) {
    echo '{"code":201,"message":"Wrong call"}';
    die();
}
$uname = $_GET['username'];
$pword = $_GET['password'];

exec("export OLWD=".$pword.";source ../../env.sh;../../cmd/runas ".$uname,  // exec()函数用于执行外部命令，用户认证，这里的目的是将后续的作业处理等aip命令以当前登录用户的名义执行，
     $out, $errno);                                                         // 具体的步骤，先export把密码放进环境变量里，source一下命令运行的环境，runas识别用户，cmdprefix命令预配置

if ($errno == '0') {
    $_SESSION['login'] = "1";  // $_SESSION全局变量，很多文件都会用到，这里1表示已登录
    $_SESSION['uname'] = $uname;
    $_SESSION['password'] = $pword;
    if (file_exists('../config.yaml')) {
        $config = yaml_parse_file('../config.yaml');
        if(isset($config['op_control'])) {  // 貌似是应用权限控制，用到很少
            $_SESSION['dbserver'] = 'localhost';
            $_SESSION['dbuser'] = 'root';
            $_SESSION['dbpassword'] = 'password';
            include 'db.php';  // 数据库操作接口，提供下面的searchRows等函数，1.0其实用到了MySQL数据库，不过数据库里存的主要是账户余额信息
            $users = searchRows('users', ['username'=>$uname]);
            if (sizeof($users) == 0)
                insertARow('users', ['username'=>$uname, 'roles'=>$_SESSION['lang']['USER'], 'acctstatus'=>'normal']);  // 插入一条数据
        }
    }
    echo '{"code":0,"data":{"username":"'.$uname.'"},"message":"login success"}';
} else if ($errno == '255') {
    echo '{"code":301,"message":"Invalid license"}';
} else {
    echo '{"code":401}';
}
?>

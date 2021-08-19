<?php
    echo '{"code":0}';
    session_start();
    session_destroy();  // 关闭seesion，回到index页面就是登出了
    session_start();
?>

<?php
$db_host = '127.0.0.1';
$db_port = '17783';
$db_user = '127_0_0_1_1145';
$db_pw = 'AAA';
$db_name = '127_0_0_1_1145';
$con = mysqli_connect($db_host, $db_user, $db_pw, $db_name);
if (!$con) {
    die('连接失败：'.mysqli_connect_error());
}
mysqli_set_charset($con, 'utf8mb4');
echo '数据库连接成功！';
?>
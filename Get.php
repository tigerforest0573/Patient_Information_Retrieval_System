<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// 记录脚本开始时间
$start_time = microtime(true);
include('mysql_con.php');              // 数据库连接
include('function/encrypt.php');       // 其余函数
include('function/check.php');
include('function/XSSProtection.php');
error_reporting(E_ALL & ~E_NOTICE);
/* 取 POST 并做合法性校验*/
if (!isset($_POST['QueryName'])) {
    exit('Unauthorized Access!');
}
$query_input = RemoveXSS($_POST['QueryName']);
$query_input = htmlspecialchars($query_input, ENT_QUOTES);
if ($query_input === '') {
    echo '<tr><th scope="row">注意</th><td colspan="3">查询失败，输入内容为空</td></tr>';
    exit;
}
/*判断输入类型并构建 SQL 查询 */
mysqli_set_charset($con, "utf8");       // 防止中文乱码
$sql = "";
$param_type = "";
$param_value = "";

// 定义要查询的数组
$queries = array();

// 检查是否为纯数字
if (ctype_digit($query_input)) {
    // 尝试按 patient_id 查询（精确匹配）
    $queries[] = array(
        "sql" => "SELECT * FROM patient WHERE patient_id = ?",
        "param_type" => "s",
        "param_value" => $query_input
    );
} elseif (isAllChinese($query_input)) {
    // 尝试按 patient_name 精确查询
    $queries[] = array(
        "sql" => "SELECT * FROM patient WHERE patient_name = ?",
        "param_type" => "s",
        "param_value" => $query_input
    );
    
    // 再尝试按 patient_data 模糊查询
    $queries[] = array(
        "sql" => "SELECT * FROM patient WHERE patient_data LIKE ?",
        "param_type" => "s",
        "param_value" => "%$query_input%"
    );
} else {
    // 既不是纯数字也不是全中文，执行模糊搜索
    $queries[] = array(
        "sql" => "SELECT * FROM patient WHERE patient_data LIKE ?",
        "param_type" => "s",
        "param_value" => "%$query_input%"
    );
}

// 标记是否找到结果
$found_results = false;
$result_data = array();

// 执行查询队列，直到找到结果或查询全部执行完毕
foreach ($queries as $query) {
    $sql = $query["sql"];
    $param_type = $query["param_type"];
    $param_value = $query["param_value"];
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        echo '<tr><th scope="row">错误</th><td colspan="3">SQL 预处理失败: '.mysqli_error($con).'</td></tr>';
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, $param_type, $param_value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        echo '<tr><th scope="row">错误</th><td colspan="3">'.mysqli_error($con).'</td></tr>';
        mysqli_stmt_close($stmt);
        continue;
    }
    
    if ($result->num_rows > 0) {
        // 找到结果，保存数据并设置标记
        while ($row = mysqli_fetch_assoc($result)) {
            $result_data[] = $row;
        }
        $found_results = true;
        mysqli_stmt_close($stmt);
        break; // 找到结果后退出循环
    }
    
    mysqli_stmt_close($stmt);
}

/*3. 输出表格数据 */
if (!$found_results) {
    echo '<tr><th scope="row">SORRY</th><td colspan="3">未查询到相关记录</td></tr>';
} else {
    $column = 1;
    foreach ($result_data as $row) {
        echo '<tr>';
        echo '<th scope="row">'.$column.'</th>';
        echo '<td>'.htmlspecialchars($row['patient_name']).'</td>';
        echo '<td>'.htmlspecialchars($row['patient_id']).'</td>';
        echo '<td>'.htmlspecialchars($row['patient_data']).'</td>';
        echo '</tr>';
        $column++;
    }
}

/*4.统计 PHP 脚本耗时*/
$end_time = microtime(true);
$elapsed_time = round(($end_time - $start_time) * 1000, 2); // 转换为毫秒并保留两位小数
/* 5. 把耗时追加一行 */
echo '<tr><th scope="row">耗时</th><td colspan="3">'.$elapsed_time.' ms</td></tr>';
mysqli_close($con);
?>
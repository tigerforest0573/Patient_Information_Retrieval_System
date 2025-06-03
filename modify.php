<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// 记录脚本开始时间
$start_time = microtime(true);

include('mysql_con.php');              // 数据库连接

include('function/check.php');
include('function/XSSProtection.php');

error_reporting(E_ALL & ~E_NOTICE);
mysqli_set_charset($con, "utf8");       // 防止中文乱码

//0. 初始化返回消息
$response_message = "";

//1. 获取操作类型和数据
if (!isset($_POST['action'])) {
    exit('错误：未指定操作类型 (action)。');
}
$action = RemoveXSS($_POST['action']);
$action = htmlspecialchars($action, ENT_QUOTES);

/*2. 根据操作类型执行相应逻辑*/
switch ($action) {
    case 'add':
        // --- 新增记录 ---
        if (!isset($_POST['patient_id'], $_POST['patient_name'], $_POST['patient_data'])) {
            $response_message = '错误：新增操作缺少必要的字段 (patient_id, patient_name, patient_data)。';
            break;
        }
        $patient_id   = RemoveXSS($_POST['patient_id']);
        $patient_name = RemoveXSS($_POST['patient_name']);
        $patient_data = RemoveXSS($_POST['patient_data']);

        $patient_id   = htmlspecialchars($patient_id, ENT_QUOTES);
        $patient_name = htmlspecialchars($patient_name, ENT_QUOTES);
        $patient_data = htmlspecialchars($patient_data, ENT_QUOTES);

        if (empty($patient_id) || empty($patient_name) || empty($patient_data)) {
            $response_message = '错误：patient_id, patient_name, patient_data 不能为空。';
            break;
        }

        $sql = "INSERT INTO patient (patient_id, patient_name, patient_data) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            $response_message = '错误：SQL 预处理失败: ' . mysqli_error($con);
            break;
        }
        mysqli_stmt_bind_param($stmt, "sss", $patient_id, $patient_name, $patient_data);
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $response_message = '成功：记录已成功添加。';
            } else {
                $response_message = '注意：记录未添加（可能已存在或无更改）。';
            }
        } else {
            // 检查是否因为主键冲突导致错误 (MySQL 错误码 1062 for duplicate entry)
            if (mysqli_stmt_errno($stmt) == 1062) {
                 $response_message = '错误：添加失败，患者 ID "' . $patient_id . '" 已存在。';
            } else {
                 $response_message = '错误：添加记录失败。' . mysqli_stmt_error($stmt);
            }
        }
        mysqli_stmt_close($stmt);
        break;

    case 'update':
        //修改记录 ---
        if (!isset($_POST['patient_id'], $_POST['patient_name'], $_POST['patient_data'])) {
            $response_message = '错误：修改操作缺少必要的字段 (patient_id, patient_name, patient_data)。';
            break;
        }
        $patient_id_to_update = RemoveXSS($_POST['patient_id']); // 用于 WHERE 子句
        $new_patient_name     = RemoveXSS($_POST['patient_name']);
        $new_patient_data     = RemoveXSS($_POST['patient_data']);

        $patient_id_to_update = htmlspecialchars($patient_id_to_update, ENT_QUOTES);
        $new_patient_name     = htmlspecialchars($new_patient_name, ENT_QUOTES);
        $new_patient_data     = htmlspecialchars($new_patient_data, ENT_QUOTES);

        if (empty($patient_id_to_update)) {
            $response_message = '错误：用于更新的 patient_id 不能为空。';
            break;
        }
         if (empty($new_patient_name) && empty($new_patient_data)) {
            $response_message = '错误：至少需要提供 patient_name 或 patient_data 进行更新。';
            break;
        }


        // 注意: 如果 patient_id //本身可以被修改，则需要一个额外的主键或唯一标识符来定位记录。 
        // 如果要更新 patient_id, SQL 语句和参数会更复杂。
        // 这里更新 name 和 data based on a given patient_id.
        $sql = "UPDATE patient SET patient_name = ?, patient_data = ? WHERE patient_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            $response_message = '错误：SQL 预处理失败: ' . mysqli_error($con);
            break;
        }
        mysqli_stmt_bind_param($stmt, "sss", $new_patient_name, $new_patient_data, $patient_id_to_update);
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $response_message = '成功：记录已成功更新。';
            } else {
                $response_message = '注意：记录未更新（可能 patient_id 不存在或数据无变化）。';
            }
        } else {
            $response_message = '错误：更新记录失败。' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'delete':
        //删除记录
        if (!isset($_POST['patient_id'])) {
            $response_message = '错误：删除操作缺少 patient_id 字段。';
            break;
        }
        $patient_id_to_delete = RemoveXSS($_POST['patient_id']);
        $patient_id_to_delete = htmlspecialchars($patient_id_to_delete, ENT_QUOTES);

        if (empty($patient_id_to_delete)) {
            $response_message = '错误：用于删除的 patient_id 不能为空。';
            break;
        }

        $sql = "DELETE FROM patient WHERE patient_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            $response_message = '错误：SQL 预处理失败: ' . mysqli_error($con);
            break;
        }
        mysqli_stmt_bind_param($stmt, "s", $patient_id_to_delete);
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $response_message = '成功：记录已成功删除。';
            } else {
                $response_message = '注意：记录未删除（可能 patient_id 不存在）。';
            }
        } else {
            $response_message = '错误：删除记录失败。' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
        break;

    default:
        $response_message = '错误：无效的操作类型。允许的操作: add, update, delete。';
        break;
}

/*3. 输出结果*/
echo $response_message;



mysqli_close($con);
?>

# 昆明理工大学 2025算法分析期末大作业

题目要求：病人信息检索系统——急诊台需在输入患者 ID、姓名前缀或病症关键词后，毫秒级检索并返回匹配患者列表；系统支持实时新增、更新与删除记录。

---



## 如何运行此项目

本项目运行在2C2G云服务器。**推荐使用宝塔面板**
requirement: `MySQL5.7.44`

在mysql_con.php，您需要配置下面的参数


`$db_host = '127.0.0.1';`
`$db_port = '17783'; `  MySQL端口
`$db_user = '127_0_0_1_1145';`       MySQL用户名
`$db_pw = 'AAA';`                                  MySQL密码
``$db_name = '127_0_0_1_1145';`MySQL数据库名

## 项目文件作用

*根目录*

**Get.php**
用于查询患者信息的后端处理脚本。它的主要功能是接收用户输入，执行数据库查询，并以HTML表格形式返回结果。

* 纯数字输入：精确匹配patient\_id字段
* 全中文输入：先尝试精确匹配patient\_name，再模糊匹配patient\_data
* 其他输入：直接模糊匹配patient\_data字段
* 采用"查询队列"机制，一旦找到结果就停止后续查询

**index.php**
前端交互界面

**manage1.html**
前端界面，用于对病人记录进行增删改查操作

**modify.php**
后端处理脚本，专门用于处理前端提交的增删改查操作

**​mysql_con.php**
​数据库连接脚本，主要用于建立与MySQL数据库的连接。



---

*function目录*

**check.php**
定义`isAllChinese()`的函数，用于检测字符串是否全部由中文字符组成（允许包含中文间隔号`·`）

**XSSProtection.php**
防XSS注入

## 参考

[An Elegant And Fast Database Query System — 优雅且快速的数据库查询系统](https://github.com/polichan/IDQS)

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>病人信息检索系统</title>
    <link href="style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>病人信息检索系统</h1>
        </div>
        
        <div class="card">
            <div class="notice">
                📢 公告测试
            </div>
            
            <div class="search-box">
                <input type="text" class="search-input" placeholder="请输入待查询姓名/ID/病症" id="QueryName" autofocus>
            </div>
            
            <button class="search-btn" onclick="query()">开始查询</button>
            
            <div class="result-section">
                <h3>查询结果</h3>
                <p style="color: #7f8c8d; margin-bottom: 15px;">注意：查询结果将在以下列表中显示</p>
                
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>姓名</th>
                            <th>病人ID</th>
                            <th>病症</th>
                        </tr>
                    </thead>
                    <tbody id="result">
                        <tr>
                            <td>1</td>
                            <td>等待查询</td>
                            <td>等待查询</td>
                            <td>等待查询</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <a href="./manage1.html" class="manage-btn">修改记录</a>
            
            <div class="update-log">
                <h4>📋 更新日志</h4>
                <li>2025/05/22 网站上线</li>
            </div>
        </div>
        
        <div class="footer">
            <p>开发人：特异人士 <a href="https://github.com/tigerforest0573">My Github</a></p>
            <p>友情链接：<a href="https://www.kmust.edu.cn/">昆明理工大学</a> | <a href="https://www.github.com/">Github</a></p>
            <p>Developed on May.21st</p>
        </div>
    </div>

    <script>
        function query() {
            $.ajax({
                type: "post",
                async: false,
                url: "Get.php",
                data: 'QueryName=' + $('#QueryName').val(),
                success: function(msg) {
                    $("#result").html(msg);
                },
                error: function(XMLHttpRequest, textStatus, thrownError) {}
            });
        }
        
        $('#QueryName').keypress(function(e) {
            if (e.which === 13) {
                query();
            }
        });
    </script>
</body>
</html>
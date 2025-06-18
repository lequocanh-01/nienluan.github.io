<?php
session_start();

// Hàm để hiển thị thông tin session
function displaySessionInfo() {
    echo "<h3>Thông tin Session</h3>";
    echo "<pre>";
    
    if (empty($_SESSION)) {
        echo "Không có thông tin session nào.";
    } else {
        foreach ($_SESSION as $key => $value) {
            echo htmlspecialchars($key) . ": " . htmlspecialchars(print_r($value, true)) . "\n";
        }
    }
    
    echo "</pre>";
}

// Hàm để hiển thị thông tin server
function displayServerInfo() {
    echo "<h3>Thông tin Server</h3>";
    echo "<pre>";
    
    $serverVars = [
        'HTTP_HOST',
        'REQUEST_URI',
        'SCRIPT_NAME',
        'DOCUMENT_ROOT',
        'SERVER_NAME',
        'SERVER_PORT',
        'HTTPS',
        'REQUEST_METHOD',
        'HTTP_USER_AGENT',
        'REMOTE_ADDR'
    ];
    
    foreach ($serverVars as $var) {
        if (isset($_SERVER[$var])) {
            echo htmlspecialchars($var) . ": " . htmlspecialchars($_SERVER[$var]) . "\n";
        }
    }
    
    echo "</pre>";
}

// Xử lý đăng nhập test
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : 'user';
    $redirectMethod = isset($_POST['redirect_method']) ? $_POST['redirect_method'] : 'relative';
    
    if (!empty($username)) {
        // Thiết lập session
        if ($userType === 'admin') {
            $_SESSION['ADMIN'] = $username;
        } else {
            $_SESSION['USER'] = $username;
        }
        
        // Xác định URL chuyển hướng
        $redirectUrl = '';
        
        if ($redirectMethod === 'absolute') {
            // Sử dụng URL tuyệt đối
            if ($userType === 'admin') {
                $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/administrator/index.php?req=userview&result=ok';
            } else {
                $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php';
            }
        } else {
            // Sử dụng URL tương đối
            if ($userType === 'admin') {
                $redirectUrl = '../../index.php?req=userview&result=ok';
            } else {
                $redirectUrl = '../../../index.php';
            }
        }
        
        // Ghi log
        error_log("TEST REDIRECT: Đăng nhập thành công với username: '$username', userType: '$userType'");
        error_log("TEST REDIRECT: Chuyển hướng đến: '$redirectUrl' (phương thức: $redirectMethod)");
        
        // Chuyển hướng
        header('Location: ' . $redirectUrl);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chuyển Hướng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .container {
            max-width: 800px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .card {
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Test Chuyển Hướng</h1>
        
        <div class="card">
            <div class="card-header">
                <h5>Đăng nhập và chuyển hướng</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="le hoang anh" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" value="123456" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại người dùng</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_user" value="user" checked>
                            <label class="form-check-label" for="user_type_user">
                                Người dùng thường (USER)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_admin" value="admin">
                            <label class="form-check-label" for="user_type_admin">
                                Quản trị viên (ADMIN)
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phương thức chuyển hướng</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="redirect_method" id="redirect_method_relative" value="relative">
                            <label class="form-check-label" for="redirect_method_relative">
                                URL tương đối (../../../index.php)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="redirect_method" id="redirect_method_absolute" value="absolute" checked>
                            <label class="form-check-label" for="redirect_method_absolute">
                                URL tuyệt đối (http://localhost:8081/index.php)
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="login">
                    <button type="submit" class="btn btn-primary">Đăng nhập và chuyển hướng</button>
                </form>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <?php displaySessionInfo(); ?>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <?php displayServerInfo(); ?>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <a href="../../userLogin.php" class="btn btn-secondary">Quay lại trang đăng nhập</a>
                    <a href="checkSession.php" class="btn btn-info">Kiểm tra Session</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

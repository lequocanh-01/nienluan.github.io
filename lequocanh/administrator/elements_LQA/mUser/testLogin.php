<?php
session_start();
require '../../elements_LQA/mod/userCls.php';

// Xóa tất cả log cũ
$log_file = __DIR__ . '/login_debug.log';
if (file_exists($log_file)) {
    unlink($log_file);
}

// Hàm ghi log
function debug_log($message) {
    $log_file = __DIR__ . '/login_debug.log';
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Hàm kiểm tra đăng nhập
function test_login($username, $password) {
    debug_log("=== BẮT ĐẦU KIỂM TRA ĐĂNG NHẬP ===");
    debug_log("Username: '$username'");
    debug_log("Password: '$password'");
    
    // Loại bỏ khoảng trắng thừa
    $username = trim($username);
    debug_log("Username sau khi trim: '$username'");
    
    // Kiểm tra trực tiếp trong cơ sở dữ liệu
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra user có tồn tại không
    $sql = "SELECT * FROM user WHERE username = ?";
    debug_log("SQL query: $sql với tham số: '$username'");
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        debug_log("Tìm thấy user trong DB: " . json_encode($user));
        
        // Kiểm tra mật khẩu
        if ($user['password'] === $password) {
            debug_log("Mật khẩu khớp");
            
            // Kiểm tra setlock
            if ($user['setlock'] == 1) {
                debug_log("Tài khoản đã kích hoạt (setlock=1)");
                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $user
                ];
            } else {
                debug_log("Tài khoản chưa kích hoạt (setlock=" . $user['setlock'] . ")");
                
                // Tự động kích hoạt tài khoản
                $update_sql = "UPDATE user SET setlock = 1 WHERE iduser = ?";
                $update_stmt = $db->prepare($update_sql);
                $update_result = $update_stmt->execute([$user['iduser']]);
                
                if ($update_result) {
                    debug_log("Đã tự động kích hoạt tài khoản");
                    return [
                        'success' => true,
                        'message' => 'Tài khoản đã được kích hoạt. Đăng nhập thành công',
                        'user' => $user
                    ];
                } else {
                    debug_log("Không thể kích hoạt tài khoản");
                    return [
                        'success' => false,
                        'message' => 'Tài khoản chưa được kích hoạt và không thể kích hoạt tự động'
                    ];
                }
            }
        } else {
            debug_log("Mật khẩu không khớp. DB: '" . $user['password'] . "', Input: '$password'");
            return [
                'success' => false,
                'message' => 'Mật khẩu không chính xác'
            ];
        }
    } else {
        debug_log("Không tìm thấy user với username: '$username'");
        
        // Kiểm tra xem có user nào gần giống không
        $sql_like = "SELECT * FROM user WHERE username LIKE ?";
        $stmt_like = $db->prepare($sql_like);
        $stmt_like->execute(['%' . $username . '%']);
        $similar_users = $stmt_like->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($similar_users) > 0) {
            debug_log("Tìm thấy các user tương tự:");
            foreach ($similar_users as $similar_user) {
                debug_log("- ID: " . $similar_user['iduser'] . ", Username: '" . $similar_user['username'] . "'");
            }
            
            return [
                'success' => false,
                'message' => 'Không tìm thấy tài khoản. Có ' . count($similar_users) . ' tài khoản tương tự',
                'similar_users' => $similar_users
            ];
        } else {
            debug_log("Không tìm thấy user nào tương tự");
            return [
                'success' => false,
                'message' => 'Không tìm thấy tài khoản'
            ];
        }
    }
}

// Xử lý form submit
$result = null;
$log_content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (!empty($username) && !empty($password)) {
        $result = test_login($username, $password);
        
        // Đọc nội dung log
        $log_file = __DIR__ . '/login_debug.log';
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        .log-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test Đăng Nhập</h2>
        <p class="text-center">Công cụ này sẽ kiểm tra chi tiết quá trình đăng nhập và hiển thị thông tin debug</p>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Kiểm tra đăng nhập</button>
            </div>
        </form>
        
        <?php if ($result): ?>
            <div class="result-container <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <h4><?php echo $result['success'] ? 'Đăng nhập thành công' : 'Đăng nhập thất bại'; ?></h4>
                <p><?php echo htmlspecialchars($result['message']); ?></p>
                
                <?php if ($result['success'] && isset($result['user'])): ?>
                    <h5>Thông tin người dùng:</h5>
                    <ul>
                        <li><strong>ID:</strong> <?php echo $result['user']['iduser']; ?></li>
                        <li><strong>Username:</strong> <?php echo htmlspecialchars($result['user']['username']); ?></li>
                        <li><strong>Họ tên:</strong> <?php echo htmlspecialchars($result['user']['hoten']); ?></li>
                        <li><strong>Trạng thái:</strong> <?php echo $result['user']['setlock'] == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?></li>
                    </ul>
                <?php endif; ?>
                
                <?php if (!$result['success'] && isset($result['similar_users'])): ?>
                    <h5>Các tài khoản tương tự:</h5>
                    <ul>
                        <?php foreach ($result['similar_users'] as $user): ?>
                            <li>
                                <strong>ID:</strong> <?php echo $user['iduser']; ?>, 
                                <strong>Username:</strong> '<?php echo htmlspecialchars($user['username']); ?>', 
                                <strong>Họ tên:</strong> <?php echo htmlspecialchars($user['hoten']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($log_content)): ?>
            <h4 class="mt-4">Chi tiết debug:</h4>
            <div class="log-container">
                <?php echo htmlspecialchars($log_content); ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-3">
            <a href="../../userLogin.php" class="btn btn-secondary">Quay lại trang đăng nhập</a>
        </div>
    </div>
</body>
</html>

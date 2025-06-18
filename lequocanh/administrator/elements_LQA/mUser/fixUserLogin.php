<?php
session_start();
require '../../elements_LQA/mod/userCls.php';

// Hàm để gửi phản hồi JSON
function sendJsonResponse($success, $message = '', $data = null)
{
    // Clear any previous output that might corrupt JSON
    if (ob_get_contents()) ob_clean();

    // Set proper headers
    header('Content-Type: application/json');
    header("Cache-Control: no-cache, must-revalidate");

    // Return JSON
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Kiểm tra và sửa người dùng
function checkAndFixUser($username, $password)
{
    $userObj = new user();
    
    // Loại bỏ khoảng trắng thừa
    $username = trim($username);
    
    // Kiểm tra xem người dùng có tồn tại không
    $sql = 'SELECT * FROM user WHERE username = ?';
    $data = array($username);
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Người dùng không tồn tại'];
    }
    
    // Kiểm tra mật khẩu
    if ($user->password !== $password) {
        return ['success' => false, 'message' => 'Mật khẩu không đúng'];
    }
    
    // Kiểm tra trạng thái setlock
    if ($user->setlock != 1) {
        // Cập nhật setlock = 1
        $sql = "UPDATE user SET setlock = 1 WHERE iduser = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user->iduser]);
        
        return [
            'success' => true, 
            'message' => 'Đã kích hoạt tài khoản thành công', 
            'data' => [
                'iduser' => $user->iduser,
                'username' => $user->username,
                'setlock' => 1
            ]
        ];
    }
    
    return [
        'success' => true, 
        'message' => 'Tài khoản đã được kích hoạt', 
        'data' => [
            'iduser' => $user->iduser,
            'username' => $user->username,
            'setlock' => $user->setlock
        ]
    ];
}

// Xử lý yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        sendJsonResponse(false, 'Vui lòng nhập tên đăng nhập và mật khẩu');
    }
    
    $result = checkAndFixUser($username, $password);
    sendJsonResponse($result['success'], $result['message'], $result['data']);
} else {
    // Hiển thị form nếu không phải là POST request
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa lỗi đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            width: 100%;
            margin-top: 20px;
        }
        #result {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sửa lỗi đăng nhập</h2>
        <p class="text-center">Công cụ này sẽ kiểm tra và kích hoạt tài khoản của bạn nếu cần thiết.</p>
        
        <form id="fixLoginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Kiểm tra và sửa</button>
        </form>
        
        <div id="result" class="mt-3"></div>
        
        <div class="text-center mt-3">
            <a href="../../userLogin.php" class="btn btn-link">Quay lại trang đăng nhập</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#fixLoginForm').on('submit', function(e) {
                e.preventDefault();
                
                var username = $('#username').val().trim();
                var password = $('#password').val();
                
                if (!username || !password) {
                    showResult('Vui lòng nhập tên đăng nhập và mật khẩu', 'danger');
                    return;
                }
                
                $.ajax({
                    url: 'fixUserLogin.php',
                    type: 'POST',
                    data: {
                        username: username,
                        password: password
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showResult(response.message, 'success');
                            setTimeout(function() {
                                window.location.href = '../../userLogin.php';
                            }, 2000);
                        } else {
                            showResult(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showResult('Đã xảy ra lỗi khi kết nối đến máy chủ', 'danger');
                    }
                });
            });
            
            function showResult(message, type) {
                $('#result').html(message)
                    .removeClass('alert-success alert-danger')
                    .addClass('alert alert-' + type)
                    .show();
            }
        });
    </script>
</body>
</html>
<?php
}
?>

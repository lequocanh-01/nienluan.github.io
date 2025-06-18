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

// Kiểm tra và sửa người dùng cụ thể
function checkAndFixSpecificUser($username = 'le hoang anh')
{
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra xem người dùng có tồn tại không
    $sql = "SELECT * FROM user WHERE username LIKE ?";
    $stmt = $db->prepare($sql);
    $stmt->execute(['%' . $username . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    
    if (count($users) > 0) {
        foreach ($users as $user) {
            $results[] = [
                'iduser' => $user['iduser'],
                'username' => $user['username'],
                'password' => $user['password'],
                'hoten' => $user['hoten'],
                'setlock' => $user['setlock'],
                'fixed' => false
            ];
            
            // Nếu tài khoản chưa kích hoạt, kích hoạt nó
            if ($user['setlock'] != 1) {
                $update_sql = "UPDATE user SET setlock = 1 WHERE iduser = ?";
                $update_stmt = $db->prepare($update_sql);
                $update_result = $update_stmt->execute([$user['iduser']]);
                
                if ($update_result) {
                    $results[count($results) - 1]['fixed'] = true;
                    $results[count($results) - 1]['setlock'] = 1;
                }
            }
        }
        
        return [
            'success' => true,
            'message' => 'Đã tìm thấy ' . count($users) . ' tài khoản',
            'data' => $results
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Không tìm thấy tài khoản nào với tên "' . $username . '"'
        ];
    }
}

// Xử lý yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'fix_specific_user') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : 'le hoang anh';
        $result = checkAndFixSpecificUser($username);
        sendJsonResponse($result['success'], $result['message'], $result['data']);
    } elseif ($action === 'update_user') {
        $iduser = isset($_POST['iduser']) ? (int)$_POST['iduser'] : 0;
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $setlock = isset($_POST['setlock']) ? (int)$_POST['setlock'] : 1;
        
        if (empty($iduser) || empty($username) || empty($password)) {
            sendJsonResponse(false, 'Vui lòng nhập đầy đủ thông tin');
        }
        
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE user SET username = ?, password = ?, setlock = ? WHERE iduser = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$username, $password, $setlock, $iduser]);
        
        if ($result) {
            sendJsonResponse(true, 'Cập nhật thành công');
        } else {
            sendJsonResponse(false, 'Cập nhật thất bại');
        }
    }
} else {
    // Hiển thị form nếu không phải là POST request
    $result = checkAndFixSpecificUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tài khoản cụ thể</title>
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
        .user-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .user-card.fixed {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .user-card h4 {
            margin-bottom: 15px;
            color: #0d6efd;
        }
        .user-card .badge {
            font-size: 0.9rem;
            margin-left: 10px;
        }
        .edit-form {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sửa tài khoản cụ thể</h2>
        <p class="text-center">Công cụ này sẽ kiểm tra và sửa tài khoản "le hoang anh" hoặc tài khoản tương tự</p>
        
        <form id="searchForm" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" id="searchUsername" name="username" placeholder="Nhập tên đăng nhập cần tìm" value="le hoang anh">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </div>
        </form>
        
        <div id="results">
            <?php if ($result['success'] && !empty($result['data'])): ?>
                <h3>Kết quả tìm kiếm:</h3>
                <?php foreach ($result['data'] as $user): ?>
                    <div class="user-card <?php echo $user['fixed'] ? 'fixed' : ''; ?>">
                        <h4>
                            <?php echo htmlspecialchars($user['username']); ?>
                            <span class="badge bg-<?php echo $user['setlock'] == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $user['setlock'] == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?>
                            </span>
                            <?php if ($user['fixed']): ?>
                                <span class="badge bg-info">Đã sửa</span>
                            <?php endif; ?>
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID:</strong> <?php echo $user['iduser']; ?></p>
                                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['hoten']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Mật khẩu:</strong> <?php echo htmlspecialchars($user['password']); ?></p>
                                <p><strong>Trạng thái:</strong> <?php echo $user['setlock'] == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $user['iduser']; ?>">Chỉnh sửa</button>
                        </div>
                        <div class="edit-form" id="edit-form-<?php echo $user['iduser']; ?>">
                            <form class="update-form">
                                <input type="hidden" name="iduser" value="<?php echo $user['iduser']; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username-<?php echo $user['iduser']; ?>" class="form-label">Tên đăng nhập</label>
                                            <input type="text" class="form-control" id="username-<?php echo $user['iduser']; ?>" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password-<?php echo $user['iduser']; ?>" class="form-label">Mật khẩu</label>
                                            <input type="text" class="form-control" id="password-<?php echo $user['iduser']; ?>" name="password" value="<?php echo htmlspecialchars($user['password']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="setlock" id="setlock-1-<?php echo $user['iduser']; ?>" value="1" <?php echo $user['setlock'] == 1 ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="setlock-1-<?php echo $user['iduser']; ?>">
                                            Đã kích hoạt
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="setlock" id="setlock-0-<?php echo $user['iduser']; ?>" value="0" <?php echo $user['setlock'] == 0 ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="setlock-0-<?php echo $user['iduser']; ?>">
                                            Chưa kích hoạt
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <?php echo $result['message']; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="../../userLogin.php" class="btn btn-secondary">Quay lại trang đăng nhập</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Xử lý tìm kiếm
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                
                var username = $('#searchUsername').val().trim();
                
                $.ajax({
                    url: 'fixSpecificUser.php',
                    type: 'POST',
                    data: {
                        action: 'fix_specific_user',
                        username: username
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var html = '<h3>Kết quả tìm kiếm:</h3>';
                            
                            $.each(response.data, function(index, user) {
                                html += '<div class="user-card ' + (user.fixed ? 'fixed' : '') + '">';
                                html += '<h4>' + user.username;
                                html += '<span class="badge bg-' + (user.setlock == 1 ? 'success' : 'danger') + '">';
                                html += user.setlock == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt';
                                html += '</span>';
                                
                                if (user.fixed) {
                                    html += '<span class="badge bg-info">Đã sửa</span>';
                                }
                                
                                html += '</h4>';
                                html += '<div class="row">';
                                html += '<div class="col-md-6">';
                                html += '<p><strong>ID:</strong> ' + user.iduser + '</p>';
                                html += '<p><strong>Họ tên:</strong> ' + user.hoten + '</p>';
                                html += '</div>';
                                html += '<div class="col-md-6">';
                                html += '<p><strong>Mật khẩu:</strong> ' + user.password + '</p>';
                                html += '<p><strong>Trạng thái:</strong> ' + (user.setlock == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt') + '</p>';
                                html += '</div>';
                                html += '</div>';
                                html += '<div class="d-flex justify-content-end">';
                                html += '<button type="button" class="btn btn-sm btn-primary edit-btn" data-id="' + user.iduser + '">Chỉnh sửa</button>';
                                html += '</div>';
                                
                                // Form chỉnh sửa
                                html += '<div class="edit-form" id="edit-form-' + user.iduser + '">';
                                html += '<form class="update-form">';
                                html += '<input type="hidden" name="iduser" value="' + user.iduser + '">';
                                html += '<div class="row">';
                                html += '<div class="col-md-6">';
                                html += '<div class="mb-3">';
                                html += '<label for="username-' + user.iduser + '" class="form-label">Tên đăng nhập</label>';
                                html += '<input type="text" class="form-control" id="username-' + user.iduser + '" name="username" value="' + user.username + '">';
                                html += '</div>';
                                html += '</div>';
                                html += '<div class="col-md-6">';
                                html += '<div class="mb-3">';
                                html += '<label for="password-' + user.iduser + '" class="form-label">Mật khẩu</label>';
                                html += '<input type="text" class="form-control" id="password-' + user.iduser + '" name="password" value="' + user.password + '">';
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                                html += '<div class="mb-3">';
                                html += '<label class="form-label">Trạng thái</label>';
                                html += '<div class="form-check">';
                                html += '<input class="form-check-input" type="radio" name="setlock" id="setlock-1-' + user.iduser + '" value="1" ' + (user.setlock == 1 ? 'checked' : '') + '>';
                                html += '<label class="form-check-label" for="setlock-1-' + user.iduser + '">Đã kích hoạt</label>';
                                html += '</div>';
                                html += '<div class="form-check">';
                                html += '<input class="form-check-input" type="radio" name="setlock" id="setlock-0-' + user.iduser + '" value="0" ' + (user.setlock == 0 ? 'checked' : '') + '>';
                                html += '<label class="form-check-label" for="setlock-0-' + user.iduser + '">Chưa kích hoạt</label>';
                                html += '</div>';
                                html += '</div>';
                                html += '<div class="d-flex justify-content-end">';
                                html += '<button type="submit" class="btn btn-success">Lưu thay đổi</button>';
                                html += '</div>';
                                html += '</form>';
                                html += '</div>';
                                html += '</div>';
                            });
                            
                            $('#results').html(html);
                            
                            // Gắn lại sự kiện cho các nút mới
                            attachEvents();
                        } else {
                            $('#results').html('<div class="alert alert-warning">' + response.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#results').html('<div class="alert alert-danger">Đã xảy ra lỗi khi kết nối đến máy chủ</div>');
                    }
                });
            });
            
            function attachEvents() {
                // Xử lý nút chỉnh sửa
                $('.edit-btn').off('click').on('click', function() {
                    var id = $(this).data('id');
                    $('#edit-form-' + id).slideToggle();
                });
                
                // Xử lý form cập nhật
                $('.update-form').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    
                    var form = $(this);
                    var iduser = form.find('input[name="iduser"]').val();
                    var username = form.find('input[name="username"]').val().trim();
                    var password = form.find('input[name="password"]').val();
                    var setlock = form.find('input[name="setlock"]:checked').val();
                    
                    $.ajax({
                        url: 'fixSpecificUser.php',
                        type: 'POST',
                        data: {
                            action: 'update_user',
                            iduser: iduser,
                            username: username,
                            password: password,
                            setlock: setlock
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Cập nhật thành công');
                                // Reload để hiển thị thông tin mới
                                location.reload();
                            } else {
                                alert('Cập nhật thất bại: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Đã xảy ra lỗi khi kết nối đến máy chủ');
                        }
                    });
                });
            }
            
            // Gắn sự kiện khi trang tải xong
            attachEvents();
        });
    </script>
</body>
</html>
<?php
}
?>

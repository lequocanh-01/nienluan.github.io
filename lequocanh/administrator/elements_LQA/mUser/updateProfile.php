<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
    header('location: ../../../userLogin.php');
    exit();
}

// Tìm đường dẫn đúng đến các file cần thiết
$paths = [
    __DIR__ . '/../../elements_LQA/mod/database.php',
    __DIR__ . '/../mod/database.php',
    __DIR__ . '/../../mod/database.php',
    './elements_LQA/mod/database.php'
];

$found = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $found = true;
        error_log("updateProfile.php - Đã tìm thấy database.php tại: " . $path);
        break;
    }
}

if (!$found) {
    error_log("updateProfile.php - Không tìm thấy file database.php");
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Lấy username từ session
$username = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : $_SESSION['USER'];
error_log("updateProfile.php - Username: " . $username);

// Kết nối database
try {
    $db = Database::getInstance()->getConnection();
    error_log("updateProfile.php - Kết nối database thành công");
} catch (Exception $e) {
    error_log("updateProfile.php - Lỗi kết nối database: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Lấy thông tin người dùng
try {
    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $currentUser = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$currentUser) {
        error_log("updateProfile.php - Không tìm thấy người dùng: " . $username);
        die("Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.");
    }

    error_log("updateProfile.php - Đã tìm thấy người dùng: " . $username . ", ID: " . $currentUser->iduser);
} catch (Exception $e) {
    error_log("updateProfile.php - Lỗi khi lấy thông tin người dùng: " . $e->getMessage());
    die("Lỗi khi lấy thông tin người dùng. Vui lòng thử lại sau.");
}

// Xử lý form cập nhật
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $hoten = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
    $gioitinh = isset($_POST['gioitinh']) ? (int)$_POST['gioitinh'] : 1;
    $ngaysinh = isset($_POST['ngaysinh']) ? $_POST['ngaysinh'] : '';
    $diachi = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
    $dienthoai = isset($_POST['dienthoai']) ? trim($_POST['dienthoai']) : '';

    // Validate dữ liệu
    $isValid = true;

    if (empty($hoten)) {
        $error_message = "Họ tên không được để trống";
        $isValid = false;
    } else if (empty($ngaysinh)) {
        $error_message = "Ngày sinh không được để trống";
        $isValid = false;
    } else if (empty($diachi)) {
        $error_message = "Địa chỉ không được để trống";
        $isValid = false;
    } else if (empty($dienthoai)) {
        $error_message = "Số điện thoại không được để trống";
        $isValid = false;
    } else if (!preg_match("/^[0-9]{10,11}$/", $dienthoai)) {
        $error_message = "Số điện thoại không hợp lệ";
        $isValid = false;
    }

    // Cập nhật thông tin nếu dữ liệu hợp lệ
    if ($isValid) {
        try {
            $sql = "UPDATE user SET hoten = ?, gioitinh = ?, ngaysinh = ?, diachi = ?, dienthoai = ? WHERE iduser = ?";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, $currentUser->iduser]);

            if ($result) {
                $success_message = "Cập nhật thông tin thành công";
                error_log("updateProfile.php - Cập nhật thông tin thành công cho user: " . $username);

                // Cập nhật lại thông tin người dùng
                $stmt = $db->prepare("SELECT * FROM user WHERE iduser = ?");
                $stmt->execute([$currentUser->iduser]);
                $currentUser = $stmt->fetch(PDO::FETCH_OBJ);

                // Hiển thị thông báo thành công
                echo '<div class="alert alert-success">Cập nhật thông tin thành công! Đang chuyển hướng...</div>';

                // Chuyển hướng về trang profile sau 2 giây
                echo '<script>
                    // Kiểm tra xem đường dẫn có hoạt động không
                    function checkUrl(url, fallbackUrl) {
                        var xhr = new XMLHttpRequest();
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    window.location.href = url;
                                } else {
                                    console.log("Đường dẫn không hoạt động, sử dụng fallback");
                                    window.location.href = fallbackUrl;
                                }
                            }
                        };
                        xhr.open("HEAD", url, true);
                        xhr.send();
                    }

                    setTimeout(function() {
                        // Chuyển hướng trực tiếp đến trang thông tin người dùng
                        window.location.href = "http://localhost:8081/administrator/elements_LQA/mUser/userProfile.php";
                    }, 2000);
                </script>';

                // Thêm nút quay lại ngay lập tức với JavaScript để kiểm tra đường dẫn
                echo '<div style="text-align: center; margin-top: 20px;">
                        <button onclick="tryRedirect()" class="btn btn-primary">Quay lại ngay</button>
                      </div>';

                // Thêm script để xử lý chuyển hướng khi nhấn nút
                echo '<script>
                    function tryRedirect() {
                        // Chuyển hướng trực tiếp đến trang thông tin người dùng
                        window.location.href = "http://localhost:8081/administrator/elements_LQA/mUser/userProfile.php";
                    }
                </script>';
            } else {
                $error_message = "Có lỗi xảy ra, vui lòng thử lại sau";
                error_log("updateProfile.php - Lỗi khi cập nhật thông tin cho user: " . $username);
            }
        } catch (Exception $e) {
            $error_message = "Có lỗi xảy ra, vui lòng thử lại sau";
            error_log("updateProfile.php - Lỗi khi cập nhật thông tin: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin tài khoản</title>
    <link rel="stylesheet" href="../../stylecss_LQA/mycss.css">
    <style>
        .update-form-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #f1f1f1;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #e2e2e2;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="update-form-container">
        <div class="form-header">
            <h2>Cập nhật thông tin tài khoản</h2>
            <p>Vui lòng điền thông tin cần thay đổi</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($currentUser->username); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="hoten">Họ và tên:</label>
                <input type="text" id="hoten" name="hoten" value="<?php echo htmlspecialchars($currentUser->hoten); ?>" required>
            </div>

            <div class="form-group">
                <label for="gioitinh">Giới tính:</label>
                <select id="gioitinh" name="gioitinh">
                    <option value="1" <?php echo $currentUser->gioitinh == 1 ? 'selected' : ''; ?>>Nam</option>
                    <option value="2" <?php echo $currentUser->gioitinh == 2 ? 'selected' : ''; ?>>Nữ</option>
                    <option value="0" <?php echo $currentUser->gioitinh == 0 ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ngaysinh">Ngày sinh:</label>
                <input type="date" id="ngaysinh" name="ngaysinh" value="<?php echo htmlspecialchars($currentUser->ngaysinh); ?>" required>
            </div>

            <div class="form-group">
                <label for="diachi">Địa chỉ:</label>
                <input type="text" id="diachi" name="diachi" value="<?php echo htmlspecialchars($currentUser->diachi); ?>" required>
            </div>

            <div class="form-group">
                <label for="dienthoai">Số điện thoại:</label>
                <input type="tel" id="dienthoai" name="dienthoai" value="<?php echo htmlspecialchars($currentUser->dienthoai); ?>" required>
            </div>

            <div class="form-actions">
                <button type="button" onclick="tryRedirect()" class="btn btn-secondary">Quay lại</button>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>

            <script>
                function tryRedirect() {
                    // Chuyển hướng trực tiếp đến trang thông tin người dùng
                    window.location.href = "http://localhost:8081/administrator/elements_LQA/mUser/userProfile.php";
                }
            </script>

            <!-- Thông tin debug (ẩn) -->
            <div style="display: none;">
                <p>Current path: <?php echo __FILE__; ?></p>
                <p>Username: <?php echo $username; ?></p>
                <p>User ID: <?php echo $currentUser->iduser; ?></p>
            </div>
        </form>
    </div>
</body>

</html>
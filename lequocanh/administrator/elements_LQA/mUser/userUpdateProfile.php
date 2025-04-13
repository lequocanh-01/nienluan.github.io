<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
    header('location: ../../userLogin.php');
    exit();
}

include_once "../../config/config.php";
include_once "../../elements_LQA/mod/userCls.php";

// Get username from session
$username = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : $_SESSION['USER'];

// Initialize user class
$userObj = new user();

// Get all users to find current user ID
$allUsers = $userObj->UserGetAll();
$currentUser = null;

foreach ($allUsers as $user) {
    if ($user->username === $username) {
        $currentUser = $user;
        break;
    }
}

// Check if user was found
if (!$currentUser) {
    header('location: ../../index.php');
    exit();
}

// Process form submission
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $hoten = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
    $gioitinh = isset($_POST['gioitinh']) ? (int)$_POST['gioitinh'] : 1;
    $ngaysinh = isset($_POST['ngaysinh']) ? $_POST['ngaysinh'] : '';
    $diachi = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
    $dienthoai = isset($_POST['dienthoai']) ? trim($_POST['dienthoai']) : '';

    // Validate input
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

    // Update user information if valid
    if ($isValid) {
        $result = $userObj->UserUpdate(
            $currentUser->username,
            $currentUser->password,
            $hoten,
            $gioitinh,
            $ngaysinh,
            $diachi,
            $dienthoai,
            $currentUser->iduser
        );

        if ($result) {
            $success_message = "Cập nhật thông tin thành công";
            // Refresh user data
            $currentUser = $userObj->UserGetbyId($currentUser->iduser);
        } else {
            $error_message = "Có lỗi xảy ra, vui lòng thử lại sau";
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
                <a href="../../index.php?req=userprofile" class="btn btn-secondary">Quay lại</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</body>

</html>
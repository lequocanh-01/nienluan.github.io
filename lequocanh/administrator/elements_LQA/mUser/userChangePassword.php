<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit();
}

// Include required files
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
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';

    // Validate input
    if (empty($currentPassword) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
        exit();
    }

    // Change password
    $result = $userObj->UserChangePassword($currentUser->iduser, $currentPassword, $newPassword);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}

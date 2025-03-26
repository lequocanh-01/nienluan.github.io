<?php
// Bắt đầu session nếu chưa bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Thiết lập header cho response JSON
header('Content-Type: application/json');

try {
    // Kiểm tra phương thức yêu cầu
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không được hỗ trợ');
    }

    // Lấy giá trị từ form post
    if (!isset($_POST['auto_apply_images'])) {
        throw new Exception('Dữ liệu không hợp lệ');
    }

    $autoApply = $_POST['auto_apply_images'] === '1';

    // Lưu vào session
    $_SESSION['auto_apply_images'] = $autoApply;

    // Trả về thành công
    echo json_encode([
        'success' => true,
        'message' => 'Tùy chọn đã được lưu',
        'value' => $autoApply
    ]);
} catch (Exception $e) {
    // Trả về lỗi
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

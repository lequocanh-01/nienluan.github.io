<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

try {
    // Nhận dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['auto_apply'])) {
        // Lưu tùy chọn vào session
        $_SESSION['auto_apply_images'] = (bool)$data['auto_apply'];

        echo json_encode([
            'success' => true,
            'message' => 'Đã lưu cấu hình thành công!'
        ]);
    } else {
        throw new Exception("Dữ liệu không hợp lệ");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

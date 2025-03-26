<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../elements_LQA/mod/hanghoaCls.php';

// Lấy ID hình ảnh từ tham số
$imageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Thiết lập header cache để tránh tải lại liên tục
header('Cache-Control: max-age=86400, public'); // Cache 1 ngày
header('Pragma: public');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

if ($imageId <= 0) {
    // Nếu không có ID hợp lệ, hiển thị hình ảnh "Đang cập nhật"
    header('Content-Type: image/png');
    // Kiểm tra cả hai vị trí có thể có của file updating-image.png
    if (file_exists('../../img_LQA/updating-image.png')) {
        readfile('../../img_LQA/updating-image.png');
    } else if (file_exists('../../../img_LQA/updating-image.png')) {
        readfile('../../../img_LQA/updating-image.png');
    } else {
        // Fallback nếu không tìm thấy hình cập nhật
        readfile('../../img_LQA/no-image.png');
    }
    exit;
}

$hanghoa = new hanghoa();
$hinhanh = $hanghoa->GetHinhAnhById($imageId);

// Debug (có thể bỏ log sau khi đã xác nhận hoạt động tốt)
// error_log("Displaying image ID: " . $imageId);
// if ($hinhanh) {
//     error_log("Image path: " . $hinhanh->duong_dan);
// }

if ($hinhanh && !empty($hinhanh->duong_dan)) {
    $imagePath = $hinhanh->duong_dan;

    // Xử lý đường dẫn
    if (strpos($imagePath, 'administrator/') === 0) {
        // Loại bỏ "administrator/" vì hiện tại đã ở trong thư mục đó
        $imagePath = substr($imagePath, strlen('administrator/'));
    }

    // Thử các vị trí khác nhau
    $possiblePaths = [
        '../../../' . $imagePath,
        '../../' . $imagePath,
        '../' . $imagePath,
        '../../../uploads/' . basename($imagePath),
        '../../uploads/' . basename($imagePath),
        '../uploads/' . basename($imagePath),
        './uploads/' . basename($imagePath),
        $imagePath
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            // error_log("Found image at: " . $path);
            // Xác định loại MIME dựa trên phần mở rộng
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $contentType = 'image/jpeg'; // Mặc định là JPEG

            if ($extension === 'png') {
                $contentType = 'image/png';
            } elseif ($extension === 'gif') {
                $contentType = 'image/gif';
            } elseif ($extension === 'webp') {
                $contentType = 'image/webp';
            }

            header('Content-Type: ' . $contentType);
            readfile($path);
            exit;
        }
    }
}

// Nếu không tìm thấy hình ảnh, hiển thị hình "Đang cập nhật"
header('Content-Type: image/png');
// Kiểm tra cả hai vị trí có thể có của file updating-image.png
if (file_exists('../../img_LQA/updating-image.png')) {
    readfile('../../img_LQA/updating-image.png');
} else if (file_exists('../../../img_LQA/updating-image.png')) {
    readfile('../../../img_LQA/updating-image.png');
} else {
    // Fallback nếu không tìm thấy hình cập nhật
    readfile('../../img_LQA/no-image.png');
}
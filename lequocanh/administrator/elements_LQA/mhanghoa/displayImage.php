<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../mod/hanghoaCls.php';

// Lấy ID hình ảnh từ tham số
$imageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Enable detailed error logging for debugging
ini_set('display_errors', 0);
error_log("displayImage.php - Requesting image ID: " . $imageId);

// Thiết lập header cache để tránh tải lại liên tục
header('Cache-Control: max-age=86400, public'); // Cache 1 ngày
header('Pragma: public');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

if ($imageId <= 0) {
    // Nếu không có ID hợp lệ, hiển thị hình ảnh "Đang cập nhật"
    error_log("displayImage.php - Invalid image ID: " . $imageId);
    header('Content-Type: image/png');
    // Kiểm tra cả hai vị trí có thể có của file updating-image.png
    if (file_exists('../../../img_LQA/updating-image.png')) {
        readfile('../../../img_LQA/updating-image.png');
    } else if (file_exists('../../img_LQA/updating-image.png')) {
        readfile('../../img_LQA/updating-image.png');
    } else {
        // Fallback nếu không tìm thấy hình cập nhật
        readfile('../../../img_LQA/no-image.png');
    }
    exit;
}

$hanghoa = new hanghoa();
$hinhanh = $hanghoa->GetHinhAnhById($imageId);

error_log("displayImage.php - Retrieved image data: " . ($hinhanh ? json_encode($hinhanh) : 'null'));

if ($hinhanh && !empty($hinhanh->duong_dan)) {
    $imagePath = $hinhanh->duong_dan;
    error_log("displayImage.php - Original path: " . $imagePath);

    // Xử lý đường dẫn
    $imagePath = str_replace('\\', '/', $imagePath);

    // Kiểm tra môi trường và cài đặt đường dẫn phù hợp
    $isDocker = (getenv('DOCKER_ENV') !== false) || file_exists('/.dockerenv');
    $uploadsAbsolutePath = $isDocker ? '/var/www/html/administrator/uploads/' : 'D:/PHP_WS/lequocanh/administrator/uploads/';

    // Debug đường dẫn
    error_log("displayImage.php - Environment: " . ($isDocker ? 'Docker' : 'Windows'));
    error_log("displayImage.php - Using uploads path: " . $uploadsAbsolutePath);

    // Nếu đường dẫn chứa "administrator/uploads", lấy phần tên file
    if (strpos($imagePath, 'administrator/uploads/') !== false) {
        $filename = basename($imagePath);
        $absolutePath = $uploadsAbsolutePath . $filename;
        error_log("displayImage.php - Using absolute path: " . $absolutePath);

        if (file_exists($absolutePath)) {
            // Xác định loại MIME dựa trên phần mở rộng
            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $contentType = 'image/jpeg'; // Mặc định là JPEG

            if ($extension === 'png') {
                $contentType = 'image/png';
            } elseif ($extension === 'gif') {
                $contentType = 'image/gif';
            } elseif ($extension === 'webp') {
                $contentType = 'image/webp';
            }

            error_log("displayImage.php - Found valid absolute path: " . $absolutePath . " with content type: " . $contentType);
            header('Content-Type: ' . $contentType);
            readfile($absolutePath);
            exit;
        }
    }

    // Thử các vị trí khác nhau (fallback)
    $possiblePaths = [
        $uploadsAbsolutePath . basename($imagePath),
        '../../../' . $imagePath,
        '../../' . $imagePath,
        '../' . $imagePath,
        '../../../uploads/' . basename($imagePath),
        '../../uploads/' . basename($imagePath),
        '../uploads/' . basename($imagePath),
        './uploads/' . basename($imagePath),
        $imagePath,
        // Thêm đường dẫn tuyệt đối
        dirname(__FILE__) . '/../../../uploads/' . basename($imagePath),
        'D:/PHP_WS/lequocanh/administrator/uploads/' . basename($imagePath)
    ];

    error_log("displayImage.php - Image paths to try: " . json_encode($possiblePaths));

    foreach ($possiblePaths as $path) {
        error_log("displayImage.php - Trying path: " . $path . " - exists: " . (file_exists($path) ? 'YES' : 'NO'));
        if (file_exists($path)) {
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

            error_log("displayImage.php - Found valid path: " . $path . " with content type: " . $contentType);
            header('Content-Type: ' . $contentType);
            readfile($path);
            exit;
        }
    }

    error_log("displayImage.php - FAILED: No valid path found for image ID: " . $imageId);
}

// Nếu không tìm thấy hình ảnh, hiển thị hình "Đang cập nhật"
error_log("displayImage.php - Falling back to default image for image ID: " . $imageId);
header('Content-Type: image/png');
// Kiểm tra cả hai vị trí có thể có của file updating-image.png
if (file_exists('../../../img_LQA/updating-image.png')) {
    readfile('../../../img_LQA/updating-image.png');
} else if (file_exists('../../img_LQA/updating-image.png')) {
    readfile('../../img_LQA/updating-image.png');
} else {
    // Fallback nếu không tìm thấy hình cập nhật
    readfile('../../../img_LQA/no-image.png');
}

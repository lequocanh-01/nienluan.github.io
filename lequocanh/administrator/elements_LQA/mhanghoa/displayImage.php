<?php
require_once("../mod/database.php");
require_once("../mod/hanghoaCls.php");

// Tắt báo lỗi để tránh output không mong muốn
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Đảm bảo có ID ảnh
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $imageId = (int)$_GET['id'];
    $hanghoa = new hanghoa();

    // Lấy thông tin hình ảnh
    $image = $hanghoa->GetHinhAnhById($imageId);

    if ($image && !empty($image->duong_dan)) {
        // Xác định môi trường và cài đặt đường dẫn phù hợp
        $isDocker = (getenv('DOCKER_ENV') !== false) || file_exists('/.dockerenv');
        $imageRelativePath = $image->duong_dan;

        // Xây dựng đường dẫn tuyệt đối
        if ($isDocker) {
            $imagePath = '/var/www/html/' . $imageRelativePath;
        } else {
            // Trong trường hợp Windows, xây dựng đường dẫn thích hợp
            $imagePath = 'D:/PHP_WS/lequocanh/' . $imageRelativePath;
        }

        // Kiểm tra xem file có tồn tại không
        if (file_exists($imagePath)) {
            // Xác định loại MIME của file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $imagePath);
            finfo_close($finfo);

            // Thiết lập header để tránh cache và định dạng đúng loại file
            header("Content-Type: $mime");
            header("Content-Length: " . filesize($imagePath));

            // Thêm header cache nếu được yêu cầu
            if (!isset($_GET['t'])) {
                header("Cache-Control: public, max-age=31536000"); // Cache 1 năm
                header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
            } else {
                // Vô hiệu hóa cache nếu có tham số timestamp
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");
                header("Expires: 0");
            }

            // Đọc và xuất nội dung file
            readfile($imagePath);
            exit;
        }
    }
}

// Nếu không tìm thấy ảnh hoặc có lỗi, trả về hình ảnh mặc định
$defaultImage = "../../../img_LQA/no-image.png";
if (file_exists($defaultImage)) {
    header("Content-Type: image/png");
    header("Content-Length: " . filesize($defaultImage));
    readfile($defaultImage);
} else {
    // Nếu không tìm thấy ảnh mặc định, trả về lỗi 404
    header("HTTP/1.0 404 Not Found");
}

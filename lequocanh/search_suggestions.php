<?php
require_once './administrator/elements_LQA/mod/hanghoaCls.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tạo file log riêng cho search
$logFile = __DIR__ . '/search_debug.log';
file_put_contents($logFile, "--- Search Log " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);

function logToFile($message)
{
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . ": " . $message . "\n", FILE_APPEND);
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
logToFile("Search term received: " . $term);

// Lấy domain của trang web
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;
logToFile("Base URL: " . $baseUrl);

if (strlen($term) >= 2) {
    try {
        $hanghoa = new hanghoa();
        $results = $hanghoa->searchHanghoa($term);
        logToFile("Search results count: " . count($results));

        $suggestions = array_map(function ($item) use ($hanghoa, $baseUrl) {
            // Lấy thông tin hình ảnh từ bảng hinhanh
            $hinhanh = $hanghoa->GetHinhAnhById($item->hinhanh);

            logToFile("Product ID: " . $item->idhanghoa . ", Name: " . $item->tenhanghoa . ", Image ID: " . $item->hinhanh);

            // Kiểm tra nếu hình ảnh tồn tại
            if ($hinhanh) {
                logToFile("Image found: " . json_encode($hinhanh));
            } else {
                logToFile("No image found for ID: " . $item->hinhanh);
            }

            // Khởi tạo đường dẫn hình ảnh mặc định
            $imagePath = $baseUrl . '/img_LQA/updating-image.png';
            $isDefaultImage = true;
            logToFile("Default image path: " . $imagePath);

            // Nếu có hình ảnh và có đường dẫn, sử dụng đường dẫn đó
            if ($hinhanh && !empty($hinhanh->duong_dan)) {
                $originalPath = $hinhanh->duong_dan;
                logToFile("Found image path in database: " . $originalPath);

                // Đảm bảo đường dẫn hình ảnh là tuyệt đối
                if (strpos($originalPath, 'http') === 0) {
                    // Đường dẫn đã là URL đầy đủ
                    $imagePath = $originalPath;
                    $isDefaultImage = false;
                    logToFile("Using existing absolute URL: " . $imagePath);
                } else {
                    // Chuẩn hóa đường dẫn tương đối
                    $relativePath = ltrim($originalPath, '/');

                    // Tạo đường dẫn tuyệt đối
                    $imagePath = $baseUrl . '/' . $relativePath;
                    $isDefaultImage = false;
                    logToFile("Created absolute URL from relative path: " . $imagePath);

                    // Kiểm tra file có tồn tại không
                    $localPath = __DIR__ . '/' . $relativePath;
                    if (file_exists($localPath)) {
                        logToFile("File exists at: " . $localPath);
                    } else {
                        logToFile("File DOES NOT exist at: " . $localPath);
                        // Reset to default image if file doesn't exist
                        $imagePath = $baseUrl . '/img_LQA/updating-image.png';
                        $isDefaultImage = true;
                    }
                }
            } else {
                logToFile("No valid image path found, using default image");
            }

            return [
                'id' => $item->idhanghoa,
                'name' => $item->tenhanghoa,
                'price' => number_format($item->giathamkhao, 0, ',', '.') . ' VNĐ',
                'image' => $imagePath,
                'isDefaultImage' => $isDefaultImage
            ];
        }, $results);

        // Log kết quả gợi ý cuối cùng
        logToFile("Final suggestions: " . json_encode($suggestions));

        header('Content-Type: application/json');
        echo json_encode($suggestions);
    } catch (Exception $e) {
        logToFile("Error in search_suggestions.php: " . $e->getMessage());
        error_log("Error in search_suggestions.php: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Có lỗi xảy ra khi tìm kiếm']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}
exit();

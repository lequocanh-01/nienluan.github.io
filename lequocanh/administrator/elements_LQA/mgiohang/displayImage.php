<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../elements_LQA/mod/hanghoaCls.php';

// Bật log lỗi để debug
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Ghi log để debug
error_log("displayImage.php được gọi với ID: " . (isset($_GET['id']) ? $_GET['id'] : 'không có ID'));

// Lấy ID hình ảnh từ tham số
$imageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Thiết lập header cache để tránh tải lại liên tục
header('Cache-Control: max-age=86400, public'); // Cache 1 ngày
header('Pragma: public');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

// Kiểm tra nếu trình duyệt đã có bản sao trong cache
$etag = md5(isset($_GET['id']) ? $_GET['id'] : '0');
header('ETag: "' . $etag . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    header('HTTP/1.1 304 Not Modified');
    exit;
}

if ($imageId <= 0) {
    // Nếu không có ID hợp lệ, hiển thị hình ảnh mặc định
    $defaultImagePaths = [
        "../../elements_LQA/img_LQA/no-image.png",
        "../../../elements_LQA/img_LQA/no-image.png",
        "../elements_LQA/img_LQA/no-image.png",
        "./elements_LQA/img_LQA/no-image.png",
        "../../../administrator/elements_LQA/img_LQA/no-image.png",
        "../../administrator/elements_LQA/img_LQA/no-image.png",
        "../administrator/elements_LQA/img_LQA/no-image.png",
        "./administrator/elements_LQA/img_LQA/no-image.png"
    ];

    $defaultImageFound = false;
    foreach ($defaultImagePaths as $defaultImage) {
        if (file_exists($defaultImage)) {
            header("Content-Type: image/png");
            header("Content-Length: " . filesize($defaultImage));
            readfile($defaultImage);
            $defaultImageFound = true;
            break;
        }
    }

    // Nếu không tìm thấy file no-image.png, tạo một hình ảnh đơn giản
    if (!$defaultImageFound) {
        // Tạo một hình ảnh đơn giản với text "No Image"
        header("Content-Type: image/png");
        $width = 200;
        $height = 200;
        $image = imagecreatetruecolor($width, $height);

        // Màu nền và màu chữ
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 100, 100, 100);

        // Vẽ nền
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Vẽ viền
        $borderColor = imagecolorallocate($image, 200, 200, 200);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        // Thêm text
        $text = "No Image";
        $font = 5; // Font mặc định
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        imagestring($image, $font, $x, $y, $text, $textColor);

        // Output hình ảnh
        imagepng($image);
        imagedestroy($image);
    }
    exit;
}

$hanghoa = new hanghoa();
$hinhanh = $hanghoa->GetHinhAnhById($imageId);

// Debug
error_log("Displaying image ID: " . $imageId);
if ($hinhanh) {
    error_log("Image path: " . $hinhanh->duong_dan);
}

// Kiểm tra xem chúng ta đang ở môi trường Docker hay không
$isDocker = (getenv('DOCKER_ENV') !== false) || file_exists('/.dockerenv');

if ($hinhanh && !empty($hinhanh->duong_dan)) {
    $imagePath = $hinhanh->duong_dan;

    // Xử lý đường dẫn
    if (strpos($imagePath, 'administrator/') === 0) {
        // Loại bỏ "administrator/" vì hiện tại đã ở trong thư mục đó
        $imagePath = substr($imagePath, strlen('administrator/'));
    }

    // Xây dựng đường dẫn tuyệt đối dựa trên môi trường
    if ($isDocker) {
        $absolutePath = '/var/www/html/' . $imagePath;
        error_log("Docker absolute path: " . $absolutePath);

        if (file_exists($absolutePath)) {
            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $contentType = 'image/jpeg'; // Mặc định là JPEG

            if ($extension === 'png') {
                $contentType = 'image/png';
            } elseif ($extension === 'gif') {
                $contentType = 'image/gif';
            } elseif ($extension === 'webp') {
                $contentType = 'image/webp';
            }

            header('Content-Type: ' . $contentType);
            readfile($absolutePath);
            exit;
        }
    }

    // Thử các vị trí khác nhau (đường dẫn tương đối)
    $possiblePaths = [
        '../../../' . $imagePath,
        '../../' . $imagePath,
        '../' . $imagePath,
        '../../../uploads/' . basename($imagePath),
        '../../uploads/' . basename($imagePath),
        '../uploads/' . basename($imagePath),
        './uploads/' . basename($imagePath),
        $imagePath,
        // Thêm các đường dẫn mới để tìm kiếm hình ảnh
        '../../../administrator/' . $imagePath,
        '../../administrator/' . $imagePath,
        '../administrator/' . $imagePath,
        './administrator/' . $imagePath,
        '../../../administrator/uploads/' . basename($imagePath),
        '../../administrator/uploads/' . basename($imagePath),
        '../administrator/uploads/' . basename($imagePath),
        './administrator/uploads/' . basename($imagePath)
    ];

    foreach ($possiblePaths as $path) {
        error_log("Checking path: " . $path);
        if (file_exists($path)) {
            error_log("Found image at: " . $path);
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

// Nếu không tìm thấy hình ảnh, hiển thị hình mặc định
$defaultImagePaths = [
    "../../elements_LQA/img_LQA/no-image.png",
    "../../../elements_LQA/img_LQA/no-image.png",
    "../elements_LQA/img_LQA/no-image.png",
    "./elements_LQA/img_LQA/no-image.png",
    "../../../administrator/elements_LQA/img_LQA/no-image.png",
    "../../administrator/elements_LQA/img_LQA/no-image.png",
    "../administrator/elements_LQA/img_LQA/no-image.png",
    "./administrator/elements_LQA/img_LQA/no-image.png"
];

$defaultImageFound = false;
foreach ($defaultImagePaths as $defaultImage) {
    if (file_exists($defaultImage)) {
        header("Content-Type: image/png");
        header("Content-Length: " . filesize($defaultImage));
        readfile($defaultImage);
        $defaultImageFound = true;
        break;
    }
}

// Nếu không tìm thấy file no-image.png, tạo một hình ảnh đơn giản
if (!$defaultImageFound) {
    // Tạo một hình ảnh đơn giản với text "No Image"
    header("Content-Type: image/png");
    $width = 200;
    $height = 200;
    $image = imagecreatetruecolor($width, $height);

    // Màu nền và màu chữ
    $bgColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);

    // Vẽ nền
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

    // Vẽ viền
    $borderColor = imagecolorallocate($image, 200, 200, 200);
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

    // Thêm text
    $text = "No Image";
    $font = 5; // Font mặc định
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;

    imagestring($image, $font, $x, $y, $text, $textColor);

    // Output hình ảnh
    imagepng($image);
    imagedestroy($image);
}

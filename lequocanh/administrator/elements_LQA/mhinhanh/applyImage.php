<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("../mod/database.php");
require_once("../mod/hanghoaCls.php");

header('Content-Type: application/json; charset=utf-8');

try {
    // Nhận dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['image_id'])) {
        $imageId = (int)$data['image_id'];
        $hanghoa = new hanghoa();

        // Lấy thông tin hình ảnh
        $imageInfo = $hanghoa->GetHinhAnhById($imageId);

        if (!$imageInfo) {
            throw new Exception("Không tìm thấy hình ảnh");
        }

        // Lấy tên file không có phần mở rộng
        $fileName = pathinfo($imageInfo->ten_file, PATHINFO_FILENAME);

        // Tìm sản phẩm có tên trùng với tên file
        $matchingProducts = $hanghoa->FindProductsByExactName($fileName);

        if (empty($matchingProducts)) {
            throw new Exception("Không tìm thấy sản phẩm nào có tên trùng khớp với tên file '{$fileName}'");
        }

        // Áp dụng hình ảnh cho các sản phẩm tìm thấy
        $appliedCount = 0;
        $appliedProducts = [];

        foreach ($matchingProducts as $product) {
            if ($hanghoa->ApplyImageToProduct($product->idhanghoa, $imageId)) {
                $appliedCount++;
                $appliedProducts[] = $product->tenhanghoa;
            }
        }

        if ($appliedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Đã áp dụng hình ảnh cho {$appliedCount} sản phẩm: " . implode(", ", $appliedProducts)
            ]);
        } else {
            throw new Exception("Không thể áp dụng hình ảnh cho bất kỳ sản phẩm nào");
        }
    } else {
        throw new Exception("Dữ liệu không hợp lệ");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

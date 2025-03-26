<?php
require_once("../mod/database.php");
require_once("../mod/hanghoaCls.php");

// Đảm bảo có ID hàng hóa được cung cấp
if (!isset($_GET['idhanghoa']) || empty($_GET['idhanghoa'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu ID hàng hóa'
    ]);
    exit;
}

$idhanghoa = intval($_GET['idhanghoa']);
$hanghoaObj = new hanghoa();

// Lấy danh sách hình ảnh của sản phẩm
$images = $hanghoaObj->GetAllImagesForProduct($idhanghoa);

// Trả về kết quả dạng JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'images' => $images
]);

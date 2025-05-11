<?php
session_start();
require_once '../../elements_LQA/mod/giohangCls.php';
require_once '../../elements_LQA/mod/mtonkhoCls.php';

$giohang = new GioHang();
$tonkho = new MTonKho();

// Nhận dữ liệu JSON từ request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['productId']) && isset($data['quantity'])) {
    $productId = (int)$data['productId'];
    $quantity = (int)$data['quantity'];

    // Kiểm tra số lượng có hợp lệ
    if ($quantity < 1) {
        $response = [
            'success' => false,
            'message' => 'Số lượng không hợp lệ!'
        ];
    } else {
        // Kiểm tra số lượng tồn kho
        $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($productId);

        if (!$tonkhoInfo || $tonkhoInfo->soLuong == 0) {
            // Sản phẩm hết hàng
            $response = [
                'success' => false,
                'message' => 'Sản phẩm đã hết hàng!',
                'outOfStock' => true
            ];
        } elseif ($quantity > $tonkhoInfo->soLuong) {
            // Số lượng yêu cầu vượt quá số lượng tồn kho
            $response = [
                'success' => false,
                'message' => 'Số lượng tồn kho chỉ còn ' . $tonkhoInfo->soLuong . ' sản phẩm!',
                'availableQuantity' => $tonkhoInfo->soLuong
            ];
        } else {
            // Cập nhật số lượng trong giỏ hàng
            $result = $giohang->updateQuantity($productId, $quantity);
            $response = [
                'success' => $result,
                'message' => $result ? 'Cập nhật thành công' : 'Cập nhật thất bại'
            ];
        }
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ!'
    ];
}

// Trả về response dạng JSON
header('Content-Type: application/json');
echo json_encode($response);

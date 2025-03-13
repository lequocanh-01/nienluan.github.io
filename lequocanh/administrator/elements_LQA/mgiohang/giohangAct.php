<?php
session_start();
require_once '../../elements_LQA/mod/giohangCls.php';

// Debug information
error_log("Session data: " . print_r($_SESSION, true));
error_log("GET data: " . print_r($_GET, true));

// Kiểm tra đăng nhập
if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
    error_log("User not logged in");
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Vui lòng đăng nhập để sử dụng giỏ hàng']);
    exit();
}

$giohang = new GioHang();

// Kiểm tra hành động từ GET
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    error_log("Action requested: " . $action);
    
    $productId = isset($_GET['productId']) ? (int)$_GET['productId'] : null;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

    switch ($action) {
        case 'add':
            if ($productId) {
                try {
                    $result = $giohang->addToCart($productId, $quantity);
                    error_log("Add to cart result: " . ($result ? "success" : "failed"));
                    
                    if ($result) {
                        // Chuyển hướng đến trang giỏ hàng
                        if (isset($_SESSION['ADMIN'])) {
                            header('Location: ../mgiohang/giohangView.php');
                        } else {
                            header('Location: ../mgiohang/giohangView.php');
                        }
                        exit();
                    } else {
                        // Nếu thêm thất bại, quay lại trang trước
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        exit();
                    }
                } catch (Exception $e) {
                    error_log("Exception in add to cart: " . $e->getMessage());
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }
            } else {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            break;
        
        case 'clear':
            $giohang->clearCart();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        
        case 'removeSelected':
            // Nhận dữ liệu JSON từ request
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['productIds']) && is_array($data['productIds'])) {
                foreach ($data['productIds'] as $productId) {
                    $giohang->removeFromCart((int)$productId);
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }
            break;
        
        default:
            $_SESSION['error'] = 'Hành động không hợp lệ.';
            break;
    }
}

exit();
?>

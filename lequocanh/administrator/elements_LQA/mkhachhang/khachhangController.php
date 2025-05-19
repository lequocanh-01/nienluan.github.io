<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require_once 'khachhangSimple.php';

// Khởi tạo đối tượng KhachHang
$khachHangObj = new KhachHang();

// Xử lý các hành động
$action = isset($_GET['act']) ? $_GET['act'] : '';
$customers = [];

try {
    switch ($action) {
        case 'add':
            // Hiển thị form thêm khách hàng
            include 'khachhangAdd.php';
            exit;
            
        case 'edit':
            // Lấy thông tin khách hàng cần sửa
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $customer = $khachHangObj->getById($id);
            
            if (!$customer) {
                $_SESSION['error_message'] = 'Không tìm thấy khách hàng!';
                header('Location: ?req=khachhangview');
                exit;
            }
            
            // Hiển thị form sửa khách hàng
            include 'khachhangEdit.php';
            exit;
            
        case 'detail':
            // Lấy thông tin chi tiết khách hàng
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $customer = $khachHangObj->getById($id);
            
            if (!$customer) {
                $_SESSION['error_message'] = 'Không tìm thấy khách hàng!';
                header('Location: ?req=khachhangview');
                exit;
            }
            
            // Lấy lịch sử mua hàng
            $orderHistory = $khachHangObj->getOrderHistory($customer['username']);
            
            // Lấy sản phẩm đã mua
            $purchasedProducts = $khachHangObj->getPurchasedProducts($customer['username']);
            
            // Hiển thị chi tiết khách hàng
            include 'khachhangDetail.php';
            exit;
            
        case 'delete':
            // Xóa khách hàng
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $result = $khachHangObj->delete($id);
            
            if ($result) {
                $_SESSION['success_message'] = 'Xóa khách hàng thành công!';
            } else {
                $_SESSION['error_message'] = 'Xóa khách hàng thất bại!';
            }
            
            header('Location: ?req=khachhangview');
            exit;
            
        default:
            // Xử lý tìm kiếm
            $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
            $searchField = isset($_GET['field']) ? $_GET['field'] : 'all';
            
            if (!empty($searchKeyword)) {
                $customers = $khachHangObj->search($searchKeyword, $searchField);
            } else {
                // Lấy tất cả khách hàng
                $customers = $khachHangObj->getAll();
            }
            
            // Hiển thị danh sách khách hàng
            include 'khachhangView.php';
            break;
    }
} catch (Exception $e) {
    // Xử lý lỗi
    $_SESSION['error_message'] = 'Đã xảy ra lỗi: ' . $e->getMessage();
    
    // Hiển thị trang demo nếu có lỗi kết nối cơ sở dữ liệu
    if (strpos($e->getMessage(), 'database') !== false) {
        include 'khachhangDemo.php';
    } else {
        include 'khachhangView.php';
    }
}
?>

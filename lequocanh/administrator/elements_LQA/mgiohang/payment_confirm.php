<?php
session_start();
require_once '../../elements_LQA/mod/database.php';
require_once '../../elements_LQA/mod/giohangCls.php';
require_once '../../elements_LQA/mod/mtonkhoCls.php';

$giohang = new GioHang();

// Kiểm tra xem người dùng có thể sử dụng giỏ hàng không
if (!$giohang->canUseCart()) {
    if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
        // Lưu URL hiện tại để chuyển hướng lại sau khi đăng nhập
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ../../userLogin.php');
    } else {
        // Nếu là admin, chuyển hướng về trang quản trị
        header('Location: ../../index.php');
    }
    exit();
}

// Kiểm tra xem có thông tin đơn hàng trong session không
if (!isset($_SESSION['order_details']) || !isset($_SESSION['total_amount']) || !isset($_SESSION['order_code'])) {
    // Nếu không có thông tin đơn hàng, chuyển hướng về trang giỏ hàng
    header('Location: giohangView.php');
    exit();
}

// Kiểm tra xem có mã đơn hàng được gửi từ form không
if (!isset($_POST['order_code']) || $_POST['order_code'] !== $_SESSION['order_code']) {
    // Nếu mã đơn hàng không khớp, chuyển hướng về trang giỏ hàng
    header('Location: giohangView.php');
    exit();
}

// Lấy thông tin đơn hàng từ session
$orderDetails = $_SESSION['order_details'];
$totalAmount = $_SESSION['total_amount'];
$orderCode = $_SESSION['order_code'];

// Khởi tạo các đối tượng
$db = Database::getInstance();
$conn = $db->getConnection();
$giohang = new GioHang();
$tonkho = new MTonKho();

// Kiểm tra xem bảng orders đã tồn tại chưa
$checkTableSql = "SHOW TABLES LIKE 'orders'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

if ($checkTableStmt->rowCount() == 0) {
    // Bảng chưa tồn tại, tạo bảng orders
    $createOrdersTableSql = "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(50) NOT NULL,
        user_id VARCHAR(50),
        total_amount DECIMAL(15,2) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($createOrdersTableSql);

    // Tạo bảng order_items
    $createOrderItemsTableSql = "CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(15,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $conn->exec($createOrderItemsTableSql);
}

// Bắt đầu transaction
$conn->beginTransaction();

try {
    // Lấy user_id từ session (nếu đã đăng nhập)
    $userId = isset($_SESSION['USER']) ? $_SESSION['USER'] : null;

    // Thêm đơn hàng vào bảng orders
    $insertOrderSql = "INSERT INTO orders (order_code, user_id, total_amount, status, payment_method)
                      VALUES (?, ?, ?, 'pending', 'bank_transfer')";
    $insertOrderStmt = $conn->prepare($insertOrderSql);
    $insertOrderStmt->execute([$orderCode, $userId, $totalAmount]);

    // Lấy ID của đơn hàng vừa thêm
    $orderId = $conn->lastInsertId();

    // Thêm các sản phẩm vào bảng order_items
    foreach ($orderDetails as $item) {
        $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                              VALUES (?, ?, ?, ?)";
        $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
        $insertOrderItemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);

        // Cập nhật số lượng tồn kho (giảm số lượng)
        $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($item['id']);
        if ($tonkhoInfo) {
            // Sử dụng hàm updateSoLuong với isIncrement = false để giảm số lượng
            $tonkho->updateSoLuong($item['id'], $item['quantity'], false);

            // Ghi log để debug
            error_log("Đã cập nhật tồn kho cho sản phẩm ID: " . $item['id'] . ", giảm: " . $item['quantity']);
        } else {
            error_log("Không tìm thấy thông tin tồn kho cho sản phẩm ID: " . $item['id']);
        }

        // Xóa sản phẩm khỏi giỏ hàng
        $giohang->removeFromCart($item['id']);
    }

    // Commit transaction
    $conn->commit();

    // Xóa thông tin đơn hàng khỏi session
    unset($_SESSION['order_details']);
    unset($_SESSION['total_amount']);
    unset($_SESSION['order_code']);

    // Lưu thông báo thành công vào session
    $_SESSION['payment_success'] = true;
    $_SESSION['order_id'] = $orderId;

    // Chuyển hướng đến trang xác nhận đơn hàng
    header('Location: order_success.php?order_id=' . $orderId);
    exit();
} catch (PDOException $e) {
    // Rollback transaction nếu có lỗi
    $conn->rollBack();

    // Lưu thông báo lỗi vào session
    $_SESSION['payment_error'] = 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage();

    // Chuyển hướng về trang giỏ hàng
    header('Location: giohangView.php');
    exit();
}

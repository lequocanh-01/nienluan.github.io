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

// Kiểm tra xem có địa chỉ giao hàng không
if (!isset($_POST['shipping_address']) || empty($_POST['shipping_address'])) {
    // Nếu không có địa chỉ giao hàng, chuyển hướng về trang giỏ hàng
    $_SESSION['checkout_error'] = 'Vui lòng nhập địa chỉ giao hàng';
    header('Location: giohangView.php');
    exit();
}

// Lấy địa chỉ giao hàng
$shippingAddress = trim($_POST['shipping_address']);

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

// Kiểm tra xem bảng orders có cột shipping_address không
$checkShippingAddressColumnSql = "SHOW COLUMNS FROM orders LIKE 'shipping_address'";
$checkShippingAddressColumnStmt = $conn->prepare($checkShippingAddressColumnSql);
$checkShippingAddressColumnStmt->execute();
$hasShippingAddressColumn = ($checkShippingAddressColumnStmt->rowCount() > 0);

// Nếu không có cột shipping_address, thêm cột này vào bảng orders
if (!$hasShippingAddressColumn) {
    try {
        $addShippingAddressColumnSql = "ALTER TABLE orders ADD COLUMN shipping_address TEXT AFTER user_id";
        $conn->exec($addShippingAddressColumnSql);
        error_log("Đã thêm cột shipping_address vào bảng orders");
    } catch (PDOException $e) {
        error_log("Lỗi khi thêm cột shipping_address: " . $e->getMessage());
    }
}

// Kiểm tra xem bảng orders có các cột thông báo không
$notificationColumns = [
    'pending_read' => "SHOW COLUMNS FROM orders LIKE 'pending_read'",
    'approved_read' => "SHOW COLUMNS FROM orders LIKE 'approved_read'",
    'cancelled_read' => "SHOW COLUMNS FROM orders LIKE 'cancelled_read'"
];

$missingColumns = [];
foreach ($notificationColumns as $column => $sql) {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $missingColumns[] = $column;
    }
}

// Nếu thiếu các cột thông báo, thêm vào
if (!empty($missingColumns)) {
    try {
        foreach ($missingColumns as $column) {
            $addColumnSql = "ALTER TABLE orders ADD COLUMN $column TINYINT(1) NOT NULL DEFAULT 0";
            $conn->exec($addColumnSql);
            error_log("Đã thêm cột $column vào bảng orders");
        }
    } catch (PDOException $e) {
        error_log("Lỗi khi thêm các cột thông báo: " . $e->getMessage());
    }
}

// Bắt đầu transaction
$conn->beginTransaction();

try {
    // Lấy user_id từ session (nếu đã đăng nhập)
    $userId = isset($_SESSION['USER']) ? $_SESSION['USER'] : null;

    // Ghi log để debug
    error_log("Bắt đầu tạo đơn hàng: order_code=" . $orderCode . ", user_id=" . $userId . ", total_amount=" . $totalAmount);

    // Kiểm tra xem các cột thông báo có tồn tại không
    $hasNotificationColumns = true;
    foreach ($notificationColumns as $column => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $hasNotificationColumns = false;
            break;
        }
    }

    // Thêm đơn hàng vào bảng orders với trạng thái thông báo
    if ($hasNotificationColumns) {
        $insertOrderSql = "INSERT INTO orders (order_code, user_id, shipping_address, total_amount, status, payment_method, pending_read)
                          VALUES (?, ?, ?, ?, 'pending', 'bank_transfer', 0)";
    } else {
        $insertOrderSql = "INSERT INTO orders (order_code, user_id, shipping_address, total_amount, status, payment_method)
                          VALUES (?, ?, ?, ?, 'pending', 'bank_transfer')";
    }

    $insertOrderStmt = $conn->prepare($insertOrderSql);
    $insertOrderStmt->execute([$orderCode, $userId, $shippingAddress, $totalAmount]);

    // Lấy ID của đơn hàng vừa thêm
    $orderId = $conn->lastInsertId();

    error_log("Đã tạo đơn hàng thành công: order_id=" . $orderId);

    // Thêm các sản phẩm vào bảng order_items
    foreach ($orderDetails as $item) {
        try {
            error_log("Thêm sản phẩm vào đơn hàng: product_id=" . $item['id'] . ", quantity=" . $item['quantity'] . ", price=" . $item['price']);

            $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                                  VALUES (?, ?, ?, ?)";
            $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
            $insertOrderItemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);

            error_log("Đã thêm sản phẩm vào đơn hàng thành công");

            // Cập nhật số lượng tồn kho (giảm số lượng)
            $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($item['id']);
            if ($tonkhoInfo) {
                error_log("Tồn kho hiện tại của sản phẩm ID " . $item['id'] . ": " . $tonkhoInfo->soLuong);

                // Sử dụng hàm updateSoLuong với isIncrement = false để giảm số lượng
                $updateResult = $tonkho->updateSoLuong($item['id'], $item['quantity'], false);

                if ($updateResult) {
                    error_log("Đã cập nhật tồn kho thành công cho sản phẩm ID: " . $item['id'] . ", giảm: " . $item['quantity']);

                    // Kiểm tra lại tồn kho sau khi cập nhật
                    $updatedTonkhoInfo = $tonkho->getTonKhoByIdHangHoa($item['id']);
                    if ($updatedTonkhoInfo) {
                        error_log("Tồn kho sau khi cập nhật của sản phẩm ID " . $item['id'] . ": " . $updatedTonkhoInfo->soLuong);
                    }
                } else {
                    error_log("Cập nhật tồn kho thất bại cho sản phẩm ID: " . $item['id']);
                }
            } else {
                error_log("Không tìm thấy thông tin tồn kho cho sản phẩm ID: " . $item['id'] . ", tạo mới tồn kho");
                // Tạo mới tồn kho với số lượng ban đầu là số lượng đặt hàng (để trừ đi)
                $tonkho->updateSoLuong($item['id'], $item['quantity'], false);
            }

            // Xóa sản phẩm khỏi giỏ hàng
            $giohang->removeFromCart($item['id']);
            error_log("Đã xóa sản phẩm ID: " . $item['id'] . " khỏi giỏ hàng");
        } catch (Exception $e) {
            error_log("Lỗi khi xử lý sản phẩm ID: " . $item['id'] . ": " . $e->getMessage());
            throw $e; // Ném lại ngoại lệ để rollback transaction
        }
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

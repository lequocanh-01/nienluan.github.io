<?php
require_once './elements_LQA/mod/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra và tạo bảng orders nếu chưa tồn tại
$checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
$checkOrdersTableStmt->execute();

if ($checkOrdersTableStmt->rowCount() == 0) {
    echo "<h3>Tạo bảng orders</h3>";
    
    $createOrdersTableSql = "CREATE TABLE `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_code` varchar(50) NOT NULL,
        `user_id` varchar(50) DEFAULT NULL,
        `total_amount` decimal(15,2) NOT NULL,
        `status` enum('pending','approved','cancelled') NOT NULL DEFAULT 'pending',
        `payment_method` varchar(50) NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    try {
        $conn->exec($createOrdersTableSql);
        echo "Bảng orders đã được tạo thành công.<br>";
    } catch (PDOException $e) {
        echo "Lỗi khi tạo bảng orders: " . $e->getMessage() . "<br>";
    }
}

// Kiểm tra và tạo bảng order_items nếu chưa tồn tại
$checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
$checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
$checkOrderItemsTableStmt->execute();

if ($checkOrderItemsTableStmt->rowCount() == 0) {
    echo "<h3>Tạo bảng order_items</h3>";
    
    $createOrderItemsTableSql = "CREATE TABLE `order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` int(11) NOT NULL,
        `price` decimal(15,2) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        KEY `product_id` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    try {
        $conn->exec($createOrderItemsTableSql);
        echo "Bảng order_items đã được tạo thành công.<br>";
    } catch (PDOException $e) {
        echo "Lỗi khi tạo bảng order_items: " . $e->getMessage() . "<br>";
    }
}

// Kiểm tra xem đã có dữ liệu trong bảng orders chưa
$checkOrdersDataSql = "SELECT COUNT(*) FROM orders";
$checkOrdersDataStmt = $conn->prepare($checkOrdersDataSql);
$checkOrdersDataStmt->execute();
$ordersCount = $checkOrdersDataStmt->fetchColumn();

if ($ordersCount == 0) {
    echo "<h3>Thêm dữ liệu mẫu vào bảng orders</h3>";
    
    // Thêm dữ liệu mẫu vào bảng orders
    $insertOrderSql = "INSERT INTO `orders` (`order_code`, `user_id`, `total_amount`, `status`, `payment_method`, `created_at`, `updated_at`) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertOrderStmt = $conn->prepare($insertOrderSql);
    
    $orderCode = 'ORDER' . time();
    $userId = null; // Khách vãng lai
    $totalAmount = 24000000.00;
    $status = 'pending';
    $paymentMethod = 'bank_transfer';
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    
    try {
        $insertOrderStmt->execute([$orderCode, $userId, $totalAmount, $status, $paymentMethod, $createdAt, $updatedAt]);
        $orderId = $conn->lastInsertId();
        echo "Đã thêm đơn hàng mẫu với ID: $orderId<br>";
        
        // Kiểm tra xem bảng hanghoa có dữ liệu không
        $checkProductsSql = "SELECT idhanghoa, tenhanghoa, dongia FROM hanghoa LIMIT 1";
        $checkProductsStmt = $conn->prepare($checkProductsSql);
        $checkProductsStmt->execute();
        $product = $checkProductsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Thêm dữ liệu mẫu vào bảng order_items
            $insertOrderItemSql = "INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price`, `created_at`) 
                                  VALUES (?, ?, ?, ?, ?)";
            $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
            
            $productId = $product['idhanghoa'];
            $quantity = 1;
            $price = $product['dongia'];
            
            $insertOrderItemStmt->execute([$orderId, $productId, $quantity, $price, $createdAt]);
            echo "Đã thêm sản phẩm vào đơn hàng mẫu.<br>";
        } else {
            echo "Không tìm thấy sản phẩm nào trong bảng hanghoa để thêm vào đơn hàng.<br>";
        }
    } catch (PDOException $e) {
        echo "Lỗi khi thêm dữ liệu mẫu: " . $e->getMessage() . "<br>";
    }
}

echo "<p>Hoàn tất. <a href='index.php?req=orders'>Quay lại trang quản lý đơn hàng</a></p>";
?>

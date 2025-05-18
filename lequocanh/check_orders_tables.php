<?php
// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Kiểm tra bảng orders và order_items</h2>";

try {
    // Kiểm tra xem bảng orders đã tồn tại chưa
    $checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
    $checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
    $checkOrdersTableStmt->execute();
    $ordersTableExists = ($checkOrdersTableStmt->rowCount() > 0);
    
    // Kiểm tra xem bảng order_items đã tồn tại chưa
    $checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
    $checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
    $checkOrderItemsTableStmt->execute();
    $orderItemsTableExists = ($checkOrderItemsTableStmt->rowCount() > 0);
    
    echo "<h3>Kết quả kiểm tra:</h3>";
    echo "<ul>";
    echo "<li>Bảng orders: " . ($ordersTableExists ? "<span style='color:green'>Đã tồn tại</span>" : "<span style='color:red'>Chưa tồn tại</span>") . "</li>";
    echo "<li>Bảng order_items: " . ($orderItemsTableExists ? "<span style='color:green'>Đã tồn tại</span>" : "<span style='color:red'>Chưa tồn tại</span>") . "</li>";
    echo "</ul>";
    
    // Nếu bảng orders chưa tồn tại, tạo bảng
    if (!$ordersTableExists) {
        echo "<h3>Tạo bảng orders</h3>";
        
        $createOrdersTableSql = "CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_code VARCHAR(50) NOT NULL,
            user_id VARCHAR(50),
            total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
            payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
            payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        try {
            $conn->exec($createOrdersTableSql);
            echo "<p style='color:green'>Đã tạo bảng orders thành công!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Lỗi khi tạo bảng orders: " . $e->getMessage() . "</p>";
        }
    }
    
    // Nếu bảng order_items chưa tồn tại, tạo bảng
    if (!$orderItemsTableExists) {
        echo "<h3>Tạo bảng order_items</h3>";
        
        $createOrderItemsTableSql = "CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES hanghoa(idhanghoa) ON DELETE RESTRICT
        )";
        
        try {
            $conn->exec($createOrderItemsTableSql);
            echo "<p style='color:green'>Đã tạo bảng order_items thành công!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Lỗi khi tạo bảng order_items: " . $e->getMessage() . "</p>";
        }
    }
    
    // Kiểm tra dữ liệu trong bảng orders nếu đã tồn tại
    if ($ordersTableExists) {
        $countOrdersSql = "SELECT COUNT(*) as count FROM orders";
        $countOrdersStmt = $conn->prepare($countOrdersSql);
        $countOrdersStmt->execute();
        $countOrders = $countOrdersStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Số lượng đơn hàng: " . $countOrders['count'] . "</h3>";
        
        // Nếu không có đơn hàng nào, thêm đơn hàng mẫu
        if ($countOrders['count'] == 0) {
            echo "<h3>Thêm đơn hàng mẫu</h3>";
            
            // Tạo mã đơn hàng
            $orderCode = 'ORD' . date('YmdHis');
            $totalAmount = 100000;
            $status = 'pending';
            $paymentMethod = 'bank_transfer';
            $paymentStatus = 'pending';
            $createdAt = date('Y-m-d H:i:s');
            
            $insertOrderSql = "INSERT INTO orders (order_code, total_amount, status, payment_method, payment_status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?)";
            $insertOrderStmt = $conn->prepare($insertOrderSql);
            
            try {
                $insertOrderStmt->execute([$orderCode, $totalAmount, $status, $paymentMethod, $paymentStatus, $createdAt]);
                $orderId = $conn->lastInsertId();
                echo "<p style='color:green'>Đã thêm đơn hàng mẫu với ID: " . $orderId . "</p>";
                
                // Thêm chi tiết đơn hàng nếu bảng order_items đã tồn tại
                if ($orderItemsTableExists) {
                    // Lấy một sản phẩm từ bảng hanghoa
                    $getProductSql = "SELECT idhanghoa, giathamkhao FROM hanghoa LIMIT 1";
                    $getProductStmt = $conn->prepare($getProductSql);
                    $getProductStmt->execute();
                    $product = $getProductStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($product) {
                        $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at) 
                                             VALUES (?, ?, ?, ?, ?)";
                        $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
                        
                        $productId = $product['idhanghoa'];
                        $quantity = 1;
                        $price = $product['giathamkhao'];
                        
                        $insertOrderItemStmt->execute([$orderId, $productId, $quantity, $price, $createdAt]);
                        echo "<p style='color:green'>Đã thêm chi tiết đơn hàng mẫu.</p>";
                    } else {
                        echo "<p style='color:orange'>Không tìm thấy sản phẩm nào trong bảng hanghoa để thêm vào đơn hàng.</p>";
                    }
                }
            } catch (PDOException $e) {
                echo "<p style='color:red'>Lỗi khi thêm đơn hàng mẫu: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p><a href='administrator/index.php?req=orders' class='btn btn-primary'>Quay lại trang quản lý đơn hàng</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage() . "</p>";
}
?>

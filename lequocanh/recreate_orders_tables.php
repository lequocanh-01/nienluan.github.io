<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Tạo lại bảng orders và order_items</h2>";

// Kiểm tra xem bảng orders và order_items có tồn tại không
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$hasOrdersTable = in_array('orders', $tables);
$hasOrderItemsTable = in_array('order_items', $tables);

echo "<h3>Trạng thái hiện tại:</h3>";
echo "<ul>";
echo "<li>Bảng orders: " . ($hasOrdersTable ? "<span style='color: green;'>Tồn tại</span>" : "<span style='color: red;'>Không tồn tại</span>") . "</li>";
echo "<li>Bảng order_items: " . ($hasOrderItemsTable ? "<span style='color: green;'>Tồn tại</span>" : "<span style='color: red;'>Không tồn tại</span>") . "</li>";
echo "</ul>";

// Xóa bảng cũ nếu tồn tại
if (isset($_POST['recreate'])) {
    try {
        // Xóa bảng order_items trước nếu tồn tại (vì có khóa ngoại)
        if ($hasOrderItemsTable) {
            $conn->exec("DROP TABLE order_items");
            echo "<p style='color: green;'>Đã xóa bảng order_items.</p>";
        }
        
        // Xóa bảng orders nếu tồn tại
        if ($hasOrdersTable) {
            $conn->exec("DROP TABLE orders");
            echo "<p style='color: green;'>Đã xóa bảng orders.</p>";
        }
        
        // Tạo lại bảng orders
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
        $conn->exec($createOrdersTableSql);
        echo "<p style='color: green;'>Đã tạo lại bảng orders.</p>";
        
        // Tạo lại bảng order_items
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
        $conn->exec($createOrderItemsTableSql);
        echo "<p style='color: green;'>Đã tạo lại bảng order_items.</p>";
        
        // Thêm dữ liệu mẫu
        $orderCode = 'ORD' . date('YmdHis');
        $totalAmount = 100000;
        $status = 'pending';
        $paymentMethod = 'bank_transfer';
        $paymentStatus = 'pending';
        $createdAt = date('Y-m-d H:i:s');
        
        $insertOrderSql = "INSERT INTO orders (order_code, total_amount, status, payment_method, payment_status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $insertOrderStmt = $conn->prepare($insertOrderSql);
        $insertOrderStmt->execute([$orderCode, $totalAmount, $status, $paymentMethod, $paymentStatus, $createdAt]);
        $orderId = $conn->lastInsertId();
        
        echo "<p style='color: green;'>Đã thêm đơn hàng mẫu với ID: " . $orderId . "</p>";
        
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
            echo "<p style='color: green;'>Đã thêm chi tiết đơn hàng mẫu.</p>";
        } else {
            echo "<p style='color: red;'>Không tìm thấy sản phẩm nào trong bảng hanghoa để thêm vào đơn hàng.</p>";
        }
        
        // Thêm đơn hàng thứ hai
        $orderCode2 = 'ORD' . date('YmdHis') . '2';
        $totalAmount2 = 200000;
        $status2 = 'approved';
        
        $insertOrderSql = "INSERT INTO orders (order_code, total_amount, status, payment_method, payment_status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $insertOrderStmt = $conn->prepare($insertOrderSql);
        $insertOrderStmt->execute([$orderCode2, $totalAmount2, $status2, $paymentMethod, $paymentStatus, $createdAt]);
        $orderId2 = $conn->lastInsertId();
        
        echo "<p style='color: green;'>Đã thêm đơn hàng mẫu thứ hai với ID: " . $orderId2 . "</p>";
        
        if ($product) {
            $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at) 
                                 VALUES (?, ?, ?, ?, ?)";
            $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
            
            $quantity2 = 2;
            
            $insertOrderItemStmt->execute([$orderId2, $productId, $quantity2, $price, $createdAt]);
            echo "<p style='color: green;'>Đã thêm chi tiết đơn hàng mẫu thứ hai.</p>";
        }
        
        // Kiểm tra lại sau khi tạo
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $hasOrdersTable = in_array('orders', $tables);
        $hasOrderItemsTable = in_array('order_items', $tables);
        
        echo "<h3>Trạng thái sau khi tạo lại:</h3>";
        echo "<ul>";
        echo "<li>Bảng orders: " . ($hasOrdersTable ? "<span style='color: green;'>Tồn tại</span>" : "<span style='color: red;'>Không tồn tại</span>") . "</li>";
        echo "<li>Bảng order_items: " . ($hasOrderItemsTable ? "<span style='color: green;'>Tồn tại</span>" : "<span style='color: red;'>Không tồn tại</span>") . "</li>";
        echo "</ul>";
        
        if ($hasOrdersTable) {
            $count = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            echo "<p>Số lượng đơn hàng: " . $count . "</p>";
            
            if ($count > 0) {
                $orders = $conn->query("SELECT * FROM orders")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Danh sách đơn hàng:</h4>";
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($orders[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                foreach ($orders as $order) {
                    echo "<tr>";
                    foreach ($order as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
        if ($hasOrderItemsTable) {
            $count = $conn->query("SELECT COUNT(*) FROM order_items")->fetchColumn();
            echo "<p>Số lượng chi tiết đơn hàng: " . $count . "</p>";
            
            if ($count > 0) {
                $orderItems = $conn->query("SELECT oi.*, h.tenhanghoa 
                                          FROM order_items oi 
                                          JOIN hanghoa h ON oi.product_id = h.idhanghoa")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Danh sách chi tiết đơn hàng:</h4>";
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($orderItems[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                foreach ($orderItems as $item) {
                    echo "<tr>";
                    foreach ($item as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
    }
}

// Form để tạo lại bảng
echo "<form method='post' onsubmit=\"return confirm('Bạn có chắc chắn muốn xóa và tạo lại bảng orders và order_items?');\">";
echo "<button type='submit' name='recreate' style='background-color: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;'>Xóa và tạo lại bảng orders và order_items</button>";
echo "</form>";

echo "<p><a href='administrator/index.php?req=orders' style='background-color: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

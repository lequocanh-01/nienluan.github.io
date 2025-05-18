<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Kiểm tra tất cả các bảng liên quan đến đơn hàng</h2>";

// Lấy danh sách tất cả các bảng trong cơ sở dữ liệu
try {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Danh sách tất cả các bảng trong cơ sở dữ liệu:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // Tìm tất cả các bảng có tên chứa 'order'
    $orderTables = array_filter($tables, function($table) {
        return stripos($table, 'order') !== false;
    });
    
    echo "<h3>Các bảng liên quan đến đơn hàng:</h3>";
    if (empty($orderTables)) {
        echo "<p>Không tìm thấy bảng nào liên quan đến đơn hàng.</p>";
    } else {
        echo "<ul>";
        foreach ($orderTables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Kiểm tra cấu trúc và dữ liệu của từng bảng
        foreach ($orderTables as $table) {
            echo "<h4>Cấu trúc bảng " . htmlspecialchars($table) . ":</h4>";
            $columns = $conn->query("DESCRIBE " . $table)->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                foreach ($column as $key => $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Đếm số lượng bản ghi
            $count = $conn->query("SELECT COUNT(*) FROM " . $table)->fetchColumn();
            echo "<p>Số lượng bản ghi: " . $count . "</p>";
            
            // Hiển thị dữ liệu mẫu nếu có
            if ($count > 0) {
                $data = $conn->query("SELECT * FROM " . $table . " LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Dữ liệu mẫu từ bảng " . htmlspecialchars($table) . ":</h4>";
                echo "<table border='1'>";
                
                // Hiển thị tiêu đề cột
                echo "<tr>";
                foreach (array_keys($data[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                // Hiển thị dữ liệu
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Không có dữ liệu trong bảng này.</p>";
            }
        }
    }
    
    // Kiểm tra bảng orders cụ thể
    if (in_array('orders', $tables)) {
        echo "<h3>Kiểm tra chi tiết bảng orders:</h3>";
        
        // Kiểm tra các ràng buộc khóa ngoại
        $foreignKeys = $conn->query("
            SELECT 
                TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                REFERENCED_TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'orders'
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Ràng buộc khóa ngoại:</h4>";
        if (empty($foreignKeys)) {
            echo "<p>Không có ràng buộc khóa ngoại.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>Bảng</th><th>Cột</th><th>Tên ràng buộc</th><th>Bảng tham chiếu</th><th>Cột tham chiếu</th></tr>";
            foreach ($foreignKeys as $key) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($key['TABLE_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['COLUMN_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['CONSTRAINT_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['REFERENCED_TABLE_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['REFERENCED_COLUMN_NAME']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Kiểm tra các bảng tham chiếu đến orders
        $referencingTables = $conn->query("
            SELECT 
                TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'orders'
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Các bảng tham chiếu đến orders:</h4>";
        if (empty($referencingTables)) {
            echo "<p>Không có bảng nào tham chiếu đến orders.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>Bảng</th><th>Cột</th><th>Tên ràng buộc</th><th>Bảng tham chiếu</th><th>Cột tham chiếu</th></tr>";
            foreach ($referencingTables as $key) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($key['TABLE_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['COLUMN_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['CONSTRAINT_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['REFERENCED_TABLE_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($key['REFERENCED_COLUMN_NAME']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Kiểm tra bảng user
    if (in_array('user', $tables)) {
        echo "<h3>Kiểm tra bảng user:</h3>";
        $userColumns = $conn->query("DESCRIBE user")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Cấu trúc bảng user:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($userColumns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Đếm số lượng người dùng
        $userCount = $conn->query("SELECT COUNT(*) FROM user")->fetchColumn();
        echo "<p>Số lượng người dùng: " . $userCount . "</p>";
        
        if ($userCount > 0) {
            $users = $conn->query("SELECT username, hoten FROM user LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Danh sách người dùng:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>Username</th><th>Họ tên</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['hoten']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

// Thêm nút để tạo lại bảng orders nếu cần
echo "<h3>Tùy chọn sửa chữa:</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='recreate_orders' style='background-color: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;'>Xóa và tạo lại bảng orders</button>";
echo "</form>";

// Xử lý khi người dùng nhấn nút tạo lại bảng
if (isset($_POST['recreate_orders'])) {
    try {
        // Xóa bảng order_items trước nếu tồn tại (vì có khóa ngoại)
        if (in_array('order_items', $tables)) {
            $conn->exec("DROP TABLE order_items");
            echo "<p style='color: green;'>Đã xóa bảng order_items.</p>";
        }
        
        // Xóa bảng orders nếu tồn tại
        if (in_array('orders', $tables)) {
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
        }
        
        echo "<p><a href='administrator/index.php?req=orders' style='background-color: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Lỗi khi tạo lại bảng: " . $e->getMessage() . "</p>";
    }
}
?>

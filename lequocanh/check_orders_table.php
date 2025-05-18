<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra bảng orders
    $checkOrdersTable = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($checkOrdersTable->rowCount() > 0) {
        echo "<h3>Bảng orders tồn tại</h3>";
        
        // Lấy cấu trúc bảng
        $describeOrders = $conn->query("DESCRIBE orders");
        echo "<h4>Cấu trúc bảng orders:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describeOrders->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Lấy số lượng bản ghi
        $countOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch(PDO::FETCH_ASSOC);
        echo "<p>Số lượng đơn hàng: " . $countOrders['count'] . "</p>";
        
        // Lấy danh sách đơn hàng
        if ($countOrders['count'] > 0) {
            $orders = $conn->query("SELECT * FROM orders");
            echo "<h4>Danh sách đơn hàng:</h4>";
            echo "<table border='1'>";
            
            // Lấy tên các cột
            $firstRow = $orders->fetch(PDO::FETCH_ASSOC);
            if ($firstRow) {
                echo "<tr>";
                foreach (array_keys($firstRow) as $column) {
                    echo "<th>" . $column . "</th>";
                }
                echo "</tr>";
                
                // Hiển thị dữ liệu của hàng đầu tiên
                echo "<tr>";
                foreach ($firstRow as $value) {
                    echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
                }
                echo "</tr>";
                
                // Hiển thị các hàng còn lại
                while ($row = $orders->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
    } else {
        echo "<h3>Bảng orders không tồn tại</h3>";
    }
    
    // Kiểm tra bảng order_items
    $checkOrderItemsTable = $conn->query("SHOW TABLES LIKE 'order_items'");
    if ($checkOrderItemsTable->rowCount() > 0) {
        echo "<h3>Bảng order_items tồn tại</h3>";
        
        // Lấy cấu trúc bảng
        $describeOrderItems = $conn->query("DESCRIBE order_items");
        echo "<h4>Cấu trúc bảng order_items:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describeOrderItems->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Lấy số lượng bản ghi
        $countOrderItems = $conn->query("SELECT COUNT(*) as count FROM order_items")->fetch(PDO::FETCH_ASSOC);
        echo "<p>Số lượng chi tiết đơn hàng: " . $countOrderItems['count'] . "</p>";
    } else {
        echo "<h3>Bảng order_items không tồn tại</h3>";
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

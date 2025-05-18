<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra bảng tonkho
    $checkTonkhoTable = $conn->query("SHOW TABLES LIKE 'tonkho'");
    if ($checkTonkhoTable->rowCount() > 0) {
        echo "<h3>Bảng tonkho tồn tại</h3>";
        
        // Lấy cấu trúc bảng
        $describeTonkho = $conn->query("DESCRIBE tonkho");
        echo "<h4>Cấu trúc bảng tonkho:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describeTonkho->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Lấy số lượng bản ghi
        $countTonkho = $conn->query("SELECT COUNT(*) as count FROM tonkho")->fetch(PDO::FETCH_ASSOC);
        echo "<p>Số lượng bản ghi tồn kho: " . $countTonkho['count'] . "</p>";
        
        // Lấy danh sách tồn kho
        if ($countTonkho['count'] > 0) {
            $tonkho = $conn->query("SELECT t.*, h.tenhanghoa 
                                   FROM tonkho t 
                                   JOIN hanghoa h ON t.idhanghoa = h.idhanghoa 
                                   ORDER BY t.idTonKho");
            echo "<h4>Danh sách tồn kho:</h4>";
            echo "<table border='1'>";
            
            // Lấy tên các cột
            $firstRow = $tonkho->fetch(PDO::FETCH_ASSOC);
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
                while ($row = $tonkho->fetch(PDO::FETCH_ASSOC)) {
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
        echo "<h3>Bảng tonkho không tồn tại</h3>";
    }
    
    // Kiểm tra các sản phẩm trong order_items
    echo "<h3>Kiểm tra sản phẩm trong order_items</h3>";
    $orderItems = $conn->query("SELECT oi.*, h.tenhanghoa 
                               FROM order_items oi 
                               JOIN hanghoa h ON oi.product_id = h.idhanghoa 
                               ORDER BY oi.id");
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Order ID</th><th>Product ID</th><th>Tên sản phẩm</th><th>Số lượng</th><th>Giá</th></tr>";
    
    while ($row = $orderItems->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['order_id'] . "</td>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['tenhanghoa'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $row['price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra hàm updateSoLuong trong MTonKho
    echo "<h3>Kiểm tra log của hàm updateSoLuong</h3>";
    $logs = $conn->query("SELECT * FROM system_logs WHERE message LIKE '%Updating tonkho%' ORDER BY created_at DESC LIMIT 20");
    
    if ($logs && $logs->rowCount() > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Message</th><th>Created At</th></tr>";
        
        while ($row = $logs->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['message'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Không tìm thấy log hoặc bảng system_logs không tồn tại.</p>";
        
        // Kiểm tra xem bảng system_logs có tồn tại không
        $checkSystemLogsTable = $conn->query("SHOW TABLES LIKE 'system_logs'");
        if ($checkSystemLogsTable->rowCount() == 0) {
            echo "<p>Bảng system_logs không tồn tại. Tạo bảng này để lưu log.</p>";
            
            // Tạo bảng system_logs
            $createSystemLogsTable = "CREATE TABLE IF NOT EXISTS system_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($createSystemLogsTable);
            echo "<p>Đã tạo bảng system_logs.</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

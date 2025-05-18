<?php
require_once './administrator/elements_LQA/mod/database.php';
require_once './administrator/elements_LQA/mod/mtonkhoCls.php';

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();
$tonkho = new MTonKho();

echo "<h1>Công cụ kiểm tra và sửa lỗi tồn kho</h1>";

// Kiểm tra bảng tonkho
echo "<h2>Kiểm tra bảng tonkho</h2>";
$checkTonkhoTableSql = "SHOW TABLES LIKE 'tonkho'";
$checkTonkhoTableStmt = $conn->prepare($checkTonkhoTableSql);
$checkTonkhoTableStmt->execute();

if ($checkTonkhoTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng tonkho không tồn tại. Đang tạo bảng...</p>";
    
    // Tạo bảng tonkho
    $createTonkhoTableSql = "CREATE TABLE IF NOT EXISTS tonkho (
        idTonKho INT AUTO_INCREMENT PRIMARY KEY,
        idhanghoa INT NOT NULL,
        soLuong INT NOT NULL DEFAULT 0,
        soLuongToiThieu INT NOT NULL DEFAULT 0,
        viTri VARCHAR(255),
        ngayCapNhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idhanghoa) REFERENCES hanghoa(idhanghoa)
    )";
    
    try {
        $conn->exec($createTonkhoTableSql);
        echo "<p style='color: green;'>Đã tạo bảng tonkho thành công!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Lỗi khi tạo bảng tonkho: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: green;'>Bảng tonkho đã tồn tại.</p>";
}

// Kiểm tra bảng system_logs
echo "<h2>Kiểm tra bảng system_logs</h2>";
$checkLogsTableSql = "SHOW TABLES LIKE 'system_logs'";
$checkLogsTableStmt = $conn->prepare($checkLogsTableSql);
$checkLogsTableStmt->execute();

if ($checkLogsTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng system_logs không tồn tại. Đang tạo bảng...</p>";
    
    // Tạo bảng system_logs
    $createLogsTableSql = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    try {
        $conn->exec($createLogsTableSql);
        echo "<p style='color: green;'>Đã tạo bảng system_logs thành công!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Lỗi khi tạo bảng system_logs: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: green;'>Bảng system_logs đã tồn tại.</p>";
}

// Kiểm tra tồn kho của tất cả sản phẩm
echo "<h2>Kiểm tra tồn kho của tất cả sản phẩm</h2>";

// Lấy danh sách tất cả sản phẩm
$getAllProductsSql = "SELECT idhanghoa, tenhanghoa FROM hanghoa";
$getAllProductsStmt = $conn->prepare($getAllProductsSql);
$getAllProductsStmt->execute();
$allProducts = $getAllProductsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'>";
echo "<th>ID</th>";
echo "<th>Tên sản phẩm</th>";
echo "<th>Tồn kho hiện tại</th>";
echo "<th>Trạng thái</th>";
echo "</tr>";

foreach ($allProducts as $product) {
    $productId = $product['idhanghoa'];
    $productName = $product['tenhanghoa'];
    
    // Kiểm tra tồn kho
    $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($productId);
    
    if ($tonkhoInfo) {
        $currentStock = $tonkhoInfo->soLuong;
        $status = "OK";
        $statusColor = "green";
    } else {
        $currentStock = "Chưa có";
        $status = "Cần tạo mới";
        $statusColor = "orange";
        
        // Tạo mới tồn kho với số lượng ban đầu là 0
        $tonkho->updateSoLuong($productId, 0, true);
        
        // Kiểm tra lại sau khi tạo mới
        $newTonkhoInfo = $tonkho->getTonKhoByIdHangHoa($productId);
        if ($newTonkhoInfo) {
            $currentStock = $newTonkhoInfo->soLuong;
            $status = "Đã tạo mới";
            $statusColor = "blue";
        }
    }
    
    echo "<tr>";
    echo "<td>" . $productId . "</td>";
    echo "<td>" . htmlspecialchars($productName) . "</td>";
    echo "<td>" . $currentStock . "</td>";
    echo "<td style='color: " . $statusColor . ";'>" . $status . "</td>";
    echo "</tr>";
}

echo "</table>";

// Kiểm tra đơn hàng và cập nhật tồn kho
echo "<h2>Kiểm tra đơn hàng và cập nhật tồn kho</h2>";

// Kiểm tra xem bảng orders có tồn tại không
$checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
$checkOrdersTableStmt->execute();

if ($checkOrdersTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng orders không tồn tại.</p>";
} else {
    echo "<p style='color: green;'>Bảng orders đã tồn tại.</p>";
    
    // Kiểm tra xem bảng order_items có tồn tại không
    $checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
    $checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
    $checkOrderItemsTableStmt->execute();
    
    if ($checkOrderItemsTableStmt->rowCount() == 0) {
        echo "<p style='color: red;'>Bảng order_items không tồn tại.</p>";
    } else {
        echo "<p style='color: green;'>Bảng order_items đã tồn tại.</p>";
        
        // Lấy danh sách đơn hàng đang ở trạng thái pending
        $getPendingOrdersSql = "SELECT id, order_code FROM orders WHERE status = 'pending'";
        $getPendingOrdersStmt = $conn->prepare($getPendingOrdersSql);
        $getPendingOrdersStmt->execute();
        $pendingOrders = $getPendingOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($pendingOrders) > 0) {
            echo "<p>Có " . count($pendingOrders) . " đơn hàng đang chờ xử lý:</p>";
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            echo "<th>ID</th>";
            echo "<th>Mã đơn hàng</th>";
            echo "<th>Sản phẩm</th>";
            echo "<th>Trạng thái tồn kho</th>";
            echo "</tr>";
            
            foreach ($pendingOrders as $order) {
                $orderId = $order['id'];
                $orderCode = $order['order_code'];
                
                // Lấy danh sách sản phẩm trong đơn hàng
                $getOrderItemsSql = "SELECT oi.product_id, oi.quantity, h.tenhanghoa 
                                    FROM order_items oi 
                                    JOIN hanghoa h ON oi.product_id = h.idhanghoa 
                                    WHERE oi.order_id = ?";
                $getOrderItemsStmt = $conn->prepare($getOrderItemsSql);
                $getOrderItemsStmt->execute([$orderId]);
                $orderItems = $getOrderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $rowspan = count($orderItems);
                $firstRow = true;
                
                foreach ($orderItems as $item) {
                    echo "<tr>";
                    
                    if ($firstRow) {
                        echo "<td rowspan='" . $rowspan . "'>" . $orderId . "</td>";
                        echo "<td rowspan='" . $rowspan . "'>" . $orderCode . "</td>";
                        $firstRow = false;
                    }
                    
                    echo "<td>" . htmlspecialchars($item['tenhanghoa']) . " (ID: " . $item['product_id'] . ", SL: " . $item['quantity'] . ")</td>";
                    
                    // Kiểm tra tồn kho
                    $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($item['product_id']);
                    
                    if ($tonkhoInfo) {
                        echo "<td style='color: green;'>Đã cập nhật tồn kho</td>";
                    } else {
                        echo "<td style='color: red;'>Chưa cập nhật tồn kho</td>";
                        
                        // Tạo mới tồn kho và cập nhật số lượng
                        $tonkho->updateSoLuong($item['product_id'], $item['quantity'], false);
                    }
                    
                    echo "</tr>";
                }
            }
            
            echo "</table>";
        } else {
            echo "<p>Không có đơn hàng nào đang chờ xử lý.</p>";
        }
    }
}

echo "<h2>Hoàn tất kiểm tra</h2>";
echo "<p>Quá trình kiểm tra và sửa lỗi tồn kho đã hoàn tất.</p>";
echo "<p><a href='administrator/index.php?req=orders' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

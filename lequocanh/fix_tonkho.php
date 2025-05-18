<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tạo bảng system_logs nếu chưa tồn tại
    $createSystemLogsTable = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createSystemLogsTable);
    echo "<p>Đã tạo bảng system_logs (nếu chưa tồn tại).</p>";
    
    // Kiểm tra bảng tonkho
    $checkTonkhoTable = $conn->query("SHOW TABLES LIKE 'tonkho'");
    if ($checkTonkhoTable->rowCount() == 0) {
        // Tạo bảng tonkho nếu chưa tồn tại
        $createTonkhoTable = "CREATE TABLE IF NOT EXISTS tonkho (
            idTonKho INT AUTO_INCREMENT PRIMARY KEY,
            idhanghoa INT NOT NULL,
            soLuong INT NOT NULL DEFAULT 0,
            soLuongToiThieu INT NOT NULL DEFAULT 0,
            viTri VARCHAR(255),
            ngayCapNhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idhanghoa) REFERENCES hanghoa(idhanghoa)
        )";
        $conn->exec($createTonkhoTable);
        echo "<p>Đã tạo bảng tonkho.</p>";
    } else {
        echo "<p>Bảng tonkho đã tồn tại.</p>";
    }
    
    // Kiểm tra xem có sản phẩm nào trong bảng tonkho không
    $countTonkho = $conn->query("SELECT COUNT(*) as count FROM tonkho")->fetch(PDO::FETCH_ASSOC);
    echo "<p>Số lượng bản ghi tồn kho: " . $countTonkho['count'] . "</p>";
    
    // Nếu không có bản ghi nào, thêm dữ liệu mẫu từ bảng hanghoa
    if ($countTonkho['count'] == 0) {
        echo "<h3>Thêm dữ liệu mẫu vào bảng tonkho</h3>";
        
        // Lấy danh sách sản phẩm từ bảng hanghoa
        $products = $conn->query("SELECT idhanghoa FROM hanghoa")->fetchAll(PDO::FETCH_ASSOC);
        
        // Thêm mỗi sản phẩm vào bảng tonkho với số lượng mặc định là 10
        $insertTonkhoStmt = $conn->prepare("INSERT INTO tonkho (idhanghoa, soLuong, soLuongToiThieu, viTri) VALUES (?, 10, 0, '')");
        
        foreach ($products as $product) {
            $insertTonkhoStmt->execute([$product['idhanghoa']]);
            echo "<p>Đã thêm sản phẩm ID: " . $product['idhanghoa'] . " vào bảng tonkho với số lượng 10.</p>";
        }
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
    
    // Kiểm tra các đơn hàng
    echo "<h3>Kiểm tra đơn hàng</h3>";
    $orders = $conn->query("SELECT * FROM orders ORDER BY id");
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Order Code</th><th>User ID</th><th>Total Amount</th><th>Status</th><th>Payment Method</th><th>Created At</th></tr>";
    
    while ($row = $orders->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['order_code'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['total_amount'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['payment_method'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Sửa lỗi trong hàm updateSoLuong
    echo "<h3>Sửa lỗi trong hàm updateSoLuong</h3>";
    
    // Kiểm tra xem file mtonkhoCls.php có tồn tại không
    $mtonkhoClsPath = 'administrator/elements_LQA/mod/mtonkhoCls.php';
    if (file_exists($mtonkhoClsPath)) {
        echo "<p>File mtonkhoCls.php tồn tại tại đường dẫn: " . $mtonkhoClsPath . "</p>";
        
        // Thêm log để theo dõi quá trình cập nhật tồn kho
        $logMessage = "Kiểm tra và sửa lỗi trong hàm updateSoLuong";
        $insertLogStmt = $conn->prepare("INSERT INTO system_logs (message) VALUES (?)");
        $insertLogStmt->execute([$logMessage]);
        echo "<p>Đã thêm log: " . $logMessage . "</p>";
        
        // Hiển thị nút để cập nhật tồn kho cho các đơn hàng
        echo "<h3>Cập nhật tồn kho cho các đơn hàng</h3>";
        echo "<form method='post'>";
        echo "<button type='submit' name='update_tonkho'>Cập nhật tồn kho cho tất cả đơn hàng</button>";
        echo "</form>";
        
        // Xử lý khi người dùng nhấn nút cập nhật tồn kho
        if (isset($_POST['update_tonkho'])) {
            // Lấy tất cả các đơn hàng có trạng thái 'pending'
            $pendingOrders = $conn->query("SELECT id FROM orders WHERE status = 'pending'")->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($pendingOrders as $order) {
                $orderId = $order['id'];
                
                // Lấy danh sách sản phẩm trong đơn hàng
                $orderItemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $orderItemsStmt->execute([$orderId]);
                $items = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    
                    // Kiểm tra xem sản phẩm có trong bảng tonkho không
                    $tonkhoStmt = $conn->prepare("SELECT soLuong FROM tonkho WHERE idhanghoa = ?");
                    $tonkhoStmt->execute([$productId]);
                    $tonkhoInfo = $tonkhoStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($tonkhoInfo) {
                        // Tính toán số lượng mới
                        $newSoLuong = max(0, $tonkhoInfo['soLuong'] - $quantity);
                        
                        // Cập nhật số lượng tồn kho
                        $updateTonkhoStmt = $conn->prepare("UPDATE tonkho SET soLuong = ?, ngayCapNhat = CURRENT_TIMESTAMP WHERE idhanghoa = ?");
                        $updateResult = $updateTonkhoStmt->execute([$newSoLuong, $productId]);
                        
                        // Thêm log
                        $logMessage = "Cập nhật tồn kho cho sản phẩm ID: " . $productId . ", đơn hàng ID: " . $orderId . 
                                     ", số lượng cũ: " . $tonkhoInfo['soLuong'] . ", số lượng mới: " . $newSoLuong . 
                                     ", kết quả: " . ($updateResult ? "thành công" : "thất bại");
                        $insertLogStmt->execute([$logMessage]);
                        
                        echo "<p>" . $logMessage . "</p>";
                    } else {
                        // Nếu sản phẩm chưa có trong bảng tonkho, thêm mới
                        $insertTonkhoStmt = $conn->prepare("INSERT INTO tonkho (idhanghoa, soLuong, soLuongToiThieu, viTri) VALUES (?, 0, 0, '')");
                        $insertResult = $insertTonkhoStmt->execute([$productId]);
                        
                        // Thêm log
                        $logMessage = "Thêm mới tồn kho cho sản phẩm ID: " . $productId . ", đơn hàng ID: " . $orderId . 
                                     ", số lượng: 0, kết quả: " . ($insertResult ? "thành công" : "thất bại");
                        $insertLogStmt->execute([$logMessage]);
                        
                        echo "<p>" . $logMessage . "</p>";
                    }
                }
                
                // Cập nhật trạng thái đơn hàng thành 'approved'
                $updateOrderStmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = ?");
                $updateOrderResult = $updateOrderStmt->execute([$orderId]);
                
                // Thêm log
                $logMessage = "Cập nhật trạng thái đơn hàng ID: " . $orderId . " thành 'approved', kết quả: " . 
                             ($updateOrderResult ? "thành công" : "thất bại");
                $insertLogStmt->execute([$logMessage]);
                
                echo "<p>" . $logMessage . "</p>";
            }
            
            echo "<p>Đã hoàn tất cập nhật tồn kho cho tất cả đơn hàng.</p>";
        }
    } else {
        echo "<p>Không tìm thấy file mtonkhoCls.php tại đường dẫn: " . $mtonkhoClsPath . "</p>";
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

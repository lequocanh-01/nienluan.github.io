<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thêm CSS để trang trông đẹp hơn
echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra và sửa lỗi kết nối cơ sở dữ liệu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Kiểm tra và sửa lỗi kết nối cơ sở dữ liệu</h1>';

// Kiểm tra file config.ini
$configFile = 'administrator/elements_LQA/mod/config.ini';
echo '<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Kiểm tra file cấu hình</h5>
    </div>
    <div class="card-body">';

if (file_exists($configFile)) {
    echo '<p class="success">File config.ini tồn tại!</p>';
    
    // Kiểm tra quyền đọc
    if (is_readable($configFile)) {
        echo '<p class="success">File config.ini có quyền đọc!</p>';
        
        // Đọc nội dung file
        $configContent = file_get_contents($configFile);
        echo '<h6>Nội dung file config.ini:</h6>';
        echo '<pre>' . htmlspecialchars($configContent) . '</pre>';
        
        // Parse file config
        $config = parse_ini_file($configFile, true);
        if ($config) {
            echo '<p class="success">Đã parse file config.ini thành công!</p>';
            
            // Hiển thị thông tin cấu hình
            echo '<h6>Thông tin cấu hình:</h6>';
            echo '<ul>';
            foreach ($config['section'] as $key => $value) {
                if ($key == 'password') {
                    echo '<li>' . $key . ': ******</li>';
                } else {
                    echo '<li>' . $key . ': ' . $value . '</li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p class="error">Không thể parse file config.ini!</p>';
        }
    } else {
        echo '<p class="error">File config.ini không có quyền đọc!</p>';
    }
} else {
    echo '<p class="error">File config.ini không tồn tại!</p>';
}

echo '</div></div>';

// Kiểm tra kết nối cơ sở dữ liệu
echo '<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Kiểm tra kết nối cơ sở dữ liệu</h5>
    </div>
    <div class="card-body">';

// Thử kết nối trực tiếp
try {
    $config = parse_ini_file($configFile, true);
    
    $servername = $config['section']['servername'];
    $dbname = $config['section']['dbname'];
    $username = $config['section']['username'];
    $password = $config['section']['password'];
    $port = $config['section']['port'];
    
    echo '<h6>Thử kết nối trực tiếp:</h6>';
    echo '<p>Thông tin kết nối: mysql:host=' . $servername . ';port=' . $port . ';dbname=' . $dbname . ';charset=utf8</p>';
    
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<p class="success">Kết nối thành công!</p>';
    
    // Kiểm tra bảng orders
    $checkTableSql = "SHOW TABLES LIKE 'orders'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() > 0) {
        echo '<p class="success">Bảng orders tồn tại!</p>';
        
        // Đếm số lượng bản ghi
        $countSql = "SELECT COUNT(*) as count FROM orders";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute();
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        
        echo '<p>Số lượng đơn hàng: ' . $count['count'] . '</p>';
        
        if ($count['count'] > 0) {
            // Lấy danh sách đơn hàng
            $ordersSql = "SELECT * FROM orders ORDER BY created_at DESC";
            $ordersStmt = $conn->prepare($ordersSql);
            $ordersStmt->execute();
            $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<h6>Danh sách đơn hàng:</h6>';
            echo '<table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã đơn hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($orders as $order) {
                echo '<tr>
                    <td>' . $order['id'] . '</td>
                    <td>' . $order['order_code'] . '</td>
                    <td>' . number_format($order['total_amount'], 0, ',', '.') . ' ₫</td>
                    <td>' . $order['status'] . '</td>
                    <td>' . $order['created_at'] . '</td>
                </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p class="error">Bảng orders không có dữ liệu!</p>';
            
            // Thêm dữ liệu mẫu
            echo '<h6>Thêm dữ liệu mẫu:</h6>';
            
            try {
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
                
                $insertOrderStmt->execute([$orderCode, $totalAmount, $status, $paymentMethod, $paymentStatus, $createdAt]);
                $orderId = $conn->lastInsertId();
                
                echo '<p class="success">Đã thêm đơn hàng mẫu với ID: ' . $orderId . '</p>';
                
                // Thêm một đơn hàng nữa
                $orderCode2 = 'ORD' . date('YmdHis') . '2';
                $totalAmount2 = 200000;
                
                $insertOrderStmt->execute([$orderCode2, $totalAmount2, $status, $paymentMethod, $paymentStatus, $createdAt]);
                $orderId2 = $conn->lastInsertId();
                
                echo '<p class="success">Đã thêm đơn hàng mẫu thứ hai với ID: ' . $orderId2 . '</p>';
                
                echo '<p>Vui lòng <a href="administrator/index.php?req=orders" class="btn btn-primary btn-sm">truy cập trang quản lý đơn hàng</a> để kiểm tra.</p>';
            } catch (PDOException $e) {
                echo '<p class="error">Lỗi khi thêm dữ liệu mẫu: ' . $e->getMessage() . '</p>';
            }
        }
    } else {
        echo '<p class="error">Bảng orders không tồn tại!</p>';
        
        // Tạo bảng orders
        echo '<h6>Tạo bảng orders:</h6>';
        
        try {
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $conn->exec($createOrdersTableSql);
            echo '<p class="success">Đã tạo bảng orders thành công!</p>';
            
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
            
            echo '<p class="success">Đã thêm đơn hàng mẫu với ID: ' . $orderId . '</p>';
            
            echo '<p>Vui lòng <a href="administrator/index.php?req=orders" class="btn btn-primary btn-sm">truy cập trang quản lý đơn hàng</a> để kiểm tra.</p>';
        } catch (PDOException $e) {
            echo '<p class="error">Lỗi khi tạo bảng orders: ' . $e->getMessage() . '</p>';
        }
    }
} catch (PDOException $e) {
    echo '<p class="error">Lỗi kết nối: ' . $e->getMessage() . '</p>';
    
    // Kiểm tra xem có thể kết nối đến máy chủ MySQL không
    try {
        $conn = new PDO("mysql:host=$servername;port=$port", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo '<p class="success">Kết nối đến máy chủ MySQL thành công!</p>';
        echo '<p class="error">Nhưng không thể kết nối đến cơ sở dữ liệu ' . $dbname . '!</p>';
        
        // Kiểm tra xem cơ sở dữ liệu có tồn tại không
        $checkDbSql = "SHOW DATABASES LIKE '$dbname'";
        $checkDbStmt = $conn->prepare($checkDbSql);
        $checkDbStmt->execute();
        
        if ($checkDbStmt->rowCount() > 0) {
            echo '<p class="success">Cơ sở dữ liệu ' . $dbname . ' tồn tại!</p>';
            echo '<p class="error">Nhưng không thể kết nối đến nó. Có thể do quyền truy cập.</p>';
        } else {
            echo '<p class="error">Cơ sở dữ liệu ' . $dbname . ' không tồn tại!</p>';
            
            // Tạo cơ sở dữ liệu
            try {
                $createDbSql = "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                $conn->exec($createDbSql);
                
                echo '<p class="success">Đã tạo cơ sở dữ liệu ' . $dbname . ' thành công!</p>';
                echo '<p>Vui lòng <a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary btn-sm">tải lại trang</a> để kiểm tra lại.</p>';
            } catch (PDOException $e) {
                echo '<p class="error">Không thể tạo cơ sở dữ liệu: ' . $e->getMessage() . '</p>';
            }
        }
    } catch (PDOException $e) {
        echo '<p class="error">Không thể kết nối đến máy chủ MySQL: ' . $e->getMessage() . '</p>';
        echo '<p>Vui lòng kiểm tra lại thông tin kết nối trong file config.ini.</p>';
    }
}

echo '</div></div>';

// Hiển thị nút truy cập trang quản lý đơn hàng
echo '<div class="text-center mt-4">
    <a href="administrator/index.php?req=orders" class="btn btn-primary">Truy cập trang quản lý đơn hàng</a>
</div>';

echo '</div>
</body>
</html>';
?>

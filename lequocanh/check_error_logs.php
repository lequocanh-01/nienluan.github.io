<?php
// Hiển thị lỗi PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Kiểm tra log lỗi</h2>";

// Kiểm tra error_log của PHP
$error_log_path = ini_get('error_log');
echo "<p>Đường dẫn error_log của PHP: " . ($error_log_path ? $error_log_path : "Không được cấu hình") . "</p>";

// Danh sách các file log cần kiểm tra
$log_files = [
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/apache/logs/access.log',
    '/var/log/apache2/error.log',
    '/var/log/apache2/access.log',
    '/var/log/nginx/error.log',
    '/var/log/nginx/access.log'
];

// Thêm đường dẫn error_log nếu được cấu hình
if ($error_log_path && !in_array($error_log_path, $log_files)) {
    $log_files[] = $error_log_path;
}

// Kiểm tra từng file log
foreach ($log_files as $log_file) {
    echo "<h3>File log: " . htmlspecialchars($log_file) . "</h3>";
    
    if (file_exists($log_file)) {
        // Đọc 100 dòng cuối cùng của file log
        $log_content = shell_exec("tail -n 100 " . escapeshellarg($log_file) . " 2>&1");
        
        if ($log_content) {
            echo "<pre style='max-height: 300px; overflow-y: auto; background-color: #f5f5f5; padding: 10px;'>";
            echo htmlspecialchars($log_content);
            echo "</pre>";
        } else {
            echo "<p>Không thể đọc nội dung file log hoặc file trống.</p>";
            
            // Thử đọc bằng file_get_contents
            $content = @file_get_contents($log_file);
            if ($content !== false) {
                // Lấy 10KB cuối cùng của file
                $content = substr($content, -10240);
                echo "<pre style='max-height: 300px; overflow-y: auto; background-color: #f5f5f5; padding: 10px;'>";
                echo htmlspecialchars($content);
                echo "</pre>";
            }
        }
    } else {
        echo "<p>File log không tồn tại.</p>";
    }
}

// Kiểm tra lỗi trong session
echo "<h3>Lỗi trong session</h3>";
if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
    echo "<ul>";
    foreach ($_SESSION['errors'] as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Không có lỗi trong session.</p>";
}

// Kiểm tra quyền truy cập
echo "<h3>Kiểm tra quyền truy cập</h3>";
session_start();
echo "<p>Session hiện tại:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Kiểm tra quyền truy cập vào trang orders
require_once 'administrator/elements_LQA/mod/phanquyenCls.php';
$phanQuyen = new PhanQuyen();
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');

echo "<p>Kiểm tra quyền truy cập vào trang orders cho user: " . htmlspecialchars($username) . "</p>";
$hasAccess = $phanQuyen->checkAccess('orders', $username);
echo "<p>Kết quả: " . ($hasAccess ? "<span style='color:green'>Có quyền truy cập</span>" : "<span style='color:red'>Không có quyền truy cập</span>") . "</p>";

// Kiểm tra kết nối database
echo "<h3>Kiểm tra kết nối database</h3>";
require_once 'administrator/elements_LQA/mod/database.php';
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color:green'>Kết nối database thành công!</p>";
    
    // Kiểm tra bảng orders
    $checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
    $checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
    $checkOrdersTableStmt->execute();
    $ordersTableExists = ($checkOrdersTableStmt->rowCount() > 0);
    
    echo "<p>Bảng orders: " . ($ordersTableExists ? "<span style='color:green'>Đã tồn tại</span>" : "<span style='color:red'>Chưa tồn tại</span>") . "</p>";
    
    if ($ordersTableExists) {
        // Kiểm tra cấu trúc bảng orders
        $descOrdersSql = "DESCRIBE orders";
        $descOrdersStmt = $conn->prepare($descOrdersSql);
        $descOrdersStmt->execute();
        $ordersColumns = $descOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Cấu trúc bảng orders:</p>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($ordersColumns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Kiểm tra dữ liệu trong bảng orders
        $countOrdersSql = "SELECT COUNT(*) as count FROM orders";
        $countOrdersStmt = $conn->prepare($countOrdersSql);
        $countOrdersStmt->execute();
        $countOrders = $countOrdersStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Số lượng đơn hàng: " . $countOrders['count'] . "</p>";
        
        if ($countOrders['count'] > 0) {
            $getOrdersSql = "SELECT * FROM orders LIMIT 5";
            $getOrdersStmt = $conn->prepare($getOrdersSql);
            $getOrdersStmt->execute();
            $orders = $getOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>Dữ liệu mẫu từ bảng orders:</p>";
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
} catch (PDOException $e) {
    echo "<p style='color:red'>Lỗi kết nối database: " . $e->getMessage() . "</p>";
}

// Kiểm tra file orders.php
echo "<h3>Kiểm tra file orders.php</h3>";
$orders_file = 'administrator/elements_LQA/madmin/orders.php';
if (file_exists($orders_file)) {
    echo "<p style='color:green'>File orders.php tồn tại!</p>";
    
    // Kiểm tra quyền đọc
    if (is_readable($orders_file)) {
        echo "<p style='color:green'>File orders.php có quyền đọc!</p>";
    } else {
        echo "<p style='color:red'>File orders.php không có quyền đọc!</p>";
    }
    
    // Hiển thị kích thước và thời gian sửa đổi
    echo "<p>Kích thước: " . filesize($orders_file) . " bytes</p>";
    echo "<p>Thời gian sửa đổi: " . date("Y-m-d H:i:s", filemtime($orders_file)) . "</p>";
} else {
    echo "<p style='color:red'>File orders.php không tồn tại!</p>";
}

echo "<p><a href='administrator/index.php?req=orders' class='btn btn-primary'>Quay lại trang quản lý đơn hàng</a></p>";
?>

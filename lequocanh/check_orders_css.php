<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Kiểm tra CSS và JavaScript cho trang quản lý đơn hàng</h2>";

// Kiểm tra xem bảng orders đã tồn tại chưa
$checkTableSql = "SHOW TABLES LIKE 'orders'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

if ($checkTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng orders không tồn tại!</p>";
} else {
    echo "<p style='color: green;'>Bảng orders tồn tại.</p>";
    
    // Đếm số lượng bản ghi
    $count = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    echo "<p>Số lượng đơn hàng: " . $count . "</p>";
}

// Kiểm tra các file CSS và JavaScript
echo "<h3>Kiểm tra các file CSS và JavaScript:</h3>";

$cssFiles = [
    'administrator/stylecss_LQA/mycss.css',
    'administrator/layoutcss_LQA/layout.css',
    'administrator/elements_LQA/css_LQA/style.css'
];

$jsFiles = [
    'administrator/js_LQA/jscript.js',
    'administrator/js_LQA/modal-handler.js',
    'administrator/elements_LQA/js_LQA/jscript.js'
];

echo "<h4>File CSS:</h4>";
echo "<ul>";
foreach ($cssFiles as $file) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>" . htmlspecialchars($file) . " - Tồn tại (Kích thước: " . filesize($file) . " bytes)</li>";
    } else {
        echo "<li style='color: red;'>" . htmlspecialchars($file) . " - Không tồn tại</li>";
    }
}
echo "</ul>";

echo "<h4>File JavaScript:</h4>";
echo "<ul>";
foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>" . htmlspecialchars($file) . " - Tồn tại (Kích thước: " . filesize($file) . " bytes)</li>";
    } else {
        echo "<li style='color: red;'>" . htmlspecialchars($file) . " - Không tồn tại</li>";
    }
}
echo "</ul>";

// Kiểm tra Bootstrap
echo "<h3>Kiểm tra Bootstrap:</h3>";
$bootstrapFiles = [
    'administrator/bootstrap/css/bootstrap.min.css',
    'administrator/bootstrap/js/bootstrap.min.js'
];

echo "<ul>";
foreach ($bootstrapFiles as $file) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>" . htmlspecialchars($file) . " - Tồn tại (Kích thước: " . filesize($file) . " bytes)</li>";
    } else {
        echo "<li style='color: red;'>" . htmlspecialchars($file) . " - Không tồn tại</li>";
    }
}
echo "</ul>";

// Kiểm tra file orders.php
echo "<h3>Kiểm tra file orders.php:</h3>";
$ordersFile = 'administrator/elements_LQA/madmin/orders.php';

if (file_exists($ordersFile)) {
    echo "<p style='color: green;'>File orders.php tồn tại (Kích thước: " . filesize($ordersFile) . " bytes)</p>";
    
    // Kiểm tra quyền đọc
    if (is_readable($ordersFile)) {
        echo "<p style='color: green;'>File orders.php có quyền đọc</p>";
    } else {
        echo "<p style='color: red;'>File orders.php không có quyền đọc</p>";
    }
    
    // Hiển thị thời gian sửa đổi
    echo "<p>Thời gian sửa đổi: " . date("Y-m-d H:i:s", filemtime($ordersFile)) . "</p>";
    
    // Kiểm tra nội dung file
    $content = file_get_contents($ordersFile);
    $contentLength = strlen($content);
    echo "<p>Độ dài nội dung: " . $contentLength . " ký tự</p>";
    
    // Kiểm tra các phần quan trọng trong file
    $checks = [
        'Bảng orders' => strpos($content, 'CREATE TABLE orders') !== false,
        'Bảng order_items' => strpos($content, 'CREATE TABLE order_items') !== false,
        'Hiển thị danh sách đơn hàng' => strpos($content, 'Danh sách đơn hàng') !== false,
        'Hiển thị chi tiết đơn hàng' => strpos($content, 'Chi tiết đơn hàng') !== false,
        'Duyệt đơn hàng' => strpos($content, 'Duyệt đơn hàng') !== false,
        'Hủy đơn hàng' => strpos($content, 'Hủy đơn hàng') !== false,
        'Truy vấn SQL lấy đơn hàng' => strpos($content, 'SELECT * FROM orders') !== false,
        'Truy vấn SQL lấy chi tiết đơn hàng' => strpos($content, 'SELECT * FROM order_items') !== false,
        'Hiển thị bảng HTML' => strpos($content, '<table class="table') !== false,
        'Hiển thị thông báo khi không có đơn hàng' => strpos($content, 'Chưa có đơn hàng nào') !== false
    ];
    
    echo "<h4>Kiểm tra nội dung file:</h4>";
    echo "<ul>";
    foreach ($checks as $check => $result) {
        echo "<li>" . htmlspecialchars($check) . ": " . ($result ? "<span style='color: green;'>Có</span>" : "<span style='color: red;'>Không</span>") . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>File orders.php không tồn tại!</p>";
}

// Kiểm tra file center.php
echo "<h3>Kiểm tra file center.php:</h3>";
$centerFile = 'administrator/elements_LQA/center.php';

if (file_exists($centerFile)) {
    echo "<p style='color: green;'>File center.php tồn tại (Kích thước: " . filesize($centerFile) . " bytes)</p>";
    
    // Kiểm tra nội dung file
    $content = file_get_contents($centerFile);
    
    // Kiểm tra xem có case 'orders' không
    if (strpos($content, "case 'orders':") !== false) {
        echo "<p style='color: green;'>File center.php có case 'orders'</p>";
        
        // Hiển thị đoạn mã liên quan
        preg_match('/case \'orders\':(.*?)(case|default|\})/s', $content, $matches);
        if (!empty($matches[1])) {
            echo "<pre style='background-color: #f5f5f5; padding: 10px;'>";
            echo htmlspecialchars("case 'orders':" . $matches[1]);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>File center.php không có case 'orders'</p>";
    }
} else {
    echo "<p style='color: red;'>File center.php không tồn tại!</p>";
}

// Kiểm tra file left.php
echo "<h3>Kiểm tra file left.php:</h3>";
$leftFile = 'administrator/elements_LQA/left.php';

if (file_exists($leftFile)) {
    echo "<p style='color: green;'>File left.php tồn tại (Kích thước: " . filesize($leftFile) . " bytes)</p>";
    
    // Kiểm tra nội dung file
    $content = file_get_contents($leftFile);
    
    // Kiểm tra xem có menu đơn hàng không
    if (strpos($content, "orders") !== false) {
        echo "<p style='color: green;'>File left.php có menu đơn hàng</p>";
        
        // Hiển thị đoạn mã liên quan
        preg_match('/\'orders\'(.*?)(\],|\})/s', $content, $matches);
        if (!empty($matches[0])) {
            echo "<pre style='background-color: #f5f5f5; padding: 10px;'>";
            echo htmlspecialchars($matches[0]);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>File left.php không có menu đơn hàng</p>";
    }
} else {
    echo "<p style='color: red;'>File left.php không tồn tại!</p>";
}

// Kiểm tra quyền truy cập
echo "<h3>Kiểm tra quyền truy cập:</h3>";
require_once 'administrator/elements_LQA/mod/phanquyenCls.php';
$phanQuyen = new PhanQuyen();

// Kiểm tra quyền truy cập cho admin
echo "<p>Quyền truy cập cho admin: " . ($phanQuyen->checkAccess('orders', 'admin') ? "<span style='color: green;'>Có</span>" : "<span style='color: red;'>Không</span>") . "</p>";

// Kiểm tra quyền truy cập cho nhân viên
echo "<p>Quyền truy cập cho nhân viên: " . ($phanQuyen->checkAccess('orders', 'nhanvien') ? "<span style='color: green;'>Có</span>" : "<span style='color: red;'>Không</span>") . "</p>";

// Kiểm tra quyền truy cập cho người dùng thông thường
echo "<p>Quyền truy cập cho người dùng thông thường: " . ($phanQuyen->checkAccess('orders', 'user') ? "<span style='color: green;'>Có</span>" : "<span style='color: red;'>Không</span>") . "</p>";

// Hiển thị nút để sửa chữa
echo "<h3>Tùy chọn sửa chữa:</h3>";
echo "<p><a href='administrator/index.php?req=orders' style='background-color: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
require_once 'administrator/elements_LQA/mod/phanquyenCls.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$phanQuyen = new PhanQuyen();

echo "<h2>Kiểm tra quyền truy cập</h2>";

// Hiển thị thông tin session hiện tại
echo "<h3>Thông tin session hiện tại:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Kiểm tra quyền truy cập cho các module
$modules = [
    'userview',
    'updateuser',
    'userupdate',
    'userprofile',
    'userUpdateProfile',
    'loaihangview',
    'hanghoaview',
    'dongiaview',
    'thuonghieuview',
    'donvitinhview',
    'nhanvienview',
    'thuoctinhview',
    'thuoctinhhhview',
    'adminGiohangView',
    'hinhanhview',
    'nhacungcapview',
    'mphieunhap',
    'mphieunhapedit',
    'mchitietphieunhap',
    'mchitietphieunhapedit',
    'mtonkho',
    'mtonkhoedit',
    'mphieunhapfixtonkho',
    'payment_config',
    'orders'
];

// Lấy username từ session
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');

echo "<h3>Kiểm tra quyền truy cập cho user: " . htmlspecialchars($username) . "</h3>";

if (empty($username)) {
    echo "<p style='color: red;'>Không có user nào đang đăng nhập!</p>";
    
    // Form đăng nhập tạm thời
    echo "<h3>Đăng nhập tạm thời:</h3>";
    echo "<form method='post'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='username'>Username:</label>";
    echo "<input type='text' id='username' name='username' required>";
    echo "</div>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='role'>Vai trò:</label>";
    echo "<select id='role' name='role'>";
    echo "<option value='admin'>Admin</option>";
    echo "<option value='user'>User thường</option>";
    echo "<option value='nhanvien'>Nhân viên</option>";
    echo "</select>";
    echo "</div>";
    echo "<button type='submit' name='login' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;'>Đăng nhập</button>";
    echo "</form>";
    
    // Xử lý đăng nhập tạm thời
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $role = $_POST['role'];
        
        // Kiểm tra xem user có tồn tại không
        $checkUserSql = "SELECT * FROM user WHERE username = ?";
        $checkUserStmt = $conn->prepare($checkUserSql);
        $checkUserStmt->execute([$username]);
        $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Tạo user mới nếu chưa tồn tại
            $insertUserSql = "INSERT INTO user (username, password, hoten, diachi, dienthoai, email) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $insertUserStmt = $conn->prepare($insertUserSql);
            $insertUserStmt->execute([
                $username,
                password_hash('password', PASSWORD_DEFAULT),
                'User ' . $username,
                'Địa chỉ mặc định',
                '0123456789',
                $username . '@example.com'
            ]);
            
            // Lấy ID của user vừa tạo
            $userId = $conn->lastInsertId();
            
            // Nếu là nhân viên, thêm vào bảng nhân viên
            if ($role == 'nhanvien') {
                $insertNhanVienSql = "INSERT INTO nhanvien (iduser, tennhanvien, diachi, dienthoai, email) 
                                     VALUES (?, ?, ?, ?, ?)";
                $insertNhanVienStmt = $conn->prepare($insertNhanVienSql);
                $insertNhanVienStmt->execute([
                    $userId,
                    'Nhân viên ' . $username,
                    'Địa chỉ mặc định',
                    '0123456789',
                    $username . '@example.com'
                ]);
            }
        }
        
        // Thiết lập session
        if ($role == 'admin') {
            $_SESSION['ADMIN'] = $username;
        } else {
            $_SESSION['USER'] = $username;
        }
        
        // Refresh trang
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
} else {
    // Kiểm tra xem user có phải là nhân viên không
    $isNhanVien = $phanQuyen->isNhanVien($username);
    
    echo "<p>User <strong>" . htmlspecialchars($username) . "</strong> là: ";
    if (isset($_SESSION['ADMIN'])) {
        echo "<span style='color: blue;'>Admin</span>";
    } elseif ($isNhanVien) {
        echo "<span style='color: green;'>Nhân viên</span>";
    } else {
        echo "<span style='color: orange;'>User thường</span>";
    }
    echo "</p>";
    
    // Hiển thị bảng quyền truy cập
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th style='padding: 8px; text-align: left;'>Module</th>";
    echo "<th style='padding: 8px; text-align: left;'>Có quyền truy cập?</th>";
    echo "</tr>";
    
    foreach ($modules as $module) {
        $hasAccess = $phanQuyen->checkAccess($module, $username);
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($module) . "</td>";
        echo "<td style='padding: 8px; " . ($hasAccess ? "color: green;" : "color: red;") . "'>";
        echo $hasAccess ? "Có" : "Không";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Nút đăng xuất
    echo "<form method='post' style='margin-top: 20px;'>";
    echo "<button type='submit' name='logout' style='background-color: #f44336; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;'>Đăng xuất</button>";
    echo "</form>";
    
    // Xử lý đăng xuất
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Kiểm tra file orders.php
echo "<h3>Kiểm tra file orders.php:</h3>";
$ordersFile = 'administrator/elements_LQA/madmin/orders.php';

if (file_exists($ordersFile)) {
    echo "<p style='color: green;'>File orders.php tồn tại!</p>";
    
    // Kiểm tra quyền đọc
    if (is_readable($ordersFile)) {
        echo "<p style='color: green;'>File orders.php có quyền đọc!</p>";
    } else {
        echo "<p style='color: red;'>File orders.php không có quyền đọc!</p>";
    }
    
    // Hiển thị kích thước và thời gian sửa đổi
    echo "<p>Kích thước: " . filesize($ordersFile) . " bytes</p>";
    echo "<p>Thời gian sửa đổi: " . date("Y-m-d H:i:s", filemtime($ordersFile)) . "</p>";
} else {
    echo "<p style='color: red;'>File orders.php không tồn tại!</p>";
}

// Kiểm tra bảng orders
echo "<h3>Kiểm tra bảng orders:</h3>";
try {
    $checkTableSql = "SHOW TABLES LIKE 'orders'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() > 0) {
        echo "<p style='color: green;'>Bảng orders tồn tại!</p>";
        
        // Đếm số lượng bản ghi
        $countOrdersSql = "SELECT COUNT(*) as count FROM orders";
        $countOrdersStmt = $conn->prepare($countOrdersSql);
        $countOrdersStmt->execute();
        $countOrders = $countOrdersStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Số lượng đơn hàng: " . $countOrders['count'] . "</p>";
        
        if ($countOrders['count'] > 0) {
            // Hiển thị dữ liệu mẫu
            $ordersSql = "SELECT * FROM orders LIMIT 5";
            $ordersStmt = $conn->prepare($ordersSql);
            $ordersStmt->execute();
            $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Dữ liệu mẫu từ bảng orders:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            foreach (array_keys($orders[0]) as $key) {
                echo "<th style='padding: 8px; text-align: left;'>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            foreach ($orders as $order) {
                echo "<tr>";
                foreach ($order as $value) {
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>Bảng orders không tồn tại!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra bảng orders: " . $e->getMessage() . "</p>";
}

// Hiển thị nút truy cập trang quản lý đơn hàng
echo "<p style='margin-top: 20px;'><a href='administrator/index.php?req=orders' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Truy cập trang quản lý đơn hàng</a></p>";
?>

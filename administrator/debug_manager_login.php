<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🔍 DEBUG ĐĂNG NHẬP MANAGER</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

// 1. Kiểm tra session hiện tại
echo "<h2>📋 THÔNG TIN SESSION HIỆN TẠI:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>SESSION['ADMIN']:</strong> " . (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : 'Không tồn tại') . "<br>";
echo "<strong>SESSION['USER']:</strong> " . (isset($_SESSION['USER']) ? $_SESSION['USER'] : 'Không tồn tại') . "<br>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Tất cả SESSION:</strong><br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "</div>";

// 2. Kiểm tra người dùng hiện tại
$currentUser = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : (isset($_SESSION['USER']) ? $_SESSION['USER'] : null);
echo "<h2>👤 NGƯỜI DÙNG HIỆN TẠI:</h2>";
echo "<div style='background: " . ($currentUser ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Username:</strong> " . ($currentUser ? $currentUser : 'Chưa đăng nhập') . "<br>";
echo "<strong>Loại tài khoản:</strong> " . (isset($_SESSION['ADMIN']) ? 'Admin' : (isset($_SESSION['USER']) ? 'User' : 'Không xác định')) . "<br>";
echo "</div>";

// 3. Kiểm tra dữ liệu trong database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>🗄️ KIỂM TRA DỮ LIỆU TRONG DATABASE:</h2>";

// Kiểm tra bảng user
echo "<h3>👥 Bảng USER:</h3>";
$stmt = $conn->prepare("SELECT username, hoten, setlock FROM user WHERE username LIKE '%manager%' OR username = ?");
$stmt->execute([$currentUser]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($users) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Họ tên</th>";
    echo "<th style='padding: 8px;'>Trạng thái</th>";
    echo "</tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $user['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $user['hoten'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($user['setlock'] == 1 ? 'Kích hoạt' : 'Chưa kích hoạt') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không tìm thấy user nào</p>";
}

// 4. Kiểm tra nhật ký hoạt động
echo "<h3>📊 NHẬT KÝ HOẠT ĐỘNG:</h3>";

// Kiểm tra tất cả nhật ký của manager
$stmt = $conn->prepare("SELECT * FROM nhat_ky_hoat_dong WHERE username LIKE '%manager%' OR username = ? ORDER BY thoi_gian DESC LIMIT 20");
$stmt->execute([$currentUser]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($activities) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>IP</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    foreach ($activities as $activity) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $activity['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['hanh_dong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['doi_tuong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['chi_tiet'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['ip_address'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($activity['thoi_gian'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không có nhật ký hoạt động nào</p>";
}

// 5. Test ghi nhật ký thủ công
echo "<h2>🧪 TEST GHI NHẬT KÝ THỦ CÔNG:</h2>";
if ($currentUser) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>Đang test ghi nhật ký cho user: <strong>$currentUser</strong></p>";
    
    $result = ghiNhatKyDangNhap($currentUser);
    if ($result) {
        echo "<p style='color: green;'>✅ Ghi nhật ký thành công - ID: $result</p>";
    } else {
        echo "<p style='color: red;'>❌ Ghi nhật ký thất bại</p>";
    }
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ Không có user để test</p>";
}

// 6. Kiểm tra cấu trúc bảng
echo "<h2>🏗️ CẤU TRÚC BẢNG NHẬT KÝ:</h2>";
$stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #6c757d; color: white;'>";
echo "<th style='padding: 8px;'>Tên cột</th>";
echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
echo "<th style='padding: 8px;'>Null</th>";
echo "<th style='padding: 8px;'>Key</th>";
echo "<th style='padding: 8px;'>Default</th>";
echo "</tr>";
foreach ($columns as $column) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>" . $column['Field'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "</div>";

// 7. Hướng dẫn khắc phục
echo "<h2>🔧 HƯỚNG DẪN KHẮC PHỤC:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<ol>";
echo "<li><strong>Kiểm tra đăng nhập:</strong> Đảm bảo bạn đã đăng nhập với tài khoản manager</li>";
echo "<li><strong>Kiểm tra session:</strong> Session phải chứa thông tin user đã đăng nhập</li>";
echo "<li><strong>Kiểm tra helper file:</strong> File nhatKyHoatDongHelper.php phải được include</li>";
echo "<li><strong>Kiểm tra database:</strong> Bảng nhat_ky_hoat_dong phải tồn tại và có cấu trúc đúng</li>";
echo "<li><strong>Kiểm tra quyền:</strong> User phải có quyền ghi vào database</li>";
echo "</ol>";
echo "</div>";
?>

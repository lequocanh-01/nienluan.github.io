<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🔍 KIỂM TRA TOÀN DIỆN HỆ THỐNG THEO DÕI HOẠT ĐỘNG</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();

// 1. Kiểm tra cấu trúc bảng
echo "<h2>🏗️ KIỂM TRA CẤU TRÚC BẢNG:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

try {
    $stmt = $conn->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Bảng nhat_ky_hoat_dong tồn tại</p>";
        
        // Kiểm tra cấu trúc
        $stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #6c757d; color: white;'>";
        echo "<th style='padding: 8px;'>Cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "</tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $column['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Bảng nhat_ky_hoat_dong không tồn tại</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi kiểm tra bảng: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 2. Kiểm tra file helper
echo "<h2>📁 KIỂM TRA FILE HELPER:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$helperFile = 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';
if (file_exists($helperFile)) {
    echo "<p style='color: green;'>✅ File helper tồn tại: $helperFile</p>";
    
    // Kiểm tra các function
    $functions = ['ghiNhatKyHoatDong', 'ghiNhatKyDangNhap', 'ghiNhatKyDangXuat'];
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "<p style='color: green;'>✅ Function $func() có sẵn</p>";
        } else {
            echo "<p style='color: red;'>❌ Function $func() không tồn tại</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ File helper không tồn tại: $helperFile</p>";
}
echo "</div>";

// 3. Kiểm tra tài khoản manager
echo "<h2>👨‍💼 KIỂM TRA TÀI KHOẢN MANAGER:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("SELECT * FROM user WHERE username LIKE '%manager%' OR username = 'admin' ORDER BY username");
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($managers) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Họ tên</th>";
    echo "<th style='padding: 8px;'>Trạng thái</th>";
    echo "<th style='padding: 8px;'>Loại tài khoản</th>";
    echo "</tr>";
    foreach ($managers as $manager) {
        $accountType = ($manager['username'] == 'admin' || strpos($manager['username'], 'manager') !== false) ? 'ADMIN' : 'USER';
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $manager['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $manager['hoten'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($manager['setlock'] == 1 ? '<span style="color: green;">Kích hoạt</span>' : '<span style="color: red;">Chưa kích hoạt</span>') . "</td>";
        echo "<td style='padding: 8px;'><strong>$accountType</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không có tài khoản manager nào</p>";
}
echo "</div>";

// 4. Test ghi nhật ký
echo "<h2>🧪 TEST GHI NHẬT KÝ:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$testUsers = ['admin', 'manager1', 'manager2'];
foreach ($testUsers as $testUser) {
    echo "<h4>Test user: $testUser</h4>";
    
    // Test đăng nhập
    $result = ghiNhatKyDangNhap($testUser);
    if ($result) {
        echo "<p style='color: green;'>✅ Ghi nhật ký đăng nhập thành công - ID: $result</p>";
    } else {
        echo "<p style='color: red;'>❌ Ghi nhật ký đăng nhập thất bại</p>";
    }
    
    // Test hoạt động khác
    $result2 = ghiNhatKyHoatDong($testUser, 'Xem danh sách', 'Nhân viên', null, 'Test xem danh sách nhân viên');
    if ($result2) {
        echo "<p style='color: blue;'>📝 Ghi nhật ký hoạt động thành công - ID: $result2</p>";
    }
    
    echo "<hr style='margin: 10px 0;'>";
}
echo "</div>";

// 5. Hiển thị nhật ký gần đây
echo "<h2>📊 NHẬT KÝ HOẠT ĐỘNG GẦN ĐÂY:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 20");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($activities) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>IP</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    foreach ($activities as $activity) {
        $isRecent = (time() - strtotime($activity['thoi_gian'])) < 300; // 5 phút gần đây
        $rowStyle = $isRecent ? "background: #d4edda;" : "";
        echo "<tr style='$rowStyle'>";
        echo "<td style='padding: 8px;'>" . $activity['id'] . "</td>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $activity['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['hanh_dong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['doi_tuong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['chi_tiet'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['ip_address'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($activity['thoi_gian'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><small>💡 Các dòng có nền xanh là hoạt động trong 5 phút gần đây</small></p>";
} else {
    echo "<p style='color: red;'>❌ Không có nhật ký hoạt động nào</p>";
}
echo "</div>";

// 6. Thống kê theo user
echo "<h2>📈 THỐNG KÊ THEO USER:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("
    SELECT username, 
           COUNT(*) as total_activities,
           COUNT(CASE WHEN hanh_dong = 'Đăng nhập' THEN 1 END) as logins,
           COUNT(CASE WHEN hanh_dong = 'Đăng xuất' THEN 1 END) as logouts,
           MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong 
    GROUP BY username 
    ORDER BY total_activities DESC
");
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($userStats) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #6c757d; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Tổng hoạt động</th>";
    echo "<th style='padding: 8px;'>Đăng nhập</th>";
    echo "<th style='padding: 8px;'>Đăng xuất</th>";
    echo "<th style='padding: 8px;'>Hoạt động cuối</th>";
    echo "</tr>";
    foreach ($userStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $stat['username'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['total_activities'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['logins'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['logouts'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($stat['last_activity'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

echo "</div>";

// 7. Kết luận và hướng dẫn
echo "<h2>📋 KẾT LUẬN VÀ HƯỚNG DẪN:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>✅ Những gì đã được cải thiện:</h3>";
echo "<ol>";
echo "<li><strong>Logic đăng nhập:</strong> Tài khoản có username chứa 'manager' sẽ được coi là ADMIN</li>";
echo "<li><strong>Ghi nhật ký:</strong> Tự động ghi nhật ký khi đăng nhập/đăng xuất</li>";
echo "<li><strong>Theo dõi hoạt động:</strong> Hệ thống ghi lại tất cả hoạt động của user</li>";
echo "</ol>";

echo "<h3>🔧 Cách sử dụng:</h3>";
echo "<ol>";
echo "<li><strong>Tạo tài khoản manager:</strong> Sử dụng create_manager_account.php</li>";
echo "<li><strong>Đăng nhập:</strong> Đăng nhập với tài khoản manager (ví dụ: manager1/123456)</li>";
echo "<li><strong>Kiểm tra nhật ký:</strong> Vào trang thống kê hoạt động nhân viên để xem dữ liệu</li>";
echo "<li><strong>Test:</strong> Sử dụng test_manager_login.php để test</li>";
echo "</ol>";

echo "<h3>⚠️ Lưu ý:</h3>";
echo "<ul>";
echo "<li>Username phải chứa 'manager' để được nhận diện là quản lý</li>";
echo "<li>Tài khoản phải được kích hoạt (setlock = 1)</li>";
echo "<li>Hệ thống sẽ tự động ghi nhật ký khi đăng nhập thành công</li>";
echo "</ul>";
echo "</div>";
?>

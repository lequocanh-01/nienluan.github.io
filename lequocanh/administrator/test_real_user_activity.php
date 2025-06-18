<?php
session_start();
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';
require_once 'elements_LQA/mod/database.php';

echo "<h1>🎭 TEST HOẠT ĐỘNG NGƯỜI DÙNG THẬT</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>🎯 MÔ PHỎNG HOẠT ĐỘNG CỦA NHÂN VIÊN</h2>";

// Mô phỏng session của nhân viên
$realUsers = ['admin', 'staff2', 'manager1', 'lequocanh'];

foreach ($realUsers as $username) {
    echo "<div style='background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #2196f3;'>";
    echo "<h3>👤 Mô phỏng hoạt động của: <strong>$username</strong></h3>";
    
    // Đặt session giả lập
    $_SESSION['username'] = $username;
    
    echo "<div style='margin: 10px 0;'>";
    
    // 1. Đăng nhập
    echo "<p>🔐 <strong>Đăng nhập vào hệ thống...</strong></p>";
    $result1 = ghiNhatKyDangNhap($username);
    echo $result1 ? "<span style='color: green;'>✅ Ghi nhật ký đăng nhập thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký đăng nhập</span><br>";
    
    // 2. Xem danh sách (mô phỏng truy cập trang)
    if ($username == 'admin') {
        echo "<p>👁️ <strong>Xem danh sách nhân viên...</strong></p>";
        $result2 = ghiNhatKyXem($username, 'Nhân viên', null, 'Truy cập trang quản lý nhân viên');
        echo $result2 ? "<span style='color: green;'>✅ Ghi nhật ký xem danh sách thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký xem</span><br>";
        
        echo "<p>➕ <strong>Thêm nhân viên mới...</strong></p>";
        $result3 = ghiNhatKyThemMoi($username, 'Nhân viên', rand(100, 999), 'Thêm nhân viên: Nguyễn Văn Test');
        echo $result3 ? "<span style='color: green;'>✅ Ghi nhật ký thêm mới thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký thêm mới</span><br>";
        
    } elseif (strpos($username, 'staff') !== false) {
        echo "<p>👁️ <strong>Xem danh sách sản phẩm...</strong></p>";
        $result2 = ghiNhatKyXem($username, 'Sản phẩm', null, 'Truy cập trang quản lý sản phẩm');
        echo $result2 ? "<span style='color: green;'>✅ Ghi nhật ký xem danh sách thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký xem</span><br>";
        
        echo "<p>✏️ <strong>Cập nhật sản phẩm...</strong></p>";
        $result3 = ghiNhatKyCapNhat($username, 'Sản phẩm', rand(1, 50), 'Cập nhật giá sản phẩm');
        echo $result3 ? "<span style='color: green;'>✅ Ghi nhật ký cập nhật thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký cập nhật</span><br>";
        
    } elseif (strpos($username, 'manager') !== false) {
        echo "<p>👁️ <strong>Xem báo cáo doanh thu...</strong></p>";
        $result2 = ghiNhatKyXem($username, 'Báo cáo', null, 'Xem báo cáo doanh thu tháng ' . date('m/Y'));
        echo $result2 ? "<span style='color: green;'>✅ Ghi nhật ký xem báo cáo thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký xem báo cáo</span><br>";
        
        echo "<p>📊 <strong>Xuất báo cáo Excel...</strong></p>";
        $result3 = ghiNhatKyHoatDong($username, 'Xuất báo cáo', 'Báo cáo', null, 'Xuất báo cáo doanh thu Excel');
        echo $result3 ? "<span style='color: green;'>✅ Ghi nhật ký xuất báo cáo thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký xuất báo cáo</span><br>";
        
    } else {
        echo "<p>👁️ <strong>Xem danh sách khách hàng...</strong></p>";
        $result2 = ghiNhatKyXem($username, 'Khách hàng', null, 'Truy cập trang quản lý khách hàng');
        echo $result2 ? "<span style='color: green;'>✅ Ghi nhật ký xem danh sách thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký xem</span><br>";
        
        echo "<p>➕ <strong>Thêm khách hàng mới...</strong></p>";
        $result3 = ghiNhatKyThemMoi($username, 'Khách hàng', rand(100, 999), 'Thêm khách hàng: Trần Thị Test');
        echo $result3 ? "<span style='color: green;'>✅ Ghi nhật ký thêm mới thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký thêm mới</span><br>";
    }
    
    // 3. Đăng xuất
    echo "<p>🚪 <strong>Đăng xuất khỏi hệ thống...</strong></p>";
    $result4 = ghiNhatKyDangXuat($username);
    echo $result4 ? "<span style='color: green;'>✅ Ghi nhật ký đăng xuất thành công</span><br>" : "<span style='color: red;'>❌ Lỗi ghi nhật ký đăng xuất</span><br>";
    
    echo "</div>";
    echo "</div>";
    
    // Nghỉ 1 giây để tạo khoảng cách thời gian
    sleep(1);
}

// Reset session
unset($_SESSION['username']);

echo "<h2>📊 KẾT QUẢ TEST HOẠT ĐỘNG THẬT:</h2>";

// Lấy dữ liệu vừa ghi trong 5 phút gần đây
$stmt = $conn->query("
    SELECT username, hanh_dong, doi_tuong, chi_tiet, thoi_gian 
    FROM nhat_ky_hoat_dong 
    WHERE thoi_gian >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ORDER BY thoi_gian DESC
");
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($recentActivities) > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #155724;'>✅ Đã ghi được " . count($recentActivities) . " hoạt động trong 5 phút gần đây!</h3>";
    echo "</div>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    
    foreach ($recentActivities as $activity) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>" . htmlspecialchars($activity['username']) . "</strong></td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['hanh_dong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['doi_tuong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['chi_tiet']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['thoi_gian']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Không có hoạt động nào được ghi trong 5 phút gần đây!</h3>";
    echo "</div>";
}

// Thống kê theo user
echo "<h2>📈 THỐNG KÊ THEO NGƯỜI DÙNG:</h2>";
$stmt = $conn->query("
    SELECT username, COUNT(*) as total_activities,
           SUM(CASE WHEN hanh_dong = 'Đăng nhập' THEN 1 ELSE 0 END) as logins,
           SUM(CASE WHEN hanh_dong = 'Thêm mới' THEN 1 ELSE 0 END) as creates,
           SUM(CASE WHEN hanh_dong = 'Cập nhật' THEN 1 ELSE 0 END) as updates,
           SUM(CASE WHEN hanh_dong = 'Xem danh sách' THEN 1 ELSE 0 END) as views
    FROM nhat_ky_hoat_dong 
    WHERE thoi_gian >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    GROUP BY username 
    ORDER BY total_activities DESC
");
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($userStats) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Tổng HĐ</th>";
    echo "<th style='padding: 8px;'>Đăng nhập</th>";
    echo "<th style='padding: 8px;'>Thêm mới</th>";
    echo "<th style='padding: 8px;'>Cập nhật</th>";
    echo "<th style='padding: 8px;'>Xem</th>";
    echo "</tr>";
    
    foreach ($userStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>" . htmlspecialchars($stat['username']) . "</strong></td>";
        echo "<td style='padding: 8px; text-align: center;'><span style='background: #007bff; color: white; padding: 2px 8px; border-radius: 3px;'>" . $stat['total_activities'] . "</span></td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['logins'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['creates'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['updates'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['views'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>🔍 CÁCH THỨC GHI NHẬT KÝ HOẠT ĐỘNG:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>📝 Quy trình hoạt động:</h3>";
echo "<ol>";
echo "<li><strong>Trigger:</strong> Khi user thực hiện hành động (login, CRUD, view...)</li>";
echo "<li><strong>Helper Function:</strong> Code gọi function tương ứng (ghiNhatKyDangNhap, ghiNhatKyThemMoi...)</li>";
echo "<li><strong>Core Function:</strong> Tất cả helper đều gọi ghiNhatKyHoatDong()</li>";
echo "<li><strong>Validation:</strong> Kiểm tra dữ liệu đầu vào</li>";
echo "<li><strong>Database Insert:</strong> Lưu vào bảng nhat_ky_hoat_dong</li>";
echo "<li><strong>Return Result:</strong> Trả về true/false</li>";
echo "</ol>";

echo "<h3>🎯 Điểm mạnh của hệ thống:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Tự động:</strong> Ghi nhật ký ngay khi có hành động</li>";
echo "<li>✅ <strong>Chi tiết:</strong> Lưu đầy đủ thông tin (user, action, object, time...)</li>";
echo "<li>✅ <strong>Linh hoạt:</strong> Có thể tùy chỉnh cho từng loại hành động</li>";
echo "<li>✅ <strong>Hiệu suất:</strong> Sử dụng prepared statement, an toàn SQL injection</li>";
echo "<li>✅ <strong>Mở rộng:</strong> Dễ dàng thêm loại hành động mới</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=thongKeNhanVienCaiThien' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê cải thiện</a>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký chi tiết</a>";
echo "</div>";

echo "</div>";

// Tự động xóa file sau 60 giây
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Test hoàn thành. Bạn có muốn xóa file test này không?')) {";
echo "    fetch('test_real_user_activity.php?delete=1');";
echo "    alert('File test đã được xóa.');";
echo "  }";
echo "}, 15000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
?>

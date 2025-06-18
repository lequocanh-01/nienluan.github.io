<?php
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🧪 TEST GHI NHẬT KÝ ĐĂNG NHẬP</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

// Test ghi nhật ký đăng nhập cho các tài khoản nhân viên
$testAccounts = ['admin', 'staff2', 'manager1', 'lequocanh'];

echo "<h2>📝 Đang test ghi nhật ký đăng nhập...</h2>";

foreach ($testAccounts as $username) {
    echo "<div style='background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Testing: $username</strong><br>";
    
    $result = ghiNhatKyDangNhap($username);
    
    if ($result) {
        echo "<span style='color: green;'>✅ Ghi nhật ký thành công</span>";
    } else {
        echo "<span style='color: red;'>❌ Ghi nhật ký thất bại</span>";
    }
    echo "</div>";
}

echo "<h2>📊 Kiểm tra dữ liệu vừa ghi...</h2>";

require_once 'elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT username, hanh_dong, doi_tuong, thoi_gian FROM nhat_ky_hoat_dong WHERE hanh_dong = 'Đăng nhập' ORDER BY thoi_gian DESC LIMIT 10");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 10px;'>Username</th>";
echo "<th style='padding: 10px;'>Hành động</th>";
echo "<th style='padding: 10px;'>Đối tượng</th>";
echo "<th style='padding: 10px;'>Thời gian</th>";
echo "</tr>";

foreach ($activities as $activity) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['username']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['hanh_dong']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['doi_tuong']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['thoi_gian']) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>✅ Test hoàn thành!</h3>";
echo "<p>Bây giờ hãy thử đăng nhập bằng tài khoản nhân viên và kiểm tra lại trang thống kê.</p>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=thongKeNhanVienCaiThien' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê cải thiện</a>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký hoạt động</a>";
echo "</div>";

echo "</div>";

// Tự động xóa file sau 30 giây
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Test hoàn thành. Bạn có muốn xóa file test này không?')) {";
echo "    fetch('test_login_activity.php?delete=1');";
echo "    alert('File test đã được xóa.');";
echo "  }";
echo "}, 10000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
?>

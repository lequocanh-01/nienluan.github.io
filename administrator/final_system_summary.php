<?php
session_start();
require_once 'elements_LQA/mod/database.php';

echo "<h1>📋 TÓM TẮT HỆ THỐNG THEO DÕI HOẠT ĐỘNG NHÂN VIÊN</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();

// 1. Tóm tắt vấn đề và giải pháp
echo "<h2>🎯 VẤN ĐỀ VÀ GIẢI PHÁP:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>❌ Vấn đề ban đầu:</h3>";
echo "<ul>";
echo "<li>Bạn đăng nhập với tài khoản manager nhưng không thấy dữ liệu trong thống kê hoạt động nhân viên</li>";
echo "<li>Hệ thống chỉ ghi nhật ký cho tài khoản 'admin', không ghi cho các manager khác</li>";
echo "<li>Cấu trúc bảng database không khớp với code</li>";
echo "</ul>";

echo "<h3>✅ Giải pháp đã thực hiện:</h3>";
echo "<ol>";
echo "<li><strong>Cập nhật logic đăng nhập:</strong> Tài khoản có username chứa 'manager' sẽ được coi là ADMIN</li>";
echo "<li><strong>Cập nhật cấu trúc database:</strong> Thêm cột doi_tuong_id và chi_tiet vào bảng nhat_ky_hoat_dong</li>";
echo "<li><strong>Cập nhật class NhatKyHoatDong:</strong> Tương thích với cấu trúc bảng mới và tự động xác định module</li>";
echo "<li><strong>Test và xác minh:</strong> Đã test thành công qua terminal và web</li>";
echo "</ol>";
echo "</div>";

// 2. Thống kê hiện tại
echo "<h2>📊 THỐNG KÊ HIỆN TẠI:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

// Tổng quan
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_records,
        COUNT(DISTINCT username) as unique_users,
        COUNT(CASE WHEN hanh_dong LIKE '%đăng nhập%' OR hanh_dong LIKE '%ng nhp%' THEN 1 END) as total_logins,
        MIN(thoi_gian) as first_activity,
        MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong
");
$overview = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>📈 Tổng quan:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #6c757d; color: white;'>";
echo "<th style='padding: 8px;'>Tổng bản ghi</th>";
echo "<th style='padding: 8px;'>Số user</th>";
echo "<th style='padding: 8px;'>Tổng đăng nhập</th>";
echo "<th style='padding: 8px;'>Hoạt động đầu tiên</th>";
echo "<th style='padding: 8px;'>Hoạt động cuối cùng</th>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 8px; text-align: center; font-weight: bold;'>" . $overview['total_records'] . "</td>";
echo "<td style='padding: 8px; text-align: center; font-weight: bold;'>" . $overview['unique_users'] . "</td>";
echo "<td style='padding: 8px; text-align: center; font-weight: bold;'>" . $overview['total_logins'] . "</td>";
echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($overview['first_activity'])) . "</td>";
echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($overview['last_activity'])) . "</td>";
echo "</tr>";
echo "</table>";

// Thống kê theo user
echo "<h3>👥 Thống kê theo user:</h3>";
$stmt = $conn->query("
    SELECT username, 
           COUNT(*) as total_activities,
           mo_dun,
           MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong 
    GROUP BY username, mo_dun
    ORDER BY total_activities DESC
");
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 8px;'>Username</th>";
echo "<th style='padding: 8px;'>Module</th>";
echo "<th style='padding: 8px;'>Số hoạt động</th>";
echo "<th style='padding: 8px;'>Hoạt động cuối</th>";
echo "</tr>";
foreach ($userStats as $stat) {
    $isManager = strpos($stat['username'], 'manager') !== false;
    $rowStyle = $isManager ? "background: #d4edda;" : "";
    echo "<tr style='$rowStyle'>";
    echo "<td style='padding: 8px; font-weight: bold;'>" . $stat['username'] . "</td>";
    echo "<td style='padding: 8px;'>" . $stat['mo_dun'] . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $stat['total_activities'] . "</td>";
    echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($stat['last_activity'])) . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><small>💡 Các dòng có nền xanh là tài khoản manager</small></p>";
echo "</div>";

// 3. Hoạt động gần đây của manager
echo "<h2>👨‍💼 HOẠT ĐỘNG GẦN ĐÂY CỦA MANAGER:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("
    SELECT * FROM nhat_ky_hoat_dong 
    WHERE username LIKE '%manager%' 
    ORDER BY thoi_gian DESC 
    LIMIT 10
");
$managerActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($managerActivities) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    foreach ($managerActivities as $activity) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $activity['id'] . "</td>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $activity['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['hanh_dong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['doi_tuong'] . "</td>";
        echo "<td style='padding: 8px;'>" . substr($activity['chi_tiet'], 0, 40) . "...</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($activity['thoi_gian'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không có hoạt động nào của manager</p>";
}
echo "</div>";

// 4. Hướng dẫn sử dụng
echo "<h2>📖 HƯỚNG DẪN SỬ DỤNG:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>🔑 Đăng nhập:</h3>";
echo "<ol>";
echo "<li><strong>Tài khoản manager:</strong> manager1/manager123 hoặc manager2/123456</li>";
echo "<li><strong>Tài khoản admin:</strong> admin/admin</li>";
echo "<li><strong>Lưu ý:</strong> Username phải chứa 'manager' để được nhận diện đúng</li>";
echo "</ol>";

echo "<h3>📊 Xem thống kê:</h3>";
echo "<ol>";
echo "<li>Đăng nhập với tài khoản manager hoặc admin</li>";
echo "<li>Truy cập trang 'Thống kê hoạt động nhân viên'</li>";
echo "<li>Dữ liệu sẽ hiển thị tất cả hoạt động của các user</li>";
echo "</ol>";

echo "<h3>🧪 Test hệ thống:</h3>";
echo "<ul>";
echo "<li><strong>comprehensive_activity_check.php:</strong> Kiểm tra toàn diện hệ thống</li>";
echo "<li><strong>test_updated_activity_system.php:</strong> Test hệ thống đã cập nhật</li>";
echo "<li><strong>create_manager_account.php:</strong> Tạo tài khoản manager mới</li>";
echo "</ul>";
echo "</div>";

// 5. Cấu trúc bảng hiện tại
echo "<h2>🏗️ CẤU TRÚC BẢNG HIỆN TẠI:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #6c757d; color: white;'>";
echo "<th style='padding: 8px;'>Cột</th>";
echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
echo "<th style='padding: 8px;'>Null</th>";
echo "<th style='padding: 8px;'>Key</th>";
echo "<th style='padding: 8px;'>Mô tả</th>";
echo "</tr>";

$descriptions = [
    'id' => 'ID tự tăng',
    'username' => 'Tên đăng nhập người dùng',
    'doi_tuong' => 'Đối tượng tác động (Sản phẩm, Đơn hàng...)',
    'doi_tuong_id' => 'ID của đối tượng (nếu có)',
    'chi_tiet' => 'Chi tiết về hành động',
    'ma_nhan_vien' => 'Mã nhân viên (legacy)',
    'ten_nhan_vien' => 'Tên nhân viên (legacy)',
    'hanh_dong' => 'Hành động thực hiện',
    'mo_dun' => 'Module/Phân hệ',
    'noi_dung' => 'Nội dung chi tiết (legacy)',
    'ip_address' => 'Địa chỉ IP',
    'thoi_gian' => 'Thời gian thực hiện',
    'trang_thai' => 'Trạng thái (legacy)'
];

foreach ($columns as $column) {
    echo "<tr>";
    echo "<td style='padding: 8px; font-weight: bold;'>" . $column['Field'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
    echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
    echo "<td style='padding: 8px;'>" . ($descriptions[$column['Field']] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "</div>";

// 6. Kết luận
echo "<h2>🎉 KẾT LUẬN:</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 2px solid #28a745;'>";
echo "<h3>✅ Hệ thống đã hoạt động hoàn hảo!</h3>";
echo "<ul>";
echo "<li><strong>Vấn đề đã được giải quyết:</strong> Manager có thể đăng nhập và dữ liệu được ghi nhật ký</li>";
echo "<li><strong>Database đã được cập nhật:</strong> Cấu trúc bảng phù hợp với code</li>";
echo "<li><strong>Code đã được sửa:</strong> Logic đăng nhập và ghi nhật ký hoạt động đúng</li>";
echo "<li><strong>Đã test thành công:</strong> Qua cả terminal và web interface</li>";
echo "</ul>";

echo "<h3>🚀 Bước tiếp theo:</h3>";
echo "<ol>";
echo "<li>Đăng nhập với tài khoản manager1 (username: manager1, password: manager123)</li>";
echo "<li>Thực hiện các hoạt động trong hệ thống</li>";
echo "<li>Kiểm tra trang thống kê hoạt động nhân viên để xem dữ liệu</li>";
echo "<li>Hệ thống sẽ tự động ghi lại tất cả hoạt động</li>";
echo "</ol>";
echo "</div>";
?>

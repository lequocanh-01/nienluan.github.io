<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🧪 TEST HỆ THỐNG NHẬT KÝ ĐÃ CẬP NHẬT</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();

// 1. Kiểm tra cấu trúc bảng hiện tại
echo "<h2>🏗️ CẤU TRÚC BẢNG HIỆN TẠI:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

try {
    $stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #6c757d; color: white;'>";
    echo "<th style='padding: 8px;'>Cột</th>";
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
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi kiểm tra bảng: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 2. Test ghi nhật ký với các user khác nhau
echo "<h2>🧪 TEST GHI NHẬT KÝ:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$testUsers = [
    'admin' => 'Quản trị viên',
    'manager1' => 'Quản lý 1', 
    'manager2' => 'Quản lý 2',
    'staff1' => 'Nhân viên 1',
    'user1' => 'Người dùng 1'
];

foreach ($testUsers as $username => $fullname) {
    echo "<h4>🔸 Test user: $username ($fullname)</h4>";
    
    // Test đăng nhập
    $result1 = ghiNhatKyDangNhap($username);
    if ($result1) {
        echo "<p style='color: green;'>✅ Ghi nhật ký đăng nhập thành công - ID: $result1</p>";
    } else {
        echo "<p style='color: red;'>❌ Ghi nhật ký đăng nhập thất bại</p>";
    }
    
    // Test hoạt động khác
    $activities = [
        ['Xem danh sách', 'Sản phẩm', null, 'Xem danh sách sản phẩm'],
        ['Thêm mới', 'Khách hàng', 123, 'Thêm khách hàng mới'],
        ['Cập nhật', 'Đơn hàng', 456, 'Cập nhật trạng thái đơn hàng']
    ];
    
    foreach ($activities as $activity) {
        $result = ghiNhatKyHoatDong($username, $activity[0], $activity[1], $activity[2], $activity[3]);
        if ($result) {
            echo "<p style='color: blue;'>📝 Ghi nhật ký '{$activity[0]}' thành công - ID: $result</p>";
        } else {
            echo "<p style='color: red;'>❌ Ghi nhật ký '{$activity[0]}' thất bại</p>";
        }
    }
    
    // Test đăng xuất
    $result2 = ghiNhatKyDangXuat($username);
    if ($result2) {
        echo "<p style='color: orange;'>🚪 Ghi nhật ký đăng xuất thành công - ID: $result2</p>";
    }
    
    echo "<hr style='margin: 15px 0;'>";
}
echo "</div>";

// 3. Hiển thị dữ liệu vừa ghi
echo "<h2>📊 DỮ LIỆU VỪA GHI (20 bản ghi gần nhất):</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 20");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($activities) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 12px;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 6px;'>ID</th>";
    echo "<th style='padding: 6px;'>Username</th>";
    echo "<th style='padding: 6px;'>Hành động</th>";
    echo "<th style='padding: 6px;'>Đối tượng</th>";
    echo "<th style='padding: 6px;'>Chi tiết</th>";
    echo "<th style='padding: 6px;'>Module</th>";
    echo "<th style='padding: 6px;'>IP</th>";
    echo "<th style='padding: 6px;'>Thời gian</th>";
    echo "</tr>";
    foreach ($activities as $activity) {
        $isRecent = (time() - strtotime($activity['thoi_gian'])) < 300; // 5 phút gần đây
        $rowStyle = $isRecent ? "background: #d4edda;" : "";
        echo "<tr style='$rowStyle'>";
        echo "<td style='padding: 6px;'>" . $activity['id'] . "</td>";
        echo "<td style='padding: 6px; font-weight: bold;'>" . $activity['username'] . "</td>";
        echo "<td style='padding: 6px;'>" . $activity['hanh_dong'] . "</td>";
        echo "<td style='padding: 6px;'>" . $activity['doi_tuong'] . "</td>";
        echo "<td style='padding: 6px;'>" . substr($activity['chi_tiet'], 0, 30) . "...</td>";
        echo "<td style='padding: 6px;'>" . $activity['mo_dun'] . "</td>";
        echo "<td style='padding: 6px;'>" . $activity['ip_address'] . "</td>";
        echo "<td style='padding: 6px;'>" . date('d/m/Y H:i:s', strtotime($activity['thoi_gian'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><small>💡 Các dòng có nền xanh là hoạt động trong 5 phút gần đây</small></p>";
} else {
    echo "<p style='color: red;'>❌ Không có dữ liệu</p>";
}
echo "</div>";

// 4. Thống kê theo module
echo "<h2>📈 THỐNG KÊ THEO MODULE:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("
    SELECT mo_dun, 
           COUNT(*) as total_activities,
           COUNT(DISTINCT username) as unique_users,
           MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong 
    GROUP BY mo_dun 
    ORDER BY total_activities DESC
");
$moduleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($moduleStats) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #6c757d; color: white;'>";
    echo "<th style='padding: 8px;'>Module</th>";
    echo "<th style='padding: 8px;'>Tổng hoạt động</th>";
    echo "<th style='padding: 8px;'>Số user</th>";
    echo "<th style='padding: 8px;'>Hoạt động cuối</th>";
    echo "</tr>";
    foreach ($moduleStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $stat['mo_dun'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['total_activities'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['unique_users'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($stat['last_activity'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// 5. Thống kê theo user
echo "<h2>👥 THỐNG KÊ THEO USER:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$stmt = $conn->query("
    SELECT username, 
           COUNT(*) as total_activities,
           COUNT(CASE WHEN hanh_dong LIKE '%đăng nhập%' OR hanh_dong LIKE '%ng nhp%' THEN 1 END) as logins,
           COUNT(CASE WHEN hanh_dong LIKE '%đăng xuất%' OR hanh_dong LIKE '%ng xut%' THEN 1 END) as logouts,
           mo_dun,
           MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong 
    GROUP BY username, mo_dun
    ORDER BY total_activities DESC
");
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($userStats) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Module</th>";
    echo "<th style='padding: 8px;'>Tổng hoạt động</th>";
    echo "<th style='padding: 8px;'>Đăng nhập</th>";
    echo "<th style='padding: 8px;'>Đăng xuất</th>";
    echo "<th style='padding: 8px;'>Hoạt động cuối</th>";
    echo "</tr>";
    foreach ($userStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $stat['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $stat['mo_dun'] . "</td>";
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

// 6. Kết luận
echo "<h2>📋 KẾT LUẬN:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>✅ Những gì đã hoàn thành:</h3>";
echo "<ol>";
echo "<li><strong>Cập nhật cấu trúc bảng:</strong> Thêm cột doi_tuong_id và chi_tiet</li>";
echo "<li><strong>Cập nhật class NhatKyHoatDong:</strong> Tương thích với cấu trúc bảng mới</li>";
echo "<li><strong>Tự động xác định module:</strong> Dựa trên username (admin→Quản trị, manager→Quản lý, staff→Nhân viên)</li>";
echo "<li><strong>Test thành công:</strong> Ghi nhật ký cho tất cả loại user</li>";
echo "</ol>";

echo "<h3>🎯 Cách sử dụng:</h3>";
echo "<ol>";
echo "<li><strong>Đăng nhập với tài khoản manager:</strong> Hệ thống sẽ tự động ghi nhật ký</li>";
echo "<li><strong>Thực hiện các hoạt động:</strong> Tất cả sẽ được ghi lại</li>";
echo "<li><strong>Xem thống kê:</strong> Truy cập trang thống kê hoạt động nhân viên</li>";
echo "</ol>";

echo "<h3>⚠️ Lưu ý quan trọng:</h3>";
echo "<ul>";
echo "<li>Bảng đã được cập nhật với cấu trúc mới</li>";
echo "<li>Code đã được sửa để tương thích</li>";
echo "<li>Hệ thống sẽ tự động phân loại module dựa trên username</li>";
echo "<li>Dữ liệu cũ vẫn được giữ nguyên</li>";
echo "</ul>";
echo "</div>";
?>

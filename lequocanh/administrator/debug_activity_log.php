<?php
session_start();
require_once 'elements_LQA/mod/database.php';

echo "<h1>🔍 DEBUG NHẬT KÝ HOẠT ĐỘNG</h1>";

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>📊 KIỂM TRA DỮ LIỆU DATABASE</h2>";

// 1. Kiểm tra bảng nhat_ky_hoat_dong
echo "<h3>1. Bảng nhat_ky_hoat_dong:</h3>";
$stmt = $conn->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "<p><strong>Tổng số bản ghi:</strong> $total</p>";

if ($total > 0) {
    echo "<h4>📋 10 bản ghi gần nhất:</h4>";
    $stmt = $conn->query("
        SELECT id, username, hanh_dong, doi_tuong, chi_tiet, thoi_gian 
        FROM nhat_ky_hoat_dong 
        ORDER BY thoi_gian DESC 
        LIMIT 10
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $record['id'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($record['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($record['hanh_dong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($record['doi_tuong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($record['chi_tiet']) . "</td>";
        echo "<td style='padding: 8px;'>" . $record['thoi_gian'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thống kê theo username
    echo "<h4>📊 Thống kê theo username:</h4>";
    $stmt = $conn->query("
        SELECT username, COUNT(*) as total 
        FROM nhat_ky_hoat_dong 
        GROUP BY username 
        ORDER BY total DESC
    ");
    $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Số lượng hoạt động</th>";
    echo "</tr>";
    
    foreach ($userStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($stat['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . $stat['total'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 2. Kiểm tra bảng nhanvien
echo "<h3>2. Bảng nhanvien:</h3>";
$stmt = $conn->query("SELECT COUNT(*) as total FROM nhanvien");
$totalNV = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "<p><strong>Tổng số nhân viên:</strong> $totalNV</p>";

if ($totalNV > 0) {
    $stmt = $conn->query("SELECT idNhanVien, tenNV, username_user FROM nhanvien LIMIT 10");
    $nhanviens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #ffc107; color: black;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Tên NV</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "</tr>";
    
    foreach ($nhanviens as $nv) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $nv['idNhanVien'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($nv['tenNV']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($nv['username_user']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Kiểm tra bảng user
echo "<h3>3. Bảng user:</h3>";
$stmt = $conn->query("SELECT COUNT(*) as total FROM user");
$totalUser = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "<p><strong>Tổng số user:</strong> $totalUser</p>";

if ($totalUser > 0) {
    $stmt = $conn->query("SELECT iduser, username, hoten FROM user WHERE username LIKE '%manager%' OR username = 'admin' LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>👥 Users quan trọng (admin, manager):</h4>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #dc3545; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Họ tên</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $user['iduser'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($user['hoten']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";

// 4. Test class NhatKyHoatDong
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🧪 TEST CLASS NHATKYHOATDONG</h2>";

require_once 'elements_LQA/mod/nhatKyHoatDongCls.php';
$nhatKyObj = new NhatKyHoatDong();

// Test lấy danh sách nhật ký
echo "<h3>Test layDanhSachNhatKy():</h3>";
$filters = [
    'tu_ngay' => date('Y-m-d', strtotime('-7 days')),
    'den_ngay' => date('Y-m-d')
];

$nhatKyList = $nhatKyObj->layDanhSachNhatKy($filters, 5, 0);
echo "<p><strong>Số bản ghi trả về:</strong> " . count($nhatKyList) . "</p>";

if (count($nhatKyList) > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #17a2b8; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    
    foreach ($nhatKyList as $nhatKy) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($nhatKy['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($nhatKy['hanh_dong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($nhatKy['doi_tuong']) . "</td>";
        echo "<td style='padding: 8px;'>" . $nhatKy['thoi_gian'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không có dữ liệu trả về từ layDanhSachNhatKy()</p>";
}

// Test đếm tổng số nhật ký
$totalRecords = $nhatKyObj->demTongSoNhatKy($filters);
echo "<p><strong>Tổng số bản ghi (demTongSoNhatKy):</strong> $totalRecords</p>";

echo "</div>";

// 5. Kiểm tra logic filter
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔍 KIỂM TRA LOGIC FILTER</h2>";

// Test với filter trống
echo "<h3>Test với filter trống:</h3>";
$emptyFilters = [];
$emptyResult = $nhatKyObj->layDanhSachNhatKy($emptyFilters, 5, 0);
echo "<p><strong>Kết quả với filter trống:</strong> " . count($emptyResult) . " bản ghi</p>";

// Test với filter chỉ có ngày
echo "<h3>Test với filter chỉ có ngày:</h3>";
$dateOnlyFilters = [
    'tu_ngay' => date('Y-m-d', strtotime('-30 days')),
    'den_ngay' => date('Y-m-d')
];
$dateOnlyResult = $nhatKyObj->layDanhSachNhatKy($dateOnlyFilters, 10, 0);
echo "<p><strong>Kết quả với filter ngày (30 ngày qua):</strong> " . count($dateOnlyResult) . " bản ghi</p>";

// Test với filter username cụ thể
echo "<h3>Test với filter username cụ thể:</h3>";
$usernames = ['admin', 'manager1', 'staff2', 'leuquocanh05'];
foreach ($usernames as $testUsername) {
    $userFilters = [
        'username' => $testUsername,
        'tu_ngay' => date('Y-m-d', strtotime('-30 days')),
        'den_ngay' => date('Y-m-d')
    ];
    $userResult = $nhatKyObj->layDanhSachNhatKy($userFilters, 5, 0);
    echo "<p><strong>$testUsername:</strong> " . count($userResult) . " bản ghi</p>";
}

echo "</div>";

// 6. Gợi ý khắc phục
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>💡 GỢI Ý KHẮC PHỤC</h2>";

if ($total == 0) {
    echo "<p style='color: red;'>❌ <strong>Vấn đề:</strong> Bảng nhat_ky_hoat_dong trống hoàn toàn</p>";
    echo "<p>🔧 <strong>Giải pháp:</strong> Cần thêm dữ liệu test hoặc kiểm tra hệ thống ghi nhật ký</p>";
} elseif (count($nhatKyList) == 0) {
    echo "<p style='color: orange;'>⚠️ <strong>Vấn đề:</strong> Có dữ liệu nhưng class không trả về</p>";
    echo "<p>🔧 <strong>Giải pháp:</strong> Kiểm tra logic trong class NhatKyHoatDong</p>";
} else {
    echo "<p style='color: green;'>✅ <strong>Dữ liệu OK:</strong> Có dữ liệu và class hoạt động</p>";
    echo "<p>🔧 <strong>Vấn đề có thể:</strong> Logic filter trong trang hiển thị</p>";
}

echo "<h3>🛠️ Các bước khắc phục:</h3>";
echo "<ol>";
echo "<li>Kiểm tra logic filter trong nhatKyHoatDongTichHop.php</li>";
echo "<li>Đảm bảo không có filter username_in khi hiển thị tất cả</li>";
echo "<li>Kiểm tra quyền truy cập của user hiện tại</li>";
echo "<li>Test với dữ liệu mẫu</li>";
echo "</ol>";

echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<h3>🔗 Links kiểm tra</h3>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop&tab=chitiet' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem nhật ký chi tiết</a>";
echo "<a href='test_improved_activity_system.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🧪 Test hệ thống</a>";
echo "</div>";
?>

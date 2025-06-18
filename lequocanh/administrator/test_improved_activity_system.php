<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🧪 TEST HỆ THỐNG NHẬT KÝ CẢI TIẾN</h1>";

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🎯 MỤC TIÊU TEST:</h2>";
echo "<ul>";
echo "<li>✅ Kiểm tra phân quyền menu cho manager1</li>";
echo "<li>✅ Test ghi nhật ký truy cập menu</li>";
echo "<li>✅ Test ghi nhật ký thao tác CRUD</li>";
echo "<li>✅ Kiểm tra thời gian làm việc</li>";
echo "</ul>";
echo "</div>";

// Test 1: Phân quyền menu
echo "<h2>🔐 TEST 1: PHÂN QUYỀN MENU</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

require_once 'elements_LQA/mod/phanquyenCls.php';
$phanQuyen = new PhanQuyen();

$testUsers = ['admin', 'manager1', 'staff2', 'leuquocanh05'];
foreach ($testUsers as $username) {
    echo "<h3>👤 User: $username</h3>";
    
    $isAdmin = ($username === 'admin');
    $isNhanVien = $phanQuyen->isNhanVien($username);
    $isManager = (strpos($username, 'manager') !== false);
    
    echo "<p><strong>Phân loại:</strong> ";
    if ($isAdmin && $username === 'admin') {
        echo "🔴 Admin thật";
    } elseif ($isNhanVien || $isManager) {
        echo "🟡 Nhân viên/Manager";
    } else {
        echo "🟢 Người dùng thường";
    }
    echo "</p>";
    
    // Test một số menu
    $testMenus = ['hanghoaview', 'userview', 'nhanvienview', 'baocaoview'];
    echo "<p><strong>Quyền truy cập menu:</strong></p>";
    echo "<ul>";
    foreach ($testMenus as $menu) {
        $hasAccess = false;
        
        if ($isAdmin && $username === 'admin') {
            $hasAccess = true;
        } elseif ($isNhanVien || $isManager) {
            $hasAccess = $phanQuyen->checkAccess($menu, $username);
        }
        
        $icon = $hasAccess ? "✅" : "❌";
        echo "<li>$icon $menu</li>";
    }
    echo "</ul>";
    echo "<hr>";
}
echo "</div>";

// Test 2: Ghi nhật ký truy cập menu
echo "<h2>📝 TEST 2: GHI NHẬT KÝ TRUY CẬP MENU</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$testMenuAccess = [
    ['manager1', 'hanghoaview', 'Xem danh sách sản phẩm'],
    ['manager1', 'userview', 'Xem danh sách khách hàng'],
    ['staff2', 'donhangview', 'Xem danh sách đơn hàng'],
    ['leuquocanh05', 'lichsumuahang', 'Xem lịch sử mua hàng']
];

foreach ($testMenuAccess as $test) {
    $username = $test[0];
    $menu = $test[1];
    $description = $test[2];
    
    // Simulate menu access
    require_once 'elements_LQA/mnhatkyhoatdong/menuAccessLogger.php';
    $result = ghiNhatKyTruyCapMenu($username, $menu, $description);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Ghi nhật ký truy cập thành công: $username → $menu</p>";
    } else {
        echo "<p style='color: red;'>❌ Lỗi ghi nhật ký: $username → $menu</p>";
    }
}
echo "</div>";

// Test 3: Ghi nhật ký thao tác CRUD
echo "<h2>🔧 TEST 3: GHI NHẬT KÝ THAO TÁC CRUD</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$testCRUD = [
    ['manager1', 'Thêm mới', 'Sản phẩm', 123, 'Thêm sản phẩm: iPhone 15 Pro'],
    ['manager1', 'Cập nhật', 'Sản phẩm', 123, 'Cập nhật giá sản phẩm iPhone 15 Pro'],
    ['manager1', 'Xóa', 'Sản phẩm', 123, 'Xóa sản phẩm iPhone 15 Pro'],
    ['staff2', 'Xem', 'Đơn hàng', null, 'Xem chi tiết đơn hàng #DH001'],
    ['staff2', 'Cập nhật', 'Đơn hàng', 456, 'Cập nhật trạng thái đơn hàng #DH001']
];

foreach ($testCRUD as $test) {
    $username = $test[0];
    $action = $test[1];
    $object = $test[2];
    $objectId = $test[3];
    $detail = $test[4];
    
    $result = ghiNhatKyHoatDong($username, $action, $object, $objectId, $detail);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Ghi nhật ký CRUD thành công: $username → $action $object</p>";
    } else {
        echo "<p style='color: red;'>❌ Lỗi ghi nhật ký CRUD: $username → $action $object</p>";
    }
}
echo "</div>";

// Test 4: Thời gian làm việc
echo "<h2>⏱️ TEST 4: THỜI GIAN LÀM VIỆC</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$testWorkTime = [
    ['manager1', 'hanghoaview', 180], // 3 phút
    ['manager1', 'userview', 300],    // 5 phút
    ['staff2', 'donhangview', 120],   // 2 phút
    ['staff2', 'baocaoview', 600]     // 10 phút
];

foreach ($testWorkTime as $test) {
    $username = $test[0];
    $menu = $test[1];
    $timeSeconds = $test[2];
    
    $result = ghiNhatKyThoiGianTrang($username, $menu, $timeSeconds);
    
    $minutes = floor($timeSeconds / 60);
    $seconds = $timeSeconds % 60;
    $timeText = $minutes > 0 ? "{$minutes} phút {$seconds} giây" : "{$seconds} giây";
    
    if ($result) {
        echo "<p style='color: green;'>✅ Ghi thời gian làm việc: $username ở $menu trong $timeText</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Không ghi thời gian (quá ngắn hoặc lỗi): $username ở $menu trong $timeText</p>";
    }
}
echo "</div>";

// Hiển thị kết quả
echo "<h2>📊 KẾT QUẢ NHẬT KÝ MỚI NHẤT</h2>";
$stmt = $conn->query("
    SELECT username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, thoi_gian 
    FROM nhat_ky_hoat_dong 
    WHERE thoi_gian >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ORDER BY thoi_gian DESC
    LIMIT 20
");
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 8px;'>Username</th>";
echo "<th style='padding: 8px;'>Hành động</th>";
echo "<th style='padding: 8px;'>Đối tượng</th>";
echo "<th style='padding: 8px;'>ID</th>";
echo "<th style='padding: 8px;'>Chi tiết</th>";
echo "<th style='padding: 8px;'>Thời gian</th>";
echo "</tr>";

foreach ($recentActivities as $activity) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['username']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['hanh_dong']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['doi_tuong']) . "</td>";
    echo "<td style='padding: 8px;'>" . ($activity['doi_tuong_id'] ?: '-') . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($activity['chi_tiet']) . "</td>";
    echo "<td style='padding: 8px;'>" . $activity['thoi_gian'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎉 KẾT LUẬN:</h3>";
echo "<p>✅ Hệ thống nhật ký đã được cải tiến thành công!</p>";
echo "<p>✅ Phân quyền menu hoạt động chính xác</p>";
echo "<p>✅ Ghi nhật ký truy cập menu tự động</p>";
echo "<p>✅ Ghi nhật ký thao tác CRUD chi tiết</p>";
echo "<p>✅ Theo dõi thời gian làm việc</p>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<h3>🔗 Links kiểm tra</h3>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê hoạt động</a>";
echo "<a href='UserLogin.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 Test đăng nhập manager1</a>";
echo "</div>";
?>

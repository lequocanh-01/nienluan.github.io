<?php
session_start();
require_once './elements_LQA/mod/phanquyenCls.php';

echo "<h1>🔍 DEBUG MENU SYSTEM</h1>";

echo "<style>
.debug-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.test-pass { color: #28a745; font-weight: bold; }
.test-fail { color: #dc3545; font-weight: bold; }
.test-warning { color: #ffc107; font-weight: bold; }
</style>";

// Test với manager1
$_SESSION['USER'] = 'manager1';
unset($_SESSION['ADMIN']);

$phanQuyen = new PhanQuyen();
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
$isAdmin = isset($_SESSION['ADMIN']);
$isNhanVien = $phanQuyen->isNhanVien($username);

echo "<div class='debug-section'>";
echo "<h2>📊 THÔNG TIN SESSION</h2>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Is Admin:</strong> " . ($isAdmin ? 'true' : 'false') . "</p>";
echo "<p><strong>Is Nhan Vien:</strong> " . ($isNhanVien ? 'true' : 'false') . "</p>";
echo "<p><strong>SESSION['USER']:</strong> " . ($_SESSION['USER'] ?? 'không có') . "</p>";
echo "<p><strong>SESSION['ADMIN']:</strong> " . ($_SESSION['ADMIN'] ?? 'không có') . "</p>";
echo "</div>";

echo "<div class='debug-section'>";
echo "<h2>🎯 TEST QUYỀN TRUY CẬP</h2>";

$testModules = [
    'baocaoview' => 'Báo cáo tổng hợp',
    'doanhThuView' => 'Báo cáo doanh thu',
    'sanPhamBanChayView' => 'Sản phẩm bán chạy',
    'loiNhuanView' => 'Báo cáo lợi nhuận',
    'hanghoaview' => 'Quản lý hàng hóa',
    'khachhangview' => 'Quản lý khách hàng',
    'nhanvienview' => 'Quản lý nhân viên'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 10px;'>Module</th>";
echo "<th style='padding: 10px;'>Tên</th>";
echo "<th style='padding: 10px;'>Có quyền?</th>";
echo "<th style='padding: 10px;'>Kết quả</th>";
echo "</tr>";

foreach ($testModules as $module => $name) {
    try {
        $hasAccess = $phanQuyen->checkAccess($module, $username);
        $result = $hasAccess ? 'CÓ' : 'KHÔNG';
        $class = $hasAccess ? 'test-pass' : 'test-fail';
        
        echo "<tr>";
        echo "<td style='padding: 10px;'>$module</td>";
        echo "<td style='padding: 10px;'>$name</td>";
        echo "<td style='padding: 10px;' class='$class'>$result</td>";
        echo "<td style='padding: 10px;' class='$class'>" . ($hasAccess ? '✅' : '❌') . "</td>";
        echo "</tr>";
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td style='padding: 10px;'>$module</td>";
        echo "<td style='padding: 10px;'>$name</td>";
        echo "<td style='padding: 10px;' class='test-fail'>LỖI</td>";
        echo "<td style='padding: 10px;' class='test-fail'>❌ " . $e->getMessage() . "</td>";
        echo "</tr>";
    }
}
echo "</table>";
echo "</div>";

echo "<div class='debug-section'>";
echo "<h2>📋 MENU ITEMS SIMULATION</h2>";

$menu_items = [
    'baocaoview' => ['icon' => 'fas fa-chart-line', 'text' => 'Báo cáo tổng hợp', 'admin_only' => false, 'hide_from_employee' => false],
    'doanhThuView' => ['icon' => 'fas fa-money-bill-wave', 'text' => 'Báo cáo doanh thu', 'admin_only' => false, 'hide_from_employee' => false],
    'sanPhamBanChayView' => ['icon' => 'fas fa-fire', 'text' => 'Sản phẩm bán chạy', 'admin_only' => false, 'hide_from_employee' => false],
    'loiNhuanView' => ['icon' => 'fas fa-chart-pie', 'text' => 'Báo cáo lợi nhuận', 'admin_only' => false, 'hide_from_employee' => false],
    'hanghoaview' => ['icon' => 'fas fa-box', 'text' => 'Hàng hóa', 'admin_only' => false, 'hide_from_employee' => false],
    'khachhangview' => ['icon' => 'fas fa-user-friends', 'text' => 'Khách hàng', 'admin_only' => false, 'hide_from_employee' => false],
    'nhanvienview' => ['icon' => 'fas fa-user-tie', 'text' => 'Nhân viên', 'admin_only' => false, 'hide_from_employee' => true],
];

echo "<h3>Menu sẽ hiển thị cho manager1:</h3>";
echo "<ul>";

$menuCount = 0;
foreach ($menu_items as $req => $item) {
    $shouldShow = false;
    $reason = '';
    
    // Logic giống như trong left.php
    if ($isAdmin && $username === 'admin') {
        $shouldShow = true;
        $reason = 'Admin access';
    }
    else if ($isNhanVien || strpos($username, 'manager') !== false) {
        if ($item['hide_from_employee']) {
            $shouldShow = false;
            $reason = 'Hidden from employee';
        } else {
            try {
                $hasAccess = $phanQuyen->checkAccess($req, $username);
                $shouldShow = $hasAccess;
                $reason = $hasAccess ? 'Has permission' : 'No permission';
            } catch (Exception $e) {
                $shouldShow = false;
                $reason = 'Error: ' . $e->getMessage();
            }
        }
    }
    else {
        $basicUserModules = ['userprofile', 'userUpdateProfile', 'lichsumuahang'];
        $shouldShow = in_array($req, $basicUserModules);
        $reason = $shouldShow ? 'Basic user module' : 'Not basic user module';
    }
    
    if ($shouldShow) {
        echo "<li style='color: green;'>✅ <strong>{$item['text']}</strong> ($req) - $reason</li>";
        $menuCount++;
    } else {
        echo "<li style='color: red;'>❌ <strong>{$item['text']}</strong> ($req) - $reason</li>";
    }
}

echo "</ul>";
echo "<p><strong>Tổng số menu sẽ hiển thị: $menuCount</strong></p>";
echo "</div>";

echo "<div class='debug-section'>";
echo "<h2>🔧 KHẮC PHỤC</h2>";

if ($menuCount == 0) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>🚨 VẤN ĐỀ: Không có menu nào hiển thị!</h3>";
    echo "<p><strong>Nguyên nhân có thể:</strong></p>";
    echo "<ul>";
    echo "<li>Security middleware đang chặn tất cả truy cập</li>";
    echo "<li>Logic checkAccess có lỗi</li>";
    echo "<li>Database không có dữ liệu phân quyền</li>";
    echo "<li>Session không đúng</li>";
    echo "</ul>";
    
    echo "<p><strong>Giải pháp:</strong></p>";
    echo "<ol>";
    echo "<li>Tạm thời disable security middleware</li>";
    echo "<li>Kiểm tra database phân quyền</li>";
    echo "<li>Sửa logic menu</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>✅ Menu hoạt động bình thường</h3>";
    echo "<p>Manager1 sẽ thấy $menuCount menu items.</p>";
    echo "</div>";
}

echo "</div>";

// Test database
echo "<div class='debug-section'>";
echo "<h2>🗄️ KIỂM TRA DATABASE</h2>";

require_once './elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra manager1 trong database
$stmt = $conn->query("
    SELECT nv.idNhanVien, nv.tenNV, u.username 
    FROM nhanvien nv 
    JOIN user u ON nv.iduser = u.iduser 
    WHERE u.username = 'manager1'
");
$manager1Info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($manager1Info) {
    echo "<p><strong>✅ Manager1 tồn tại trong database:</strong></p>";
    echo "<ul>";
    echo "<li>ID Nhân viên: " . $manager1Info['idNhanVien'] . "</li>";
    echo "<li>Tên: " . $manager1Info['tenNV'] . "</li>";
    echo "</ul>";
    
    // Kiểm tra quyền
    $stmt = $conn->prepare("
        SELECT pq.maPhanHe, pq.tenPhanHe 
        FROM NhanVien_PhanHeQuanLy nvpq 
        JOIN PhanHeQuanLy pq ON nvpq.idPhanHe = pq.idPhanHe 
        WHERE nvpq.idNhanVien = ?
    ");
    $stmt->execute([$manager1Info['idNhanVien']]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Quyền được gán:</strong></p>";
    echo "<ul>";
    foreach ($permissions as $perm) {
        echo "<li>" . $perm['maPhanHe'] . " - " . $perm['tenPhanHe'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>❌ Manager1 không tồn tại trong database!</strong></p>";
}

echo "</div>";

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session đã được reset sau debug.</em></p>";
?>

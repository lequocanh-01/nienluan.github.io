<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/phanquyenCls.php';

echo "<h1>🧪 TEST TRUY CẬP THỰC TẾ MANAGER1</h1>";

// Giả lập đăng nhập manager1
$_SESSION['USER'] = 'manager1';
unset($_SESSION['ADMIN']); // Đảm bảo không có quyền admin

$phanQuyen = new PhanQuyen();

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔐 THIẾT LẬP TEST</h2>";
echo "<p><strong>Session USER:</strong> " . ($_SESSION['USER'] ?? 'Không có') . "</p>";
echo "<p><strong>Session ADMIN:</strong> " . ($_SESSION['ADMIN'] ?? 'Không có') . "</p>";
echo "</div>";

// Danh sách module cần test
$testModules = [
    // Module được gán (nên có quyền)
    'baocaoview' => 'Báo cáo tổng hợp',
    'doanhThuView' => 'Báo cáo doanh thu', 
    'sanPhamBanChayView' => 'Báo cáo sản phẩm bán chạy',
    'loiNhuanView' => 'Báo cáo lợi nhuận',
    
    // Module KHÔNG được gán (không nên có quyền)
    'hanghoaview' => 'Quản lý hàng hóa',
    'khachhangview' => 'Quản lý khách hàng',
    'nhanvienview' => 'Quản lý nhân viên',
    'orders' => 'Quản lý đơn hàng',
    'mtonkho' => 'Quản lý tồn kho',
    'loaihangview' => 'Quản lý loại hàng',
    'dongiaview' => 'Quản lý đơn giá',
    'thuonghieuview' => 'Quản lý thương hiệu',
    'nhacungcapview' => 'Quản lý nhà cung cấp',
    'mphieunhap' => 'Quản lý phiếu nhập',
    'roleview' => 'Quản lý vai trò',
    'vaiTroView' => 'Quản lý vai trò người dùng',
    
    // Module cơ bản (nên có quyền)
    'userprofile' => 'Hồ sơ cá nhân',
    'userUpdateProfile' => 'Cập nhật hồ sơ',
    'thongbao' => 'Thông báo'
];

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔍 KẾT QUẢ TEST TRUY CẬP</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 8px;'>Module</th>";
echo "<th style='padding: 8px;'>Tên</th>";
echo "<th style='padding: 8px;'>Có quyền?</th>";
echo "<th style='padding: 8px;'>Đánh giá</th>";
echo "<th style='padding: 8px;'>isAdmin()</th>";
echo "<th style='padding: 8px;'>isNhanVien()</th>";
echo "</tr>";

$violations = [];
$allowedCount = 0;
$deniedCount = 0;

foreach ($testModules as $moduleCode => $moduleName) {
    $hasAccess = $phanQuyen->checkAccess($moduleCode, 'manager1');
    $isAdmin = $phanQuyen->isAdmin('manager1');
    $isNhanVien = $phanQuyen->isNhanVien('manager1');
    
    // Xác định module nào nên có quyền
    $shouldHaveAccess = in_array($moduleCode, [
        'baocaoview', 'doanhThuView', 'sanPhamBanChayView', 'loiNhuanView',
        'userprofile', 'userUpdateProfile', 'thongbao'
    ]);
    
    $evaluation = '';
    $rowColor = '';
    
    if ($hasAccess && $shouldHaveAccess) {
        $evaluation = '✅ ĐÚNG';
        $rowColor = 'background: #d4edda;';
        $allowedCount++;
    } elseif (!$hasAccess && !$shouldHaveAccess) {
        $evaluation = '✅ ĐÚNG';
        $rowColor = 'background: #d4edda;';
        $deniedCount++;
    } elseif ($hasAccess && !$shouldHaveAccess) {
        $evaluation = '🚨 VI PHẠM';
        $rowColor = 'background: #f8d7da;';
        $violations[] = $moduleCode . ' - ' . $moduleName;
    } else {
        $evaluation = '⚠️ THIẾU QUYỀN';
        $rowColor = 'background: #fff3cd;';
    }
    
    echo "<tr style='$rowColor'>";
    echo "<td style='padding: 8px;'>$moduleCode</td>";
    echo "<td style='padding: 8px;'>$moduleName</td>";
    echo "<td style='padding: 8px;'>" . ($hasAccess ? '✅' : '❌') . "</td>";
    echo "<td style='padding: 8px;'><strong>$evaluation</strong></td>";
    echo "<td style='padding: 8px;'>" . ($isAdmin ? '✅' : '❌') . "</td>";
    echo "<td style='padding: 8px;'>" . ($isNhanVien ? '✅' : '❌') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "</div>";

// Báo cáo tổng kết
echo "<div style='background: " . (count($violations) > 0 ? '#f8d7da' : '#d4edda') . "; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>" . (count($violations) > 0 ? '🚨 PHÁT HIỆN VI PHẠM BẢO MẬT!' : '✅ BẢO MẬT OK') . "</h2>";

echo "<h3>📊 Thống kê:</h3>";
echo "<ul>";
echo "<li><strong>Tổng module test:</strong> " . count($testModules) . "</li>";
echo "<li><strong>Được phép truy cập:</strong> $allowedCount</li>";
echo "<li><strong>Bị từ chối:</strong> $deniedCount</li>";
echo "<li><strong>Vi phạm bảo mật:</strong> " . count($violations) . "</li>";
echo "</ul>";

if (count($violations) > 0) {
    echo "<h3>🚨 Danh sách vi phạm:</h3>";
    echo "<ul>";
    foreach ($violations as $violation) {
        echo "<li style='color: #721c24;'><strong>$violation</strong></li>";
    }
    echo "</ul>";
    
    echo "<h3>🛠️ Hành động cần thực hiện:</h3>";
    echo "<ol>";
    echo "<li>Kiểm tra logic isAdmin() - manager1 có đang được coi là admin?</li>";
    echo "<li>Kiểm tra basicModules - có module nào thừa không?</li>";
    echo "<li>Kiểm tra checkNhanVienHasAccess() - có lỗi logic không?</li>";
    echo "<li>Kiểm tra session - có session ADMIN nào bị rò rỉ không?</li>";
    echo "</ol>";
} else {
    echo "<p style='color: #155724;'>Manager1 chỉ truy cập được các module được phép. Hệ thống bảo mật hoạt động tốt!</p>";
}

echo "</div>";

// Debug thông tin chi tiết
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔧 THÔNG TIN DEBUG</h2>";

echo "<h3>Session hiện tại:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Kiểm tra chi tiết manager1:</h3>";
echo "<ul>";
echo "<li><strong>isAdmin('manager1'):</strong> " . ($phanQuyen->isAdmin('manager1') ? 'true' : 'false') . "</li>";
echo "<li><strong>isNhanVien('manager1'):</strong> " . ($phanQuyen->isNhanVien('manager1') ? 'true' : 'false') . "</li>";
echo "</ul>";

// Kiểm tra database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h3>Thông tin database:</h3>";
$stmt = $conn->query("
    SELECT nv.idNhanVien, nv.tenNV, u.username, u.iduser
    FROM nhanvien nv 
    JOIN user u ON nv.iduser = u.iduser 
    WHERE u.username = 'manager1'
");
$manager1Info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($manager1Info) {
    echo "<ul>";
    echo "<li><strong>ID Nhân viên:</strong> " . $manager1Info['idNhanVien'] . "</li>";
    echo "<li><strong>ID User:</strong> " . $manager1Info['iduser'] . "</li>";
    echo "<li><strong>Tên:</strong> " . htmlspecialchars($manager1Info['tenNV']) . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Không tìm thấy thông tin manager1 trong database!</p>";
}

echo "</div>";

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session đã được reset sau test.</em></p>";
?>

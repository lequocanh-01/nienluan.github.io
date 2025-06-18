<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/phanquyenCls.php';
require_once 'elements_LQA/mod/phanHeQuanLyCls.php';

echo "<h1>🔒 KIỂM TRA BẢO MẬT PHÂN QUYỀN</h1>";

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

$phanQuyen = new PhanQuyen();
$phanHeObj = new PhanHeQuanLy();

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>⚠️ KIỂM TRA QUYỀN MANAGER1</h2>";

// 1. Lấy thông tin manager1
$stmt = $conn->query("
    SELECT nv.*, u.username 
    FROM nhanvien nv 
    JOIN user u ON nv.iduser = u.iduser 
    WHERE u.username = 'manager1'
");
$manager1 = $stmt->fetch(PDO::FETCH_ASSOC);

if ($manager1) {
    echo "<h3>👤 Thông tin Manager1:</h3>";
    echo "<p><strong>ID Nhân viên:</strong> " . $manager1['idNhanVien'] . "</p>";
    echo "<p><strong>Tên:</strong> " . htmlspecialchars($manager1['tenNV']) . "</p>";
    echo "<p><strong>Username:</strong> " . $manager1['username'] . "</p>";
    
    // 2. Lấy quyền đã được gán
    echo "<h3>✅ Quyền đã được gán:</h3>";
    $stmt = $conn->query("
        SELECT nvpq.*, pq.maPhanHe, pq.tenPhanHe 
        FROM NhanVien_PhanHeQuanLy nvpq 
        JOIN PhanHeQuanLy pq ON nvpq.idPhanHe = pq.idPhanHe 
        WHERE nvpq.idNhanVien = " . $manager1['idNhanVien']
    );
    $assignedPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>Mã phần hệ</th>";
    echo "<th style='padding: 8px;'>Tên phần hệ</th>";
    echo "<th style='padding: 8px;'>Ngày gán</th>";
    echo "</tr>";
    
    $allowedModules = [];
    foreach ($assignedPermissions as $perm) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($perm['maPhanHe']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($perm['tenPhanHe']) . "</td>";
        echo "<td style='padding: 8px;'>" . $perm['ngayGan'] . "</td>";
        echo "</tr>";
        $allowedModules[] = $perm['maPhanHe'];
    }
    echo "</table>";
    
    // 3. Kiểm tra tất cả module trong hệ thống
    echo "<h3>🔍 Kiểm tra truy cập tất cả module:</h3>";
    $stmt = $conn->query("SELECT * FROM PhanHeQuanLy ORDER BY maPhanHe");
    $allModules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>Module</th>";
    echo "<th style='padding: 8px;'>Tên</th>";
    echo "<th style='padding: 8px;'>Được gán?</th>";
    echo "<th style='padding: 8px;'>checkAccess()</th>";
    echo "<th style='padding: 8px;'>checkNhanVienHasAccess()</th>";
    echo "<th style='padding: 8px;'>Rủi ro</th>";
    echo "</tr>";
    
    $riskyModules = [];
    foreach ($allModules as $module) {
        $isAssigned = in_array($module['maPhanHe'], $allowedModules);
        $checkAccess = $phanQuyen->checkAccess($module['maPhanHe'], 'manager1');
        $checkNhanVienAccess = $phanHeObj->checkNhanVienHasAccess($manager1['idNhanVien'], $module['maPhanHe']);
        
        // Xác định rủi ro
        $risk = '';
        $rowColor = '';
        if (!$isAssigned && ($checkAccess || $checkNhanVienAccess)) {
            $risk = '🚨 CAO';
            $rowColor = 'background: #f8d7da;';
            $riskyModules[] = $module;
        } elseif ($isAssigned && ($checkAccess || $checkNhanVienAccess)) {
            $risk = '✅ OK';
            $rowColor = 'background: #d4edda;';
        } elseif ($isAssigned && !($checkAccess || $checkNhanVienAccess)) {
            $risk = '⚠️ TRUNG';
            $rowColor = 'background: #fff3cd;';
        } else {
            $risk = '✅ AN TOÀN';
        }
        
        echo "<tr style='$rowColor'>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($module['maPhanHe']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($module['tenPhanHe']) . "</td>";
        echo "<td style='padding: 8px;'>" . ($isAssigned ? '✅' : '❌') . "</td>";
        echo "<td style='padding: 8px;'>" . ($checkAccess ? '✅' : '❌') . "</td>";
        echo "<td style='padding: 8px;'>" . ($checkNhanVienAccess ? '✅' : '❌') . "</td>";
        echo "<td style='padding: 8px;'><strong>$risk</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Báo cáo rủi ro
    if (count($riskyModules) > 0) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border: 2px solid #dc3545;'>";
        echo "<h3>🚨 CẢNH BÁO BẢO MẬT!</h3>";
        echo "<p><strong>Manager1 có thể truy cập " . count($riskyModules) . " module KHÔNG được phép:</strong></p>";
        echo "<ul>";
        foreach ($riskyModules as $risky) {
            echo "<li><strong>" . htmlspecialchars($risky['maPhanHe']) . "</strong> - " . htmlspecialchars($risky['tenPhanHe']) . "</li>";
        }
        echo "</ul>";
        echo "<p style='color: #721c24;'><strong>Khuyến nghị:</strong> Cần kiểm tra và sửa logic phân quyền ngay lập tức!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ BẢO MẬT OK</h3>";
        echo "<p>Manager1 chỉ truy cập được các module đã được gán quyền.</p>";
        echo "</div>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Không tìm thấy manager1 trong hệ thống!</p>";
}

echo "</div>";

// 5. Kiểm tra logic phân quyền
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔧 PHÂN TÍCH LOGIC PHÂN QUYỀN</h2>";

echo "<h3>📋 Các module cơ bản (luôn cho phép nhân viên):</h3>";
$basicModules = ['userprofile', 'userUpdateProfile', 'thongbao'];
echo "<ul>";
foreach ($basicModules as $basic) {
    echo "<li>$basic</li>";
}
echo "</ul>";

echo "<h3>🔍 Test một số module quan trọng:</h3>";
$testModules = [
    'hanghoaview' => 'Quản lý hàng hóa',
    'khachhangview' => 'Quản lý khách hàng', 
    'nhanvienview' => 'Quản lý nhân viên',
    'orders' => 'Quản lý đơn hàng',
    'mtonkho' => 'Quản lý tồn kho'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #17a2b8; color: white;'>";
echo "<th style='padding: 8px;'>Module</th>";
echo "<th style='padding: 8px;'>Tên</th>";
echo "<th style='padding: 8px;'>Manager1 có quyền?</th>";
echo "<th style='padding: 8px;'>Đánh giá</th>";
echo "</tr>";

foreach ($testModules as $moduleCode => $moduleName) {
    $hasAccess = $phanQuyen->checkAccess($moduleCode, 'manager1');
    $evaluation = $hasAccess ? '⚠️ CÓ QUYỀN' : '✅ KHÔNG QUYỀN';
    $rowColor = $hasAccess ? 'background: #fff3cd;' : '';
    
    echo "<tr style='$rowColor'>";
    echo "<td style='padding: 8px;'>$moduleCode</td>";
    echo "<td style='padding: 8px;'>$moduleName</td>";
    echo "<td style='padding: 8px;'>" . ($hasAccess ? '✅' : '❌') . "</td>";
    echo "<td style='padding: 8px;'><strong>$evaluation</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "</div>";

// 6. Gợi ý khắc phục
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>💡 GỢI Ý KHẮC PHỤC</h2>";

echo "<h3>🛠️ Các bước cần thực hiện:</h3>";
echo "<ol>";
echo "<li><strong>Kiểm tra logic isNhanVien():</strong> Đảm bảo chỉ nhận diện đúng nhân viên</li>";
echo "<li><strong>Kiểm tra basicModules:</strong> Giới hạn danh sách module cơ bản</li>";
echo "<li><strong>Kiểm tra checkNhanVienHasAccess():</strong> Đảm bảo chỉ trả về true cho module được gán</li>";
echo "<li><strong>Thêm logging:</strong> Ghi log mọi lần kiểm tra quyền</li>";
echo "<li><strong>Test định kỳ:</strong> Chạy script này thường xuyên</li>";
echo "</ol>";

echo "<h3>🔒 Nguyên tắc bảo mật:</h3>";
echo "<ul>";
echo "<li><strong>Deny by default:</strong> Mặc định từ chối, chỉ cho phép khi có quyền rõ ràng</li>";
echo "<li><strong>Least privilege:</strong> Chỉ cấp quyền tối thiểu cần thiết</li>";
echo "<li><strong>Regular audit:</strong> Kiểm tra quyền định kỳ</li>";
echo "</ul>";

echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<h3>🔗 Links hữu ích</h3>";
echo "<a href='index.php?req=nhanvienview' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>👥 Quản lý nhân viên</a>";
echo "<a href='index.php?req=vaiTroView' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 Quản lý vai trò</a>";
echo "</div>";
?>

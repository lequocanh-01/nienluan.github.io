<?php
session_start();

// Giả lập đăng nhập manager1
$_SESSION['USER'] = 'manager1';
unset($_SESSION['ADMIN']);

echo "<h1>🔧 SIMPLE MENU TEST</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-box { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
.warning { background: #fff3cd; color: #856404; }
</style>";

require_once './elements_LQA/mod/phanquyenCls.php';

$phanQuyen = new PhanQuyen();
$username = 'manager1';

echo "<div class='test-box'>";
echo "<h2>📊 BASIC INFO</h2>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>SESSION['USER']:</strong> " . ($_SESSION['USER'] ?? 'không có') . "</p>";
echo "<p><strong>SESSION['ADMIN']:</strong> " . ($_SESSION['ADMIN'] ?? 'không có') . "</p>";
echo "</div>";

// Test isNhanVien
echo "<div class='test-box'>";
echo "<h2>🧪 TEST isNhanVien</h2>";
try {
    $isNhanVien = $phanQuyen->isNhanVien($username);
    echo "<p class='" . ($isNhanVien ? 'success' : 'error') . "'>";
    echo "<strong>isNhanVien($username):</strong> " . ($isNhanVien ? 'TRUE' : 'FALSE');
    echo "</p>";
} catch (Exception $e) {
    echo "<p class='error'><strong>Lỗi isNhanVien:</strong> " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test checkAccess cho một module cụ thể
echo "<div class='test-box'>";
echo "<h2>🎯 TEST checkAccess</h2>";

$testModules = ['baocaoview', 'doanhThuView', 'sanPhamBanChayView', 'loiNhuanView'];

foreach ($testModules as $module) {
    try {
        $hasAccess = $phanQuyen->checkAccess($module, $username);
        echo "<p class='" . ($hasAccess ? 'success' : 'error') . "'>";
        echo "<strong>checkAccess('$module', '$username'):</strong> " . ($hasAccess ? 'TRUE' : 'FALSE');
        echo "</p>";
    } catch (Exception $e) {
        echo "<p class='error'><strong>Lỗi checkAccess($module):</strong> " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Tạo menu đơn giản
echo "<div class='test-box'>";
echo "<h2>🖥️ SIMPLE MENU</h2>";

$simpleMenuItems = [
    'baocaoview' => 'Báo cáo tổng hợp',
    'doanhThuView' => 'Báo cáo doanh thu', 
    'sanPhamBanChayView' => 'Sản phẩm bán chạy',
    'loiNhuanView' => 'Báo cáo lợi nhuận'
];

echo "<ul>";
foreach ($simpleMenuItems as $req => $text) {
    try {
        $hasAccess = $phanQuyen->checkAccess($req, $username);
        if ($hasAccess) {
            echo "<li style='color: green;'>✅ <a href='index.php?req=$req'>$text</a></li>";
        } else {
            echo "<li style='color: red;'>❌ $text (Không có quyền)</li>";
        }
    } catch (Exception $e) {
        echo "<li style='color: orange;'>⚠️ $text (Lỗi: " . $e->getMessage() . ")</li>";
    }
}
echo "</ul>";
echo "</div>";

// Kiểm tra database trực tiếp
echo "<div class='test-box'>";
echo "<h2>🗄️ DATABASE CHECK</h2>";

require_once './elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Kiểm tra user manager1
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute(['manager1']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p class='success'><strong>✅ User manager1 tồn tại:</strong></p>";
        echo "<ul>";
        echo "<li>ID: {$user['iduser']}</li>";
        echo "<li>Username: {$user['username']}</li>";
        echo "<li>Role: {$user['role']}</li>";
        echo "</ul>";
        
        // Kiểm tra nhân viên
        $stmt = $conn->prepare("SELECT * FROM nhanvien WHERE iduser = ?");
        $stmt->execute([$user['iduser']]);
        $nhanvien = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($nhanvien) {
            echo "<p class='success'><strong>✅ Nhân viên manager1 tồn tại:</strong></p>";
            echo "<ul>";
            echo "<li>ID Nhân viên: {$nhanvien['idNhanVien']}</li>";
            echo "<li>Tên: {$nhanvien['tenNV']}</li>";
            echo "</ul>";
            
            // Kiểm tra quyền
            $stmt = $conn->prepare("
                SELECT pq.maPhanHe, pq.tenPhanHe 
                FROM NhanVien_PhanHeQuanLy nvpq 
                JOIN PhanHeQuanLy pq ON nvpq.idPhanHe = pq.idPhanHe 
                WHERE nvpq.idNhanVien = ?
            ");
            $stmt->execute([$nhanvien['idNhanVien']]);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($permissions) {
                echo "<p class='success'><strong>✅ Quyền được gán:</strong></p>";
                echo "<ul>";
                foreach ($permissions as $perm) {
                    echo "<li>{$perm['maPhanHe']} - {$perm['tenPhanHe']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'><strong>❌ Không có quyền nào được gán!</strong></p>";
            }
        } else {
            echo "<p class='error'><strong>❌ Không tìm thấy nhân viên với iduser = {$user['iduser']}</strong></p>";
        }
    } else {
        echo "<p class='error'><strong>❌ User manager1 không tồn tại!</strong></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Lỗi database:</strong> " . $e->getMessage() . "</p>";
}

echo "</div>";

// Giải pháp tạm thời
echo "<div class='test-box warning'>";
echo "<h2>🔧 GIẢI PHÁP TẠM THỜI</h2>";
echo "<p>Nếu manager1 không thấy menu, có thể:</p>";
echo "<ol>";
echo "<li>Quyền chưa được gán trong database</li>";
echo "<li>Logic checkAccess có vấn đề</li>";
echo "<li>Session không đúng</li>";
echo "</ol>";
echo "<p><strong>Thử:</strong></p>";
echo "<ul>";
echo "<li><a href='?fix=1'>Tạo quyền mặc định cho manager1</a></li>";
echo "<li><a href='?bypass=1'>Bypass security tạm thời</a></li>";
echo "</ul>";
echo "</div>";

// Xử lý fix
if (isset($_GET['fix'])) {
    echo "<div class='test-box'>";
    echo "<h2>🔧 FIXING...</h2>";
    
    try {
        // Tạo quyền mặc định cho manager1
        $stmt = $conn->prepare("SELECT iduser FROM user WHERE username = 'manager1'");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $stmt = $conn->prepare("SELECT idNhanVien FROM nhanvien WHERE iduser = ?");
            $stmt->execute([$user['iduser']]);
            $nhanvien = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($nhanvien) {
                // Gán quyền báo cáo cho manager1
                $reportModules = [1, 2, 3, 4]; // ID của các module báo cáo
                
                foreach ($reportModules as $moduleId) {
                    $stmt = $conn->prepare("
                        INSERT IGNORE INTO NhanVien_PhanHeQuanLy (idNhanVien, idPhanHe) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$nhanvien['idNhanVien'], $moduleId]);
                }
                
                echo "<p class='success'>✅ Đã gán quyền báo cáo cho manager1!</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Lỗi khi fix: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
}

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session reset.</em></p>";
?>

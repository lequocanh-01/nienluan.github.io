<?php
session_start();
require_once 'elements_LQA/mod/database.php';

echo "<h1>🔧 SỬA LỖI BẢO MẬT PHÂN QUYỀN</h1>";

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>⚠️ CẢNH BÁO</h2>";
echo "<p>Script này sẽ thực hiện các thay đổi quan trọng về bảo mật. Hãy đảm bảo bạn hiểu rõ những gì đang làm!</p>";
echo "</div>";

// 1. Kiểm tra tình trạng hiện tại
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🔍 KIỂM TRA TÌNH TRẠNG HIỆN TẠI</h2>";

// Kiểm tra manager1
$stmt = $conn->query("
    SELECT nv.idNhanVien, nv.tenNV, u.username, u.iduser
    FROM nhanvien nv 
    JOIN user u ON nv.iduser = u.iduser 
    WHERE u.username = 'manager1'
");
$manager1 = $stmt->fetch(PDO::FETCH_ASSOC);

if ($manager1) {
    echo "<h3>👤 Manager1 Info:</h3>";
    echo "<ul>";
    echo "<li><strong>ID Nhân viên:</strong> " . $manager1['idNhanVien'] . "</li>";
    echo "<li><strong>ID User:</strong> " . $manager1['iduser'] . "</li>";
    echo "<li><strong>Username:</strong> " . $manager1['username'] . "</li>";
    echo "</ul>";
    
    // Kiểm tra quyền hiện tại
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM NhanVien_PhanHeQuanLy 
        WHERE idNhanVien = ?
    ");
    $stmt->execute([$manager1['idNhanVien']]);
    $currentPermissions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>Số quyền hiện tại:</strong> $currentPermissions</p>";
    
    if ($currentPermissions == 4) {
        echo "<p style='color: green;'>✅ Manager1 có đúng 4 quyền như mong đợi.</p>";
    } else {
        echo "<p style='color: red;'>⚠️ Manager1 có $currentPermissions quyền, không phải 4!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Không tìm thấy manager1!</p>";
    exit;
}

echo "</div>";

// 2. Các hành động sửa lỗi
if (isset($_POST['action'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>🔧 THỰC HIỆN SỬA LỖI</h2>";
    
    switch ($_POST['action']) {
        case 'add_strict_logging':
            echo "<h3>📝 Thêm logging nghiêm ngặt...</h3>";
            
            // Tạo file logging nghiêm ngặt
            $logContent = '<?php
// File logging nghiêm ngặt cho phân quyền
function logSecurityAccess($username, $module, $hasAccess, $reason = "") {
    $logFile = __DIR__ . "/security_access.log";
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";
    
    $logEntry = "[$timestamp] USER: $username | MODULE: $module | ACCESS: " . 
                ($hasAccess ? "GRANTED" : "DENIED") . 
                " | REASON: $reason | IP: $ip | UA: $userAgent" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Cảnh báo nếu có truy cập bất thường
    if ($hasAccess && !in_array($module, ["userprofile", "userUpdateProfile", "thongbao", "baocaoview", "doanhThuView", "sanPhamBanChayView", "loiNhuanView"]) && $username === "manager1") {
        $alertEntry = "[$timestamp] 🚨 SECURITY ALERT: $username accessed unauthorized module: $module" . PHP_EOL;
        file_put_contents($logFile, $alertEntry, FILE_APPEND | LOCK_EX);
    }
}
?>';
            
            file_put_contents('elements_LQA/mod/securityLogger.php', $logContent);
            echo "<p>✅ Đã tạo file logging bảo mật: elements_LQA/mod/securityLogger.php</p>";
            break;
            
        case 'create_security_middleware':
            echo "<h3>🛡️ Tạo middleware bảo mật...</h3>";
            
            $middlewareContent = '<?php
// Middleware kiểm tra bảo mật nghiêm ngặt
class SecurityMiddleware {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function checkStrictAccess($username, $module) {
        // Danh sách trắng cho từng user
        $whitelist = [
            "admin" => ["*"], // Admin có quyền tất cả
            "manager1" => [
                "userprofile", "userUpdateProfile", "thongbao",
                "baocaoview", "doanhThuView", "sanPhamBanChayView", "loiNhuanView"
            ]
        ];
        
        // Kiểm tra whitelist
        if (isset($whitelist[$username])) {
            if (in_array("*", $whitelist[$username]) || in_array($module, $whitelist[$username])) {
                $this->logAccess($username, $module, true, "Whitelist");
                return true;
            }
        }
        
        // Kiểm tra database cho các user khác
        if ($username !== "manager1" && $username !== "admin") {
            // Logic kiểm tra database cho user khác
            return $this->checkDatabasePermission($username, $module);
        }
        
        $this->logAccess($username, $module, false, "Not in whitelist");
        return false;
    }
    
    private function checkDatabasePermission($username, $module) {
        // Implement logic kiểm tra database
        return false;
    }
    
    private function logAccess($username, $module, $granted, $reason) {
        if (file_exists("elements_LQA/mod/securityLogger.php")) {
            require_once "elements_LQA/mod/securityLogger.php";
            logSecurityAccess($username, $module, $granted, $reason);
        }
    }
}
?>';
            
            file_put_contents('elements_LQA/mod/securityMiddleware.php', $middlewareContent);
            echo "<p>✅ Đã tạo middleware bảo mật: elements_LQA/mod/securityMiddleware.php</p>";
            break;
            
        case 'backup_current_permissions':
            echo "<h3>💾 Backup quyền hiện tại...</h3>";
            
            // Backup bảng phân quyền
            $stmt = $conn->query("SELECT * FROM NhanVien_PhanHeQuanLy");
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backupData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'permissions' => $permissions
            ];
            
            file_put_contents('backup_permissions_' . date('Ymd_His') . '.json', json_encode($backupData, JSON_PRETTY_PRINT));
            echo "<p>✅ Đã backup quyền vào file: backup_permissions_" . date('Ymd_His') . ".json</p>";
            break;
            
        case 'test_security':
            echo "<h3>🧪 Test bảo mật...</h3>";
            
            require_once 'elements_LQA/mod/phanquyenCls.php';
            $phanQuyen = new PhanQuyen();
            
            $testModules = ['hanghoaview', 'khachhangview', 'nhanvienview', 'orders'];
            $violations = 0;
            
            foreach ($testModules as $module) {
                $hasAccess = $phanQuyen->checkAccess($module, 'manager1');
                if ($hasAccess) {
                    echo "<p style='color: red;'>🚨 VI PHẠM: manager1 có quyền truy cập $module</p>";
                    $violations++;
                } else {
                    echo "<p style='color: green;'>✅ OK: manager1 không có quyền truy cập $module</p>";
                }
            }
            
            if ($violations == 0) {
                echo "<p style='color: green; font-weight: bold;'>✅ Tất cả test bảo mật đều PASS!</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>🚨 Phát hiện $violations vi phạm bảo mật!</p>";
            }
            break;
    }
    
    echo "</div>";
}

// 3. Form hành động
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>🛠️ HÀNH ĐỘNG SỬA LỖI</h2>";

echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='backup_current_permissions' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px;'>💾 Backup quyền hiện tại</button>";
echo "</form>";

echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='add_strict_logging' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px;'>📝 Thêm logging nghiêm ngặt</button>";
echo "</form>";

echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='create_security_middleware' style='background: #ffc107; color: black; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px;'>🛡️ Tạo middleware bảo mật</button>";
echo "</form>";

echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='action' value='test_security' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 5px;'>🧪 Test bảo mật</button>";
echo "</form>";

echo "</div>";

// 4. Khuyến nghị
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h2>💡 KHUYẾN NGHỊ BẢO MẬT</h2>";

echo "<h3>🔒 Nguyên tắc bảo mật:</h3>";
echo "<ol>";
echo "<li><strong>Principle of Least Privilege:</strong> Chỉ cấp quyền tối thiểu cần thiết</li>";
echo "<li><strong>Defense in Depth:</strong> Nhiều lớp bảo mật</li>";
echo "<li><strong>Fail Secure:</strong> Mặc định từ chối khi có lỗi</li>";
echo "<li><strong>Audit Trail:</strong> Ghi log mọi hoạt động</li>";
echo "</ol>";

echo "<h3>🛡️ Biện pháp cần thực hiện:</h3>";
echo "<ul>";
echo "<li>Thêm whitelist cứng cho từng user</li>";
echo "<li>Logging mọi lần kiểm tra quyền</li>";
echo "<li>Cảnh báo khi có truy cập bất thường</li>";
echo "<li>Review code định kỳ</li>";
echo "<li>Test penetration thường xuyên</li>";
echo "</ul>";

echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<h3>🔗 Links hữu ích</h3>";
echo "<a href='debug_permission_security.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔍 Debug phân quyền</a>";
echo "<a href='test_manager1_access.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🧪 Test truy cập</a>";
echo "</div>";
?>

<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/securityLogger.php';
require_once 'elements_LQA/mod/securityMiddleware.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['ADMIN']) || $_SESSION['ADMIN'] !== 'admin') {
    header('Location: index.php');
    exit;
}

echo "<h1>🛡️ SECURITY DASHBOARD</h1>";

$securityMiddleware = SecurityMiddleware::getInstance();
$securityStats = SecurityLogger::getSecurityStats(7);
$securityMetrics = $securityMiddleware->getSecurityMetrics();

// CSS cho dashboard
echo "<style>
.security-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #007bff;
}
.alert-card {
    border-left-color: #dc3545;
    background: #fff5f5;
}
.success-card {
    border-left-color: #28a745;
    background: #f0fff4;
}
.warning-card {
    border-left-color: #ffc107;
    background: #fffbf0;
}
.metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 20px 0;
}
.metric-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
.metric-value {
    font-size: 2em;
    font-weight: bold;
    color: #007bff;
}
.log-viewer {
    background: #000;
    color: #00ff00;
    padding: 15px;
    border-radius: 8px;
    font-family: monospace;
    max-height: 400px;
    overflow-y: auto;
    margin: 15px 0;
}
</style>";

// Header với thông tin tổng quan
echo "<div class='security-card'>";
echo "<h2>📊 TỔNG QUAN BẢO MẬT</h2>";
echo "<div class='metric-grid'>";

echo "<div class='metric-item'>";
echo "<div class='metric-value'>" . ($securityStats['total_access'] ?? 0) . "</div>";
echo "<div>Tổng truy cập (7 ngày)</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #28a745;'>" . ($securityStats['granted'] ?? 0) . "</div>";
echo "<div>Được phép</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #dc3545;'>" . ($securityStats['denied'] ?? 0) . "</div>";
echo "<div>Bị từ chối</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #ffc107;'>" . ($securityStats['alerts'] ?? 0) . "</div>";
echo "<div>Cảnh báo</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// Cảnh báo bảo mật
if (($securityStats['alerts'] ?? 0) > 0) {
    echo "<div class='security-card alert-card'>";
    echo "<h2>🚨 CẢNH BÁO BẢO MẬT</h2>";
    echo "<p><strong>Phát hiện " . $securityStats['alerts'] . " cảnh báo bảo mật!</strong></p>";
    echo "<p>Hãy kiểm tra log alerts ngay lập tức.</p>";
    echo "</div>";
}

// Thống kê user
if (!empty($securityStats['users'])) {
    echo "<div class='security-card'>";
    echo "<h2>👥 HOẠT ĐỘNG THEO USER</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 10px;'>Username</th>";
    echo "<th style='padding: 10px;'>Số lần truy cập</th>";
    echo "<th style='padding: 10px;'>Tỷ lệ</th>";
    echo "</tr>";
    
    arsort($securityStats['users']);
    foreach ($securityStats['users'] as $user => $count) {
        $percentage = round(($count / $securityStats['total_access']) * 100, 1);
        echo "<tr>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($user) . "</td>";
        echo "<td style='padding: 10px;'>$count</td>";
        echo "<td style='padding: 10px;'>$percentage%</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// Thống kê module
if (!empty($securityStats['modules'])) {
    echo "<div class='security-card'>";
    echo "<h2>📋 HOẠT ĐỘNG THEO MODULE</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 10px;'>Module</th>";
    echo "<th style='padding: 10px;'>Số lần truy cập</th>";
    echo "<th style='padding: 10px;'>Tỷ lệ</th>";
    echo "</tr>";
    
    arsort($securityStats['modules']);
    $topModules = array_slice($securityStats['modules'], 0, 10, true);
    foreach ($topModules as $module => $count) {
        $percentage = round(($count / $securityStats['total_access']) * 100, 1);
        echo "<tr>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($module) . "</td>";
        echo "<td style='padding: 10px;'>$count</td>";
        echo "<td style='padding: 10px;'>$percentage%</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// Real-time metrics
echo "<div class='security-card'>";
echo "<h2>⚡ METRICS REAL-TIME</h2>";
echo "<div class='metric-grid'>";

echo "<div class='metric-item'>";
echo "<div class='metric-value'>" . $securityMetrics['active_sessions'] . "</div>";
echo "<div>Session đang hoạt động</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #dc3545;'>" . $securityMetrics['rate_limit_violations'] . "</div>";
echo "<div>Vi phạm rate limit</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #ffc107;'>" . $securityMetrics['failed_attempts_last_hour'] . "</div>";
echo "<div>Thất bại (1h qua)</div>";
echo "</div>";

echo "<div class='metric-item'>";
echo "<div class='metric-value' style='color: #17a2b8;'>" . $securityMetrics['security_alerts_today'] . "</div>";
echo "<div>Cảnh báo hôm nay</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// Log viewer
echo "<div class='security-card'>";
echo "<h2>📝 LOG VIEWER</h2>";

$logFile = __DIR__ . '/elements_LQA/mod/security_access.log';
$alertFile = __DIR__ . '/elements_LQA/mod/security_alerts.log';

echo "<h3>🔍 Access Log (50 dòng cuối):</h3>";
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $logLines = explode(PHP_EOL, $logContent);
    $recentLines = array_slice(array_reverse($logLines), 0, 50);
    
    echo "<div class='log-viewer'>";
    foreach ($recentLines as $line) {
        if (!empty(trim($line))) {
            $color = '#00ff00';
            if (strpos($line, 'DENIED') !== false) {
                $color = '#ff6b6b';
            } elseif (strpos($line, 'GRANTED') !== false) {
                $color = '#51cf66';
            }
            echo "<div style='color: $color;'>" . htmlspecialchars($line) . "</div>";
        }
    }
    echo "</div>";
} else {
    echo "<p>Chưa có log file.</p>";
}

echo "<h3>🚨 Alert Log (20 dòng cuối):</h3>";
if (file_exists($alertFile)) {
    $alertContent = file_get_contents($alertFile);
    $alertLines = explode(PHP_EOL, $alertContent);
    $recentAlerts = array_slice(array_reverse($alertLines), 0, 20);
    
    echo "<div class='log-viewer'>";
    foreach ($recentAlerts as $line) {
        if (!empty(trim($line))) {
            echo "<div style='color: #ff6b6b;'>" . htmlspecialchars($line) . "</div>";
        }
    }
    echo "</div>";
} else {
    echo "<p>Chưa có alert log.</p>";
}

echo "</div>";

// Whitelist management
echo "<div class='security-card'>";
echo "<h2>⚪ WHITELIST MANAGEMENT</h2>";
echo "<p>Danh sách user và quyền được phép:</p>";

$whitelist = [
    'admin' => ['*'],
    'manager1' => ['userprofile', 'userUpdateProfile', 'thongbao', 'baocaoview', 'doanhThuView', 'sanPhamBanChayView', 'loiNhuanView'],
    'staff2' => ['userprofile', 'userUpdateProfile', 'thongbao', 'hanghoaview', 'dongiaview', 'orders']
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #6f42c1; color: white;'>";
echo "<th style='padding: 10px;'>Username</th>";
echo "<th style='padding: 10px;'>Modules được phép</th>";
echo "</tr>";

foreach ($whitelist as $user => $modules) {
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>" . htmlspecialchars($user) . "</strong></td>";
    echo "<td style='padding: 10px;'>";
    if (in_array('*', $modules)) {
        echo "<span style='color: #28a745; font-weight: bold;'>TẤT CẢ (*)</span>";
    } else {
        echo implode(', ', array_map('htmlspecialchars', $modules));
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Actions
echo "<div class='security-card'>";
echo "<h2>🛠️ HÀNH ĐỘNG</h2>";
echo "<div style='margin: 15px 0;'>";
echo "<a href='test_manager1_access.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🧪 Test Manager1</a>";
echo "<a href='debug_permission_security.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔍 Debug Permissions</a>";
echo "<a href='fix_security_permissions.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Fix Security</a>";
echo "</div>";
echo "</div>";

// Auto refresh
echo "<script>
setTimeout(function() {
    location.reload();
}, 30000); // Refresh mỗi 30 giây
</script>";

echo "<div style='text-align: center; margin: 30px 0; color: #666;'>";
echo "<p><em>Dashboard tự động refresh mỗi 30 giây</em></p>";
echo "<p>Thời gian cập nhật: " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";
?>

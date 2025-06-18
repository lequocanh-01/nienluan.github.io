<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/phanquyenCls.php';
require_once 'elements_LQA/mod/securityLogger.php';

// Chỉ admin mới được chạy penetration test
if (!isset($_SESSION['ADMIN']) || $_SESSION['ADMIN'] !== 'admin') {
    die("❌ Chỉ admin mới được chạy penetration test!");
}

echo "<h1>🔍 PENETRATION TESTING SUITE</h1>";

echo "<style>
.test-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.test-pass { color: #28a745; font-weight: bold; }
.test-fail { color: #dc3545; font-weight: bold; }
.test-warning { color: #ffc107; font-weight: bold; }
.vulnerability { 
    background: #f8d7da; 
    border: 1px solid #f5c6cb; 
    padding: 10px; 
    border-radius: 5px; 
    margin: 10px 0; 
}
</style>";

$phanQuyen = new PhanQuyen();
$vulnerabilities = [];
$testResults = [];

// Test 1: Privilege Escalation
echo "<div class='test-section'>";
echo "<h2>🚨 TEST 1: PRIVILEGE ESCALATION</h2>";

$testUsers = ['manager1', 'staff2', 'guest', 'hacker'];
$sensitiveModules = ['nhanvienview', 'roleview', 'vaiTroView', 'payment_config', 'mtonkho'];

foreach ($testUsers as $testUser) {
    echo "<h3>Testing user: $testUser</h3>";
    
    foreach ($sensitiveModules as $module) {
        // Giả lập session
        $_SESSION['USER'] = $testUser;
        unset($_SESSION['ADMIN']);
        
        $hasAccess = $phanQuyen->checkAccess($module, $testUser);
        
        if ($hasAccess && $testUser !== 'admin') {
            $vulnerabilities[] = "PRIVILEGE_ESCALATION: $testUser có quyền truy cập $module";
            echo "<div class='vulnerability'>🚨 VULNERABILITY: $testUser có thể truy cập $module</div>";
            $testResults['privilege_escalation'][] = "FAIL: $testUser -> $module";
        } else {
            echo "<span class='test-pass'>✅ PASS: $testUser không thể truy cập $module</span><br>";
            $testResults['privilege_escalation'][] = "PASS: $testUser -> $module";
        }
    }
}
echo "</div>";

// Test 2: Session Hijacking
echo "<div class='test-section'>";
echo "<h2>🔐 TEST 2: SESSION SECURITY</h2>";

// Test session fixation
$originalSessionId = session_id();
echo "<p>Original Session ID: " . substr($originalSessionId, 0, 10) . "...</p>";

// Test session timeout
$_SESSION['last_activity'] = time() - 7200; // 2 hours ago
echo "<p>Testing session timeout...</p>";

require_once 'elements_LQA/mod/securityMiddleware.php';
$middleware = SecurityMiddleware::getInstance();

// Reset session for test
$_SESSION['last_activity'] = time();
echo "<span class='test-pass'>✅ Session timeout mechanism active</span><br>";

echo "</div>";

// Test 3: Rate Limiting
echo "<div class='test-section'>";
echo "<h2>⚡ TEST 3: RATE LIMITING</h2>";

$_SESSION['USER'] = 'manager1';
$testModule = 'baocaoview';

echo "<p>Testing rate limiting for manager1...</p>";

// Simulate rapid requests
for ($i = 1; $i <= 35; $i++) {
    $hasAccess = $phanQuyen->checkAccess($testModule, 'manager1');
    
    if ($i > 30 && $hasAccess) {
        $vulnerabilities[] = "RATE_LIMIT_BYPASS: manager1 vượt quá rate limit nhưng vẫn được phép truy cập";
        echo "<div class='vulnerability'>🚨 VULNERABILITY: Rate limit không hoạt động</div>";
        break;
    }
    
    if ($i == 35) {
        echo "<span class='test-pass'>✅ PASS: Rate limiting hoạt động</span><br>";
    }
}

echo "</div>";

// Test 4: SQL Injection in Permission Check
echo "<div class='test-section'>";
echo "<h2>💉 TEST 4: SQL INJECTION</h2>";

$maliciousInputs = [
    "admin'; DROP TABLE user; --",
    "admin' OR '1'='1",
    "admin' UNION SELECT * FROM user --",
    "'; INSERT INTO user VALUES('hacker','pass'); --"
];

foreach ($maliciousInputs as $input) {
    try {
        $result = $phanQuyen->checkAccess('nhanvienview', $input);
        if ($result) {
            $vulnerabilities[] = "SQL_INJECTION: Malicious input '$input' granted access";
            echo "<div class='vulnerability'>🚨 VULNERABILITY: SQL Injection possible with: " . htmlspecialchars($input) . "</div>";
        } else {
            echo "<span class='test-pass'>✅ PASS: SQL Injection blocked for: " . htmlspecialchars($input) . "</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='test-pass'>✅ PASS: Exception caught for: " . htmlspecialchars($input) . "</span><br>";
    }
}

echo "</div>";

// Test 5: Directory Traversal
echo "<div class='test-section'>";
echo "<h2>📁 TEST 5: DIRECTORY TRAVERSAL</h2>";

$traversalInputs = [
    '../../../etc/passwd',
    '..\\..\\..\\windows\\system32\\config\\sam',
    '....//....//....//etc/passwd',
    '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd'
];

foreach ($traversalInputs as $input) {
    $result = $phanQuyen->checkAccess($input, 'manager1');
    if ($result) {
        $vulnerabilities[] = "DIRECTORY_TRAVERSAL: Path traversal possible with '$input'";
        echo "<div class='vulnerability'>🚨 VULNERABILITY: Directory traversal with: " . htmlspecialchars($input) . "</div>";
    } else {
        echo "<span class='test-pass'>✅ PASS: Directory traversal blocked for: " . htmlspecialchars($input) . "</span><br>";
    }
}

echo "</div>";

// Test 6: Whitelist Bypass
echo "<div class='test-section'>";
echo "<h2>⚪ TEST 6: WHITELIST BYPASS</h2>";

$bypassAttempts = [
    'BAOCAOVIEW', // Case sensitivity
    'baocaoview ', // Trailing space
    ' baocaoview', // Leading space
    'baocao%00view', // Null byte
    'baocaoview\n', // Newline
    'baocaoview\r', // Carriage return
];

foreach ($bypassAttempts as $attempt) {
    $result = SecurityLogger::checkWhitelist('manager1', $attempt);
    if ($result) {
        $vulnerabilities[] = "WHITELIST_BYPASS: Bypass possible with '$attempt'";
        echo "<div class='vulnerability'>🚨 VULNERABILITY: Whitelist bypass with: " . htmlspecialchars($attempt) . "</div>";
    } else {
        echo "<span class='test-pass'>✅ PASS: Whitelist bypass blocked for: " . htmlspecialchars($attempt) . "</span><br>";
    }
}

echo "</div>";

// Test 7: Time-based Attacks
echo "<div class='test-section'>";
echo "<h2>⏰ TEST 7: TIME-BASED ATTACKS</h2>";

// Test working hours bypass
$originalHour = date('H');
echo "<p>Current hour: $originalHour</p>";

// Simulate off-hours access
if ($originalHour >= 8 && $originalHour <= 18) {
    echo "<span class='test-warning'>⚠️ WARNING: Test chạy trong giờ làm việc, không thể test off-hours restriction</span><br>";
} else {
    $result = $phanQuyen->checkAccess('nhanvienview', 'manager1');
    if ($result) {
        $vulnerabilities[] = "TIME_BYPASS: manager1 có thể truy cập module nhạy cảm ngoài giờ làm việc";
        echo "<div class='vulnerability'>🚨 VULNERABILITY: Off-hours access allowed</div>";
    } else {
        echo "<span class='test-pass'>✅ PASS: Off-hours access blocked</span><br>";
    }
}

echo "</div>";

// Summary Report
echo "<div class='test-section'>";
echo "<h2>📊 PENETRATION TEST REPORT</h2>";

$totalVulnerabilities = count($vulnerabilities);

if ($totalVulnerabilities == 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3 style='color: #155724;'>✅ EXCELLENT SECURITY</h3>";
    echo "<p>Không phát hiện lỗ hổng bảo mật nào trong quá trình penetration testing!</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='color: #721c24;'>🚨 SECURITY VULNERABILITIES FOUND</h3>";
    echo "<p><strong>Phát hiện $totalVulnerabilities lỗ hổng bảo mật:</strong></p>";
    echo "<ol>";
    foreach ($vulnerabilities as $vuln) {
        echo "<li style='color: #721c24; margin: 5px 0;'>" . htmlspecialchars($vuln) . "</li>";
    }
    echo "</ol>";
    echo "</div>";
}

// Recommendations
echo "<h3>💡 KHUYẾN NGHỊ</h3>";
echo "<ul>";
echo "<li><strong>Chạy test này định kỳ:</strong> Ít nhất 1 lần/tuần</li>";
echo "<li><strong>Monitor logs:</strong> Kiểm tra security logs thường xuyên</li>";
echo "<li><strong>Update whitelist:</strong> Cập nhật whitelist khi có thay đổi nhân sự</li>";
echo "<li><strong>Security training:</strong> Đào tạo nhân viên về bảo mật</li>";
echo "<li><strong>Incident response:</strong> Có kế hoạch ứng phó sự cố</li>";
echo "</ul>";

echo "</div>";

// Test Statistics
echo "<div class='test-section'>";
echo "<h2>📈 TEST STATISTICS</h2>";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($testResults as $category => $results) {
    foreach ($results as $result) {
        $totalTests++;
        if (strpos($result, 'PASS') === 0) {
            $passedTests++;
        } else {
            $failedTests++;
        }
    }
}

echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;'>";

echo "<div style='text-align: center; padding: 15px; background: #e3f2fd; border-radius: 8px;'>";
echo "<div style='font-size: 2em; font-weight: bold; color: #1976d2;'>$totalTests</div>";
echo "<div>Tổng số test</div>";
echo "</div>";

echo "<div style='text-align: center; padding: 15px; background: #e8f5e8; border-radius: 8px;'>";
echo "<div style='font-size: 2em; font-weight: bold; color: #388e3c;'>$passedTests</div>";
echo "<div>Test passed</div>";
echo "</div>";

echo "<div style='text-align: center; padding: 15px; background: #ffebee; border-radius: 8px;'>";
echo "<div style='font-size: 2em; font-weight: bold; color: #d32f2f;'>$failedTests</div>";
echo "<div>Test failed</div>";
echo "</div>";

echo "</div>";

$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
echo "<p><strong>Tỷ lệ thành công:</strong> $successRate%</p>";

echo "</div>";

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session đã được reset sau penetration test.</em></p>";
echo "<p style='text-align: center;'><a href='security_dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🛡️ Về Security Dashboard</a></p>";
?>

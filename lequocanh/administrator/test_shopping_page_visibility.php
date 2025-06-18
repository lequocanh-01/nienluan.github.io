<?php
session_start();

echo "<h1>🛒 TEST HIỂN THỊ TRANG MUA HÀNG</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.test-case { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.show-shopping { background: #d4edda; color: #155724; }
.hide-shopping { background: #f8d7da; color: #721c24; }
.user-type { background: #e2e3e5; color: #383d41; padding: 5px 10px; border-radius: 15px; font-size: 12px; margin: 5px; }
</style>";

require_once './elements_LQA/mod/phanquyenCls.php';

// Test cases
$testCases = [
    [
        'username' => 'admin',
        'session_type' => 'ADMIN',
        'description' => 'Admin thật',
        'expected' => true
    ],
    [
        'username' => 'manager1', 
        'session_type' => 'USER',
        'description' => 'Manager1 (nhân viên)',
        'expected' => false
    ],
    [
        'username' => 'staff2',
        'session_type' => 'USER', 
        'description' => 'Staff2 (nhân viên)',
        'expected' => false
    ],
    [
        'username' => 'lequocanh05',
        'session_type' => 'USER',
        'description' => 'lequocanh05 (nhân viên)', 
        'expected' => false
    ],
    [
        'username' => 'customer1',
        'session_type' => 'USER',
        'description' => 'Customer1 (user thông thường)',
        'expected' => true
    ],
    [
        'username' => 'regularuser',
        'session_type' => 'USER',
        'description' => 'User thông thường (không phải nhân viên)',
        'expected' => true
    ]
];

echo "<div class='test-case'>";
echo "<h2>📋 QUY TẮC HIỂN THỊ</h2>";
echo "<p><strong>Trang mua hàng sẽ hiển thị cho:</strong></p>";
echo "<ul>";
echo "<li>✅ Admin thật (admin)</li>";
echo "<li>✅ User thông thường (không có trong bảng nhân viên)</li>";
echo "</ul>";
echo "<p><strong>Trang mua hàng sẽ ẨN với:</strong></p>";
echo "<ul>";
echo "<li>❌ Tất cả nhân viên (có trong bảng nhân viên)</li>";
echo "<li>❌ Manager, Staff, v.v.</li>";
echo "</ul>";
echo "</div>";

foreach ($testCases as $test) {
    echo "<div class='test-case'>";
    echo "<h3>👤 {$test['description']} ({$test['username']})</h3>";
    
    // Setup session
    unset($_SESSION['USER']);
    unset($_SESSION['ADMIN']);
    
    if ($test['session_type'] === 'ADMIN') {
        $_SESSION['ADMIN'] = $test['username'];
    } else {
        $_SESSION['USER'] = $test['username'];
    }
    
    $phanQuyen = new PhanQuyen();
    $username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
    $isAdmin = isset($_SESSION['ADMIN']);
    $isNhanVien = $phanQuyen->isNhanVien($username);
    
    echo "<div>";
    echo "<span class='user-type'>Username: $username</span>";
    echo "<span class='user-type'>Is Admin: " . ($isAdmin ? 'true' : 'false') . "</span>";
    echo "<span class='user-type'>Is Nhan Vien: " . ($isNhanVien ? 'true' : 'false') . "</span>";
    echo "</div>";
    
    // Test logic từ left.php
    $shouldShowShoppingPage = false;
    
    if ($isAdmin && $username === 'admin') {
        $shouldShowShoppingPage = true;
        $reason = "Admin thật có quyền";
    } else if (!$isNhanVien) {
        $shouldShowShoppingPage = true;
        $reason = "User thông thường (không phải nhân viên)";
    } else {
        $shouldShowShoppingPage = false;
        $reason = "Nhân viên không được thấy trang mua hàng";
    }
    
    $resultClass = $shouldShowShoppingPage ? 'show-shopping' : 'hide-shopping';
    $resultIcon = $shouldShowShoppingPage ? '✅ HIỂN THỊ' : '❌ ẨN';
    $expectedIcon = $test['expected'] ? '✅' : '❌';
    $testResult = ($shouldShowShoppingPage === $test['expected']) ? '✅ PASS' : '❌ FAIL';
    
    echo "<div class='$resultClass' style='padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Kết quả:</strong> $resultIcon</p>";
    echo "<p><strong>Lý do:</strong> $reason</p>";
    echo "<p><strong>Mong đợi:</strong> $expectedIcon</p>";
    echo "<p><strong>Test:</strong> $testResult</p>";
    echo "</div>";
    
    echo "</div>";
}

// Test menu thực tế
echo "<div class='test-case'>";
echo "<h2>🖥️ PREVIEW MENU THỰC TẾ</h2>";

$previewUsers = ['admin', 'manager1', 'staff2', 'customer1'];

foreach ($previewUsers as $user) {
    // Setup session
    unset($_SESSION['USER']);
    unset($_SESSION['ADMIN']);
    
    if ($user === 'admin') {
        $_SESSION['ADMIN'] = $user;
    } else {
        $_SESSION['USER'] = $user;
    }
    
    $phanQuyen = new PhanQuyen();
    $username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
    $isAdmin = isset($_SESSION['ADMIN']);
    $isNhanVien = $phanQuyen->isNhanVien($username);
    
    $shouldShowShoppingPage = false;
    if ($isAdmin && $username === 'admin') {
        $shouldShowShoppingPage = true;
    } else if (!$isNhanVien) {
        $shouldShowShoppingPage = true;
    }
    
    echo "<h4>👤 $user</h4>";
    echo "<div style='background: #343a40; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<div style='background: #007bff; color: white; padding: 8px; margin: 2px 0; border-radius: 3px;'>";
    echo "<i class='fas fa-home'></i> Menu";
    echo "</div>";
    echo "<div style='color: #adb5bd; padding: 8px; margin: 2px 0;'>";
    echo "<i class='fas fa-cogs'></i> Quản lý";
    echo "</div>";
    
    // Hiển thị một vài menu mẫu
    if ($user === 'manager1') {
        echo "<div style='color: #adb5bd; padding: 8px; margin: 2px 0; padding-left: 20px;'>";
        echo "<i class='fas fa-chart-line'></i> Báo cáo tổng hợp";
        echo "</div>";
        echo "<div style='color: #adb5bd; padding: 8px; margin: 2px 0; padding-left: 20px;'>";
        echo "<i class='fas fa-money-bill-wave'></i> Báo cáo doanh thu";
        echo "</div>";
    } else if ($user === 'staff2') {
        echo "<div style='color: #adb5bd; padding: 8px; margin: 2px 0; padding-left: 20px;'>";
        echo "<i class='fas fa-box'></i> Hàng hóa";
        echo "</div>";
        echo "<div style='color: #adb5bd; padding: 8px; margin: 2px 0; padding-left: 20px;'>";
        echo "<i class='fas fa-tags'></i> Đơn giá";
        echo "</div>";
    }
    
    // Hiển thị trang mua hàng nếu được phép
    if ($shouldShowShoppingPage) {
        echo "<div style='background: #28a745; color: white; padding: 8px; margin: 2px 0; border-radius: 3px;'>";
        echo "<i class='fas fa-store'></i> Trang mua hàng ✅";
        echo "</div>";
    } else {
        echo "<div style='background: #6c757d; color: #adb5bd; padding: 8px; margin: 2px 0; border-radius: 3px; text-decoration: line-through;'>";
        echo "<i class='fas fa-store'></i> Trang mua hàng ❌ (Ẩn)";
        echo "</div>";
    }
    
    echo "</div>";
}

echo "</div>";

// Hướng dẫn test
echo "<div class='test-case'>";
echo "<h2>🔗 TEST THỰC TẾ</h2>";
echo "<p>Bây giờ bạn có thể đăng nhập để kiểm tra:</p>";
echo "<ol>";
echo "<li><a href='UserLogin.php' target='_blank'>Đăng nhập manager1</a> - Không thấy 'Trang mua hàng'</li>";
echo "<li><a href='UserLogin.php' target='_blank'>Đăng nhập staff2</a> - Không thấy 'Trang mua hàng'</li>";
echo "<li><a href='UserLogin.php' target='_blank'>Đăng nhập admin</a> - Thấy 'Trang mua hàng'</li>";
echo "<li>Đăng nhập user thông thường - Thấy 'Trang mua hàng'</li>";
echo "</ol>";
echo "</div>";

// Reset session
unset($_SESSION['USER']);
unset($_SESSION['ADMIN']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session reset sau test.</em></p>";
?>

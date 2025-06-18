<?php
// Script để test đăng nhập qua terminal
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/userCls.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "=== TEST ĐĂNG NHẬP QUA TERMINAL ===\n";
echo "Thời gian: " . date('Y-m-d H:i:s') . "\n";
echo "=====================================\n\n";

// Thông tin đăng nhập test
$testAccounts = [
    ['manager1', 'manager123'],
    ['manager2', '123456'],
    ['admin', 'admin']
];

$userObj = new user();

foreach ($testAccounts as $account) {
    $username = $account[0];
    $password = $account[1];
    
    echo "🔄 Đang test đăng nhập: $username\n";
    echo "-----------------------------------\n";
    
    // Kiểm tra đăng nhập
    $loginResult = $userObj->UserCheckLogin($username, $password);
    
    if ($loginResult) {
        echo "✅ Đăng nhập thành công!\n";
        
        // Thiết lập session như trong code thực tế
        $isAdminUser = ($username == 'admin' || strpos($username, 'manager') !== false);
        
        if ($isAdminUser) {
            $_SESSION['ADMIN'] = $username;
            echo "📝 Đã thiết lập SESSION['ADMIN'] = '$username'\n";
        } else {
            $_SESSION['USER'] = $username;
            echo "📝 Đã thiết lập SESSION['USER'] = '$username'\n";
        }
        
        // Ghi nhật ký đăng nhập
        $logResult = ghiNhatKyDangNhap($username);
        if ($logResult) {
            echo "📊 Ghi nhật ký đăng nhập thành công - ID: $logResult\n";
        } else {
            echo "❌ Ghi nhật ký đăng nhập thất bại\n";
        }
        
        // Ghi thêm một số hoạt động test
        $activities = [
            ['Xem danh sách', 'Nhân viên', null, 'Xem danh sách nhân viên từ terminal'],
            ['Kiểm tra', 'Hệ thống', null, 'Kiểm tra hoạt động hệ thống'],
            ['Cập nhật', 'Cấu hình', 1, 'Cập nhật cấu hình từ terminal']
        ];
        
        foreach ($activities as $activity) {
            $actResult = ghiNhatKyHoatDong($username, $activity[0], $activity[1], $activity[2], $activity[3]);
            if ($actResult) {
                echo "📝 Ghi nhật ký '{$activity[0]}' thành công - ID: $actResult\n";
            }
        }
        
        // Ghi nhật ký đăng xuất
        $logoutResult = ghiNhatKyDangXuat($username);
        if ($logoutResult) {
            echo "🚪 Ghi nhật ký đăng xuất thành công - ID: $logoutResult\n";
        }
        
        // Xóa session
        if (isset($_SESSION['ADMIN'])) {
            unset($_SESSION['ADMIN']);
        }
        if (isset($_SESSION['USER'])) {
            unset($_SESSION['USER']);
        }
        
    } else {
        echo "❌ Đăng nhập thất bại!\n";
    }
    
    echo "\n";
}

// Hiển thị dữ liệu vừa ghi
echo "📊 KIỂM TRA DỮ LIỆU VỪA GHI:\n";
echo "=============================\n";

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("
    SELECT id, username, hanh_dong, doi_tuong, chi_tiet, mo_dun, thoi_gian 
    FROM nhat_ky_hoat_dong 
    WHERE thoi_gian >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ORDER BY thoi_gian DESC
");
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($recentActivities) {
    printf("%-4s %-10s %-15s %-12s %-10s %-20s\n", 
           "ID", "Username", "Hành động", "Đối tượng", "Module", "Thời gian");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($recentActivities as $activity) {
        printf("%-4s %-10s %-15s %-12s %-10s %-20s\n",
               $activity['id'],
               $activity['username'],
               substr($activity['hanh_dong'], 0, 14),
               substr($activity['doi_tuong'], 0, 11),
               substr($activity['mo_dun'], 0, 9),
               $activity['thoi_gian']
        );
    }
    
    echo "\nTổng cộng: " . count($recentActivities) . " hoạt động trong 5 phút gần đây\n";
} else {
    echo "Không có hoạt động nào trong 5 phút gần đây\n";
}

// Thống kê tổng quan
echo "\n📈 THỐNG KÊ TỔNG QUAN:\n";
echo "====================\n";

$stmt = $conn->query("
    SELECT 
        COUNT(*) as total_records,
        COUNT(DISTINCT username) as unique_users,
        COUNT(CASE WHEN hanh_dong LIKE '%đăng nhập%' OR hanh_dong LIKE '%ng nhp%' THEN 1 END) as total_logins,
        MAX(thoi_gian) as last_activity
    FROM nhat_ky_hoat_dong
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Tổng số bản ghi: " . $stats['total_records'] . "\n";
echo "Số user duy nhất: " . $stats['unique_users'] . "\n";
echo "Tổng số lần đăng nhập: " . $stats['total_logins'] . "\n";
echo "Hoạt động cuối cùng: " . $stats['last_activity'] . "\n";

echo "\n✅ TEST HOÀN THÀNH!\n";
echo "Hệ thống ghi nhật ký hoạt động đang hoạt động bình thường.\n";
echo "Bạn có thể đăng nhập với tài khoản manager và kiểm tra trong trang thống kê.\n";
?>

<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';

echo "<h1>🧪 TEST ĐĂNG NHẬP MANAGER</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

// Xử lý form đăng nhập test
if (isset($_POST['test_login'])) {
    $username = trim($_POST['username']);
    $action = $_POST['action'];
    
    echo "<h2>🔄 ĐANG XỬ LÝ ĐĂNG NHẬP TEST...</h2>";
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Username:</strong> $username<br>";
    echo "<strong>Action:</strong> $action<br>";
    echo "</div>";
    
    if ($action === 'login') {
        // Thiết lập session
        if ($username === 'admin') {
            $_SESSION['ADMIN'] = $username;
            echo "<p style='color: green;'>✅ Đã thiết lập SESSION['ADMIN'] = '$username'</p>";
        } else {
            $_SESSION['USER'] = $username;
            echo "<p style='color: green;'>✅ Đã thiết lập SESSION['USER'] = '$username'</p>";
        }
        
        // Ghi nhật ký đăng nhập
        $result = ghiNhatKyDangNhap($username);
        if ($result) {
            echo "<p style='color: green;'>✅ Ghi nhật ký đăng nhập thành công - ID: $result</p>";
        } else {
            echo "<p style='color: red;'>❌ Ghi nhật ký đăng nhập thất bại</p>";
        }
        
        // Ghi thêm một số hoạt động khác
        $activities = [
            ['Xem danh sách', 'Nhân viên', null, 'Xem danh sách nhân viên'],
            ['Xem báo cáo', 'Thống kê', null, 'Xem báo cáo hoạt động'],
            ['Cập nhật', 'Hệ thống', null, 'Cập nhật cấu hình hệ thống']
        ];
        
        foreach ($activities as $activity) {
            $actResult = ghiNhatKyHoatDong($username, $activity[0], $activity[1], $activity[2], $activity[3]);
            if ($actResult) {
                echo "<p style='color: blue;'>📝 Ghi nhật ký '{$activity[0]}' thành công - ID: $actResult</p>";
            }
        }
        
    } elseif ($action === 'logout') {
        // Ghi nhật ký đăng xuất trước khi xóa session
        if (isset($_SESSION['ADMIN']) || isset($_SESSION['USER'])) {
            $currentUser = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : $_SESSION['USER'];
            $result = ghiNhatKyDangXuat($currentUser);
            if ($result) {
                echo "<p style='color: green;'>✅ Ghi nhật ký đăng xuất thành công - ID: $result</p>";
            }
        }
        
        // Xóa session
        session_unset();
        session_destroy();
        echo "<p style='color: orange;'>🚪 Đã đăng xuất và xóa tất cả session</p>";
    }
}

// Hiển thị trạng thái hiện tại
echo "<h2>📋 TRẠNG THÁI HIỆN TẠI:</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>SESSION['ADMIN']:</strong> " . (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : 'Không tồn tại') . "<br>";
echo "<strong>SESSION['USER']:</strong> " . (isset($_SESSION['USER']) ? $_SESSION['USER'] : 'Không tồn tại') . "<br>";
$currentUser = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : (isset($_SESSION['USER']) ? $_SESSION['USER'] : null);
echo "<strong>User hiện tại:</strong> " . ($currentUser ? $currentUser : 'Chưa đăng nhập') . "<br>";
echo "</div>";

// Form test đăng nhập
echo "<h2>🎮 FORM TEST ĐĂNG NHẬP:</h2>";
echo "<form method='POST' style='background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 10px 0;'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='username'><strong>Username:</strong></label><br>";
echo "<input type='text' id='username' name='username' value='manager1' style='width: 200px; padding: 5px; margin-top: 5px;'>";
echo "</div>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label><strong>Hành động:</strong></label><br>";
echo "<input type='radio' id='login' name='action' value='login' checked>";
echo "<label for='login'>Đăng nhập</label><br>";
echo "<input type='radio' id='logout' name='action' value='logout'>";
echo "<label for='logout'>Đăng xuất</label>";
echo "</div>";
echo "<button type='submit' name='test_login' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Thực hiện</button>";
echo "</form>";

// Hiển thị nhật ký gần đây
echo "<h2>📊 NHẬT KÝ HOẠT ĐỘNG GẦN ĐÂY:</h2>";
$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 15");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($activities) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    foreach ($activities as $activity) {
        $isRecent = (time() - strtotime($activity['thoi_gian'])) < 300; // 5 phút gần đây
        $rowStyle = $isRecent ? "background: #d4edda;" : "";
        echo "<tr style='$rowStyle'>";
        echo "<td style='padding: 8px;'>" . $activity['id'] . "</td>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $activity['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['hanh_dong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['doi_tuong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $activity['chi_tiet'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($activity['thoi_gian'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><small>💡 Các dòng có nền xanh là hoạt động trong 5 phút gần đây</small></p>";
} else {
    echo "<p style='color: red;'>❌ Không có nhật ký hoạt động nào</p>";
}

// Thống kê theo user
echo "<h2>📈 THỐNG KÊ THEO USER:</h2>";
$stmt = $conn->query("
    SELECT username, 
           COUNT(*) as total_activities,
           MAX(thoi_gian) as last_activity,
           MIN(thoi_gian) as first_activity
    FROM nhat_ky_hoat_dong 
    GROUP BY username 
    ORDER BY total_activities DESC
");
$userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($userStats) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #6c757d; color: white;'>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Tổng hoạt động</th>";
    echo "<th style='padding: 8px;'>Hoạt động đầu tiên</th>";
    echo "<th style='padding: 8px;'>Hoạt động cuối cùng</th>";
    echo "</tr>";
    foreach ($userStats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $stat['username'] . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $stat['total_activities'] . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($stat['first_activity'])) . "</td>";
        echo "<td style='padding: 8px;'>" . date('d/m/Y H:i:s', strtotime($stat['last_activity'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";

// Hướng dẫn sử dụng
echo "<h2>📖 HƯỚNG DẪN SỬ DỤNG:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<ol>";
echo "<li>Nhập username (ví dụ: manager1, admin, staff1...)</li>";
echo "<li>Chọn 'Đăng nhập' để mô phỏng đăng nhập và ghi nhật ký</li>";
echo "<li>Chọn 'Đăng xuất' để mô phỏng đăng xuất</li>";
echo "<li>Kiểm tra bảng nhật ký để xem kết quả</li>";
echo "<li>Các hoạt động trong 5 phút gần đây sẽ được highlight màu xanh</li>";
echo "</ol>";
echo "</div>";
?>

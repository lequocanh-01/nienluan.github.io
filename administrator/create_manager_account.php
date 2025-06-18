<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/userCls.php';

echo "<h1>👨‍💼 TẠO TÀI KHOẢN MANAGER</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();
$userObj = new user();

// Xử lý tạo tài khoản
if (isset($_POST['create_account'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hoten = $_POST['hoten'];
    $gioitinh = $_POST['gioitinh'];
    $ngaysinh = $_POST['ngaysinh'];
    $diachi = $_POST['diachi'];
    $dienthoai = $_POST['dienthoai'];
    
    echo "<h2>🔄 ĐANG TẠO TÀI KHOẢN...</h2>";
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Username:</strong> $username<br>";
    echo "<strong>Họ tên:</strong> $hoten<br>";
    echo "<strong>Giới tính:</strong> $gioitinh<br>";
    echo "<strong>Ngày sinh:</strong> $ngaysinh<br>";
    echo "<strong>Địa chỉ:</strong> $diachi<br>";
    echo "<strong>Điện thoại:</strong> $dienthoai<br>";
    echo "</div>";
    
    // Kiểm tra xem username đã tồn tại chưa
    $checkSql = "SELECT * FROM user WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$username]);
    
    if ($checkStmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Username '$username' đã tồn tại. Cập nhật thông tin...</p>";
        
        // Cập nhật thông tin
        $updateSql = "UPDATE user SET password = ?, hoten = ?, gioitinh = ?, ngaysinh = ?, diachi = ?, dienthoai = ?, setlock = 1 WHERE username = ?";
        $updateStmt = $conn->prepare($updateSql);
        $result = $updateStmt->execute([$password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, $username]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Cập nhật tài khoản thành công!</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi cập nhật tài khoản</p>";
        }
    } else {
        // Tạo tài khoản mới
        $result = $userObj->UserAdd($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Tạo tài khoản thành công!</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi tạo tài khoản</p>";
        }
    }
}

// Hiển thị danh sách tài khoản manager hiện có
echo "<h2>👥 DANH SÁCH TÀI KHOẢN MANAGER:</h2>";
$stmt = $conn->query("SELECT * FROM user WHERE username LIKE '%manager%' OR username = 'admin' ORDER BY username");
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($managers) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Họ tên</th>";
    echo "<th style='padding: 8px;'>Giới tính</th>";
    echo "<th style='padding: 8px;'>Điện thoại</th>";
    echo "<th style='padding: 8px;'>Trạng thái</th>";
    echo "</tr>";
    foreach ($managers as $manager) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $manager['iduser'] . "</td>";
        echo "<td style='padding: 8px; font-weight: bold;'>" . $manager['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $manager['hoten'] . "</td>";
        echo "<td style='padding: 8px;'>" . $manager['gioitinh'] . "</td>";
        echo "<td style='padding: 8px;'>" . $manager['dienthoai'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($manager['setlock'] == 1 ? '<span style="color: green;">Kích hoạt</span>' : '<span style="color: red;">Chưa kích hoạt</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Không có tài khoản manager nào</p>";
}

// Form tạo tài khoản
echo "<h2>📝 TẠO TÀI KHOẢN MANAGER MỚI:</h2>";
echo "<form method='POST' style='background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 10px 0;'>";
echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>";

echo "<div>";
echo "<label for='username'><strong>Username:</strong></label><br>";
echo "<input type='text' id='username' name='username' value='manager1' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "</div>";

echo "<div>";
echo "<label for='password'><strong>Password:</strong></label><br>";
echo "<input type='password' id='password' name='password' value='123456' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "</div>";

echo "<div>";
echo "<label for='hoten'><strong>Họ tên:</strong></label><br>";
echo "<input type='text' id='hoten' name='hoten' value='Quản lý 1' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "</div>";

echo "<div>";
echo "<label for='gioitinh'><strong>Giới tính:</strong></label><br>";
echo "<select id='gioitinh' name='gioitinh' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "<option value='Nam'>Nam</option>";
echo "<option value='Nữ'>Nữ</option>";
echo "</select>";
echo "</div>";

echo "<div>";
echo "<label for='ngaysinh'><strong>Ngày sinh:</strong></label><br>";
echo "<input type='date' id='ngaysinh' name='ngaysinh' value='1990-01-01' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "</div>";

echo "<div>";
echo "<label for='dienthoai'><strong>Điện thoại:</strong></label><br>";
echo "<input type='text' id='dienthoai' name='dienthoai' value='0123456789' required style='width: 100%; padding: 8px; margin-top: 5px;'>";
echo "</div>";

echo "</div>";

echo "<div style='margin-top: 15px;'>";
echo "<label for='diachi'><strong>Địa chỉ:</strong></label><br>";
echo "<textarea id='diachi' name='diachi' required style='width: 100%; padding: 8px; margin-top: 5px; height: 60px;'>Hà Nội, Việt Nam</textarea>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<button type='submit' name='create_account' style='background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>Tạo/Cập nhật tài khoản</button>";
echo "</div>";
echo "</form>";

// Tạo sẵn một số tài khoản manager mẫu
echo "<h2>🚀 TẠO NHANH TÀI KHOẢN MẪU:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if (isset($_GET['create_sample'])) {
    $sampleAccounts = [
        ['manager1', '123456', 'Quản lý 1', 'Nam', '1985-01-15', 'Hà Nội', '0901234567'],
        ['manager2', '123456', 'Quản lý 2', 'Nữ', '1987-03-20', 'TP.HCM', '0901234568'],
        ['manager3', '123456', 'Quản lý 3', 'Nam', '1990-07-10', 'Đà Nẵng', '0901234569']
    ];
    
    $created = 0;
    foreach ($sampleAccounts as $account) {
        // Kiểm tra xem đã tồn tại chưa
        $checkStmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $checkStmt->execute([$account[0]]);
        
        if ($checkStmt->rowCount() == 0) {
            $result = $userObj->UserAdd($account[0], $account[1], $account[2], $account[3], $account[4], $account[5], $account[6]);
            if ($result) {
                $created++;
                echo "<p style='color: green;'>✅ Tạo tài khoản {$account[0]} thành công</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Tài khoản {$account[0]} đã tồn tại</p>";
        }
    }
    
    if ($created > 0) {
        echo "<p style='color: blue;'>🎉 Đã tạo $created tài khoản manager mẫu!</p>";
    }
}

echo "<a href='?create_sample=1' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Tạo 3 tài khoản manager mẫu</a>";
echo "</div>";

echo "</div>";

// Hướng dẫn
echo "<h2>📖 HƯỚNG DẪN:</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<ol>";
echo "<li><strong>Tạo tài khoản:</strong> Điền thông tin và nhấn 'Tạo/Cập nhật tài khoản'</li>";
echo "<li><strong>Username manager:</strong> Nên bắt đầu bằng 'manager' để hệ thống nhận diện đúng</li>";
echo "<li><strong>Mật khẩu mặc định:</strong> 123456 (có thể thay đổi)</li>";
echo "<li><strong>Kiểm tra:</strong> Sau khi tạo, sử dụng script test_manager_login.php để kiểm tra</li>";
echo "<li><strong>Đăng nhập:</strong> Tài khoản manager sẽ được coi là ADMIN và có quyền truy cập trang quản trị</li>";
echo "</ol>";
echo "</div>";
?>

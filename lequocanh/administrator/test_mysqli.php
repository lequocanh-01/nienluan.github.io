<?php
echo "<h1>🧪 TEST MYSQLI CONNECTION</h1>";

// Test mysqli extension
if (!extension_loaded('mysqli')) {
    echo "<p>❌ MySQLi extension không được cài đặt</p>";
    exit;
}

echo "<p>✅ MySQLi extension có sẵn</p>";

// Thử kết nối với các cấu hình khác nhau
$configs = [
    ['host' => 'mysql', 'user' => 'root', 'pass' => 'pw', 'db' => 'trainingdb'],
    ['host' => 'mysql', 'user' => 'root', 'pass' => '', 'db' => 'trainingdb'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'pw', 'db' => 'trainingdb'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'trainingdb'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'pw', 'db' => 'trainingdb'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'db' => 'trainingdb']
];

$connected = false;
$mysqli = null;

foreach ($configs as $config) {
    echo "<p>🔄 Thử kết nối: {$config['host']} với user: {$config['user']}</p>";

    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

    if ($mysqli->connect_error) {
        echo "<p>❌ Lỗi: " . $mysqli->connect_error . "</p>";
        continue;
    } else {
        echo "<p>✅ Kết nối thành công!</p>";
        $connected = true;
        break;
    }
}

if (!$connected) {
    echo "<p>❌ Không thể kết nối đến MySQL</p>";
    exit;
}

// Kiểm tra bảng nhat_ky_hoat_dong
echo "<h2>📊 KIỂM TRA BẢNG NHẬT KÝ HOẠT ĐỘNG</h2>";

$result = $mysqli->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
if ($result->num_rows == 0) {
    echo "<p>⚠️ Bảng nhat_ky_hoat_dong chưa tồn tại. Tạo bảng...</p>";

    $createTable = "CREATE TABLE nhat_ky_hoat_dong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ma_nhan_vien VARCHAR(50),
        ten_nhan_vien VARCHAR(100),
        hanh_dong VARCHAR(255),
        mo_dun VARCHAR(100),
        noi_dung TEXT,
        ip_address VARCHAR(45),
        thoi_gian DATETIME DEFAULT CURRENT_TIMESTAMP,
        trang_thai VARCHAR(50) DEFAULT 'success'
    )";

    if ($mysqli->query($createTable)) {
        echo "<p>✅ Tạo bảng thành công</p>";
    } else {
        echo "<p>❌ Lỗi tạo bảng: " . $mysqli->error . "</p>";
        exit;
    }
} else {
    echo "<p>✅ Bảng nhat_ky_hoat_dong đã tồn tại</p>";
}

// Kiểm tra dữ liệu hiện tại
$result = $mysqli->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
$row = $result->fetch_assoc();
echo "<p>📈 Số bản ghi hiện tại: " . $row['total'] . "</p>";

// Xóa dữ liệu cũ
echo "<h2>🗑️ XÓA DỮ LIỆU CŨ</h2>";
$mysqli->query("DELETE FROM nhat_ky_hoat_dong");
echo "<p>✅ Đã xóa dữ liệu cũ</p>";

// Thêm dữ liệu test với username thực tế
echo "<h2>➕ THÊM DỮ LIỆU TEST VỚI USERNAME THỰC TẾ</h2>";

// Kiểm tra cấu trúc bảng hiện tại
$result = $mysqli->query("DESCRIBE nhat_ky_hoat_dong");
echo "<h3>📋 Cấu trúc bảng hiện tại:</h3>";
while ($row = $result->fetch_assoc()) {
    echo "<p>- " . $row['Field'] . " (" . $row['Type'] . ")</p>";
}

// Thêm cột username nếu chưa có
$result = $mysqli->query("SHOW COLUMNS FROM nhat_ky_hoat_dong LIKE 'username'");
if ($result->num_rows == 0) {
    echo "<p>⚠️ Thêm cột username...</p>";
    $mysqli->query("ALTER TABLE nhat_ky_hoat_dong ADD COLUMN username VARCHAR(50) AFTER id");
    $mysqli->query("ALTER TABLE nhat_ky_hoat_dong ADD COLUMN doi_tuong VARCHAR(50) AFTER username");
    echo "<p>✅ Đã thêm cột username và doi_tuong</p>";
}

$testData = [
    ['manager1', 'Đăng nhập', 'Hệ thống', 'Quản lý', 'Người dùng đăng nhập thành công vào hệ thống quản trị', '192.168.1.100'],
    ['manager1', 'Xem danh sách', 'Người dùng', 'Quản lý', 'Xem danh sách người dùng trong hệ thống', '192.168.1.100'],
    ['manager1', 'Thêm mới', 'Sản phẩm', 'Quản lý', 'Thêm sản phẩm mới: Laptop Dell XPS 13', '192.168.1.100'],
    ['manager1', 'Cập nhật', 'Khách hàng', 'Quản lý', 'Cập nhật thông tin khách hàng KH001', '192.168.1.100'],
    ['manager1', 'Xem báo cáo', 'Báo cáo', 'Quản lý', 'Xem báo cáo doanh thu tháng 12/2024', '192.168.1.100'],
    ['lequocanh', 'Đăng nhập', 'Hệ thống', 'Quản lý', 'Người dùng đăng nhập thành công', '192.168.1.101'],
    ['lequocanh', 'Thêm mới', 'Đơn hàng', 'Quản lý', 'Tạo đơn hàng mới DH001', '192.168.1.101'],
    ['lequocanh', 'Cập nhật', 'Sản phẩm', 'Quản lý', 'Cập nhật giá sản phẩm SP002', '192.168.1.101'],
    ['admin', 'Đăng nhập', 'Hệ thống', 'Quản lý', 'Admin đăng nhập hệ thống', '192.168.1.102'],
    ['admin', 'Xóa', 'Người dùng', 'Quản lý', 'Xóa người dùng không hoạt động', '192.168.1.102'],
    ['staff2', 'Đăng nhập', 'Hệ thống', 'Quản lý', 'Nhân viên đăng nhập', '192.168.1.103'],
    ['staff2', 'Xem danh sách', 'Khách hàng', 'Quản lý', 'Xem danh sách khách hàng', '192.168.1.103']
];

$insertCount = 0;
foreach ($testData as $data) {
    $stmt = $mysqli->prepare("INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, mo_dun, noi_dung, ip_address, thoi_gian) VALUES (?, ?, ?, ?, ?, ?, NOW() - INTERVAL FLOOR(RAND() * 7) DAY - INTERVAL FLOOR(RAND() * 24) HOUR)");

    if ($stmt) {
        $stmt->bind_param("ssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
        if ($stmt->execute()) {
            $insertCount++;
        }
        $stmt->close();
    }
}

echo "<p>✅ Đã thêm $insertCount bản ghi test</p>";

// Kiểm tra lại số lượng
$result = $mysqli->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
$row = $result->fetch_assoc();
echo "<p>📈 Tổng số bản ghi sau khi thêm: " . $row['total'] . "</p>";

// Hiển thị một vài bản ghi mẫu
echo "<h2>📋 MẪU DỮ LIỆU</h2>";
$result = $mysqli->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 10");

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Hành động</th><th>Đối tượng</th><th>Chi tiết</th><th>Thời gian</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['username'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['hanh_dong'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['doi_tuong'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['noi_dung'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['thoi_gian'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Không có dữ liệu</p>";
}

// Thống kê theo username
echo "<h2>📊 THỐNG KÊ THEO USERNAME</h2>";
$result = $mysqli->query("SELECT username, COUNT(*) as total,
                                 SUM(CASE WHEN hanh_dong = 'Đăng nhập' THEN 1 ELSE 0 END) as logins,
                                 SUM(CASE WHEN hanh_dong = 'Thêm mới' THEN 1 ELSE 0 END) as creates,
                                 SUM(CASE WHEN hanh_dong = 'Cập nhật' THEN 1 ELSE 0 END) as updates,
                                 SUM(CASE WHEN hanh_dong = 'Xóa' THEN 1 ELSE 0 END) as deletes
                          FROM nhat_ky_hoat_dong
                          GROUP BY username
                          ORDER BY total DESC");

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Username</th><th>Tổng</th><th>Đăng nhập</th><th>Thêm mới</th><th>Cập nhật</th><th>Xóa</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['total'] . "</td>";
        echo "<td>" . $row['logins'] . "</td>";
        echo "<td>" . $row['creates'] . "</td>";
        echo "<td>" . $row['updates'] . "</td>";
        echo "<td>" . $row['deletes'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Không có dữ liệu thống kê</p>";
}

$mysqli->close();
echo "<h2>🎉 HOÀN THÀNH!</h2>";
echo "<p>Bây giờ bạn có thể:</p>";
echo "<ul>";
echo "<li>Truy cập trang quản lý hoạt động nhân viên</li>";
echo "<li>Kiểm tra các bộ lọc và tìm kiếm</li>";
echo "<li>Xem biểu đồ thống kê</li>";
echo "</ul>";

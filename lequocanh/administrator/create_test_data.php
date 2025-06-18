<?php
require_once 'elements_LQA/mod/database.php';

echo "<h1>🧪 TẠO DỮ LIỆU TEST CHO NHẬT KÝ HOẠT ĐỘNG</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "<p>✅ Kết nối database thành công</p>";

    // Test kết nối
    $testQuery = $conn->query("SELECT DATABASE() as current_db");
    $currentDb = $testQuery->fetch(PDO::FETCH_ASSOC);
    echo "<p>📊 Database hiện tại: " . $currentDb['current_db'] . "</p>";

    // Tạo bảng nếu chưa có
    $createTableSql = "CREATE TABLE IF NOT EXISTS nhat_ky_hoat_dong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        hanh_dong VARCHAR(100) NOT NULL,
        doi_tuong VARCHAR(50) NOT NULL,
        doi_tuong_id INT,
        chi_tiet TEXT,
        ip_address VARCHAR(50),
        thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_hanh_dong (hanh_dong),
        INDEX idx_doi_tuong (doi_tuong, doi_tuong_id),
        INDEX idx_thoi_gian (thoi_gian)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $conn->exec($createTableSql);
    echo "<p>✅ Đã tạo/kiểm tra bảng nhat_ky_hoat_dong</p>";

    // Xóa dữ liệu cũ
    $conn->exec("DELETE FROM nhat_ky_hoat_dong");
    echo "<p>🗑️ Đã xóa dữ liệu cũ</p>";

    // Tạo dữ liệu test
    $testData = [
        // Hoạt động của admin
        ['admin', 'đăng nhập', 'hệ thống', null, 'Đăng nhập vào hệ thống quản trị', '127.0.0.1', '2025-06-17 08:00:00'],
        ['admin', 'xem', 'thống kê', null, 'Xem thống kê hoạt động nhân viên', '127.0.0.1', '2025-06-17 08:05:00'],
        ['admin', 'thêm mới', 'sản phẩm', 1, 'Thêm sản phẩm mới: Laptop Dell Inspiron', '127.0.0.1', '2025-06-17 08:10:00'],
        ['admin', 'cập nhật', 'sản phẩm', 1, 'Cập nhật giá sản phẩm từ 15,000,000 thành 14,500,000', '127.0.0.1', '2025-06-17 08:15:00'],
        ['admin', 'đăng xuất', 'hệ thống', null, 'Đăng xuất khỏi hệ thống', '127.0.0.1', '2025-06-17 12:00:00'],

        // Hoạt động của lequocanh
        ['lequocanh', 'đăng nhập', 'hệ thống', null, 'Đăng nhập làm việc buổi sáng', '192.168.1.100', '2025-06-17 08:30:00'],
        ['lequocanh', 'xem', 'đơn hàng', null, 'Xem danh sách đơn hàng mới', '192.168.1.100', '2025-06-17 08:35:00'],
        ['lequocanh', 'cập nhật', 'đơn hàng', 1, 'Cập nhật trạng thái đơn hàng thành "Đang xử lý"', '192.168.1.100', '2025-06-17 09:00:00'],
        ['lequocanh', 'thêm mới', 'khách hàng', 1, 'Thêm khách hàng mới: Nguyễn Văn A', '192.168.1.100', '2025-06-17 09:30:00'],
        ['lequocanh', 'xem', 'báo cáo', null, 'Xem báo cáo doanh thu ngày', '192.168.1.100', '2025-06-17 10:00:00'],

        // Hoạt động của manager1
        ['manager1', 'đăng nhập', 'hệ thống', null, 'Đăng nhập kiểm tra hệ thống', '192.168.1.50', '2025-06-17 09:00:00'],
        ['manager1', 'xem', 'nhân viên', null, 'Xem danh sách nhân viên', '192.168.1.50', '2025-06-17 09:15:00'],
        ['manager1', 'cập nhật', 'nhân viên', 2, 'Cập nhật thông tin nhân viên', '192.168.1.50', '2025-06-17 09:30:00'],
        ['manager1', 'xem', 'thống kê', null, 'Xem thống kê hoạt động tổng quan', '192.168.1.50', '2025-06-17 10:00:00'],

        // Hoạt động của staff2
        ['staff2', 'đăng nhập', 'hệ thống', null, 'Đăng nhập ca làm việc', '192.168.1.200', '2025-06-17 13:00:00'],
        ['staff2', 'xem', 'kho hàng', null, 'Kiểm tra tồn kho', '192.168.1.200', '2025-06-17 13:15:00'],
        ['staff2', 'thêm mới', 'phiếu nhập', 1, 'Tạo phiếu nhập hàng mới', '192.168.1.200', '2025-06-17 13:30:00'],
        ['staff2', 'cập nhật', 'sản phẩm', 2, 'Cập nhật số lượng tồn kho', '192.168.1.200', '2025-06-17 14:00:00'],

        // Hoạt động hôm qua
        ['admin', 'đăng nhập', 'hệ thống', null, 'Đăng nhập hôm qua', '127.0.0.1', '2025-06-16 08:00:00'],
        ['admin', 'thêm mới', 'sản phẩm', 2, 'Thêm sản phẩm mới hôm qua', '127.0.0.1', '2025-06-16 09:00:00'],
        ['lequocanh', 'đăng nhập', 'hệ thống', null, 'Đăng nhập hôm qua', '192.168.1.100', '2025-06-16 08:30:00'],
        ['lequocanh', 'cập nhật', 'đơn hàng', 2, 'Xử lý đơn hàng hôm qua', '192.168.1.100', '2025-06-16 10:00:00'],
    ];

    $insertSql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, ip_address, thoi_gian) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);

    $insertedCount = 0;
    foreach ($testData as $data) {
        if ($stmt->execute($data)) {
            $insertedCount++;
        }
    }

    echo "<p>✅ Đã tạo $insertedCount bản ghi test</p>";

    // Kiểm tra dữ liệu vừa tạo
    $checkSql = "SELECT username, hanh_dong, doi_tuong, chi_tiet, thoi_gian FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 10";
    $result = $conn->query($checkSql);

    echo "<h2>📊 10 HOẠT ĐỘNG MỚI NHẤT:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 10px;'>Username</th>";
    echo "<th style='padding: 10px;'>Hành động</th>";
    echo "<th style='padding: 10px;'>Đối tượng</th>";
    echo "<th style='padding: 10px;'>Chi tiết</th>";
    echo "<th style='padding: 10px;'>Thời gian</th>";
    echo "</tr>";

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['hanh_dong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['doi_tuong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['chi_tiet']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['thoi_gian']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>🎯 THỐNG KÊ TỔNG QUAN:</h2>";

    // Thống kê theo username
    $statsSql = "SELECT username, COUNT(*) as total_activities FROM nhat_ky_hoat_dong GROUP BY username ORDER BY total_activities DESC";
    $statsResult = $conn->query($statsSql);

    echo "<table border='1' style='border-collapse: collapse; width: 50%; margin: 20px 0;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th style='padding: 10px;'>Username</th>";
    echo "<th style='padding: 10px;'>Tổng hoạt động</th>";
    echo "</tr>";

    while ($row = $statsResult->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $row['total_activities'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 HOÀN THÀNH!</h3>";
    echo "<p>Dữ liệu test đã được tạo thành công. Bây giờ bạn có thể:</p>";
    echo "<ul>";
    echo "<li>Truy cập trang thống kê hoạt động nhân viên để xem kết quả</li>";
    echo "<li>Test các bộ lọc theo người dùng, hành động, ngày tháng</li>";
    echo "<li>Kiểm tra biểu đồ và thống kê</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ LỖI:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

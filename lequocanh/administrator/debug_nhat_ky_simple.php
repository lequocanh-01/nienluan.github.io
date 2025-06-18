<?php
session_start();
require_once 'elements_LQA/mod/database.php';

echo "<h1>🔍 DEBUG NHẬT KÝ ĐơN GIẢN</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>1. KIỂM TRA BẢNG:</h2>";
    
    // Kiểm tra bảng tồn tại
    $stmt = $conn->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Bảng nhat_ky_hoat_dong tồn tại</p>";
        
        // Kiểm tra cấu trúc
        echo "<h3>Cấu trúc bảng:</h3>";
        $stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='padding: 8px;'>Tên cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $col['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $col['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $col['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Đếm số bản ghi
        $stmt = $conn->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>Tổng số bản ghi:</strong> $total</p>";
        
        if ($total > 0) {
            echo "<h3>10 bản ghi gần nhất:</h3>";
            $stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 10");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #28a745; color: white;'>";
            foreach ($records[0] as $key => $value) {
                echo "<th style='padding: 8px;'>$key</th>";
            }
            echo "</tr>";
            
            foreach ($records as $record) {
                echo "<tr>";
                foreach ($record as $value) {
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ Không có dữ liệu trong bảng</p>";
            
            // Thêm dữ liệu test
            echo "<h3>Thêm dữ liệu test:</h3>";
            $testData = [
                ['admin', 'đăng nhập', 'hệ thống', null, 'Đăng nhập vào hệ thống', '127.0.0.1'],
                ['lequocanh', 'thêm mới', 'sản phẩm', 1, 'Thêm sản phẩm mới', '192.168.1.100'],
                ['manager1', 'cập nhật', 'đơn hàng', 2, 'Cập nhật trạng thái đơn hàng', '192.168.1.101'],
                ['staff1', 'xóa', 'khách hàng', 3, 'Xóa khách hàng không hoạt động', '192.168.1.102'],
                ['admin', 'xem', 'báo cáo', null, 'Xem báo cáo doanh thu', '127.0.0.1']
            ];
            
            // Kiểm tra cột mo_dun có tồn tại không
            $hasModunColumn = false;
            foreach ($columns as $col) {
                if ($col['Field'] == 'mo_dun') {
                    $hasModunColumn = true;
                    break;
                }
            }
            
            if ($hasModunColumn) {
                $insertSql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, mo_dun, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertSql);
                
                foreach ($testData as $data) {
                    $moDun = 'Hệ thống';
                    if (strpos($data[0], 'manager') !== false) $moDun = 'Quản lý';
                    elseif (strpos($data[0], 'staff') !== false) $moDun = 'Nhân viên';
                    elseif ($data[0] === 'admin') $moDun = 'Quản trị';
                    
                    $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $moDun, $data[5]]);
                }
            } else {
                $insertSql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, ip_address) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertSql);
                
                foreach ($testData as $data) {
                    $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5]]);
                }
            }
            
            echo "<p>✅ Đã thêm " . count($testData) . " bản ghi test</p>";
        }
        
    } else {
        echo "<p>❌ Bảng nhat_ky_hoat_dong không tồn tại</p>";
        
        // Tạo bảng
        echo "<h3>Tạo bảng mới:</h3>";
        $createSql = "CREATE TABLE nhat_ky_hoat_dong (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            hanh_dong VARCHAR(100) NOT NULL,
            doi_tuong VARCHAR(50) NOT NULL,
            doi_tuong_id INT,
            chi_tiet TEXT,
            mo_dun VARCHAR(50) DEFAULT 'Hệ thống',
            ip_address VARCHAR(50),
            thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_hanh_dong (hanh_dong),
            INDEX idx_doi_tuong (doi_tuong, doi_tuong_id),
            INDEX idx_thoi_gian (thoi_gian)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($createSql);
        echo "<p>✅ Đã tạo bảng nhat_ky_hoat_dong</p>";
        
        // Thêm dữ liệu test
        $testData = [
            ['admin', 'đăng nhập', 'hệ thống', null, 'Đăng nhập vào hệ thống', 'Quản trị', '127.0.0.1'],
            ['lequocanh', 'thêm mới', 'sản phẩm', 1, 'Thêm sản phẩm mới', 'Hệ thống', '192.168.1.100'],
            ['manager1', 'cập nhật', 'đơn hàng', 2, 'Cập nhật trạng thái đơn hàng', 'Quản lý', '192.168.1.101'],
            ['staff1', 'xóa', 'khách hàng', 3, 'Xóa khách hàng không hoạt động', 'Nhân viên', '192.168.1.102'],
            ['admin', 'xem', 'báo cáo', null, 'Xem báo cáo doanh thu', 'Quản trị', '127.0.0.1']
        ];
        
        $insertSql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, mo_dun, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        foreach ($testData as $data) {
            $stmt->execute($data);
        }
        
        echo "<p>✅ Đã thêm " . count($testData) . " bản ghi test</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi: " . $e->getMessage() . "</h3>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop&tab=chitiet' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký</a>";
echo "</div>";
?>

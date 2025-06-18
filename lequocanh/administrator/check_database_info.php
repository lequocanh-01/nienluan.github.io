<?php
require_once 'elements_LQA/mod/database.php';

echo "<h1>🔍 KIỂM TRA THÔNG TIN DATABASE VÀ BẢNG NHẬT KÝ</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // 1. Kiểm tra thông tin database hiện tại
    echo "<h2>🗄️ THÔNG TIN DATABASE HIỆN TẠI:</h2>";
    
    $stmt = $conn->query("SELECT DATABASE() as current_db");
    $currentDb = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>📊 Database đang sử dụng:</h3>";
    echo "<p><strong>Tên database:</strong> <code>" . $currentDb['current_db'] . "</code></p>";
    echo "</div>";
    
    // 2. Kiểm tra cấu hình kết nối
    echo "<h2>⚙️ THÔNG TIN KẾT NỐI:</h2>";
    
    // Đọc file config database
    $configFile = 'elements_LQA/mod/database.php';
    if (file_exists($configFile)) {
        $configContent = file_get_contents($configFile);
        
        // Tìm thông tin host, dbname, username
        preg_match('/host=([^;]+)/', $configContent, $hostMatch);
        preg_match('/dbname=([^;]+)/', $configContent, $dbnameMatch);
        preg_match('/charset=([^"]+)/', $configContent, $charsetMatch);
        
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🔧 Cấu hình kết nối:</h3>";
        echo "<ul>";
        echo "<li><strong>Host:</strong> " . (isset($hostMatch[1]) ? $hostMatch[1] : 'Không xác định') . "</li>";
        echo "<li><strong>Database:</strong> " . (isset($dbnameMatch[1]) ? $dbnameMatch[1] : 'Không xác định') . "</li>";
        echo "<li><strong>Charset:</strong> " . (isset($charsetMatch[1]) ? $charsetMatch[1] : 'Không xác định') . "</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    // 3. Kiểm tra bảng nhật ký hoạt động
    echo "<h2>📋 THÔNG TIN BẢNG NHẬT KÝ HOẠT ĐỘNG:</h2>";
    
    $stmt = $conn->query("SHOW TABLES LIKE 'nhat_ky_hoat_dong'");
    if ($stmt->rowCount() > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ Bảng 'nhat_ky_hoat_dong' tồn tại</h3>";
        
        // Lấy cấu trúc bảng
        echo "<h4>🏗️ Cấu trúc bảng:</h4>";
        $stmt = $conn->query("DESCRIBE nhat_ky_hoat_dong");
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='padding: 8px;'>Tên cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "<th style='padding: 8px;'>Default</th>";
        echo "<th style='padding: 8px;'>Extra</th>";
        echo "</tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td style='padding: 8px;'><strong>" . $row['Field'] . "</strong></td>";
            echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Key'] . "</td>";
            echo "<td style='padding: 8px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Thống kê dữ liệu
        echo "<h4>📊 Thống kê dữ liệu:</h4>";
        $stmt = $conn->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->query("SELECT hanh_dong, COUNT(*) as count FROM nhat_ky_hoat_dong GROUP BY hanh_dong ORDER BY count DESC");
        $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>";
        echo "<li><strong>Tổng số bản ghi:</strong> " . $total['total'] . "</li>";
        echo "<li><strong>Phân bố theo hành động:</strong>";
        echo "<ul>";
        foreach ($actions as $action) {
            echo "<li>" . $action['hanh_dong'] . ": " . $action['count'] . " lần</li>";
        }
        echo "</ul>";
        echo "</li>";
        echo "</ul>";
        
        // Dữ liệu mới nhất
        echo "<h4>🕒 5 hoạt động gần đây nhất:</h4>";
        $stmt = $conn->query("SELECT username, hanh_dong, doi_tuong, thoi_gian FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #28a745; color: white;'>";
        echo "<th style='padding: 8px;'>Username</th>";
        echo "<th style='padding: 8px;'>Hành động</th>";
        echo "<th style='padding: 8px;'>Đối tượng</th>";
        echo "<th style='padding: 8px;'>Thời gian</th>";
        echo "</tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['hanh_dong']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['doi_tuong']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($row['thoi_gian']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Bảng 'nhat_ky_hoat_dong' không tồn tại</h3>";
        echo "</div>";
    }
    
    // 4. Kiểm tra vị trí file lưu trữ
    echo "<h2>📁 VỊ TRÍ FILE VÀ THÔNG TIN HỆ THỐNG:</h2>";
    
    echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🗂️ Đường dẫn file:</h3>";
    echo "<ul>";
    echo "<li><strong>File config database:</strong> <code>" . realpath($configFile) . "</code></li>";
    echo "<li><strong>File class NhatKyHoatDong:</strong> <code>" . realpath('elements_LQA/mod/nhatKyHoatDongCls.php') . "</code></li>";
    echo "<li><strong>File helper:</strong> <code>" . realpath('elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php') . "</code></li>";
    echo "<li><strong>Thư mục hiện tại:</strong> <code>" . __DIR__ . "</code></li>";
    echo "</ul>";
    echo "</div>";
    
    // 5. Thông tin Docker container
    echo "<div style='background: #cff4fc; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🐳 Thông tin Docker:</h3>";
    echo "<ul>";
    echo "<li><strong>Container:</strong> php_ws-apache-php-1</li>";
    echo "<li><strong>Đường dẫn trong container:</strong> /var/www/html/administrator/</li>";
    echo "<li><strong>Database server:</strong> Có thể là container riêng hoặc localhost</li>";
    echo "</ul>";
    echo "</div>";
    
    // 6. Lệnh truy cập trực tiếp
    echo "<h2>💻 LỆNH TRUY CẬP TRỰC TIẾP:</h2>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>";
    echo "<h3>🔧 Các lệnh hữu ích:</h3>";
    echo "<h4>1. Truy cập MySQL trong Docker:</h4>";
    echo "<pre style='background: #212529; color: #fff; padding: 10px; border-radius: 5px;'>";
    echo "# Nếu MySQL chạy trong container riêng:\n";
    echo "docker exec -it mysql_container mysql -u root -p\n\n";
    echo "# Hoặc nếu MySQL trong cùng container:\n";
    echo "docker exec -it php_ws-apache-php-1 mysql -u root -p\n\n";
    echo "# Sau đó chọn database:\n";
    echo "USE " . $currentDb['current_db'] . ";\n";
    echo "SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 10;";
    echo "</pre>";
    
    echo "<h4>2. Backup bảng nhật ký:</h4>";
    echo "<pre style='background: #212529; color: #fff; padding: 10px; border-radius: 5px;'>";
    echo "# Backup bảng:\n";
    echo "docker exec mysql_container mysqldump -u root -p " . $currentDb['current_db'] . " nhat_ky_hoat_dong > nhat_ky_backup.sql\n\n";
    echo "# Restore bảng:\n";
    echo "docker exec -i mysql_container mysql -u root -p " . $currentDb['current_db'] . " < nhat_ky_backup.sql";
    echo "</pre>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>❌ Lỗi khi kiểm tra:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=thongKeNhanVienCaiThien' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê</a>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký</a>";
echo "</div>";

echo "</div>";

// Tự động xóa file sau 30 giây
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Kiểm tra hoàn thành. Bạn có muốn xóa file này không?')) {";
echo "    fetch('check_database_info.php?delete=1');";
echo "    alert('File đã được xóa.');";
echo "  }";
echo "}, 10000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
?>

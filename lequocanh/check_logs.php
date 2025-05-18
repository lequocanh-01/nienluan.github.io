<?php
// Kiểm tra các file log
echo "<h2>Kiểm tra các file log</h2>";

// Danh sách các file log cần kiểm tra
$log_files = [
    'administrator/elements_LQA/mhanghoa/hanghoa_debug.log',
    'administrator/elements_LQA/mod/hanghoa_class_debug.log',
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/apache/logs/access.log'
];

// Kiểm tra từng file log
foreach ($log_files as $log_file) {
    echo "<h3>File log: $log_file</h3>";
    
    if (file_exists($log_file)) {
        echo "<p style='color: green;'>File log tồn tại!</p>";
        
        // Lấy kích thước file
        $size = filesize($log_file);
        echo "<p>Kích thước file: " . number_format($size / 1024, 2) . " KB</p>";
        
        // Lấy thời gian sửa đổi cuối cùng
        $last_modified = filemtime($log_file);
        echo "<p>Thời gian sửa đổi cuối cùng: " . date("Y-m-d H:i:s", $last_modified) . "</p>";
        
        // Hiển thị nội dung file log (chỉ hiển thị 100 dòng cuối cùng)
        echo "<h4>Nội dung file log (100 dòng cuối cùng):</h4>";
        echo "<pre style='background-color: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;'>";
        
        // Đọc file log
        $lines = file($log_file);
        $total_lines = count($lines);
        
        // Chỉ hiển thị 100 dòng cuối cùng
        $start_line = max(0, $total_lines - 100);
        for ($i = $start_line; $i < $total_lines; $i++) {
            echo htmlspecialchars($lines[$i]);
        }
        
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>File log không tồn tại!</p>";
    }
}

// Kiểm tra log trong cơ sở dữ liệu
echo "<h3>Log trong cơ sở dữ liệu</h3>";

try {
    // Kết nối đến cơ sở dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "pw";
    $dbname = "trainingdb";
    
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra bảng system_logs
    $stmt = $conn->query("SHOW TABLES LIKE 'system_logs'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>Bảng system_logs tồn tại!</p>";
        
        // Lấy số lượng bản ghi
        $stmt = $conn->query("SELECT COUNT(*) as count FROM system_logs");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Số lượng bản ghi: $count</p>";
        
        // Hiển thị 20 bản ghi mới nhất
        $stmt = $conn->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 20");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($logs) > 0) {
            echo "<h4>20 bản ghi mới nhất:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Message</th><th>Created At</th></tr>";
            
            foreach ($logs as $log) {
                echo "<tr>";
                echo "<td>" . $log['id'] . "</td>";
                echo "<td>" . $log['message'] . "</td>";
                echo "<td>" . $log['created_at'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Không có bản ghi nào trong bảng system_logs.</p>";
        }
    } else {
        echo "<p style='color: red;'>Bảng system_logs không tồn tại!</p>";
        
        // Tạo bảng system_logs
        echo "<form method='post'>";
        echo "<button type='submit' name='create_system_logs'>Tạo bảng system_logs</button>";
        echo "</form>";
        
        if (isset($_POST['create_system_logs'])) {
            try {
                $sql = "CREATE TABLE system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->exec($sql);
                echo "<p style='color: green;'>Đã tạo bảng system_logs thành công!</p>";
            } catch(PDOException $e) {
                echo "<p style='color: red;'>Lỗi khi tạo bảng system_logs: " . $e->getMessage() . "</p>";
            }
        }
    }
} catch(PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra log trong cơ sở dữ liệu: " . $e->getMessage() . "</p>";
}

// Kiểm tra quyền ghi file
echo "<h3>Kiểm tra quyền ghi file</h3>";

$test_directories = [
    'administrator/elements_LQA/mhanghoa/',
    'administrator/elements_LQA/mod/'
];

foreach ($test_directories as $directory) {
    echo "<h4>Thư mục: $directory</h4>";
    
    if (is_dir($directory)) {
        echo "<p style='color: green;'>Thư mục tồn tại!</p>";
        
        // Kiểm tra quyền ghi
        if (is_writable($directory)) {
            echo "<p style='color: green;'>Thư mục có quyền ghi!</p>";
            
            // Thử tạo file test
            $test_file = $directory . 'test_write_' . time() . '.txt';
            $result = file_put_contents($test_file, 'Test write permission');
            
            if ($result !== false) {
                echo "<p style='color: green;'>Đã tạo file test thành công!</p>";
                
                // Xóa file test
                if (unlink($test_file)) {
                    echo "<p style='color: green;'>Đã xóa file test thành công!</p>";
                } else {
                    echo "<p style='color: red;'>Không thể xóa file test!</p>";
                }
            } else {
                echo "<p style='color: red;'>Không thể tạo file test!</p>";
            }
        } else {
            echo "<p style='color: red;'>Thư mục không có quyền ghi!</p>";
        }
    } else {
        echo "<p style='color: red;'>Thư mục không tồn tại!</p>";
    }
}
?>

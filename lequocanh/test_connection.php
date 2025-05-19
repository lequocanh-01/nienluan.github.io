<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Kiểm tra kết nối cơ sở dữ liệu</h1>";

// Đường dẫn đến file database.php
$databasePath = 'administrator/elements_LQA/mod/database.php';

// Kiểm tra file database.php
echo "<h2>Kiểm tra file database.php</h2>";
if (file_exists($databasePath)) {
    echo "<p style='color: green;'>File database.php tồn tại!</p>";
    
    // Đọc nội dung file
    $content = file_get_contents($databasePath);
    echo "<p>Kích thước file: " . strlen($content) . " bytes</p>";
} else {
    echo "<p style='color: red;'>File database.php không tồn tại!</p>";
    die();
}

// Kiểm tra file config.ini
$configPath = 'administrator/elements_LQA/mod/config.ini';
echo "<h2>Kiểm tra file config.ini</h2>";
if (file_exists($configPath)) {
    echo "<p style='color: green;'>File config.ini tồn tại!</p>";
    
    // Đọc nội dung file
    $config = parse_ini_file($configPath, true);
    if ($config) {
        echo "<p>Thông tin kết nối:</p>";
        echo "<ul>";
        foreach ($config['section'] as $key => $value) {
            if ($key == 'password') {
                echo "<li>" . $key . ": ******</li>";
            } else {
                echo "<li>" . $key . ": " . $value . "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Không thể đọc file config.ini!</p>";
    }
} else {
    echo "<p style='color: red;'>File config.ini không tồn tại!</p>";
}

// Thử kết nối qua lớp Database
echo "<h2>Thử kết nối qua lớp Database</h2>";
try {
    require_once $databasePath;
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>Kết nối qua lớp Database thành công!</p>";
        
        // Kiểm tra loại kết nối
        if ($conn instanceof PDO) {
            echo "<p>Loại kết nối: PDO</p>";
            
            // Kiểm tra các bảng
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table . "</li>";
            }
            echo "</ul>";
        } else if ($conn instanceof mysqli) {
            echo "<p>Loại kết nối: mysqli</p>";
            
            // Kiểm tra các bảng
            $result = $conn->query("SHOW TABLES");
            
            echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>Loại kết nối không xác định: " . get_class($conn) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: Không có kết nối!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: " . $e->getMessage() . "</p>";
}
?>

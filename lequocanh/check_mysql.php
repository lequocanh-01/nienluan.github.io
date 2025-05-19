<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Kiểm tra kết nối MySQL</h1>";

// Thông tin kết nối
$hosts = [
    'php_ws-mysql-1',
    'mysql',
    'localhost',
    '127.0.0.1'
];
$port = 3306;
$dbname = 'trainingdb';
$username = 'root';
$password = 'pw';

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Host</th><th>Kết quả</th><th>Chi tiết</th></tr>";

foreach ($hosts as $host) {
    echo "<tr>";
    echo "<td>" . $host . "</td>";
    
    // Thử kết nối bằng mysqli
    $mysqli = @new mysqli($host, $username, $password, $dbname, $port);
    
    if ($mysqli->connect_error) {
        echo "<td style='color: red;'>Thất bại</td>";
        echo "<td>" . $mysqli->connect_error . "</td>";
    } else {
        echo "<td style='color: green;'>Thành công</td>";
        
        // Kiểm tra các bảng
        $result = $mysqli->query("SHOW TABLES");
        
        echo "<td>";
        echo "Phiên bản MySQL: " . $mysqli->server_info . "<br>";
        echo "Danh sách bảng:<br><ul>";
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
        } else {
            echo "<li>Không thể lấy danh sách bảng: " . $mysqli->error . "</li>";
        }
        
        echo "</ul></td>";
        
        $mysqli->close();
    }
    
    echo "</tr>";
}

echo "</table>";

// Kiểm tra kết nối đến MySQL container bằng cách sử dụng mysqli
echo "<h2>Kiểm tra kết nối đến MySQL container</h2>";

$mysqli = @new mysqli('php_ws-mysql-1', 'root', 'pw', '', 3306);

if ($mysqli->connect_error) {
    echo "<p style='color: red;'>Kết nối thất bại: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Kết nối thành công!</p>";
    
    // Kiểm tra các cơ sở dữ liệu
    $result = $mysqli->query("SHOW DATABASES");
    
    echo "<p>Danh sách cơ sở dữ liệu:</p>";
    echo "<ul>";
    
    if ($result) {
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
    } else {
        echo "<li>Không thể lấy danh sách cơ sở dữ liệu: " . $mysqli->error . "</li>";
    }
    
    echo "</ul>";
    
    // Kiểm tra xem cơ sở dữ liệu trainingdb có tồn tại không
    $result = $mysqli->query("SHOW DATABASES LIKE 'trainingdb'");
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Cơ sở dữ liệu trainingdb tồn tại!</p>";
        
        // Thử kết nối đến cơ sở dữ liệu trainingdb
        $mysqli->select_db('trainingdb');
        
        if ($mysqli->error) {
            echo "<p style='color: red;'>Không thể kết nối đến cơ sở dữ liệu trainingdb: " . $mysqli->error . "</p>";
        } else {
            echo "<p style='color: green;'>Kết nối đến cơ sở dữ liệu trainingdb thành công!</p>";
            
            // Kiểm tra các bảng
            $result = $mysqli->query("SHOW TABLES");
            
            echo "<p>Danh sách bảng trong cơ sở dữ liệu trainingdb:</p>";
            echo "<ul>";
            
            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_array()) {
                        echo "<li>" . $row[0] . "</li>";
                    }
                } else {
                    echo "<li>Không có bảng nào trong cơ sở dữ liệu trainingdb</li>";
                }
            } else {
                echo "<li>Không thể lấy danh sách bảng: " . $mysqli->error . "</li>";
            }
            
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>Cơ sở dữ liệu trainingdb không tồn tại!</p>";
        
        // Tạo cơ sở dữ liệu trainingdb
        echo "<p>Đang tạo cơ sở dữ liệu trainingdb...</p>";
        
        $result = $mysqli->query("CREATE DATABASE IF NOT EXISTS trainingdb");
        
        if ($result) {
            echo "<p style='color: green;'>Đã tạo cơ sở dữ liệu trainingdb thành công!</p>";
        } else {
            echo "<p style='color: red;'>Không thể tạo cơ sở dữ liệu trainingdb: " . $mysqli->error . "</p>";
        }
    }
    
    $mysqli->close();
}
?>

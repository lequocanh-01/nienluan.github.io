<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Kiểm tra kết nối MySQL</h1>";

// Đọc thông tin kết nối từ file config.ini
$configPath = 'administrator/elements_LQA/mod/config.ini';
if (file_exists($configPath)) {
    echo "<p>File config.ini tồn tại!</p>";
    
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
        
        $servername = $config['section']['servername'];
        $port = $config['section']['port'];
        $dbname = $config['section']['dbname'];
        $username = $config['section']['username'];
        $password = $config['section']['password'];
        
        // Thử kết nối bằng mysqli
        echo "<h2>Thử kết nối bằng mysqli</h2>";
        $mysqli = new mysqli($servername, $username, $password, $dbname, $port);
        
        if ($mysqli->connect_error) {
            echo "<p style='color: red;'>Kết nối mysqli thất bại: " . $mysqli->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>Kết nối mysqli thành công!</p>";
            
            // Kiểm tra các bảng
            $result = $mysqli->query("SHOW TABLES");
            if ($result) {
                echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
                echo "<ul>";
                while ($row = $result->fetch_array()) {
                    echo "<li>" . $row[0] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>Không thể lấy danh sách bảng: " . $mysqli->error . "</p>";
            }
            
            $mysqli->close();
        }
        
        // Thử các cách kết nối khác
        echo "<h2>Thử các cách kết nối khác</h2>";
        $connections = [
            [
                'name' => 'Kết nối đến ' . $servername,
                'host' => $servername,
                'port' => $port
            ],
            [
                'name' => 'Kết nối đến localhost',
                'host' => 'localhost',
                'port' => $port
            ],
            [
                'name' => 'Kết nối đến 127.0.0.1',
                'host' => '127.0.0.1',
                'port' => $port
            ],
            [
                'name' => 'Kết nối đến mysql',
                'host' => 'mysql',
                'port' => $port
            ]
        ];
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Phương thức</th><th>Kết quả</th></tr>";
        
        foreach ($connections as $conn) {
            echo "<tr>";
            echo "<td>" . $conn['name'] . "</td>";
            
            $mysqli = new mysqli($conn['host'], $username, $password, $dbname, $conn['port']);
            
            if ($mysqli->connect_error) {
                echo "<td style='color: red;'>Thất bại: " . $mysqli->connect_error . "</td>";
            } else {
                echo "<td style='color: green;'>Thành công!</td>";
                $mysqli->close();
            }
            
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Không thể đọc file config.ini!</p>";
    }
} else {
    echo "<p style='color: red;'>File config.ini không tồn tại!</p>";
}
?>

<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Kiểm tra kết nối trực tiếp đến MySQL</h1>";

// Thông tin kết nối
$connections = [
    [
        'name' => 'localhost',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'db' => 'trainingdb'
    ],
    [
        'name' => 'localhost với mật khẩu pw',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => 'pw',
        'db' => 'trainingdb'
    ],
    [
        'name' => '127.0.0.1',
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'db' => 'trainingdb'
    ],
    [
        'name' => '127.0.0.1 với mật khẩu pw',
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'pass' => 'pw',
        'db' => 'trainingdb'
    ]
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Kết nối</th><th>Kết quả</th><th>Chi tiết</th></tr>";

foreach ($connections as $conn) {
    echo "<tr>";
    echo "<td>" . $conn['name'] . "</td>";
    
    // Thử kết nối bằng mysqli
    $mysqli = @new mysqli($conn['host'], $conn['user'], $conn['pass'], $conn['db'], $conn['port']);
    
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
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_array()) {
                    echo "<li>" . $row[0] . "</li>";
                }
            } else {
                echo "<li>Không có bảng nào trong cơ sở dữ liệu</li>";
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

// Thử kết nối đến MySQL mà không chỉ định cơ sở dữ liệu
echo "<h2>Thử kết nối đến MySQL mà không chỉ định cơ sở dữ liệu</h2>";

$connections = [
    [
        'name' => 'localhost',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => ''
    ],
    [
        'name' => 'localhost với mật khẩu pw',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => 'pw'
    ]
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Kết nối</th><th>Kết quả</th><th>Chi tiết</th></tr>";

foreach ($connections as $conn) {
    echo "<tr>";
    echo "<td>" . $conn['name'] . "</td>";
    
    // Thử kết nối bằng mysqli
    $mysqli = @new mysqli($conn['host'], $conn['user'], $conn['pass'], '', $conn['port']);
    
    if ($mysqli->connect_error) {
        echo "<td style='color: red;'>Thất bại</td>";
        echo "<td>" . $mysqli->connect_error . "</td>";
    } else {
        echo "<td style='color: green;'>Thành công</td>";
        
        // Kiểm tra các cơ sở dữ liệu
        $result = $mysqli->query("SHOW DATABASES");
        
        echo "<td>";
        echo "Phiên bản MySQL: " . $mysqli->server_info . "<br>";
        echo "Danh sách cơ sở dữ liệu:<br><ul>";
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
        } else {
            echo "<li>Không thể lấy danh sách cơ sở dữ liệu: " . $mysqli->error . "</li>";
        }
        
        echo "</ul></td>";
        
        $mysqli->close();
    }
    
    echo "</tr>";
}

echo "</table>";
?>

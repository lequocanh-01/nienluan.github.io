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

// Thử kết nối trực tiếp
echo "<h2>Thử kết nối trực tiếp</h2>";
try {
    $mysqli = new mysqli("localhost", "root", "", "trainingdb");
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>Kết nối thất bại: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>Kết nối trực tiếp thành công!</p>";
        
        // Kiểm tra các bảng
        $result = $mysqli->query("SHOW TABLES");
        
        echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
        echo "<ul>";
        
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
        
        echo "</ul>";
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
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
        if ($conn instanceof mysqli) {
            echo "<p>Loại kết nối: mysqli</p>";
            
            // Kiểm tra các bảng
            $result = $conn->query("SHOW TABLES");
            
            echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
            echo "<ul>";
            
            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_array()) {
                        echo "<li>" . $row[0] . "</li>";
                    }
                } else {
                    echo "<li>Không có bảng nào trong cơ sở dữ liệu</li>";
                }
            } else {
                echo "<li>Không thể lấy danh sách bảng: " . $conn->error . "</li>";
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

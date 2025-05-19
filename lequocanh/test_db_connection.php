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

// Kiểm tra PDO MySQL driver
echo "<h2>Kiểm tra PDO MySQL driver</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>PDO MySQL driver đã được cài đặt!</p>";
} else {
    echo "<p style='color: red;'>PDO MySQL driver chưa được cài đặt!</p>";
}

// Thử kết nối trực tiếp
echo "<h2>Thử kết nối trực tiếp</h2>";
try {
    $config = parse_ini_file($configPath, true);
    
    $servername = $config['section']['servername'];
    $port = $config['section']['port'];
    $dbname = $config['section']['dbname'];
    $username = $config['section']['username'];
    $password = $config['section']['password'];
    
    echo "<p>Thông tin kết nối: mysql:host=" . $servername . ";port=" . $port . ";dbname=" . $dbname . ";charset=utf8</p>";
    
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>Kết nối thành công!</p>";
    
    // Kiểm tra các bảng
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Kết nối thất bại: " . $e->getMessage() . "</p>";
    
    // Thử kết nối đến máy chủ MySQL
    try {
        $conn = new PDO("mysql:host=$servername;port=$port", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>Kết nối đến máy chủ MySQL thành công!</p>";
        echo "<p style='color: red;'>Nhưng không thể kết nối đến cơ sở dữ liệu " . $dbname . "!</p>";
        
        // Kiểm tra xem cơ sở dữ liệu có tồn tại không
        $stmt = $conn->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Danh sách cơ sở dữ liệu:</p>";
        echo "<ul>";
        foreach ($databases as $database) {
            echo "<li>" . $database . "</li>";
        }
        echo "</ul>";
    } catch (PDOException $e2) {
        echo "<p style='color: red;'>Không thể kết nối đến máy chủ MySQL: " . $e2->getMessage() . "</p>";
    }
}

// Thử kết nối qua lớp Database
echo "<h2>Thử kết nối qua lớp Database</h2>";
try {
    require_once $databasePath;
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>Kết nối qua lớp Database thành công!</p>";
        
        // Kiểm tra các bảng
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Danh sách bảng trong cơ sở dữ liệu:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: Không có kết nối!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: " . $e->getMessage() . "</p>";
}
?>

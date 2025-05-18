<?php
// Kiểm tra kết nối cơ sở dữ liệu
echo "<h2>Kiểm tra kết nối cơ sở dữ liệu</h2>";

// Thông tin kết nối
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

// Kiểm tra kết nối trực tiếp
echo "<h3>1. Kiểm tra kết nối trực tiếp</h3>";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>Kết nối trực tiếp thành công!</p>";
    
    // Kiểm tra phiên bản MySQL
    $stmt = $conn->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Phiên bản MySQL: " . $version['version'] . "</p>";
    
    // Kiểm tra quyền truy cập
    echo "<h4>Kiểm tra quyền truy cập</h4>";
    $stmt = $conn->query("SHOW GRANTS FOR CURRENT_USER()");
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>Kết nối trực tiếp thất bại: " . $e->getMessage() . "</p>";
}

// Kiểm tra kết nối qua lớp Database
echo "<h3>2. Kiểm tra kết nối qua lớp Database</h3>";
try {
    require_once 'administrator/elements_LQA/mod/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn instanceof PDO) {
        echo "<p style='color: green;'>Kết nối qua lớp Database thành công!</p>";
        
        // Kiểm tra truy vấn đơn giản
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['test'] == 1) {
            echo "<p>Truy vấn đơn giản thành công!</p>";
        } else {
            echo "<p style='color: red;'>Truy vấn đơn giản thất bại!</p>";
        }
    } else {
        echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: Không nhận được đối tượng PDO</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>Kết nối qua lớp Database thất bại: " . $e->getMessage() . "</p>";
}

// Kiểm tra file config.ini
echo "<h3>3. Kiểm tra file config.ini</h3>";
$config_path = 'administrator/elements_LQA/mod/config.ini';
if (file_exists($config_path)) {
    echo "<p style='color: green;'>File config.ini tồn tại!</p>";
    
    $config = parse_ini_file($config_path, true);
    if ($config) {
        echo "<p>Nội dung file config.ini:</p>";
        echo "<pre>";
        print_r($config);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>Không thể đọc file config.ini!</p>";
    }
} else {
    echo "<p style='color: red;'>File config.ini không tồn tại!</p>";
}

// Kiểm tra kết nối MySQL từ Docker
echo "<h3>4. Kiểm tra kết nối MySQL từ Docker</h3>";
echo "<p>Thông tin kết nối:</p>";
echo "<ul>";
echo "<li>Host: $servername</li>";
echo "<li>Username: $username</li>";
echo "<li>Password: " . str_repeat("*", strlen($password)) . "</li>";
echo "<li>Database: $dbname</li>";
echo "</ul>";

// Kiểm tra kết nối từ Docker
echo "<p>Nếu bạn đang sử dụng Docker, hãy đảm bảo rằng:</p>";
echo "<ol>";
echo "<li>Container MySQL đang chạy</li>";
echo "<li>Container PHP có thể kết nối đến container MySQL</li>";
echo "<li>Các biến môi trường đã được cấu hình đúng</li>";
echo "</ol>";

// Kiểm tra các bảng trong cơ sở dữ liệu
echo "<h3>5. Kiểm tra các bảng trong cơ sở dữ liệu</h3>";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Số lượng bảng: " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra các bảng: " . $e->getMessage() . "</p>";
}

// Kiểm tra kết nối từ lớp hanghoa
echo "<h3>6. Kiểm tra kết nối từ lớp hanghoa</h3>";
try {
    require_once 'administrator/elements_LQA/mod/hanghoaCls.php';
    $hanghoa = new hanghoa();
    
    // Thử lấy danh sách hàng hóa
    $list_hanghoa = $hanghoa->HanghoaGetAll();
    
    if (is_array($list_hanghoa)) {
        echo "<p style='color: green;'>Kết nối từ lớp hanghoa thành công!</p>";
        echo "<p>Số lượng hàng hóa: " . count($list_hanghoa) . "</p>";
    } else {
        echo "<p style='color: red;'>Kết nối từ lớp hanghoa thất bại: Không nhận được danh sách hàng hóa</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>Kết nối từ lớp hanghoa thất bại: " . $e->getMessage() . "</p>";
}

// Kiểm tra thêm hàng hóa từ lớp hanghoa
echo "<h3>7. Kiểm tra thêm hàng hóa từ lớp hanghoa</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='test_add_hanghoa'>Thử thêm hàng hóa từ lớp hanghoa</button>";
echo "</form>";

if (isset($_POST['test_add_hanghoa'])) {
    try {
        require_once 'administrator/elements_LQA/mod/hanghoaCls.php';
        require_once 'administrator/elements_LQA/mod/loaihangCls.php';
        
        $hanghoa = new hanghoa();
        $loaihang = new loaihang();
        
        // Lấy ID loại hàng đầu tiên
        $list_loaihang = $loaihang->LoaihangGetAll();
        if (count($list_loaihang) > 0) {
            $idloaihang = $list_loaihang[0]->idloaihang;
            
            // Thêm hàng hóa mẫu
            $tenhanghoa = "Hàng hóa mẫu " . date("YmdHis");
            $mota = "Mô tả hàng hóa mẫu";
            $giathamkhao = 100000;
            $id_hinhanh = null;
            $idThuongHieu = null;
            $idDonViTinh = null;
            $idNhanVien = null;
            
            $result = $hanghoa->HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien);
            
            if ($result) {
                echo "<p style='color: green;'>Thêm hàng hóa từ lớp hanghoa thành công!</p>";
            } else {
                echo "<p style='color: red;'>Thêm hàng hóa từ lớp hanghoa thất bại!</p>";
            }
        } else {
            echo "<p style='color: red;'>Không tìm thấy loại hàng nào trong cơ sở dữ liệu!</p>";
        }
    } catch(Exception $e) {
        echo "<p style='color: red;'>Lỗi khi thêm hàng hóa từ lớp hanghoa: " . $e->getMessage() . "</p>";
    }
}
?>

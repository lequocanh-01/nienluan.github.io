<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bật chế độ hiển thị lỗi
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    echo "<h2>Kiểm tra và sửa lỗi thêm hàng hóa</h2>";
    
    // Kiểm tra cấu trúc bảng hanghoa
    echo "<h3>Cấu trúc bảng hanghoa</h3>";
    $stmt = $conn->query("DESCRIBE hanghoa");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra các ràng buộc khóa ngoại
    echo "<h3>Ràng buộc khóa ngoại của bảng hanghoa</h3>";
    $stmt = $conn->query("
        SELECT 
            TABLE_NAME, 
            COLUMN_NAME, 
            CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_SCHEMA = '$dbname'
            AND TABLE_NAME = 'hanghoa'
    ");
    
    echo "<table border='1'>";
    echo "<tr><th>Table</th><th>Column</th><th>Constraint</th><th>Referenced Table</th><th>Referenced Column</th></tr>";
    $hasConstraints = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasConstraints = true;
        echo "<tr>";
        echo "<td>" . $row['TABLE_NAME'] . "</td>";
        echo "<td>" . $row['COLUMN_NAME'] . "</td>";
        echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
        echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
        echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
        echo "</tr>";
    }
    if (!$hasConstraints) {
        echo "<tr><td colspan='5'>Không tìm thấy ràng buộc khóa ngoại nào</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra dữ liệu trong bảng loaihang
    echo "<h3>Dữ liệu trong bảng loaihang</h3>";
    $stmt = $conn->query("SELECT * FROM loaihang");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên loại hàng</th></tr>";
    $hasLoaihang = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasLoaihang = true;
        echo "<tr>";
        echo "<td>" . $row['idloaihang'] . "</td>";
        echo "<td>" . $row['tenloaihang'] . "</td>";
        echo "</tr>";
    }
    if (!$hasLoaihang) {
        echo "<tr><td colspan='2'>Không có dữ liệu trong bảng loaihang</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra dữ liệu trong bảng hinhanh
    echo "<h3>Dữ liệu trong bảng hinhanh</h3>";
    $stmt = $conn->query("SELECT id, ten_file FROM hinhanh LIMIT 5");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên file</th></tr>";
    $hasHinhanh = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasHinhanh = true;
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['ten_file'] . "</td>";
        echo "</tr>";
    }
    if (!$hasHinhanh) {
        echo "<tr><td colspan='2'>Không có dữ liệu trong bảng hinhanh</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra xem có lỗi nào trong file log không
    echo "<h3>Kiểm tra file log</h3>";
    $log_file = __DIR__ . '/administrator/elements_LQA/mod/hanghoa_class_debug.log';
    if (file_exists($log_file)) {
        echo "<pre>";
        echo file_get_contents($log_file);
        echo "</pre>";
    } else {
        echo "<p>Không tìm thấy file log: $log_file</p>";
    }
    
    // Thử thêm một hàng hóa mẫu
    echo "<h3>Thử thêm hàng hóa mẫu</h3>";
    
    // Lấy ID loại hàng đầu tiên
    $stmt = $conn->query("SELECT idloaihang FROM loaihang LIMIT 1");
    $idloaihang = $stmt->fetchColumn();
    
    if (!$idloaihang) {
        echo "<p style='color: red;'>Không tìm thấy loại hàng nào trong cơ sở dữ liệu. Cần thêm loại hàng trước.</p>";
    } else {
        try {
            // Thêm hàng hóa mẫu
            $tenhanghoa = "Hàng hóa mẫu " . date("YmdHis");
            $mota = "Mô tả hàng hóa mẫu";
            $giathamkhao = 100000;
            $id_hinhanh = null; // Không cần hình ảnh
            $idThuongHieu = null;
            $idDonViTinh = null;
            $idNhanVien = null;
            
            $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang, idThuongHieu, idDonViTinh, idNhanVien) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien]);
            
            if ($result) {
                $lastId = $conn->lastInsertId();
                echo "<p style='color: green;'>Thêm hàng hóa mẫu thành công! ID: $lastId</p>";
                
                // Sửa lỗi trong file hanghoaView.php
                $hanghoaViewPath = __DIR__ . '/administrator/elements_LQA/mhanghoa/hanghoaView.php';
                if (file_exists($hanghoaViewPath)) {
                    $content = file_get_contents($hanghoaViewPath);
                    
                    // Tìm và sửa lỗi trong form thêm hàng hóa
                    $oldContent = '<select name="id_hinhanh" id="imageSelector" required>';
                    $newContent = '<select name="id_hinhanh" id="imageSelector">';
                    
                    if (strpos($content, $oldContent) !== false) {
                        $content = str_replace($oldContent, $newContent, $content);
                        file_put_contents($hanghoaViewPath, $content);
                        echo "<p style='color: green;'>Đã sửa lỗi trong file hanghoaView.php: Đã bỏ thuộc tính required cho trường hình ảnh.</p>";
                    } else {
                        echo "<p style='color: orange;'>Không tìm thấy đoạn code cần sửa trong file hanghoaView.php.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>Không tìm thấy file hanghoaView.php tại đường dẫn: $hanghoaViewPath</p>";
                }
            } else {
                echo "<p style='color: red;'>Thêm hàng hóa mẫu thất bại!</p>";
                echo "<pre>";
                print_r($stmt->errorInfo());
                echo "</pre>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><a href='./administrator/index.php?req=hanghoaview' class='btn'>Quay lại trang quản lý hàng hóa</a></p>";
    
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>

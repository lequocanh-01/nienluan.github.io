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
    
    echo "<h2>Kiểm tra và thêm hàng hóa</h2>";
    
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
    
    // Thử thêm hàng hóa mới
    if (isset($_POST['submit'])) {
        try {
            // Lấy dữ liệu từ form
            $tenhanghoa = $_POST['tenhanghoa'];
            $mota = $_POST['mota'];
            $giathamkhao = $_POST['giathamkhao'];
            $idloaihang = $_POST['idloaihang'];
            $id_hinhanh = !empty($_POST['id_hinhanh']) ? $_POST['id_hinhanh'] : null;
            $idThuongHieu = !empty($_POST['idThuongHieu']) ? $_POST['idThuongHieu'] : null;
            $idDonViTinh = !empty($_POST['idDonViTinh']) ? $_POST['idDonViTinh'] : null;
            $idNhanVien = !empty($_POST['idNhanVien']) ? $_POST['idNhanVien'] : null;
            
            // In ra thông tin để debug
            echo "<h3>Thông tin hàng hóa cần thêm:</h3>";
            echo "<pre>";
            echo "Tên hàng hóa: $tenhanghoa\n";
            echo "Mô tả: $mota\n";
            echo "Giá tham khảo: $giathamkhao\n";
            echo "ID loại hàng: $idloaihang\n";
            echo "ID hình ảnh: " . ($id_hinhanh ?? 'NULL') . "\n";
            echo "ID thương hiệu: " . ($idThuongHieu ?? 'NULL') . "\n";
            echo "ID đơn vị tính: " . ($idDonViTinh ?? 'NULL') . "\n";
            echo "ID nhân viên: " . ($idNhanVien ?? 'NULL') . "\n";
            echo "</pre>";
            
            // Chuẩn bị câu lệnh SQL
            $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang, idThuongHieu, idDonViTinh, idNhanVien) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Chuẩn bị và thực thi câu lệnh
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien]);
            
            if ($result) {
                echo "<p style='color: green;'>Thêm hàng hóa thành công! ID: " . $conn->lastInsertId() . "</p>";
            } else {
                echo "<p style='color: red;'>Thêm hàng hóa thất bại!</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
    // Form thêm hàng hóa
    echo "<h3>Form thêm hàng hóa mới</h3>";
    echo "<form method='post'>";
    echo "<table>";
    echo "<tr><td>Tên hàng hóa:</td><td><input type='text' name='tenhanghoa' required></td></tr>";
    echo "<tr><td>Mô tả:</td><td><input type='text' name='mota'></td></tr>";
    echo "<tr><td>Giá tham khảo:</td><td><input type='number' name='giathamkhao' required></td></tr>";
    
    // Dropdown chọn loại hàng
    echo "<tr><td>Loại hàng:</td><td>";
    echo "<select name='idloaihang' required>";
    echo "<option value=''>-- Chọn loại hàng --</option>";
    $stmt = $conn->query("SELECT idloaihang, tenloaihang FROM loaihang");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idloaihang'] . "'>" . $row['tenloaihang'] . "</option>";
    }
    echo "</select></td></tr>";
    
    // Dropdown chọn hình ảnh
    echo "<tr><td>Hình ảnh:</td><td>";
    echo "<select name='id_hinhanh'>";
    echo "<option value=''>-- Chọn hình ảnh (không bắt buộc) --</option>";
    $stmt = $conn->query("SELECT id, ten_file FROM hinhanh");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . $row['ten_file'] . "</option>";
    }
    echo "</select></td></tr>";
    
    // Dropdown chọn thương hiệu
    echo "<tr><td>Thương hiệu:</td><td>";
    echo "<select name='idThuongHieu'>";
    echo "<option value=''>-- Chọn thương hiệu (không bắt buộc) --</option>";
    $stmt = $conn->query("SELECT idThuongHieu, tenTH FROM thuonghieu");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idThuongHieu'] . "'>" . $row['tenTH'] . "</option>";
    }
    echo "</select></td></tr>";
    
    // Dropdown chọn đơn vị tính
    echo "<tr><td>Đơn vị tính:</td><td>";
    echo "<select name='idDonViTinh'>";
    echo "<option value=''>-- Chọn đơn vị tính (không bắt buộc) --</option>";
    $stmt = $conn->query("SELECT idDonViTinh, tenDonViTinh FROM donvitinh");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idDonViTinh'] . "'>" . $row['tenDonViTinh'] . "</option>";
    }
    echo "</select></td></tr>";
    
    // Dropdown chọn nhân viên
    echo "<tr><td>Nhân viên:</td><td>";
    echo "<select name='idNhanVien'>";
    echo "<option value=''>-- Chọn nhân viên (không bắt buộc) --</option>";
    $stmt = $conn->query("SELECT idNhanVien, tenNV FROM nhanvien");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idNhanVien'] . "'>" . $row['tenNV'] . "</option>";
    }
    echo "</select></td></tr>";
    
    echo "<tr><td colspan='2'><input type='submit' name='submit' value='Thêm hàng hóa'></td></tr>";
    echo "</table>";
    echo "</form>";
    
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>

<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    // Kiểm tra bảng loaihang
    echo "<h3>Danh sách loại hàng</h3>";
    $stmt = $conn->query("SELECT * FROM loaihang");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên loại hàng</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['idloaihang'] . "</td>";
        echo "<td>" . $row['tenloaihang'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra bảng hinhanh
    echo "<h3>Danh sách hình ảnh</h3>";
    $stmt = $conn->query("SELECT id, ten_file FROM hinhanh LIMIT 10");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên file</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['ten_file'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Form thêm hàng hóa mới
    echo "<h3>Thêm hàng hóa mới</h3>";
    echo "<form method='post'>";
    echo "<table>";
    echo "<tr><td>Tên hàng hóa:</td><td><input type='text' name='tenhanghoa' required></td></tr>";
    echo "<tr><td>Giá tham khảo:</td><td><input type='number' name='giathamkhao' required></td></tr>";
    echo "<tr><td>Mô tả:</td><td><input type='text' name='mota'></td></tr>";
    
    // Dropdown chọn hình ảnh
    echo "<tr><td>Hình ảnh:</td><td>";
    echo "<select name='id_hinhanh' required>";
    echo "<option value=''>-- Chọn hình ảnh --</option>";
    $stmt = $conn->query("SELECT id, ten_file FROM hinhanh");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . $row['ten_file'] . "</option>";
    }
    echo "</select>";
    echo "</td></tr>";
    
    // Radio button chọn loại hàng
    echo "<tr><td>Loại hàng:</td><td>";
    $stmt = $conn->query("SELECT idloaihang, tenloaihang FROM loaihang");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<input type='radio' name='idloaihang' value='" . $row['idloaihang'] . "' required> " . $row['tenloaihang'] . "<br>";
    }
    echo "</td></tr>";
    
    // Dropdown chọn thương hiệu
    echo "<tr><td>Thương hiệu:</td><td>";
    echo "<select name='idThuongHieu'>";
    echo "<option value=''>-- Chọn thương hiệu --</option>";
    $stmt = $conn->query("SELECT idThuongHieu, tenTH FROM thuonghieu");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idThuongHieu'] . "'>" . $row['tenTH'] . "</option>";
    }
    echo "</select>";
    echo "</td></tr>";
    
    // Dropdown chọn đơn vị tính
    echo "<tr><td>Đơn vị tính:</td><td>";
    echo "<select name='idDonViTinh'>";
    echo "<option value=''>-- Chọn đơn vị tính --</option>";
    $stmt = $conn->query("SELECT idDonViTinh, tenDonViTinh FROM donvitinh");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idDonViTinh'] . "'>" . $row['tenDonViTinh'] . "</option>";
    }
    echo "</select>";
    echo "</td></tr>";
    
    // Dropdown chọn nhân viên
    echo "<tr><td>Nhân viên:</td><td>";
    echo "<select name='idNhanVien'>";
    echo "<option value=''>-- Chọn nhân viên --</option>";
    $stmt = $conn->query("SELECT idNhanVien, tenNV FROM nhanvien");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='" . $row['idNhanVien'] . "'>" . $row['tenNV'] . "</option>";
    }
    echo "</select>";
    echo "</td></tr>";
    
    echo "<tr><td></td><td><input type='submit' name='submit' value='Thêm hàng hóa'></td></tr>";
    echo "</table>";
    echo "</form>";
    
    // Xử lý khi form được submit
    if (isset($_POST['submit'])) {
        $tenhanghoa = $_POST['tenhanghoa'];
        $giathamkhao = $_POST['giathamkhao'];
        $mota = $_POST['mota'];
        $id_hinhanh = $_POST['id_hinhanh'];
        $idloaihang = $_POST['idloaihang'];
        $idThuongHieu = $_POST['idThuongHieu'] ?: null;
        $idDonViTinh = $_POST['idDonViTinh'] ?: null;
        $idNhanVien = $_POST['idNhanVien'] ?: null;
        
        // Thêm hàng hóa mới
        $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang, idThuongHieu, idDonViTinh, idNhanVien) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien]);
        
        if ($result) {
            $lastId = $conn->lastInsertId();
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px;'>";
            echo "<strong>Thành công!</strong> Đã thêm hàng hóa mới với ID: " . $lastId;
            echo "</div>";
            
            // Hiển thị thông tin hàng hóa vừa thêm
            $sql = "SELECT h.*, l.tenloaihang, t.tenTH, d.tenDonViTinh, n.tenNV 
                    FROM hanghoa h 
                    LEFT JOIN loaihang l ON h.idloaihang = l.idloaihang 
                    LEFT JOIN thuonghieu t ON h.idThuongHieu = t.idThuongHieu 
                    LEFT JOIN donvitinh d ON h.idDonViTinh = d.idDonViTinh 
                    LEFT JOIN nhanvien n ON h.idNhanVien = n.idNhanVien 
                    WHERE h.idhanghoa = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$lastId]);
            $hanghoa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Thông tin hàng hóa vừa thêm</h3>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Tên hàng hóa</th><th>Giá tham khảo</th><th>Mô tả</th><th>Loại hàng</th><th>Thương hiệu</th><th>Đơn vị tính</th><th>Nhân viên</th></tr>";
            echo "<tr>";
            echo "<td>" . $hanghoa['idhanghoa'] . "</td>";
            echo "<td>" . $hanghoa['tenhanghoa'] . "</td>";
            echo "<td>" . number_format($hanghoa['giathamkhao'], 0, ',', '.') . " đ</td>";
            echo "<td>" . $hanghoa['mota'] . "</td>";
            echo "<td>" . $hanghoa['tenloaihang'] . "</td>";
            echo "<td>" . ($hanghoa['tenTH'] ?: 'Chưa chọn') . "</td>";
            echo "<td>" . ($hanghoa['tenDonViTinh'] ?: 'Chưa chọn') . "</td>";
            echo "<td>" . ($hanghoa['tenNV'] ?: 'Chưa chọn') . "</td>";
            echo "</tr>";
            echo "</table>";
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px;'>";
            echo "<strong>Lỗi!</strong> Không thể thêm hàng hóa mới.";
            echo "</div>";
        }
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
require_once 'administrator/elements_LQA/mod/hanghoaCls.php';

$db = Database::getInstance()->getConnection();
$hanghoa = new hanghoa();

echo "<h2>Kiểm tra thêm hàng hóa sau khi sửa</h2>";

// Thử thêm hàng hóa mới
if (isset($_POST['submit'])) {
    try {
        // Lấy dữ liệu từ form
        $tenhanghoa = $_POST['tenhanghoa'];
        $mota = $_POST['mota'];
        $giathamkhao = $_POST['giathamkhao'];
        $idloaihang = $_POST['idloaihang'];
        $id_hinhanh = !empty($_POST['id_hinhanh']) ? $_POST['id_hinhanh'] : 0;
        $idThuongHieu = !empty($_POST['idThuongHieu']) ? $_POST['idThuongHieu'] : null;
        $idDonViTinh = !empty($_POST['idDonViTinh']) ? $_POST['idDonViTinh'] : null;
        $idNhanVien = !empty($_POST['idNhanVien']) ? $_POST['idNhanVien'] : null;
        $ghichu = !empty($_POST['ghichu']) ? $_POST['ghichu'] : '';
        
        // In ra thông tin để debug
        echo "<h3>Thông tin hàng hóa cần thêm:</h3>";
        echo "<pre>";
        echo "Tên hàng hóa: $tenhanghoa\n";
        echo "Mô tả: $mota\n";
        echo "Giá tham khảo: $giathamkhao\n";
        echo "ID loại hàng: $idloaihang\n";
        echo "ID hình ảnh: " . ($id_hinhanh ?? '0') . "\n";
        echo "ID thương hiệu: " . ($idThuongHieu ?? 'NULL') . "\n";
        echo "ID đơn vị tính: " . ($idDonViTinh ?? 'NULL') . "\n";
        echo "ID nhân viên: " . ($idNhanVien ?? 'NULL') . "\n";
        echo "Ghi chú: " . ($ghichu ?? '') . "\n";
        echo "</pre>";
        
        // Thêm hàng hóa sử dụng lớp hanghoa
        $result = $hanghoa->HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $ghichu);
        
        if ($result) {
            echo "<p style='color: green;'>Thêm hàng hóa thành công! ID: " . $result . "</p>";
            
            // Kiểm tra dữ liệu vừa thêm
            $newProduct = $hanghoa->HanghoaGetbyId($result);
            
            echo "<h3>Dữ liệu hàng hóa vừa thêm:</h3>";
            echo "<table border='1'>";
            echo "<tr>";
            foreach ($newProduct as $key => $value) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            echo "<tr>";
            foreach ($newProduct as $key => $value) {
                echo "<td>" . (is_null($value) ? "NULL" : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
            echo "</table>";
            
            // Kiểm tra xem đã thêm vào bảng tonkho chưa
            $stmt = $db->prepare("SELECT * FROM tonkho WHERE idhanghoa = ?");
            $stmt->execute([$result]);
            $tonkho = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tonkho) {
                echo "<h3>Dữ liệu tonkho tương ứng:</h3>";
                echo "<table border='1'>";
                echo "<tr>";
                foreach ($tonkho as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                echo "<tr>";
                foreach ($tonkho as $value) {
                    echo "<td>" . (is_null($value) ? "NULL" : htmlspecialchars($value)) . "</td>";
                }
                echo "</tr>";
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>Chưa có dữ liệu trong bảng tonkho cho hàng hóa này.</p>";
            }
        } else {
            echo "<p style='color: red;'>Thêm hàng hóa thất bại!</p>";
            
            // Kiểm tra file log
            $log_file = 'administrator/elements_LQA/mod/hanghoa_class_debug.log';
            if (file_exists($log_file)) {
                echo "<h3>Nội dung file log:</h3>";
                echo "<pre style='max-height: 300px; overflow-y: auto; background-color: #f5f5f5; padding: 10px;'>";
                echo htmlspecialchars(file_get_contents($log_file));
                echo "</pre>";
            }
        }
    } catch (Exception $e) {
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
echo "<tr><td>Ghi chú:</td><td><input type='text' name='ghichu'></td></tr>";

// Dropdown chọn hình ảnh
echo "<tr><td>Hình ảnh:</td><td>";
echo "<select name='id_hinhanh'>";
echo "<option value='0'>-- Chọn hình ảnh (không bắt buộc) --</option>";
$stmt = $db->query("SELECT id, ten_file FROM hinhanh");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $row['id'] . "'>" . $row['ten_file'] . "</option>";
}
echo "</select>";
echo "</td></tr>";

// Dropdown chọn loại hàng
echo "<tr><td>Loại hàng:</td><td>";
echo "<select name='idloaihang' required>";
echo "<option value=''>-- Chọn loại hàng --</option>";
$stmt = $db->query("SELECT idloaihang, tenloaihang FROM loaihang");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $row['idloaihang'] . "'>" . $row['tenloaihang'] . "</option>";
}
echo "</select>";
echo "</td></tr>";

// Dropdown chọn thương hiệu
echo "<tr><td>Thương hiệu:</td><td>";
echo "<select name='idThuongHieu'>";
echo "<option value=''>-- Chọn thương hiệu --</option>";
$stmt = $db->query("SELECT idThuongHieu, tenTH FROM thuonghieu");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $row['idThuongHieu'] . "'>" . $row['tenTH'] . "</option>";
}
echo "</select>";
echo "</td></tr>";

// Dropdown chọn đơn vị tính
echo "<tr><td>Đơn vị tính:</td><td>";
echo "<select name='idDonViTinh'>";
echo "<option value=''>-- Chọn đơn vị tính --</option>";
$stmt = $db->query("SELECT idDonViTinh, tenDonViTinh FROM donvitinh");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $row['idDonViTinh'] . "'>" . $row['tenDonViTinh'] . "</option>";
}
echo "</select>";
echo "</td></tr>";

// Dropdown chọn nhân viên
echo "<tr><td>Nhân viên:</td><td>";
echo "<select name='idNhanVien'>";
echo "<option value=''>-- Chọn nhân viên --</option>";
$stmt = $db->query("SELECT idNhanVien, tenNV FROM nhanvien");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . $row['idNhanVien'] . "'>" . $row['tenNV'] . "</option>";
}
echo "</select>";
echo "</td></tr>";

echo "<tr><td colspan='2'><input type='submit' name='submit' value='Thêm hàng hóa'></td></tr>";
echo "</table>";
echo "</form>";
?>

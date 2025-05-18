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
    
    // Kiểm tra dữ liệu trong bảng hanghoa
    echo "<h3>Dữ liệu trong bảng hanghoa</h3>";
    $stmt = $conn->query("SELECT * FROM hanghoa ORDER BY idhanghoa DESC LIMIT 10");
    $columns = array();
    $data = array();
    
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $columns[] = $col['name'];
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }
    
    if (count($data) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . $column . "</th>";
        }
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<td>" . (isset($row[$column]) ? $row[$column] : "NULL") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Không có dữ liệu trong bảng hanghoa.</p>";
    }
    
    // Kiểm tra các bảng liên quan
    echo "<h3>Các bảng liên quan</h3>";
    
    // Kiểm tra bảng loaihang
    echo "<h4>Bảng loaihang</h4>";
    $stmt = $conn->query("SELECT * FROM loaihang");
    $columns = array();
    $data = array();
    
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $columns[] = $col['name'];
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }
    
    if (count($data) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . $column . "</th>";
        }
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<td>" . (isset($row[$column]) ? $row[$column] : "NULL") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Không có dữ liệu trong bảng loaihang.</p>";
    }
    
    // Kiểm tra bảng hinhanh
    echo "<h4>Bảng hinhanh</h4>";
    $stmt = $conn->query("SELECT * FROM hinhanh LIMIT 5");
    $columns = array();
    $data = array();
    
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $columns[] = $col['name'];
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }
    
    if (count($data) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($columns as $column) {
            if ($column != 'duong_dan') {
                echo "<th>" . $column . "</th>";
            }
        }
        echo "<th>Hình ảnh</th>";
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($columns as $column) {
                if ($column != 'duong_dan') {
                    echo "<td>" . (isset($row[$column]) ? $row[$column] : "NULL") . "</td>";
                }
            }
            echo "<td>Hình ảnh (dữ liệu nhị phân)</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Không có dữ liệu trong bảng hinhanh.</p>";
    }
    
    // Kiểm tra bảng thuonghieu
    echo "<h4>Bảng thuonghieu</h4>";
    $stmt = $conn->query("SELECT idThuongHieu, tenTH FROM thuonghieu");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên thương hiệu</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['idThuongHieu'] . "</td>";
        echo "<td>" . $row['tenTH'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra bảng donvitinh
    echo "<h4>Bảng donvitinh</h4>";
    $stmt = $conn->query("SELECT idDonViTinh, tenDonViTinh FROM donvitinh");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên đơn vị tính</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['idDonViTinh'] . "</td>";
        echo "<td>" . $row['tenDonViTinh'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra bảng nhanvien
    echo "<h4>Bảng nhanvien</h4>";
    $stmt = $conn->query("SELECT idNhanVien, tenNhanVien FROM nhanvien");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Tên nhân viên</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['idNhanVien'] . "</td>";
        echo "<td>" . $row['tenNhanVien'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

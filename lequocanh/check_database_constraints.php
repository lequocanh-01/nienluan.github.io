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
    
    // Kiểm tra các bảng tham chiếu đến bảng hanghoa
    echo "<h3>Các bảng tham chiếu đến bảng hanghoa</h3>";
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
            AND REFERENCED_TABLE_NAME = 'hanghoa'
    ");
    
    echo "<table border='1'>";
    echo "<tr><th>Table</th><th>Column</th><th>Constraint</th><th>Referenced Table</th><th>Referenced Column</th></tr>";
    $hasReferences = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasReferences = true;
        echo "<tr>";
        echo "<td>" . $row['TABLE_NAME'] . "</td>";
        echo "<td>" . $row['COLUMN_NAME'] . "</td>";
        echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
        echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
        echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
        echo "</tr>";
    }
    if (!$hasReferences) {
        echo "<tr><td colspan='5'>Không tìm thấy bảng nào tham chiếu đến bảng hanghoa</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra các bảng liên quan
    echo "<h3>Kiểm tra bảng loaihang</h3>";
    $stmt = $conn->query("DESCRIBE loaihang");
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
    
    // Kiểm tra bảng hinhanh
    echo "<h3>Kiểm tra bảng hinhanh</h3>";
    $stmt = $conn->query("DESCRIBE hinhanh");
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
    
    // Kiểm tra bảng thuonghieu
    echo "<h3>Kiểm tra bảng thuonghieu</h3>";
    $stmt = $conn->query("DESCRIBE thuonghieu");
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
    
    // Kiểm tra bảng donvitinh
    echo "<h3>Kiểm tra bảng donvitinh</h3>";
    $stmt = $conn->query("DESCRIBE donvitinh");
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
    
    // Kiểm tra bảng nhanvien
    echo "<h3>Kiểm tra bảng nhanvien</h3>";
    $stmt = $conn->query("DESCRIBE nhanvien");
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
    
    // Thử thêm hàng hóa trực tiếp qua SQL
    echo "<h3>Thử thêm hàng hóa trực tiếp qua SQL</h3>";
    echo "<form method='post'>";
    echo "<button type='submit' name='test_insert'>Thử thêm hàng hóa mẫu</button>";
    echo "</form>";
    
    if (isset($_POST['test_insert'])) {
        try {
            // Lấy ID loại hàng đầu tiên
            $stmt = $conn->query("SELECT idloaihang FROM loaihang LIMIT 1");
            $idloaihang = $stmt->fetchColumn();
            
            if (!$idloaihang) {
                echo "<p style='color: red;'>Không tìm thấy loại hàng nào trong cơ sở dữ liệu.</p>";
            } else {
                // Thêm hàng hóa mẫu
                $tenhanghoa = "Hàng hóa mẫu " . date("YmdHis");
                $mota = "Mô tả hàng hóa mẫu";
                $giathamkhao = 100000;
                $hinhanh = null;
                
                $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$tenhanghoa, $mota, $giathamkhao, $hinhanh, $idloaihang]);
                
                if ($result) {
                    $lastId = $conn->lastInsertId();
                    echo "<p style='color: green;'>Thêm hàng hóa mẫu thành công với ID: " . $lastId . "</p>";
                    
                    // Hiển thị thông tin hàng hóa vừa thêm
                    $sql = "SELECT * FROM hanghoa WHERE idhanghoa = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$lastId]);
                    $hanghoa = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<table border='1'>";
                    echo "<tr>";
                    foreach ($hanghoa as $key => $value) {
                        echo "<th>" . $key . "</th>";
                    }
                    echo "</tr>";
                    echo "<tr>";
                    foreach ($hanghoa as $value) {
                        echo "<td>" . ($value === null ? "NULL" : $value) . "</td>";
                    }
                    echo "</tr>";
                    echo "</table>";
                } else {
                    echo "<p style='color: red;'>Thêm hàng hóa mẫu thất bại.</p>";
                    echo "<p>Lỗi: " . print_r($stmt->errorInfo(), true) . "</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

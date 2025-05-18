<?php
// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance()->getConnection();

echo "<h2>Kiểm tra cấu trúc bảng hanghoa</h2>";

try {
    // Lấy cấu trúc bảng hanghoa
    $stmt = $db->query("DESCRIBE hanghoa");
    echo "<h3>Cấu trúc bảng hanghoa</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . ($value === null ? "NULL" : htmlspecialchars($value)) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    // Kiểm tra dữ liệu trong bảng hanghoa
    $stmt = $db->query("SELECT * FROM hanghoa LIMIT 5");
    echo "<h3>Dữ liệu mẫu trong bảng hanghoa</h3>";
    
    // Lấy tên các cột
    $columns = array();
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $columns[] = $col['name'];
    }
    
    echo "<table border='1'>";
    echo "<tr>";
    foreach ($columns as $column) {
        echo "<th>" . htmlspecialchars($column) . "</th>";
    }
    echo "</tr>";
    
    // Hiển thị dữ liệu
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<td>" . (isset($row[$column]) ? htmlspecialchars($row[$column]) : "NULL") . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    // Kiểm tra xem có cột hinhanh hay id_hinhanh
    $hasHinhanh = false;
    $hasIdHinhanh = false;
    
    foreach ($columns as $column) {
        if ($column === 'hinhanh') {
            $hasHinhanh = true;
        }
        if ($column === 'id_hinhanh') {
            $hasIdHinhanh = true;
        }
    }
    
    echo "<h3>Kết quả kiểm tra cột hình ảnh</h3>";
    echo "<p>Cột 'hinhanh': " . ($hasHinhanh ? "Có" : "Không") . "</p>";
    echo "<p>Cột 'id_hinhanh': " . ($hasIdHinhanh ? "Có" : "Không") . "</p>";
    
    // Kiểm tra kiểu dữ liệu của cột hinhanh hoặc id_hinhanh
    if ($hasHinhanh || $hasIdHinhanh) {
        $columnName = $hasHinhanh ? 'hinhanh' : 'id_hinhanh';
        $stmt = $db->query("DESCRIBE hanghoa $columnName");
        $columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Kiểu dữ liệu của cột '$columnName': " . $columnInfo['Type'] . "</p>";
        echo "<p>Cho phép NULL: " . ($columnInfo['Null'] === 'YES' ? "Có" : "Không") . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>

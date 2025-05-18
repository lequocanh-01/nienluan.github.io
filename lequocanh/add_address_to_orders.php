<?php
require_once './administrator/elements_LQA/mod/database.php';

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Kiểm tra xem cột shipping_address đã tồn tại chưa
    $checkColumnSql = "SHOW COLUMNS FROM orders LIKE 'shipping_address'";
    $checkColumnStmt = $conn->prepare($checkColumnSql);
    $checkColumnStmt->execute();
    
    if ($checkColumnStmt->rowCount() == 0) {
        // Thêm cột shipping_address vào bảng orders
        $alterTableSql = "ALTER TABLE orders ADD COLUMN shipping_address TEXT AFTER user_id";
        $conn->exec($alterTableSql);
        echo "<p style='color: green;'>Đã thêm cột shipping_address vào bảng orders thành công!</p>";
    } else {
        echo "<p>Cột shipping_address đã tồn tại trong bảng orders.</p>";
    }
    
    // Hiển thị cấu trúc bảng sau khi thêm cột
    $sql = "DESCRIBE orders";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Cấu trúc bảng orders sau khi cập nhật</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>

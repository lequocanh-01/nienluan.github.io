<?php
require_once './administrator/elements_LQA/mod/database.php';

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra cấu trúc bảng orders
$sql = "DESCRIBE orders";
$stmt = $conn->prepare($sql);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Cấu trúc bảng orders</h2>";
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
?>

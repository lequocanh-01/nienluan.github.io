<?php
require_once './elements_LQA/mod/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra bảng orders
echo "<h3>Kiểm tra bảng orders</h3>";
$checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
$checkOrdersTableStmt->execute();

if ($checkOrdersTableStmt->rowCount() > 0) {
    echo "Bảng orders tồn tại.<br>";
    
    // Lấy cấu trúc bảng orders
    echo "<h4>Cấu trúc bảng orders:</h4>";
    $orderStructureSql = "DESCRIBE orders";
    $orderStructureStmt = $conn->prepare($orderStructureSql);
    $orderStructureStmt->execute();
    $orderStructure = $orderStructureStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($orderStructure);
    echo "</pre>";
    
    // Lấy dữ liệu từ bảng orders
    echo "<h4>Dữ liệu trong bảng orders:</h4>";
    $orderDataSql = "SELECT * FROM orders";
    $orderDataStmt = $conn->prepare($orderDataSql);
    $orderDataStmt->execute();
    $orderData = $orderDataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($orderData);
    echo "</pre>";
} else {
    echo "Bảng orders không tồn tại.<br>";
}

// Kiểm tra bảng order_items
echo "<h3>Kiểm tra bảng order_items</h3>";
$checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
$checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
$checkOrderItemsTableStmt->execute();

if ($checkOrderItemsTableStmt->rowCount() > 0) {
    echo "Bảng order_items tồn tại.<br>";
    
    // Lấy cấu trúc bảng order_items
    echo "<h4>Cấu trúc bảng order_items:</h4>";
    $orderItemsStructureSql = "DESCRIBE order_items";
    $orderItemsStructureStmt = $conn->prepare($orderItemsStructureSql);
    $orderItemsStructureStmt->execute();
    $orderItemsStructure = $orderItemsStructureStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($orderItemsStructure);
    echo "</pre>";
    
    // Lấy dữ liệu từ bảng order_items
    echo "<h4>Dữ liệu trong bảng order_items:</h4>";
    $orderItemsDataSql = "SELECT * FROM order_items";
    $orderItemsDataStmt = $conn->prepare($orderItemsDataSql);
    $orderItemsDataStmt->execute();
    $orderItemsData = $orderItemsDataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($orderItemsData);
    echo "</pre>";
} else {
    echo "Bảng order_items không tồn tại.<br>";
}

// Kiểm tra bảng user
echo "<h3>Kiểm tra bảng user</h3>";
$checkUserTableSql = "SHOW TABLES LIKE 'user'";
$checkUserTableStmt = $conn->prepare($checkUserTableSql);
$checkUserTableStmt->execute();

if ($checkUserTableStmt->rowCount() > 0) {
    echo "Bảng user tồn tại.<br>";
    
    // Lấy cấu trúc bảng user
    echo "<h4>Cấu trúc bảng user:</h4>";
    $userStructureSql = "DESCRIBE user";
    $userStructureStmt = $conn->prepare($userStructureSql);
    $userStructureStmt->execute();
    $userStructure = $userStructureStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($userStructure);
    echo "</pre>";
} else {
    echo "Bảng user không tồn tại.<br>";
}
?>

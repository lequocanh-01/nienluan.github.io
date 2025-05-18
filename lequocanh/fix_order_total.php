<?php
// Script để cập nhật lại tổng tiền cho tất cả các đơn hàng

// Kết nối đến cơ sở dữ liệu
require_once './administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra kết nối
if (!$conn) {
    die("Không thể kết nối đến cơ sở dữ liệu!");
}

echo "<h1>Cập nhật lại tổng tiền cho các đơn hàng</h1>";

// Lấy danh sách tất cả các đơn hàng
$ordersSql = "SELECT id, order_code, total_amount FROM orders";
$ordersStmt = $conn->prepare($ordersSql);
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Danh sách đơn hàng trước khi cập nhật:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Mã đơn hàng</th><th>Tổng tiền hiện tại</th></tr>";

foreach ($orders as $order) {
    echo "<tr>";
    echo "<td>" . $order['id'] . "</td>";
    echo "<td>" . $order['order_code'] . "</td>";
    echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " đ</td>";
    echo "</tr>";
}

echo "</table>";

// Cập nhật tổng tiền cho từng đơn hàng
echo "<h2>Cập nhật tổng tiền:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Mã đơn hàng</th><th>Tổng tiền cũ</th><th>Tổng tiền mới</th><th>Kết quả</th></tr>";

foreach ($orders as $order) {
    // Lấy chi tiết đơn hàng
    $orderItemsSql = "SELECT product_id, quantity, price FROM order_items WHERE order_id = ?";
    $orderItemsStmt = $conn->prepare($orderItemsSql);
    $orderItemsStmt->execute([$order['id']]);
    $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tính lại tổng tiền
    $newTotalAmount = 0;
    foreach ($orderItems as $item) {
        $newTotalAmount += $item['quantity'] * $item['price'];
    }
    
    // Cập nhật tổng tiền trong bảng orders
    $updateOrderSql = "UPDATE orders SET total_amount = ? WHERE id = ?";
    $updateOrderStmt = $conn->prepare($updateOrderSql);
    $result = $updateOrderStmt->execute([$newTotalAmount, $order['id']]);
    
    echo "<tr>";
    echo "<td>" . $order['id'] . "</td>";
    echo "<td>" . $order['order_code'] . "</td>";
    echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " đ</td>";
    echo "<td>" . number_format($newTotalAmount, 0, ',', '.') . " đ</td>";
    echo "<td>" . ($result ? "<span style='color: green;'>Thành công</span>" : "<span style='color: red;'>Thất bại</span>") . "</td>";
    echo "</tr>";
}

echo "</table>";

// Lấy danh sách đơn hàng sau khi cập nhật
$ordersAfterSql = "SELECT id, order_code, total_amount FROM orders";
$ordersAfterStmt = $conn->prepare($ordersAfterSql);
$ordersAfterStmt->execute();
$ordersAfter = $ordersAfterStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Danh sách đơn hàng sau khi cập nhật:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Mã đơn hàng</th><th>Tổng tiền mới</th></tr>";

foreach ($ordersAfter as $order) {
    echo "<tr>";
    echo "<td>" . $order['id'] . "</td>";
    echo "<td>" . $order['order_code'] . "</td>";
    echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " đ</td>";
    echo "</tr>";
}

echo "</table>";

echo "<p>Quá trình cập nhật đã hoàn tất.</p>";
echo "<p><a href='administrator/index.php?req=orders' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

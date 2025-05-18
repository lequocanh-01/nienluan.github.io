<?php
require_once './administrator/elements_LQA/mod/database.php';

// Kết nối database
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h1>Kiểm tra đơn hàng</h1>";

// Kiểm tra bảng orders
echo "<h2>Kiểm tra bảng orders</h2>";
$checkOrdersTableSql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
$checkOrdersTableStmt->execute();

if ($checkOrdersTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng orders không tồn tại.</p>";
} else {
    echo "<p style='color: green;'>Bảng orders đã tồn tại.</p>";
    
    // Kiểm tra cấu trúc bảng orders
    $descOrdersTableSql = "DESCRIBE orders";
    $descOrdersTableStmt = $conn->prepare($descOrdersTableSql);
    $descOrdersTableStmt->execute();
    $ordersColumns = $descOrdersTableStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Cấu trúc bảng orders:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($ordersColumns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra dữ liệu trong bảng orders
    $getOrdersSql = "SELECT * FROM orders ORDER BY id DESC";
    $getOrdersStmt = $conn->prepare($getOrdersSql);
    $getOrdersStmt->execute();
    $orders = $getOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Dữ liệu trong bảng orders:</h3>";
    
    if (count($orders) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        
        // Hiển thị tên cột
        foreach ($orders[0] as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "<th>Chi tiết</th>";
        echo "</tr>";
        
        // Hiển thị dữ liệu
        foreach ($orders as $order) {
            echo "<tr>";
            foreach ($order as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "<td><a href='#order_" . $order['id'] . "' onclick='toggleOrderItems(" . $order['id'] . ")'>Xem chi tiết</a></td>";
            echo "</tr>";
            
            // Hiển thị chi tiết đơn hàng (ẩn ban đầu)
            echo "<tr id='order_items_" . $order['id'] . "' style='display: none;'>";
            echo "<td colspan='" . (count($order) + 1) . "'>";
            
            // Lấy chi tiết đơn hàng
            $getOrderItemsSql = "SELECT oi.*, h.tenhanghoa 
                                FROM order_items oi 
                                LEFT JOIN hanghoa h ON oi.product_id = h.idhanghoa 
                                WHERE oi.order_id = ?";
            $getOrderItemsStmt = $conn->prepare($getOrderItemsSql);
            $getOrderItemsStmt->execute([$order['id']]);
            $orderItems = $getOrderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($orderItems) > 0) {
                echo "<h4>Chi tiết đơn hàng #" . $order['id'] . ":</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background-color: #e6e6e6;'>";
                echo "<th>ID</th><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Thành tiền</th>";
                echo "</tr>";
                
                $totalAmount = 0;
                foreach ($orderItems as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $totalAmount += $subtotal;
                    
                    echo "<tr>";
                    echo "<td>" . $item['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($item['tenhanghoa'] ?? 'Sản phẩm không xác định') . " (ID: " . $item['product_id'] . ")</td>";
                    echo "<td>" . $item['quantity'] . "</td>";
                    echo "<td>" . number_format($item['price'], 0, ',', '.') . " đ</td>";
                    echo "<td>" . number_format($subtotal, 0, ',', '.') . " đ</td>";
                    echo "</tr>";
                }
                
                echo "<tr style='background-color: #f2f2f2;'>";
                echo "<td colspan='4' style='text-align: right;'><strong>Tổng cộng:</strong></td>";
                echo "<td><strong>" . number_format($totalAmount, 0, ',', '.') . " đ</strong></td>";
                echo "</tr>";
                
                // Kiểm tra tổng tiền
                if ($totalAmount != $order['total_amount']) {
                    echo "<tr style='background-color: #ffcccc;'>";
                    echo "<td colspan='5' style='text-align: center; color: red;'>";
                    echo "<strong>Cảnh báo:</strong> Tổng tiền tính toán (" . number_format($totalAmount, 0, ',', '.') . " đ) ";
                    echo "không khớp với tổng tiền trong đơn hàng (" . number_format($order['total_amount'], 0, ',', '.') . " đ)";
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p style='color: red;'>Không có chi tiết đơn hàng.</p>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Không có đơn hàng nào.</p>";
    }
}

// Kiểm tra bảng order_items
echo "<h2>Kiểm tra bảng order_items</h2>";
$checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
$checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
$checkOrderItemsTableStmt->execute();

if ($checkOrderItemsTableStmt->rowCount() == 0) {
    echo "<p style='color: red;'>Bảng order_items không tồn tại.</p>";
} else {
    echo "<p style='color: green;'>Bảng order_items đã tồn tại.</p>";
    
    // Kiểm tra cấu trúc bảng order_items
    $descOrderItemsTableSql = "DESCRIBE order_items";
    $descOrderItemsTableStmt = $conn->prepare($descOrderItemsTableSql);
    $descOrderItemsTableStmt->execute();
    $orderItemsColumns = $descOrderItemsTableStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Cấu trúc bảng order_items:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($orderItemsColumns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// JavaScript để hiển thị/ẩn chi tiết đơn hàng
echo "<script>
function toggleOrderItems(orderId) {
    var orderItemsRow = document.getElementById('order_items_' + orderId);
    if (orderItemsRow.style.display === 'none') {
        orderItemsRow.style.display = 'table-row';
    } else {
        orderItemsRow.style.display = 'none';
    }
}
</script>";

echo "<p><a href='administrator/index.php?req=orders' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

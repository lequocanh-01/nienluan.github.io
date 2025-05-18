<?php
// Script để kiểm tra và sửa lỗi trong quá trình tạo đơn hàng

// Kết nối đến cơ sở dữ liệu
require_once './administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra kết nối
if (!$conn) {
    die("Không thể kết nối đến cơ sở dữ liệu!");
}

echo "<h1>Kiểm tra và sửa lỗi trong quá trình tạo đơn hàng</h1>";

// Kiểm tra cấu trúc bảng orders
$checkOrdersTableSql = "SHOW COLUMNS FROM orders";
$checkOrdersTableStmt = $conn->prepare($checkOrdersTableSql);
$checkOrdersTableStmt->execute();
$ordersColumns = $checkOrdersTableStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Cấu trúc bảng orders:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

foreach ($ordersColumns as $column) {
    echo "<tr>";
    echo "<td>" . $column['Field'] . "</td>";
    echo "<td>" . $column['Type'] . "</td>";
    echo "<td>" . $column['Null'] . "</td>";
    echo "<td>" . $column['Key'] . "</td>";
    echo "<td>" . $column['Default'] . "</td>";
    echo "<td>" . $column['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Kiểm tra cấu trúc bảng order_items
$checkOrderItemsTableSql = "SHOW COLUMNS FROM order_items";
$checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
$checkOrderItemsTableStmt->execute();
$orderItemsColumns = $checkOrderItemsTableStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Cấu trúc bảng order_items:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

foreach ($orderItemsColumns as $column) {
    echo "<tr>";
    echo "<td>" . $column['Field'] . "</td>";
    echo "<td>" . $column['Type'] . "</td>";
    echo "<td>" . $column['Null'] . "</td>";
    echo "<td>" . $column['Key'] . "</td>";
    echo "<td>" . $column['Default'] . "</td>";
    echo "<td>" . $column['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Tạo đơn hàng mẫu với tổng tiền được tính chính xác
echo "<h2>Tạo đơn hàng mẫu với tổng tiền chính xác:</h2>";

// Lấy một sản phẩm từ bảng hanghoa
$getProductSql = "SELECT idhanghoa, tenhanghoa, giathamkhao FROM hanghoa LIMIT 1";
$getProductStmt = $conn->prepare($getProductSql);
$getProductStmt->execute();
$product = $getProductStmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // Tạo mã đơn hàng
    $orderCode = 'ORD_TEST_' . date('YmdHis');
    $quantity = 2; // Số lượng sản phẩm
    $price = $product['giathamkhao']; // Giá sản phẩm
    $totalAmount = $quantity * $price; // Tính tổng tiền chính xác
    $status = 'pending';
    $paymentMethod = 'bank_transfer';
    $createdAt = date('Y-m-d H:i:s');
    
    // Bắt đầu transaction
    $conn->beginTransaction();
    
    try {
        // Thêm đơn hàng vào bảng orders
        $insertOrderSql = "INSERT INTO orders (order_code, total_amount, status, payment_method, created_at)
                          VALUES (?, ?, ?, ?, ?)";
        $insertOrderStmt = $conn->prepare($insertOrderSql);
        $insertOrderStmt->execute([$orderCode, $totalAmount, $status, $paymentMethod, $createdAt]);
        
        // Lấy ID của đơn hàng vừa thêm
        $orderId = $conn->lastInsertId();
        
        // Thêm sản phẩm vào bảng order_items
        $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at)
                              VALUES (?, ?, ?, ?, ?)";
        $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
        $insertOrderItemStmt->execute([$orderId, $product['idhanghoa'], $quantity, $price, $createdAt]);
        
        // Commit transaction
        $conn->commit();
        
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
        echo "<h3>Đã tạo đơn hàng mẫu thành công!</h3>";
        echo "<p>Mã đơn hàng: " . $orderCode . "</p>";
        echo "<p>ID đơn hàng: " . $orderId . "</p>";
        echo "<p>Sản phẩm: " . $product['tenhanghoa'] . "</p>";
        echo "<p>Số lượng: " . $quantity . "</p>";
        echo "<p>Đơn giá: " . number_format($price, 0, ',', '.') . " đ</p>";
        echo "<p>Tổng tiền: " . number_format($totalAmount, 0, ',', '.') . " đ</p>";
        echo "</div>";
        
        // Kiểm tra lại đơn hàng vừa tạo
        $checkOrderSql = "SELECT * FROM orders WHERE id = ?";
        $checkOrderStmt = $conn->prepare($checkOrderSql);
        $checkOrderStmt->execute([$orderId]);
        $order = $checkOrderStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Thông tin đơn hàng vừa tạo:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Mã đơn hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Phương thức thanh toán</th><th>Ngày tạo</th></tr>";
        
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td>" . $order['order_code'] . "</td>";
        echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " đ</td>";
        echo "<td>" . $order['status'] . "</td>";
        echo "<td>" . $order['payment_method'] . "</td>";
        echo "<td>" . $order['created_at'] . "</td>";
        echo "</tr>";
        
        echo "</table>";
        
        // Kiểm tra chi tiết đơn hàng
        $checkOrderItemsSql = "SELECT oi.*, h.tenhanghoa FROM order_items oi
                              JOIN hanghoa h ON oi.product_id = h.idhanghoa
                              WHERE oi.order_id = ?";
        $checkOrderItemsStmt = $conn->prepare($checkOrderItemsSql);
        $checkOrderItemsStmt->execute([$orderId]);
        $orderItems = $checkOrderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Chi tiết đơn hàng:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>";
        
        $calculatedTotal = 0;
        foreach ($orderItems as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            $calculatedTotal += $subtotal;
            
            echo "<tr>";
            echo "<td>" . $item['id'] . "</td>";
            echo "<td>" . $item['tenhanghoa'] . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . number_format($item['price'], 0, ',', '.') . " đ</td>";
            echo "<td>" . number_format($subtotal, 0, ',', '.') . " đ</td>";
            echo "</tr>";
        }
        
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<td colspan='4' style='text-align: right;'><strong>Tổng cộng:</strong></td>";
        echo "<td><strong>" . number_format($calculatedTotal, 0, ',', '.') . " đ</strong></td>";
        echo "</tr>";
        
        echo "</table>";
        
        // Kiểm tra xem tổng tiền có khớp không
        if ($calculatedTotal == $order['total_amount']) {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
            echo "<p><strong>Kết quả kiểm tra:</strong> Tổng tiền đơn hàng khớp với tổng giá trị các sản phẩm.</p>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
            echo "<p><strong>Kết quả kiểm tra:</strong> Tổng tiền đơn hàng không khớp với tổng giá trị các sản phẩm!</p>";
            echo "<p>Tổng tiền trong đơn hàng: " . number_format($order['total_amount'], 0, ',', '.') . " đ</p>";
            echo "<p>Tổng giá trị các sản phẩm: " . number_format($calculatedTotal, 0, ',', '.') . " đ</p>";
            echo "</div>";
        }
    } catch (PDOException $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollBack();
        
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
        echo "<h3>Lỗi khi tạo đơn hàng mẫu!</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<h3>Không tìm thấy sản phẩm nào trong bảng hanghoa!</h3>";
    echo "</div>";
}

echo "<p><a href='administrator/index.php?req=orders' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý đơn hàng</a></p>";
?>

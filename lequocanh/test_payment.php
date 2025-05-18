<?php
session_start();
require_once 'administrator/elements_LQA/mod/database.php';
require_once 'administrator/elements_LQA/mod/giohangCls.php';
require_once 'administrator/elements_LQA/mod/mtonkhoCls.php';
require_once 'administrator/elements_LQA/mod/hanghoaCls.php';

// Khởi tạo các đối tượng
$db = Database::getInstance();
$conn = $db->getConnection();
$giohang = new GioHang();
$tonkho = new MTonKho();
$hanghoa = new hanghoa();

// Kiểm tra xem bảng system_logs có tồn tại không
$checkTableSql = "SHOW TABLES LIKE 'system_logs'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

if ($checkTableStmt->rowCount() == 0) {
    // Bảng chưa tồn tại, tạo bảng system_logs
    $createSystemLogsTableSql = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createSystemLogsTableSql);
    echo "<p>Đã tạo bảng system_logs.</p>";
}

// Thêm log
function addLog($conn, $message) {
    $insertLogSql = "INSERT INTO system_logs (message) VALUES (?)";
    $insertLogStmt = $conn->prepare($insertLogSql);
    $insertLogStmt->execute([$message]);
    echo "<p>" . $message . "</p>";
}

// Hiển thị danh sách sản phẩm
function displayProducts($conn, $hanghoa) {
    $products = $hanghoa->HanghoaGetAll();
    
    echo "<h3>Danh sách sản phẩm</h3>";
    echo "<form method='post'>";
    echo "<table border='1'>";
    echo "<tr><th>Chọn</th><th>ID</th><th>Tên sản phẩm</th><th>Giá</th><th>Số lượng tồn kho</th><th>Số lượng mua</th></tr>";
    
    foreach ($products as $product) {
        // Lấy số lượng tồn kho
        $tonkhoSql = "SELECT soLuong FROM tonkho WHERE idhanghoa = ?";
        $tonkhoStmt = $conn->prepare($tonkhoSql);
        $tonkhoStmt->execute([$product->idhanghoa]);
        $tonkhoInfo = $tonkhoStmt->fetch(PDO::FETCH_ASSOC);
        $soLuongTonKho = $tonkhoInfo ? $tonkhoInfo['soLuong'] : 0;
        
        echo "<tr>";
        echo "<td><input type='checkbox' name='products[]' value='" . $product->idhanghoa . "'></td>";
        echo "<td>" . $product->idhanghoa . "</td>";
        echo "<td>" . $product->tenhanghoa . "</td>";
        echo "<td>" . number_format($product->giathamkhao, 0, ',', '.') . " VNĐ</td>";
        echo "<td>" . $soLuongTonKho . "</td>";
        echo "<td><input type='number' name='quantity[" . $product->idhanghoa . "]' value='1' min='1' max='" . $soLuongTonKho . "'></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<button type='submit' name='add_to_cart'>Thêm vào giỏ hàng</button>";
    echo "</form>";
}

// Hiển thị giỏ hàng
function displayCart($conn, $giohang) {
    $cart = $giohang->getCart();
    
    echo "<h3>Giỏ hàng</h3>";
    
    if (count($cart) > 0) {
        echo "<form method='post'>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Tên sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th></tr>";
        
        $totalAmount = 0;
        
        foreach ($cart as $item) {
            $subtotal = $item['giathamkhao'] * $item['quantity'];
            $totalAmount += $subtotal;
            
            echo "<tr>";
            echo "<td>" . $item['product_id'] . "</td>";
            echo "<td>" . $item['tenhanghoa'] . "</td>";
            echo "<td>" . number_format($item['giathamkhao'], 0, ',', '.') . " VNĐ</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . number_format($subtotal, 0, ',', '.') . " VNĐ</td>";
            echo "</tr>";
        }
        
        echo "<tr><td colspan='4' align='right'><strong>Tổng cộng:</strong></td><td>" . number_format($totalAmount, 0, ',', '.') . " VNĐ</td></tr>";
        echo "</table>";
        
        echo "<button type='submit' name='checkout'>Thanh toán</button>";
        echo "</form>";
    } else {
        echo "<p>Giỏ hàng trống.</p>";
    }
}

// Xử lý khi người dùng thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $productId) {
            $quantity = isset($_POST['quantity'][$productId]) ? (int)$_POST['quantity'][$productId] : 1;
            
            // Kiểm tra số lượng tồn kho
            $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($productId);
            
            if ($tonkhoInfo && $tonkhoInfo->soLuong >= $quantity) {
                // Thêm vào giỏ hàng
                $giohang->addToCart($productId, $quantity);
                
                // Thêm log
                addLog($conn, "Đã thêm sản phẩm ID: " . $productId . " vào giỏ hàng với số lượng: " . $quantity);
            } else {
                // Thêm log
                addLog($conn, "Không thể thêm sản phẩm ID: " . $productId . " vào giỏ hàng vì không đủ số lượng tồn kho.");
            }
        }
    }
}

// Xử lý khi người dùng thanh toán
if (isset($_POST['checkout'])) {
    $cart = $giohang->getCart();
    
    if (count($cart) > 0) {
        // Tạo mã đơn hàng
        $orderCode = "ORDER" . time() . rand(1000, 9999);
        
        // Tính tổng tiền
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['giathamkhao'] * $item['quantity'];
        }
        
        // Lưu thông tin đơn hàng vào session
        $_SESSION['order_details'] = $cart;
        $_SESSION['total_amount'] = $totalAmount;
        $_SESSION['order_code'] = $orderCode;
        
        // Bắt đầu transaction
        $conn->beginTransaction();
        
        try {
            // Lấy user_id từ session (nếu đã đăng nhập)
            $userId = isset($_SESSION['USER']) ? $_SESSION['USER'] : null;
            
            // Thêm đơn hàng vào bảng orders
            $insertOrderSql = "INSERT INTO orders (order_code, user_id, total_amount, status, payment_method)
                              VALUES (?, ?, ?, 'pending', 'bank_transfer')";
            $insertOrderStmt = $conn->prepare($insertOrderSql);
            $insertOrderStmt->execute([$orderCode, $userId, $totalAmount]);
            
            // Lấy ID của đơn hàng vừa thêm
            $orderId = $conn->lastInsertId();
            
            // Thêm các sản phẩm vào bảng order_items
            foreach ($cart as $item) {
                $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                                      VALUES (?, ?, ?, ?)";
                $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);
                $insertOrderItemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['giathamkhao']]);
                
                // Cập nhật số lượng tồn kho (giảm số lượng)
                $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($item['product_id']);
                if ($tonkhoInfo) {
                    // Sử dụng hàm updateSoLuong với isIncrement = false để giảm số lượng
                    $tonkho->updateSoLuong($item['product_id'], $item['quantity'], false);
                    
                    // Thêm log
                    addLog($conn, "Đã cập nhật tồn kho cho sản phẩm ID: " . $item['product_id'] . ", giảm: " . $item['quantity']);
                } else {
                    addLog($conn, "Không tìm thấy thông tin tồn kho cho sản phẩm ID: " . $item['product_id']);
                }
                
                // Xóa sản phẩm khỏi giỏ hàng
                $giohang->removeFromCart($item['product_id']);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Thêm log
            addLog($conn, "Đã tạo đơn hàng thành công với mã: " . $orderCode . ", ID: " . $orderId);
            
            // Hiển thị thông báo thành công
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
            echo "<h3>Đặt hàng thành công!</h3>";
            echo "<p>Mã đơn hàng: " . $orderCode . "</p>";
            echo "<p>Tổng tiền: " . number_format($totalAmount, 0, ',', '.') . " VNĐ</p>";
            echo "</div>";
            
            // Xóa thông tin đơn hàng khỏi session
            unset($_SESSION['order_details']);
            unset($_SESSION['total_amount']);
            unset($_SESSION['order_code']);
        } catch (PDOException $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollBack();
            
            // Thêm log
            addLog($conn, "Lỗi khi tạo đơn hàng: " . $e->getMessage());
            
            // Hiển thị thông báo lỗi
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
            echo "<h3>Đã xảy ra lỗi!</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}

// Hiển thị danh sách tồn kho
function displayTonkho($conn) {
    $tonkhoSql = "SELECT t.*, h.tenhanghoa 
                 FROM tonkho t 
                 JOIN hanghoa h ON t.idhanghoa = h.idhanghoa 
                 ORDER BY t.idTonKho";
    $tonkhoStmt = $conn->prepare($tonkhoSql);
    $tonkhoStmt->execute();
    $tonkhoList = $tonkhoStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Danh sách tồn kho</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ID Hàng hóa</th><th>Tên hàng hóa</th><th>Số lượng</th><th>Số lượng tối thiểu</th><th>Vị trí</th><th>Ngày cập nhật</th></tr>";
    
    foreach ($tonkhoList as $row) {
        echo "<tr>";
        echo "<td>" . $row['idTonKho'] . "</td>";
        echo "<td>" . $row['idhanghoa'] . "</td>";
        echo "<td>" . $row['tenhanghoa'] . "</td>";
        echo "<td>" . $row['soLuong'] . "</td>";
        echo "<td>" . $row['soLuongToiThieu'] . "</td>";
        echo "<td>" . $row['viTri'] . "</td>";
        echo "<td>" . $row['ngayCapNhat'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Hiển thị danh sách đơn hàng
function displayOrders($conn) {
    $ordersSql = "SELECT * FROM orders ORDER BY id DESC";
    $ordersStmt = $conn->prepare($ordersSql);
    $ordersStmt->execute();
    $ordersList = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Danh sách đơn hàng</h3>";
    
    if (count($ordersList) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Mã đơn hàng</th><th>User ID</th><th>Tổng tiền</th><th>Trạng thái</th><th>Phương thức thanh toán</th><th>Ngày tạo</th><th>Thao tác</th></tr>";
        
        foreach ($ordersList as $order) {
            echo "<tr>";
            echo "<td>" . $order['id'] . "</td>";
            echo "<td>" . $order['order_code'] . "</td>";
            echo "<td>" . $order['user_id'] . "</td>";
            echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " VNĐ</td>";
            echo "<td>" . $order['status'] . "</td>";
            echo "<td>" . $order['payment_method'] . "</td>";
            echo "<td>" . $order['created_at'] . "</td>";
            echo "<td><a href='?view_order=" . $order['id'] . "'>Xem chi tiết</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Không có đơn hàng nào.</p>";
    }
}

// Hiển thị chi tiết đơn hàng
if (isset($_GET['view_order'])) {
    $orderId = $_GET['view_order'];
    
    // Lấy thông tin đơn hàng
    $orderSql = "SELECT * FROM orders WHERE id = ?";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "<h3>Chi tiết đơn hàng #" . $orderId . "</h3>";
        echo "<p><strong>Mã đơn hàng:</strong> " . $order['order_code'] . "</p>";
        echo "<p><strong>Tổng tiền:</strong> " . number_format($order['total_amount'], 0, ',', '.') . " VNĐ</p>";
        echo "<p><strong>Trạng thái:</strong> " . $order['status'] . "</p>";
        echo "<p><strong>Phương thức thanh toán:</strong> " . $order['payment_method'] . "</p>";
        echo "<p><strong>Ngày tạo:</strong> " . $order['created_at'] . "</p>";
        
        // Lấy danh sách sản phẩm trong đơn hàng
        $orderItemsSql = "SELECT oi.*, h.tenhanghoa 
                         FROM order_items oi 
                         JOIN hanghoa h ON oi.product_id = h.idhanghoa 
                         WHERE oi.order_id = ?";
        $orderItemsStmt = $conn->prepare($orderItemsSql);
        $orderItemsStmt->execute([$orderId]);
        $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Danh sách sản phẩm</h4>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Tên sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Thành tiền</th></tr>";
        
        foreach ($orderItems as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            
            echo "<tr>";
            echo "<td>" . $item['product_id'] . "</td>";
            echo "<td>" . $item['tenhanghoa'] . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . number_format($item['price'], 0, ',', '.') . " VNĐ</td>";
            echo "<td>" . number_format($subtotal, 0, ',', '.') . " VNĐ</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><a href='test_payment.php'>Quay lại</a></p>";
    } else {
        echo "<p>Không tìm thấy đơn hàng.</p>";
        echo "<p><a href='test_payment.php'>Quay lại</a></p>";
    }
} else {
    // Hiển thị trang chính
    echo "<h2>Kiểm tra thanh toán và cập nhật tồn kho</h2>";
    
    // Hiển thị danh sách tồn kho
    displayTonkho($conn);
    
    // Hiển thị danh sách sản phẩm
    displayProducts($conn, $hanghoa);
    
    // Hiển thị giỏ hàng
    displayCart($conn, $giohang);
    
    // Hiển thị danh sách đơn hàng
    displayOrders($conn);
}
?>

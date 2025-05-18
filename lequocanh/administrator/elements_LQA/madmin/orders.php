<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['ADMIN'])) {
    header('Location: ../../userLogin.php');
    exit();
}

// Tắt hiển thị lỗi
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once './elements_LQA/mod/database.php';
require_once './elements_LQA/mod/hanghoaCls.php';
require_once './elements_LQA/mod/mtonkhoCls.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die('<div class="alert alert-danger">Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra lại cấu hình kết nối.</div>');
}

// Kiểm tra xem cơ sở dữ liệu có hoạt động không
try {
    $testQuery = $conn->query("SELECT 1");
    if (!$testQuery) {
        die('<div class="alert alert-danger">Kết nối cơ sở dữ liệu không hoạt động. Vui lòng kiểm tra lại cấu hình kết nối.</div>');
    }
} catch (PDOException $e) {
    die('<div class="alert alert-danger">Lỗi khi kiểm tra kết nối cơ sở dữ liệu: ' . $e->getMessage() . '</div>');
}

$hanghoa = new hanghoa();
$tonkho = new MTonKho();

// Kiểm tra xem bảng orders đã tồn tại chưa
$checkTableSql = "SHOW TABLES LIKE 'orders'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

// Nếu bảng orders chưa tồn tại, tạo bảng
if ($checkTableStmt->rowCount() == 0) {
    try {
        $createOrdersTableSql = "CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_code VARCHAR(50) NOT NULL,
            user_id VARCHAR(50),
            total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
            payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
            payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $conn->exec($createOrdersTableSql);
        // error_log("Đã tạo bảng orders thành công!");

        // Kiểm tra lại xem bảng đã được tạo thành công chưa
        $checkTableAgainSql = "SHOW TABLES LIKE 'orders'";
        $checkTableAgainStmt = $conn->prepare($checkTableAgainSql);
        $checkTableAgainStmt->execute();
        if ($checkTableAgainStmt->rowCount() == 0) {
            echo '<div class="alert alert-danger">Không thể tạo bảng orders. Vui lòng kiểm tra quyền của cơ sở dữ liệu.</div>';
        } else {
            echo '<div class="alert alert-success">Đã tạo bảng orders thành công!</div>';
        }
    } catch (PDOException $e) {
        // error_log("Lỗi khi tạo bảng orders: " . $e->getMessage());
        echo '<div class="alert alert-danger">Lỗi khi tạo bảng orders: ' . $e->getMessage() . '</div>';
    }
}

// Kiểm tra xem bảng order_items đã tồn tại chưa
$checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
$checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
$checkOrderItemsTableStmt->execute();

// Nếu bảng order_items chưa tồn tại, tạo bảng
if ($checkOrderItemsTableStmt->rowCount() == 0) {
    try {
        $createOrderItemsTableSql = "CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES hanghoa(idhanghoa) ON DELETE RESTRICT
        )";
        $conn->exec($createOrderItemsTableSql);
        // error_log("Đã tạo bảng order_items thành công!");
    } catch (PDOException $e) {
        // error_log("Lỗi khi tạo bảng order_items: " . $e->getMessage());
    }
}

// Kiểm tra lại xem bảng orders đã tồn tại chưa (sau khi có thể đã tạo)
$checkTableSql = "SHOW TABLES LIKE 'orders'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

if ($checkTableStmt->rowCount() == 0) {
    // Bảng orders chưa tồn tại, hiển thị thông báo
    $noOrdersTable = true;
} else {
    $noOrdersTable = false;

    // Kiểm tra xem có dữ liệu trong bảng orders không
    $countOrdersSql = "SELECT COUNT(*) as count FROM orders";
    $countOrdersStmt = $conn->prepare($countOrdersSql);
    $countOrdersStmt->execute();
    $countOrders = $countOrdersStmt->fetch(PDO::FETCH_ASSOC);

    // Nếu không có dữ liệu, thêm dữ liệu mẫu
    if ($countOrders['count'] == 0) {
        try {
            // Tạo mã đơn hàng
            $orderCode = 'ORD' . date('YmdHis');
            $totalAmount = 100000;
            $status = 'pending';
            $paymentMethod = 'bank_transfer';
            $paymentStatus = 'pending';
            $createdAt = date('Y-m-d H:i:s');

            $insertOrderSql = "INSERT INTO orders (order_code, total_amount, status, payment_method, payment_status, created_at)
                              VALUES (?, ?, ?, ?, ?, ?)";
            $insertOrderStmt = $conn->prepare($insertOrderSql);

            $insertOrderStmt->execute([$orderCode, $totalAmount, $status, $paymentMethod, $paymentStatus, $createdAt]);
            $orderId = $conn->lastInsertId();
            // Đã thêm đơn hàng mẫu

            // Kiểm tra xem bảng order_items đã tồn tại không
            $checkOrderItemsTableSql = "SHOW TABLES LIKE 'order_items'";
            $checkOrderItemsTableStmt = $conn->prepare($checkOrderItemsTableSql);
            $checkOrderItemsTableStmt->execute();

            if ($checkOrderItemsTableStmt->rowCount() > 0) {
                // Lấy một sản phẩm từ bảng hanghoa
                $getProductSql = "SELECT idhanghoa, giathamkhao FROM hanghoa LIMIT 1";
                $getProductStmt = $conn->prepare($getProductSql);
                $getProductStmt->execute();
                $product = $getProductStmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $insertOrderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, created_at)
                                         VALUES (?, ?, ?, ?, ?)";
                    $insertOrderItemStmt = $conn->prepare($insertOrderItemSql);

                    $productId = $product['idhanghoa'];
                    $quantity = 1;
                    $price = $product['giathamkhao'];

                    $insertOrderItemStmt->execute([$orderId, $productId, $quantity, $price, $createdAt]);
                    // Đã thêm chi tiết đơn hàng mẫu
                } else {
                    // Không tìm thấy sản phẩm nào trong bảng hanghoa để thêm vào đơn hàng
                }
            }
        } catch (PDOException $e) {
            // Lỗi khi thêm đơn hàng mẫu
        }
    }

    // Kiểm tra xem bảng order_items có tồn tại không
    $noOrderItemsTable = ($checkOrderItemsTableStmt->rowCount() == 0);

    // Xử lý hành động
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $action = $_GET['action'];
        $orderId = (int)$_GET['id'];

        switch ($action) {
            case 'approve':
                // Cập nhật trạng thái đơn hàng thành 'approved'
                $updateOrderSql = "UPDATE orders SET status = 'approved' WHERE id = ?";
                $updateOrderStmt = $conn->prepare($updateOrderSql);
                $updateOrderStmt->execute([$orderId]);

                // Kiểm tra xem bảng order_items có tồn tại không
                if (!$noOrderItemsTable) {
                    try {
                        // Lấy danh sách sản phẩm trong đơn hàng
                        $orderItemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
                        $orderItemsStmt = $conn->prepare($orderItemsSql);
                        $orderItemsStmt->execute([$orderId]);
                        $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        // Lỗi khi lấy sản phẩm trong đơn hàng
                        $orderItems = [];
                    }
                } else {
                    $orderItems = [];
                }

                // Không cần cập nhật số lượng tồn kho ở đây vì đã được cập nhật khi tạo đơn hàng
                // Đơn hàng đã được duyệt. Không cần cập nhật số lượng tồn kho vì đã cập nhật khi tạo đơn hàng.

                $_SESSION['order_message'] = 'Đơn hàng #' . $orderId . ' đã được duyệt thành công.';
                break;

            case 'cancel':
                // Cập nhật trạng thái đơn hàng thành 'cancelled'
                $updateOrderSql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
                $updateOrderStmt = $conn->prepare($updateOrderSql);
                $updateOrderStmt->execute([$orderId]);

                // Kiểm tra xem bảng order_items có tồn tại không
                if (!$noOrderItemsTable) {
                    try {
                        // Lấy danh sách sản phẩm trong đơn hàng
                        $orderItemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
                        $orderItemsStmt = $conn->prepare($orderItemsSql);
                        $orderItemsStmt->execute([$orderId]);
                        $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);

                        // Hoàn trả số lượng tồn kho cho từng sản phẩm
                        foreach ($orderItems as $item) {
                            $productId = $item['product_id'];
                            $quantity = $item['quantity'];

                            // Sử dụng hàm updateSoLuong với isIncrement = true để tăng số lượng
                            $tonkho->updateSoLuong($productId, $quantity, true);

                            // Ghi log
                            error_log("Đã hoàn trả tồn kho cho sản phẩm ID: " . $productId . ", tăng: " . $quantity);
                        }

                        $_SESSION['order_message'] = 'Đơn hàng #' . $orderId . ' đã được hủy và số lượng tồn kho đã được hoàn trả.';
                    } catch (PDOException $e) {
                        // Lỗi khi hoàn trả tồn kho
                        error_log("Lỗi khi hoàn trả tồn kho: " . $e->getMessage());
                        $_SESSION['order_message'] = 'Đơn hàng #' . $orderId . ' đã được hủy nhưng có lỗi khi hoàn trả tồn kho.';
                    }
                } else {
                    $_SESSION['order_message'] = 'Đơn hàng #' . $orderId . ' đã được hủy.';
                }
                break;

            case 'view':
                // Lấy thông tin chi tiết đơn hàng
                $orderSql = "SELECT * FROM orders WHERE id = ?";
                $orderStmt = $conn->prepare($orderSql);
                $orderStmt->execute([$orderId]);
                $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

                if ($order) {
                    // Kiểm tra xem bảng order_items có tồn tại không
                    if (!$noOrderItemsTable) {
                        try {
                            // Lấy danh sách sản phẩm trong đơn hàng
                            $orderItemsSql = "SELECT oi.*, h.tenhanghoa
                                             FROM order_items oi
                                             JOIN hanghoa h ON oi.product_id = h.idhanghoa
                                             WHERE oi.order_id = ?";
                            $orderItemsStmt = $conn->prepare($orderItemsSql);
                            $orderItemsStmt->execute([$orderId]);
                            $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            // Lỗi khi lấy chi tiết đơn hàng
                            $orderItems = [];
                        }
                    } else {
                        $orderItems = [];
                    }

                    // Hiển thị chi tiết đơn hàng
                    $viewOrderDetail = true;
                } else {
                    $_SESSION['order_message'] = 'Không tìm thấy đơn hàng #' . $orderId . '.';
                    $viewOrderDetail = false;
                }
                break;
        }

        if ($action != 'view') {
            // Sử dụng JavaScript để chuyển hướng thay vì header()
            echo '<script>window.location.href = "index.php?req=orders";</script>';
            exit();
        }
    }

    // Lấy danh sách đơn hàng
    try {
        // Kiểm tra xem cột user_id có tồn tại trong bảng orders không
        $checkUserIdColumnSql = "SHOW COLUMNS FROM orders LIKE 'user_id'";
        $checkUserIdColumnStmt = $conn->prepare($checkUserIdColumnSql);
        $checkUserIdColumnStmt->execute();
        $hasUserIdColumn = ($checkUserIdColumnStmt->rowCount() > 0);

        // Kiểm tra xem bảng user có tồn tại không
        $checkUserTableSql = "SHOW TABLES LIKE 'user'";
        $checkUserTableStmt = $conn->prepare($checkUserTableSql);
        $checkUserTableStmt->execute();
        $hasUserTable = ($checkUserTableStmt->rowCount() > 0);

        // Kiểm tra các cột trong bảng orders
        $columnsQuery = "SHOW COLUMNS FROM orders";
        $columnsStmt = $conn->prepare($columnsQuery);
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

        error_log("Các cột trong bảng orders: " . implode(", ", $columns));

        // Xây dựng truy vấn dựa trên các cột có sẵn
        $selectColumns = "id, order_code, user_id";

        // Thêm các cột tùy chọn nếu chúng tồn tại
        if (in_array('shipping_address', $columns)) {
            $selectColumns .= ", shipping_address";
        }

        if (in_array('total_amount', $columns)) {
            $selectColumns .= ", total_amount";
        } else {
            $selectColumns .= ", 0 as total_amount";
        }

        if (in_array('status', $columns)) {
            $selectColumns .= ", status";
        } else {
            $selectColumns .= ", 'pending' as status";
        }

        if (in_array('payment_method', $columns)) {
            $selectColumns .= ", payment_method";
        } else {
            $selectColumns .= ", 'bank_transfer' as payment_method";
        }

        if (in_array('payment_status', $columns)) {
            $selectColumns .= ", payment_status";
        }

        if (in_array('created_at', $columns)) {
            $selectColumns .= ", created_at";
        }

        if (in_array('updated_at', $columns)) {
            $selectColumns .= ", updated_at";
        }

        // Truy vấn an toàn
        $ordersSql = "SELECT $selectColumns FROM orders ORDER BY created_at DESC";
        error_log("SQL query: " . $ordersSql);

        $ordersStmt = $conn->prepare($ordersSql);
        $ordersStmt->execute();

        $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Nếu có cột user_id và bảng user tồn tại, thực hiện JOIN riêng để lấy thông tin người dùng
        if ($hasUserIdColumn && $hasUserTable && count($orders) > 0) {
            foreach ($orders as $key => $order) {
                if (!empty($order['user_id'])) {
                    $userSql = "SELECT hoten FROM user WHERE username = ?";
                    $userStmt = $conn->prepare($userSql);
                    $userStmt->execute([$order['user_id']]);
                    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $orders[$key]['hoten'] = $user['hoten'];
                    }
                }
            }
        }

        // Số lượng đơn hàng: count($orders)
    } catch (PDOException $e) {
        // Lỗi khi lấy danh sách đơn hàng
        $orders = [];
    }
}
?>

<div class="admin-title">Quản lý đơn hàng</div>
<hr>

<!-- Thêm Bootstrap CSS nếu chưa có -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<?php
// Phần hiển thị lỗi PHP và thông tin debug đã được xóa
?>

<?php if (isset($_SESSION['order_message'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['order_message']; ?>
    </div>
    <?php unset($_SESSION['order_message']); ?>
<?php endif; ?>

<?php if ($noOrdersTable): ?>
    <div class="alert alert-warning">
        <p>Chưa có bảng đơn hàng trong cơ sở dữ liệu. Bảng sẽ được tạo tự động khi có đơn hàng đầu tiên.</p>
    </div>
<?php elseif (isset($viewOrderDetail) && $viewOrderDetail): ?>
    <!-- Chi tiết đơn hàng -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #<?php echo $order['id']; ?></h5>
            <a href="index.php?req=orders" class="btn btn-light btn-sm">Quay lại</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Thông tin đơn hàng</h6>
                    <p><strong>Mã đơn hàng:</strong> <?php echo $order['order_code']; ?></p>
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Trạng thái:</strong>
                        <?php
                        switch ($order['status']) {
                            case 'pending':
                                echo '<span class="badge badge-warning">Chờ xác nhận</span>';
                                break;
                            case 'approved':
                                echo '<span class="badge badge-success">Đã duyệt</span>';
                                break;
                            case 'cancelled':
                                echo '<span class="badge badge-danger">Đã hủy</span>';
                                break;
                            default:
                                echo '<span class="badge badge-secondary">Không xác định</span>';
                        }
                        ?>
                    </p>
                    <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method'] == 'bank_transfer' ? 'Chuyển khoản ngân hàng' : $order['payment_method']; ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Thông tin khách hàng</h6>
                    <?php if (isset($order['user_id']) && !empty($order['user_id'])): ?>
                        <p><strong>Tài khoản:</strong> <?php echo $order['user_id']; ?></p>
                    <?php else: ?>
                        <p><strong>Khách hàng:</strong> Khách vãng lai</p>
                    <?php endif; ?>

                    <!-- Hiển thị địa chỉ giao hàng -->
                    <?php if (isset($order['shipping_address']) && !empty($order['shipping_address'])): ?>
                        <div class="mt-3">
                            <p><strong>Địa chỉ giao hàng:</strong></p>
                            <div class="p-2 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <h6>Danh sách sản phẩm</h6>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['tenhanghoa']); ?></td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?> ₫</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> ₫</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Tổng tiền:</strong></td>
                        <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</strong></td>
                    </tr>
                </tfoot>
            </table>

            <?php if ($order['status'] == 'pending'): ?>
                <div class="mt-4">
                    <a href="index.php?req=orders&action=approve&id=<?php echo $order['id']; ?>" class="btn btn-success" onclick="return confirm('Xác nhận duyệt đơn hàng này?');">Duyệt đơn hàng</a>
                    <a href="index.php?req=orders&action=cancel&id=<?php echo $order['id']; ?>" class="btn btn-danger" onclick="return confirm('Xác nhận hủy đơn hàng này? Số lượng tồn kho sẽ được hoàn trả.');">Hủy đơn hàng</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Danh sách đơn hàng -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Danh sách đơn hàng</h5>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    <p>Chưa có đơn hàng nào.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Địa chỉ</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo $order['order_code']; ?></td>
                                    <td><?php
                                        if (isset($order['user_id']) && !empty($order['user_id'])) {
                                            echo isset($order['hoten']) && !empty($order['hoten']) ? $order['hoten'] : $order['user_id'];
                                        } else {
                                            echo 'Khách vãng lai';
                                        }
                                        ?></td>
                                    <td>
                                        <?php if (isset($order['shipping_address']) && !empty($order['shipping_address'])): ?>
                                            <?php
                                                // Hiển thị tối đa 30 ký tự đầu tiên của địa chỉ
                                                $shortAddress = mb_substr(htmlspecialchars($order['shipping_address']), 0, 30);
                                                if (mb_strlen($order['shipping_address']) > 30) {
                                                    $shortAddress .= '...';
                                                }
                                                echo $shortAddress;
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                                    <td>
                                        <?php
                                        switch ($order['status']) {
                                            case 'pending':
                                                echo '<span class="badge badge-warning">Chờ xác nhận</span>';
                                                break;
                                            case 'approved':
                                                echo '<span class="badge badge-success">Đã duyệt</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="badge badge-danger">Đã hủy</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Không xác định</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="index.php?req=orders&action=view&id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Xem</a>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <a href="index.php?req=orders&action=approve&id=<?php echo $order['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Xác nhận duyệt đơn hàng này?');">Duyệt</a>
                                            <a href="index.php?req=orders&action=cancel&id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận hủy đơn hàng này? Số lượng tồn kho sẽ được hoàn trả.');">Hủy</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
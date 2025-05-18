<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['ADMIN'])) {
    header('Location: ../../userLogin.php');
    exit();
}

require_once './elements_LQA/mod/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra xem bảng payment_config đã tồn tại chưa
$checkTableSql = "SHOW TABLES LIKE 'payment_config'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

if ($checkTableStmt->rowCount() == 0) {
    // Bảng chưa tồn tại, tạo bảng payment_config
    $createTableSql = "CREATE TABLE payment_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bank_name VARCHAR(100) NOT NULL,
        account_number VARCHAR(50) NOT NULL,
        account_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($createTableSql);
}

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankName = $_POST['bank_name'];
    $accountNumber = $_POST['account_number'];
    $accountName = $_POST['account_name'];

    // Kiểm tra xem đã có cấu hình thanh toán chưa
    $checkConfigSql = "SELECT COUNT(*) FROM payment_config";
    $checkConfigStmt = $conn->prepare($checkConfigSql);
    $checkConfigStmt->execute();
    $configCount = $checkConfigStmt->fetchColumn();

    if ($configCount > 0) {
        // Đã có cấu hình, cập nhật
        $updateSql = "UPDATE payment_config SET bank_name = ?, account_number = ?, account_name = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$bankName, $accountNumber, $accountName]);
    } else {
        // Chưa có cấu hình, thêm mới
        $insertSql = "INSERT INTO payment_config (bank_name, account_number, account_name) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->execute([$bankName, $accountNumber, $accountName]);
    }

    // Lưu thông báo thành công
    $_SESSION['payment_config_success'] = true;

    // Chuyển hướng để tránh gửi lại form khi refresh
    header('Location: index.php?req=payment_config');
    exit();
}

// Lấy thông tin cấu hình thanh toán hiện tại
$configSql = "SELECT * FROM payment_config LIMIT 1";
$configStmt = $conn->prepare($configSql);
$configStmt->execute();
$config = $configStmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="admin-title">Cấu hình thanh toán</div>
<hr>

<?php if (isset($_SESSION['payment_config_success'])): ?>
<div class="alert alert-success">
    Cấu hình thanh toán đã được cập nhật thành công.
</div>
<?php unset($_SESSION['payment_config_success']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Cấu hình tài khoản ngân hàng</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="bank_name" class="form-label">Tên ngân hàng</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name"
                    value="<?php echo $config ? htmlspecialchars($config['bank_name']) : ''; ?>" required>
                <div class="form-text">Nhập tên ngân hàng (VD: VIETCOMBANK, AGRIBANK, TECHCOMBANK, ...)</div>
            </div>
            <div class="mb-3">
                <label for="account_number" class="form-label">Số tài khoản</label>
                <input type="text" class="form-control" id="account_number" name="account_number"
                    value="<?php echo $config ? htmlspecialchars($config['account_number']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="account_name" class="form-label">Tên chủ tài khoản</label>
                <input type="text" class="form-control" id="account_name" name="account_name"
                    value="<?php echo $config ? htmlspecialchars($config['account_name']) : ''; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Hướng dẫn cấu hình</h5>
    </div>
    <div class="card-body">
        <p>Để cấu hình thanh toán qua VietQR, bạn cần nhập đúng thông tin tài khoản ngân hàng:</p>
        <ul>
            <li><strong>Tên ngân hàng:</strong> Nhập chính xác tên ngân hàng (VD: VIETCOMBANK, AGRIBANK, TECHCOMBANK,
                ...)</li>
            <li><strong>Số tài khoản:</strong> Nhập số tài khoản ngân hàng của bạn</li>
            <li><strong>Tên chủ tài khoản:</strong> Nhập tên chủ tài khoản ngân hàng</li>
        </ul>
        <p>Sau khi cấu hình, hệ thống sẽ tự động tạo mã QR VietQR cho khách hàng khi thanh toán.</p>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Quản lý đơn hàng</h5>
    </div>
    <div class="card-body">
        <p>Bạn có thể quản lý các đơn hàng và xác nhận thanh toán tại <a href="index.php?req=orders">Quản lý đơn
                hàng</a>.</p>
    </div>
</div>
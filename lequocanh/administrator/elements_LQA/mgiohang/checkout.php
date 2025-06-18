<?php
session_start();
require_once '../../elements_LQA/mod/giohangCls.php';
require_once '../../elements_LQA/mod/hanghoaCls.php';
require_once '../../elements_LQA/mod/mtonkhoCls.php';
require_once '../../elements_LQA/mod/database.php';

$giohang = new GioHang();

// Kiểm tra xem người dùng có thể sử dụng giỏ hàng không
if (!$giohang->canUseCart()) {
    if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
        // Lưu URL hiện tại để chuyển hướng lại sau khi đăng nhập
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ../../userLogin.php');
    } else {
        // Nếu là admin, chuyển hướng về trang quản trị
        header('Location: ../../index.php');
    }
    exit();
}

// Kiểm tra xem có dữ liệu sản phẩm được gửi từ form không
if (!isset($_POST['selected_products']) || empty($_POST['selected_products'])) {
    // Nếu không có sản phẩm được chọn, chuyển hướng về trang giỏ hàng
    header('Location: giohangView.php');
    exit();
}

// Lấy dữ liệu sản phẩm từ form
$selectedProducts = json_decode($_POST['selected_products'], true);

// Khởi tạo các đối tượng
$giohang = new GioHang();
$hanghoa = new hanghoa();
$tonkho = new MTonKho();

// Lấy thông tin người dùng nếu đã đăng nhập
$userAddress = '';
if (isset($_SESSION['USER'])) {
    require_once '../../elements_LQA/mod/userCls.php';
    $userObj = new user();
    $currentUser = $userObj->UserGetbyUsername($_SESSION['USER']);
    if ($currentUser && !empty($currentUser->diachi)) {
        $userAddress = $currentUser->diachi;
    }
}

// Lấy thông tin chi tiết của các sản phẩm đã chọn
$orderDetails = [];
$totalAmount = 0;

foreach ($selectedProducts as $product) {
    $productId = $product['productId'];
    $quantity = $product['quantity'];

    // Lấy thông tin sản phẩm
    $productInfo = $hanghoa->HanghoaGetbyId($productId);

    // Kiểm tra tồn kho
    $tonkhoInfo = $tonkho->getTonKhoByIdHangHoa($productId);

    if (!$productInfo) {
        // Nếu không tìm thấy sản phẩm, bỏ qua
        continue;
    }

    if (!$tonkhoInfo || $tonkhoInfo->soLuong < $quantity) {
        // Nếu không đủ hàng, hiển thị thông báo lỗi
        $_SESSION['checkout_error'] = 'Sản phẩm "' . $productInfo->tenhanghoa . '" không đủ số lượng trong kho.';
        header('Location: giohangView.php');
        exit();
    }

    // Lấy thông tin hình ảnh
    $hinhanh = $hanghoa->GetHinhAnhById($productInfo->hinhanh);
    $imageSrc = "";

    if ($hinhanh && !empty($hinhanh->duong_dan)) {
        $imageSrc = "../../elements_LQA/mhanghoa/displayImage.php?id=" . $productInfo->hinhanh;
    } else {
        $imageSrc = "../../elements_LQA/img_LQA/no-image.png";
    }

    // Tính tổng tiền cho sản phẩm
    $subtotal = $productInfo->giathamkhao * $quantity;
    $totalAmount += $subtotal;

    // Thêm vào danh sách sản phẩm đã chọn
    $orderDetails[] = [
        'id' => $productId,
        'name' => $productInfo->tenhanghoa,
        'price' => $productInfo->giathamkhao,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'image' => $imageSrc
    ];
}

// Lưu thông tin đơn hàng vào session để sử dụng sau khi thanh toán
$_SESSION['order_details'] = $orderDetails;
$_SESSION['total_amount'] = $totalAmount;

// Lấy thông tin cấu hình thanh toán từ cơ sở dữ liệu
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra xem bảng cau_hinh_thanh_toan đã tồn tại chưa
$checkTableSql = "SHOW TABLES LIKE 'cau_hinh_thanh_toan'";
$checkTableStmt = $conn->prepare($checkTableSql);
$checkTableStmt->execute();

$paymentConfig = [
    'ten_ngan_hang' => '',
    'so_tai_khoan' => '',
    'ten_tai_khoan' => ''
];

if ($checkTableStmt->rowCount() > 0) {
    // Bảng đã tồn tại, lấy thông tin cấu hình
    $configSql = "SELECT * FROM cau_hinh_thanh_toan LIMIT 1";
    $configStmt = $conn->prepare($configSql);
    $configStmt->execute();

    if ($configStmt->rowCount() > 0) {
        $config = $configStmt->fetch(PDO::FETCH_ASSOC);
        // Map tên cột mới sang tên cũ để tương thích với code hiển thị
        $paymentConfig = [
            'bank_name' => $config['ten_ngan_hang'],
            'account_number' => $config['so_tai_khoan'],
            'account_name' => $config['ten_tai_khoan']
        ];
    }
}

// Tạo mã đơn hàng ngẫu nhiên
$orderCode = 'ORDER' . time() . rand(1000, 9999);
$_SESSION['order_code'] = $orderCode;

// Tạo nội dung chuyển khoản
$transferContent = $orderCode;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../public_files/mycss.css">
    <style>
    .checkout-container {
        max-width: 1200px;
        margin: 20px auto;
        background: #fff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }

    .payment-methods {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    .payment-method {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .payment-method.active {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }

    .payment-method img {
        height: 40px;
        margin-bottom: 10px;
    }

    .qr-container {
        text-align: center;
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        background-color: #f8f9fa;
    }

    .qr-code {
        max-width: 300px;
        margin: 0 auto;
    }

    .bank-info {
        margin-top: 20px;
        padding: 15px;
        background-color: #e9ecef;
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <div class="checkout-container">
        <h2 class="mb-4">Thanh toán đơn hàng</h2>

        <!-- Thông tin địa chỉ giao hàng -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Địa chỉ giao hàng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="shipping-address" class="form-label">Địa chỉ nhận hàng</label>
                    <textarea class="form-control" id="shipping-address" rows="3"
                        placeholder="Nhập địa chỉ giao hàng"><?php echo htmlspecialchars($userAddress); ?></textarea>
                    <div class="form-text">Vui lòng nhập địa chỉ đầy đủ để chúng tôi giao hàng đến bạn.</div>
                </div>
            </div>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Mã đơn hàng:</strong> <?php echo $orderCode; ?></p>
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
                        <?php foreach ($orderDetails as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $item['image']; ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image me-3">
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?> ₫</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> ₫</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng tiền:</strong></td>
                            <td><strong><?php echo number_format($totalAmount, 0, ',', '.'); ?> ₫</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Phương thức thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="payment-methods">
                    <div class="payment-method active" id="bank-transfer">
                        <i class="fas fa-university" style="font-size: 2rem; color: #0d6efd; margin-bottom: 10px;"></i>
                        <h5>Chuyển khoản ngân hàng</h5>
                        <p class="text-muted">Quét mã QR để thanh toán qua ứng dụng ngân hàng</p>
                    </div>
                </div>

                <!-- Thông tin thanh toán qua VietQR -->
                <div class="qr-container" id="bank-transfer-details">
                    <?php if (!empty($paymentConfig['account_number']) && !empty($paymentConfig['bank_name'])): ?>
                    <h5>Quét mã QR để thanh toán</h5>
                    <div class="qr-code">
                        <?php
                            // Tạo URL VietQR
                            $bankCode = ''; // Mã ngân hàng, cần cập nhật theo ngân hàng thực tế
                            $amount = $totalAmount;
                            $description = $transferContent;

                            // Xác định mã ngân hàng dựa trên tên ngân hàng
                            switch (strtoupper($paymentConfig['bank_name'])) {
                                case 'VIETCOMBANK':
                                case 'VCB':
                                    $bankCode = 'VCB';
                                    break;
                                case 'AGRIBANK':
                                    $bankCode = 'AGR';
                                    break;
                                case 'VIETINBANK':
                                case 'VIETTINBANK':
                                    $bankCode = 'ICB';
                                    break;
                                case 'BIDV':
                                    $bankCode = 'BIDV';
                                    break;
                                case 'TECHCOMBANK':
                                case 'TCB':
                                    $bankCode = 'TCB';
                                    break;
                                case 'MB':
                                case 'MBB':
                                    $bankCode = 'MB';
                                    break;
                                case 'ACB':
                                    $bankCode = 'ACB';
                                    break;
                                case 'TPB':
                                case 'TPBANK':
                                    $bankCode = 'TPB';
                                    break;
                                default:
                                    $bankCode = '';
                            }

                            // Tạo URL VietQR - Sử dụng mã ngân hàng mặc định nếu không xác định được
                            if (empty($bankCode)) {
                                $bankCode = 'TCB'; // Mặc định là Techcombank nếu không xác định được
                            }

                            // Đảm bảo các tham số được mã hóa đúng cách
                            $encodedAccountName = urlencode($paymentConfig['account_name']);
                            $encodedDescription = urlencode($description);

                            // Tạo URL VietQR
                            $vietQrUrl = "https://img.vietqr.io/image/{$bankCode}-{$paymentConfig['account_number']}-compact.png?amount={$amount}&addInfo={$encodedDescription}&accountName={$encodedAccountName}";

                            // Debug
                            error_log("VietQR URL: " . $vietQrUrl);
                            ?>
                        <img src="<?php echo $vietQrUrl; ?>" alt="QR Code" class="img-fluid">
                    </div>
                    <div class="bank-info mt-3">
                        <p><strong>Ngân hàng:</strong> <?php echo htmlspecialchars($paymentConfig['bank_name']); ?></p>
                        <p><strong>Số tài khoản:</strong>
                            <?php echo htmlspecialchars($paymentConfig['account_number']); ?></p>
                        <p><strong>Chủ tài khoản:</strong>
                            <?php echo htmlspecialchars($paymentConfig['account_name']); ?></p>
                        <p><strong>Nội dung chuyển khoản:</strong> <?php echo $transferContent; ?></p>
                    </div>
                    <div class="alert alert-info mt-3">
                        <p>Sau khi thanh toán, vui lòng nhấn nút "Xác nhận đã thanh toán" bên dưới.</p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <p>Chưa có thông tin tài khoản ngân hàng. Vui lòng liên hệ quản trị viên để cập nhật.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Nút xác nhận thanh toán -->
        <div class="d-flex justify-content-between">
            <a href="giohangView.php" class="btn btn-secondary">Quay lại giỏ hàng</a>
            <button id="confirmPaymentBtn" class="btn btn-primary">Xác nhận đã thanh toán</button>
        </div>

        <!-- Thông báo đang xử lý -->
        <div id="processingPayment" class="mt-3 text-center" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang xử lý...</span>
            </div>
            <p class="mt-2">Đang xử lý thanh toán, vui lòng đợi...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
        const processingPayment = document.getElementById('processingPayment');

        confirmPaymentBtn.addEventListener('click', function() {
            // Hiển thị thông báo đang xử lý
            confirmPaymentBtn.disabled = true;
            processingPayment.style.display = 'block';

            // Lấy địa chỉ giao hàng
            const shippingAddress = document.getElementById('shipping-address').value.trim();

            // Kiểm tra địa chỉ giao hàng
            if (!shippingAddress) {
                alert('Vui lòng nhập địa chỉ giao hàng');
                confirmPaymentBtn.disabled = false;
                processingPayment.style.display = 'none';
                return;
            }

            // Tạo form data
            const formData = new FormData();
            formData.append('order_code', '<?php echo $orderCode; ?>');
            formData.append('shipping_address', shippingAddress);

            // Gửi request bằng fetch API
            fetch('payment_confirm.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.redirected) {
                        // Nếu server chuyển hướng, theo URL đó
                        window.location.href = response.url;
                    } else {
                        // Nếu không có chuyển hướng, đọc response
                        return response.text().then(text => {
                            // Kiểm tra nếu response chứa URL chuyển hướng
                            if (text.includes('order_success.php')) {
                                // Trích xuất order_id từ text
                                const match = text.match(
                                    /order_success\.php\?order_id=(\d+)/);
                                if (match && match[1]) {
                                    window.location.href = 'order_success.php?order_id=' +
                                        match[1];
                                } else {
                                    window.location.href = 'giohangView.php';
                                }
                            } else {
                                // Nếu không tìm thấy URL, chuyển về trang giỏ hàng
                                window.location.href = 'giohangView.php';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi xử lý thanh toán. Vui lòng thử lại.');
                    confirmPaymentBtn.disabled = false;
                    processingPayment.style.display = 'none';
                });
        });
    });
    </script>
</body>

</html>
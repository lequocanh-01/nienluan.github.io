<?php
session_start();
require_once '../../elements_LQA/mod/giohangCls.php';
require_once '../../elements_LQA/mod/hanghoaCls.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
    header('Location: ../../userLogin.php');
    exit();
}

$giohang = new GioHang();
$cart = $giohang->getCart();
$cartDetails = [];
$totalAmount = 0;

if (!empty($cart)) {
    foreach ($cart as $item) {
        if (isset($item['product_id'], $item['tenhanghoa'], $item['giathamkhao'], $item['quantity'], $item['hinhanh'])) {
            $cartDetails[] = [
                'id' => $item['product_id'],
                'name' => $item['tenhanghoa'],
                'price' => $item['giathamkhao'],
                'quantity' => $item['quantity'],
                'hinhanh' => $item['hinhanh'],
                'subtotal' => $item['giathamkhao'] * $item['quantity']
            ];
            $totalAmount += $item['giathamkhao'] * $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/lequocanh/public_files/mycss.css">
</head>

<body>
    <div class="cart-container">
        <?php if (empty($cartDetails)): ?>
        <div class="text-center py-5">
            <h3 class="mb-4">Giỏ hàng của bạn đang trống</h3>
            <a href="<?php echo isset($_SESSION['ADMIN']) ? '../../index.php' : '../../../index.php'; ?>"
                class="btn btn-primary btn-lg">
                Tiếp tục mua hàng
            </a>
        </div>
        <?php else: ?>
        <h2 class="mb-4">Giỏ hàng của bạn</h2>
        <table class="cart-table">
            <thead>
                <tr>
                    <th width="5%">
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th width="45%">Sản phẩm</th>
                    <th width="15%">Đơn giá</th>
                    <th width="20%">Số lượng</th>
                    <th width="15%">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartDetails as $item): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input product-select">
                    </td>
                    <td class="product-info">
                        <img src="data:image/jpeg;base64,<?php echo $item['hinhanh']; ?>"
                            alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                        <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    </td>
                    <td class="price" data-price="<?php echo $item['price']; ?>">
                        <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫
                    </td>
                    <td>
                        <div class="quantity-controls">
                            <button class="btn btn-outline-secondary decrease-quantity" type="button">−</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1"
                                data-product-id="<?php echo $item['id']; ?>">
                            <button class="btn btn-outline-secondary increase-quantity" type="button">+</button>
                        </div>
                    </td>
                    <td class="subtotal">
                        <?php echo number_format($item['subtotal'], 0, ',', '.'); ?> ₫
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="left-actions">
                    <input type="checkbox" id="select-all-bottom" class="form-check-input me-2">
                    <button onclick="deleteSelectedItems()" class="btn btn-outline-danger ms-3">
                        Xóa đã chọn
                    </button>
                    <a href="<?php echo isset($_SESSION['ADMIN']) ? '../../index.php' : '../../../index.php'; ?>"
                        class="btn btn-outline-primary ms-3">
                        <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua hàng
                    </a>
                </div>

                <div class="right-actions d-flex align-items-center">
                    <div class="total-section me-4">
                        <span class="me-2">Tổng tiền:</span>
                        <span class="total-amount fw-bold text-danger fs-4">
                            <?php echo number_format($totalAmount, 0, ',', '.'); ?> ₫
                        </span>
                    </div>
                    <button onclick="proceedToCheckout()" class="btn btn-primary btn-lg">
                        Mua hàng
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="../../../public_files/js_LQAjscript.js"></script>
</body>

</html>
<?php
session_start();
require_once '../../elements_LQA/mod/giohangCls.php';
require_once '../../elements_LQA/mod/hanghoaCls.php';

// Remove login requirement to allow viewing cart with session ID
// Users can have items in cart linked to their session without being logged in

// Enable error reporting can be kept for development
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$giohang = new GioHang();
$hanghoa = new hanghoa();

// Get cart items
$cart = $giohang->getCart();
$cartDetails = [];
$totalAmount = 0;

if (!empty($cart)) {
    foreach ($cart as $item) {
        if (isset($item['product_id'])) {
            $cartDetails[] = [
                'id' => $item['product_id'],
                'name' => $item['tenhanghoa'] ?? 'Unknown Product',
                'price' => $item['giathamkhao'] ?? 0,
                'quantity' => $item['quantity'],
                'hinhanh' => $item['hinhanh'] ?? null,
                'subtotal' => ($item['giathamkhao'] ?? 0) * $item['quantity']
            ];
            $totalAmount += ($item['giathamkhao'] ?? 0) * $item['quantity'];
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
    <link rel="stylesheet" href="../../public_files/mycss.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .cart-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            color: #495057;
            font-weight: 600;
        }

        .cart-table td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .product-info {
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .product-name {
            font-weight: 500;
            margin-left: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px;
        }

        .decrease-quantity,
        .increase-quantity {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border: 1px solid #dee2e6;
        }

        .price,
        .total-price {
            font-weight: 500;
            color: #ee4d2d;
        }

        .delete-item {
            padding: 4px 12px;
            font-size: 14px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .cart-footer {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 15px;
            border-top: 1px solid #dee2e6;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .right-actions {
            min-width: 400px;
            justify-content: flex-end;
        }

        .total-amount {
            color: #dc3545;
            font-size: 1.25rem;
        }

        .btn-primary {
            min-width: 150px;
            font-weight: 500;
        }

        .form-check-input {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .form-check-input:checked {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }

        .empty-cart {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-cart img {
            width: 150px;
            margin-bottom: 25px;
            opacity: 0.7;
        }

        .empty-cart h5 {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
        }

        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            border-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(149, 165, 166, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .cart-container {
                padding: 15px;
            }

            .product-image {
                width: 80px;
                height: 80px;
            }

            .cart-footer {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }
    </style>
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
                                <?php
                                // Kiểm tra xem hinhanh có phải là ID hợp lệ không
                                if (isset($item['hinhanh']) && is_numeric($item['hinhanh']) && $item['hinhanh'] > 0) {
                                    // Sử dụng displayImage.php để hiển thị hình ảnh theo ID
                                    $imageSrc = "displayImage.php?id=" . $item['hinhanh'];
                                } else {
                                    // Nếu không có hình ảnh hợp lệ, sử dụng ngay hình ảnh mặc định
                                    $imageSrc = "../../img_LQA/no-image.png";
                                }
                                ?>
                                <img src="<?php echo $imageSrc; ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="product-image"
                                    onerror="this.onerror=null; this.src='../../img_LQA/no-image.png';">
                                <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            </td>
                            <td class="price" data-price="<?php echo $item['price']; ?>">
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫
                            </td>
                            <td>
                                <div class="quantity-controls">
                                    <button class="btn btn-outline-secondary decrease-quantity" type="button">−</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>"
                                        min="1" data-product-id="<?php echo $item['id']; ?>">
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

    <script>
        // Thêm hàm xử lý mua hàng
        function proceedToCheckout() {
            // Lấy danh sách sản phẩm được chọn
            const selectedProducts = [];
            document.querySelectorAll('.product-select:checked').forEach(checkbox => {
                const row = checkbox.closest('tr');
                const productId = row.querySelector('.quantity-input').dataset.productId;
                const quantity = parseInt(row.querySelector('.quantity-input').value);
                selectedProducts.push({
                    productId: productId,
                    quantity: quantity
                });
            });

            if (selectedProducts.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm để mua');
                return;
            }

            // Chuyển đến trang thanh toán với các sản phẩm đã chọn
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';

            // Thêm input ẩn chứa dữ liệu sản phẩm
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products';
            input.value = JSON.stringify(selectedProducts);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        // Cập nhật các hàm hiện có
        function updateTotalPrice() {
            let total = 0;
            document.querySelectorAll('.cart-table tbody tr').forEach(row => {
                if (row.querySelector('.product-select').checked) {
                    const subtotal = parseInt(row.querySelector('.subtotal').textContent.replace(/[^\d]/g, ''));
                    total += subtotal;
                }
            });
            document.querySelector('.total-amount').textContent =
                new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
        }

        // Thêm sự kiện cho checkboxes
        document.querySelectorAll('.product-select, #select-all, #select-all-bottom').forEach(checkbox => {
            checkbox.addEventListener('change', updateTotalPrice);
        });

        // Xử lý nút tăng giảm số lượng
        document.querySelectorAll('.quantity-controls').forEach(control => {
            const decreaseBtn = control.querySelector('.decrease-quantity');
            const increaseBtn = control.querySelector('.increase-quantity');
            const input = control.querySelector('.quantity-input');
            const productId = input.dataset.productId;

            decreaseBtn.addEventListener('click', () => updateQuantity(productId, -1, input));
            increaseBtn.addEventListener('click', () => updateQuantity(productId, 1, input));

            input.addEventListener('change', () => {
                let value = parseInt(input.value);
                if (value < 1) value = 1;
                input.value = value;
                updateQuantity(productId, 0, input);
            });
        });

        // Hàm cập nhật số lượng
        async function updateQuantity(productId, change, input) {
            const currentValue = parseInt(input.value);
            let newValue = change === 0 ? currentValue : currentValue + change;
            if (newValue < 1) newValue = 1;

            try {
                const response = await fetch('giohangUpdate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        productId: productId,
                        quantity: newValue
                    })
                });

                const data = await response.json();
                if (data.success) {
                    input.value = newValue;
                    const row = input.closest('tr');
                    const price = parseInt(row.querySelector('.price').dataset.price);
                    const subtotal = price * newValue;
                    row.querySelector('.subtotal').textContent =
                        new Intl.NumberFormat('vi-VN').format(subtotal) + ' ₫';
                    updateTotalPrice();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Thêm hàm xóa sản phẩm đã chọn
        async function deleteSelectedItems() {
            const selectedProducts = [];
            document.querySelectorAll('.product-select:checked').forEach(checkbox => {
                const row = checkbox.closest('tr');
                const productId = row.querySelector('.quantity-input').dataset.productId;
                selectedProducts.push(productId);
            });

            if (selectedProducts.length === 0) {
                alert('Vui lòng chọn sản phẩm để xóa');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn xóa các sản phẩm đã chọn?')) {
                try {
                    const response = await fetch('giohangAct.php?action=removeSelected', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            productIds: selectedProducts
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        // Xóa các hàng đã chọn khỏi bảng
                        selectedProducts.forEach(productId => {
                            const row = document.querySelector(`input[data-product-id="${productId}"]`).closest('tr');
                            row.remove();
                        });
                        // Cập nhật tổng tiền
                        updateTotalPrice();
                        // Bỏ chọn checkbox "Chọn tất cả"
                        document.querySelectorAll('#select-all, #select-all-bottom').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                    } else {
                        alert('Có lỗi xảy ra khi xóa sản phẩm!');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa sản phẩm!');
                }
            }
        }

        // Thêm xử lý cho checkbox "Chọn tất cả"
        document.querySelectorAll('#select-all, #select-all-bottom').forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                // Đồng bộ trạng thái của cả hai checkbox "Chọn tất cả"
                document.querySelectorAll('#select-all, #select-all-bottom').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                // Cập nhật trạng thái của tất cả các checkbox sản phẩm
                document.querySelectorAll('.product-select').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                // Cập nhật tổng tiền
                updateTotalPrice();
            });
        });

        // Thêm xử lý cho các checkbox sản phẩm
        document.querySelectorAll('.product-select').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Kiểm tra xem tất cả các checkbox sản phẩm có được chọn không
                const allChecked = Array.from(document.querySelectorAll('.product-select'))
                    .every(cb => cb.checked);
                // Cập nhật trạng thái của các checkbox "Chọn tất cả"
                document.querySelectorAll('#select-all, #select-all-bottom').forEach(selectAll => {
                    selectAll.checked = allChecked;
                });
                // Cập nhật tổng tiền
                updateTotalPrice();
            });
        });
    </script>
</body>

</html>
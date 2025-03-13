<?php
if (!isset($_SESSION['ADMIN'])) {
    header('Location: ../../userLogin.php');
    exit();
}

require_once './elements_LQA/mod/giohangCls.php';
require_once './elements_LQA/mod/userCls.php';

$giohang = new GioHang();
$user = new user();
$users = $user->UserGetAll();

$totalCartsValue = 0;
$activeCartsCount = 0;
$totalItemsCount = 0;

foreach ($users as $u) {
    if ($u->username !== 'admin') {
        $cart = $giohang->getCartByUserId($u->username);
        if (!empty($cart)) {
            $activeCartsCount++;
            foreach ($cart as $item) {
                $totalCartsValue += $item['giathamkhao'] * $item['quantity'];
                $totalItemsCount += $item['quantity'];
            }
        }
    }
}
?>

<div class="admin-title">Quản lý giỏ hàng</div>
<hr>

<div class="admin-dashboard">
    <div class="dashboard-cards">
        <div class="dashboard-card primary">
            <div class="card-content">
                <div class="card-info">
                    <h4>Tổng giỏ hàng đang hoạt động</h4>
                    <h2><?php echo $activeCartsCount; ?></h2>
                </div>
                <div class="card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-card success">
            <div class="card-content">
                <div class="card-info">
                    <h4>Tổng số sản phẩm trong giỏ</h4>
                    <h2><?php echo $totalItemsCount; ?></h2>
                </div>
                <div class="card-icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-card info">
            <div class="card-content">
                <div class="card-info">
                    <h4>Tổng giá trị giỏ hàng</h4>
                    <h2><?php echo number_format($totalCartsValue, 0, ',', '.'); ?> đ</h2>
                </div>
                <div class="card-icon">
                    <i class="fas fa-money-bill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <div class="content-header">
            <h3>Chi tiết giỏ hàng theo người dùng</h3>
            <button class="btn-print" onclick="printReport()">
                <i class="fas fa-print"></i> In báo cáo
            </button>
        </div>
        
        <div id="print-section">
            <div class="print-header">
                <h2>Báo Cáo Chi Tiết Giỏ Hàng</h2>
                <p>Ngày in: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
            
            <div class="dashboard-summary">
                <div class="summary-item">
                    <span>Tổng giỏ hàng: <?php echo $activeCartsCount; ?></span>
                </div>
                <div class="summary-item">
                    <span>Tổng sản phẩm: <?php echo $totalItemsCount; ?></span>
                </div>
                <div class="summary-item">
                    <span>Tổng giá trị: <?php echo number_format($totalCartsValue, 0, ',', '.'); ?> đ</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Tên sản phẩm</th>
                            <th>Hình ảnh</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): 
                            if ($u->username !== 'admin'):
                                $cart = $giohang->getCartByUserId($u->username);
                                if (!empty($cart)):
                                    foreach ($cart as $item):
                                        $subtotal = $item['giathamkhao'] * $item['quantity'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u->username); ?></td>
                            <td><?php echo htmlspecialchars($item['tenhanghoa']); ?></td>
                            <td>
                                <img src="data:image/png;base64,<?php echo $item['hinhanh']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['tenhanghoa']); ?>"
                                     class="product-img">
                            </td>
                            <td><?php echo number_format($item['giathamkhao'], 0, ',', '.'); ?> đ</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</td>
                            <td>
                                <button class="btn-delete" onclick="removeItem('<?php echo htmlspecialchars($u->username); ?>', <?php echo $item['product_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                                    endforeach;
                                endif;
                            endif;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Style cho chế độ in */
@media print {
    /* Ẩn các phần không cần in */
    body * {
        visibility: hidden;
    }
    
    #print-section, #print-section * {
        visibility: visible;
    }
    
    #print-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .btn-print, .btn-delete, nav, footer {
        display: none !important;
    }
    
    /* Style cho header khi in */
    .print-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .print-header h2 {
        margin: 0;
        color: #333;
    }
    
    .print-header p {
        margin: 5px 0;
        color: #666;
    }
    
    /* Style cho bảng khi in */
    .table-responsive {
        overflow: visible;
        margin-top: 20px;
    }
    
    .content-table {
        border-collapse: collapse;
        width: 100%;
    }
    
    .content-table th,
    .content-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    /* Style cho dashboard summary khi in */
    .dashboard-summary {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
        padding: 10px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }
    
    .summary-item {
        text-align: center;
    }
    
    /* Đảm bảo hình ảnh hiển thị đúng khi in */
    .product-img {
        max-width: 60px;
        height: auto;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    /* Định dạng trang in */
    @page {
        size: landscape;
        margin: 2cm;
    }
}
</style>

<script>
function printReport() {
    window.print();
}

function exportExcel() {
    alert('Tính năng đang được phát triển');
}

function removeItem(userId, productId) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        // Thêm code xử lý xóa sản phẩm ở đây
        alert('Tính năng đang được phát triển');
    }
}

// CSS cho chế độ in
const style = document.createElement('style');
style.textContent = `
    @media print {
        .btn, nav, footer {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
        }
        .table {
            width: 100% !important;
        }
        @page {
            size: landscape;
        }
    }
`;
document.head.appendChild(style);
</script>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
}
.table > :not(caption) > * > * {
    padding: 0.75rem;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
}

/* Chỉnh sửa style cho hình ảnh sản phẩm */
.product-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.product-img:hover {
    transform: scale(1.1);
    cursor: pointer;
}

/* Thêm style cho cột hình ảnh */
.content-table td:nth-child(3) {
    width: 100px;
    text-align: center;
    padding: 10px;
}

/* Responsive cho hình ảnh */
@media (max-width: 768px) {
    .product-img {
        width: 60px;
        height: 60px;
    }
}

@media (max-width: 576px) {
    .product-img {
        width: 50px;
        height: 50px;
    }
}

/* Thêm hiệu ứng lightbox khi hover */
.product-img-wrapper {
    position: relative;
    display: inline-block;
}

.product-img-wrapper:hover::after {
    content: '🔍';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 20px;
    text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
}
</style> 
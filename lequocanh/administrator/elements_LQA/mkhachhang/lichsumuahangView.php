<?php

/**
 * File: lichsumuahangView.php
 * Hiển thị lịch sử mua hàng của khách hàng
 */

// Kết nối database
require_once './elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Lấy thông tin tìm kiếm
$search_username = isset($_GET['search_username']) ? trim($_GET['search_username']) : '';
$search_customer = isset($_GET['search_customer']) ? trim($_GET['search_customer']) : '';

// Lấy danh sách khách hàng để hiển thị trong dropdown
$customersSql = "SELECT DISTINCT u.iduser, u.username, u.hoten 
                 FROM user u 
                 WHERE u.username != 'admin' 
                 AND u.iduser NOT IN (SELECT DISTINCT iduser FROM nhanvien WHERE iduser IS NOT NULL)
                 ORDER BY u.hoten ASC";
$customersStmt = $conn->prepare($customersSql);
$customersStmt->execute();
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

// Xây dựng câu truy vấn lịch sử mua hàng
$whereClauses = [];
$params = [];

if (!empty($search_username)) {
    $whereClauses[] = "dh.ma_nguoi_dung LIKE ?";
    $params[] = "%$search_username%";
}

if (!empty($search_customer)) {
    $whereClauses[] = "(u.hoten LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search_customer%";
    $params[] = "%$search_customer%";
}

$whereClause = '';
if (!empty($whereClauses)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Truy vấn lịch sử mua hàng
$sql = "SELECT dh.*,
               MAX(u.hoten) as hoten,
               MAX(u.username) as username,
               MAX(u.dienthoai) as dienthoai,
               COUNT(ctdh.id) as so_san_pham,
               SUM(ctdh.so_luong) as tong_so_luong
        FROM don_hang dh
        LEFT JOIN user u ON dh.ma_nguoi_dung COLLATE utf8mb4_general_ci = u.username COLLATE utf8mb4_general_ci
        LEFT JOIN chi_tiet_don_hang ctdh ON dh.id = ctdh.ma_don_hang
        $whereClause
        GROUP BY dh.id, dh.ma_don_hang_text, dh.ma_nguoi_dung, dh.dia_chi_giao_hang,
                 dh.tong_tien, dh.trang_thai, dh.phuong_thuc_thanh_toan,
                 dh.trang_thai_thanh_toan, dh.ngay_tao, dh.ngay_cap_nhat
        ORDER BY dh.ngay_tao DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm format trạng thái
function formatStatus($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'approved':
            return '<span class="badge bg-success">Đã duyệt</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Đã hủy</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

// Hàm format trạng thái thanh toán
function formatPaymentStatus($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Chờ thanh toán</span>';
        case 'paid':
            return '<span class="badge bg-success">Đã thanh toán</span>';
        case 'failed':
            return '<span class="badge bg-danger">Thanh toán thất bại</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

// Hàm format phương thức thanh toán
function formatPaymentMethod($method)
{
    switch ($method) {
        case 'bank_transfer':
            return 'Chuyển khoản ngân hàng';
        case 'cash':
            return 'Tiền mặt';
        case 'credit_card':
            return 'Thẻ tín dụng';
        default:
            return 'Không xác định';
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Lịch sử mua hàng của khách hàng</h3>
                </div>

                <!-- Form tìm kiếm -->
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <input type="hidden" name="req" value="lichsumuahang">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="search_username" class="form-label">Tìm theo Username:</label>
                                <input type="text" class="form-control" id="search_username" name="search_username"
                                    value="<?php echo htmlspecialchars($search_username); ?>"
                                    placeholder="Nhập username...">
                            </div>
                            <div class="col-md-4">
                                <label for="search_customer" class="form-label">Tìm theo tên khách hàng:</label>
                                <input type="text" class="form-control" id="search_customer" name="search_customer"
                                    value="<?php echo htmlspecialchars($search_customer); ?>"
                                    placeholder="Nhập tên khách hàng...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
                                    <a href="?req=lichsumuahang" class="btn btn-secondary">🔄 Làm mới</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Bảng hiển thị lịch sử -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Mã đơn hàng</th>
                                    <th>Khách hàng</th>
                                    <th>Username</th>
                                    <th>Liên hệ</th>
                                    <th>Số SP</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="alert alert-info">
                                                📝 Không có lịch sử mua hàng nào được tìm thấy.
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['ma_don_hang_text']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($order['hoten'] ?? 'N/A'); ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($order['username'] ?? $order['ma_nguoi_dung']); ?></code>
                                            </td>
                                            <td>
                                                <small>
                                                    📞 <?php echo htmlspecialchars($order['dienthoai'] ?? 'N/A'); ?><br>
                                                    📍 <?php echo htmlspecialchars($order['diachi'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $order['so_san_pham']; ?> SP</span><br>
                                                <small><?php echo $order['tong_so_luong']; ?> sản phẩm</small>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> đ
                                                </strong>
                                            </td>
                                            <td><?php echo formatStatus($order['trang_thai']); ?></td>
                                            <td>
                                                <?php echo formatPaymentStatus($order['trang_thai_thanh_toan']); ?><br>
                                                <small><?php echo formatPaymentMethod($order['phuong_thuc_thanh_toan']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($order['ngay_tao'])); ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary fw-bold" onclick="viewOrderDetail(<?php echo $order['id']; ?>)"
                                                    style="background: linear-gradient(45deg, #007bff, #0056b3); border: none; box-shadow: 0 2px 4px rgba(0,123,255,0.3);">
                                                    👁️ XEM CHI TIẾT
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Thống kê -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-light">
                                <strong>📊 Thống kê:</strong>
                                Tổng cộng có <strong><?php echo count($orders); ?></strong> đơn hàng
                                <?php if (!empty($search_username) || !empty($search_customer)): ?>
                                    (đã lọc)
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="modal-header" style="background: linear-gradient(45deg, #007bff, #0056b3); color: white; border-radius: 15px 15px 0 0;">
                <h4 class="modal-title fw-bold">
                    <i class="fas fa-receipt me-2"></i>CHI TIẾT ĐỚN HÀNG
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="orderDetailContent" style="background-color: #f8f9fa; min-height: 400px;">
                <!-- Nội dung chi tiết sẽ được load bằng AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-3 text-muted">Đang tải thông tin chi tiết...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-xl {
        max-width: 1200px;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
        transform: scale(1.01);
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-2px);
        transition: all 0.2s ease-in-out;
    }

    .modal-content {
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .order-detail-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .order-detail-header {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        padding: 15px;
        font-weight: bold;
    }
</style>

<script>
    function viewOrderDetail(orderId) {
        // Hiển thị modal
        var modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        modal.show();

        // Reset nội dung modal với loading animation đẹp
        document.getElementById('orderDetailContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <h5 class="text-primary">Đang tải thông tin chi tiết...</h5>
                <p class="text-muted">Vui lòng chờ trong giây lát</p>
            </div>
        `;

        // Load chi tiết đơn hàng bằng AJAX
        fetch('elements_LQA/mkhachhang/orderDetailAjax.php?order_id=' + orderId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                // Thêm hiệu ứng fade in
                document.getElementById('orderDetailContent').style.opacity = '0';
                document.getElementById('orderDetailContent').innerHTML = data;

                // Fade in effect
                setTimeout(() => {
                    document.getElementById('orderDetailContent').style.transition = 'opacity 0.3s ease-in-out';
                    document.getElementById('orderDetailContent').style.opacity = '1';
                }, 100);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('orderDetailContent').innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Lỗi!</strong> Không thể tải chi tiết đơn hàng.
                        <br><small>Vui lòng thử lại sau.</small>
                    </div>
                `;
            });
    }
</script>
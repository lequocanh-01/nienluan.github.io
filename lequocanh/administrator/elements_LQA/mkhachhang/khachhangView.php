<?php
// Xử lý thông báo
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Xóa thông báo sau khi hiển thị
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Lấy thông tin tìm kiếm
$searchKeyword = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$searchField = isset($_GET['field']) ? $_GET['field'] : 'all';
?>

<div class="admin-content">
    <h3 class="admin-title">Quản lý khách hàng</h3>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Thanh tìm kiếm -->
    <div class="row mb-3">
        <div class="col-12">
            <form method="GET" class="d-flex">
                <input type="hidden" name="req" value="khachhangView">
                <select name="field" class="form-select me-2" style="width: auto;">
                    <option value="all" <?php echo $searchField == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="hoten" <?php echo $searchField == 'hoten' ? 'selected' : ''; ?>>Họ tên</option>
                    <option value="dienthoai" <?php echo $searchField == 'dienthoai' ? 'selected' : ''; ?>>Điện thoại</option>
                    <option value="diachi" <?php echo $searchField == 'diachi' ? 'selected' : ''; ?>>Địa chỉ</option>
                </select>
                <input type="text" name="search" class="form-control me-2" placeholder="Nhập từ khóa tìm kiếm..." value="<?php echo $searchKeyword; ?>">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if (!empty($searchKeyword)): ?>
                    <a href="?req=khachhangView" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </form>
        </div>

    </div>

    <!-- Bảng danh sách khách hàng -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Danh sách khách hàng
                <?php if (!empty($searchKeyword)): ?>
                    <small class="text-muted">(Kết quả tìm kiếm: "<?php echo $searchKeyword; ?>")</small>
                <?php endif; ?>
                <span class="badge bg-primary ms-2"><?php echo count($customers); ?> khách hàng</span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($customers)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Giới tính</th>
                                <th>Ngày sinh</th>
                                <th>Điện thoại</th>
                                <th>Địa chỉ</th>
                                <th>Ngày đăng ký</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['id']; ?></td>
                                    <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['hoten']); ?></td>
                                    <td><?php echo KhachHang::formatGender($customer['gioitinh']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($customer['ngaysinh'])); ?></td>
                                    <td><?php echo htmlspecialchars($customer['dienthoai'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['diachi'] ?? ''); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($customer['ngaytao'])); ?></td>
                                    <td>
                                        <?php if ($customer['setlock'] == 1): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bị khóa</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">
                        <?php if (!empty($searchKeyword)): ?>
                            Không tìm thấy khách hàng nào với từ khóa "<?php echo $searchKeyword; ?>"
                        <?php else: ?>
                            Chưa có khách hàng nào
                        <?php endif; ?>
                    </h5>
                    <?php if (empty($searchKeyword)): ?>
                        <a href="?req=khachhangView&act=add" class="btn btn-primary mt-2">
                            <i class="fas fa-plus"></i> Thêm khách hàng đầu tiên
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .admin-content {
        padding: 20px;
    }

    .admin-title {
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: none;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .badge {
        font-size: 0.75em;
    }

    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>
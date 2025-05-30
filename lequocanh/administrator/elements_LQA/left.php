<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?php
require_once './elements_LQA/mod/phanquyenCls.php';
$phanQuyen = new PhanQuyen();
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
$isAdmin = isset($_SESSION['ADMIN']);
$isNhanVien = $phanQuyen->isNhanVien($username);
?>
<div class="left-menu">
    <div>
        <a href="index.php" class="<?php echo !isset($_GET['req']) ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Menu
        </a>
    </div>
    <div class="">
        <a href="#"><i class="fas fa-cogs"></i> Quản lý</a>
    </div>
    <div class="">
        <ul>
            <?php
            $current_page = isset($_GET['req']) ? $_GET['req'] : '';
            $menu_items = [
                'userview' => ['icon' => 'fas fa-users', 'text' => 'Tài khoản', 'admin_only' => true, 'hide_from_employee' => true],
                'khachhangview' => ['icon' => 'fas fa-user-friends', 'text' => 'Khách hàng', 'admin_only' => false, 'hide_from_employee' => false],
                'loaihangview' => ['icon' => 'fas fa-tags', 'text' => 'Loại hàng', 'admin_only' => false, 'hide_from_employee' => false],
                'hanghoaview' => ['icon' => 'fas fa-box', 'text' => 'Hàng hóa', 'admin_only' => false, 'hide_from_employee' => false],
                'thuoctinhhhview' => ['icon' => 'fas fa-list-ul', 'text' => 'Thuộc tính hàng hóa', 'admin_only' => false, 'hide_from_employee' => false],
                'thuoctinhview' => ['icon' => 'fas fa-clipboard-list', 'text' => 'Thuộc tính', 'admin_only' => false, 'hide_from_employee' => false],
                'dongiaview' => ['icon' => 'fas fa-dollar-sign', 'text' => 'Đơn giá', 'admin_only' => false, 'hide_from_employee' => false],
                'thuonghieuview' => ['icon' => 'fas fa-trademark', 'text' => 'Thương hiệu', 'admin_only' => false, 'hide_from_employee' => false],
                'donvitinhview' => ['icon' => 'fas fa-balance-scale', 'text' => 'Đơn vị tính', 'admin_only' => false, 'hide_from_employee' => false],
                'nhanvienview' => ['icon' => 'fas fa-user-tie', 'text' => 'Nhân viên', 'admin_only' => false, 'hide_from_employee' => true],
                'adminGiohangView' => ['icon' => 'fas fa-shopping-cart', 'text' => 'Giỏ hàng', 'admin_only' => false, 'hide_from_employee' => false],
                'orders' => ['icon' => 'fas fa-clipboard-check', 'text' => 'Đơn hàng', 'admin_only' => true, 'hide_from_employee' => true],
                'payment_config' => ['icon' => 'fas fa-money-check-alt', 'text' => 'Cấu hình thanh toán', 'admin_only' => true, 'hide_from_employee' => true],
                'hinhanhview' => ['icon' => 'fas fa-images', 'text' => 'Hình ảnh', 'admin_only' => false, 'hide_from_employee' => false],
                'nhacungcapview' => ['icon' => 'fas fa-truck', 'text' => 'Nhà cung cấp', 'admin_only' => false, 'hide_from_employee' => false],
                'mphieunhap' => ['icon' => 'fas fa-file-invoice', 'text' => 'Phiếu nhập kho', 'admin_only' => false, 'hide_from_employee' => false],
                'mtonkho' => ['icon' => 'fas fa-warehouse', 'text' => 'Tồn kho', 'admin_only' => false, 'hide_from_employee' => false],
                'lichsumuahang' => ['icon' => 'fas fa-history', 'text' => 'Lịch sử mua hàng', 'admin_only' => false, 'hide_from_employee' => false],
                'baocaoview' => ['icon' => 'fas fa-chart-line', 'text' => 'Báo cáo & Thống kê', 'admin_only' => false, 'hide_from_employee' => false],
            ];

            foreach ($menu_items as $req => $item) {
                // Kiểm tra quyền truy cập
                if (($item['admin_only'] && !$isAdmin) ||
                    ($item['hide_from_employee'] && $isNhanVien && !$isAdmin)
                ) {
                    continue; // Bỏ qua các mục không có quyền
                }

                $active_class = ($current_page === $req) ? 'active' : '';
                echo "<li><a href='index.php?req=$req' class='$active_class'><i class='{$item['icon']}'></i> {$item['text']}</a></li>";
            }
            ?>
            <li><a href="../index.php"><i class="fas fa-store"></i> Trang mua hàng</a></li>
        </ul>
    </div>
</div>
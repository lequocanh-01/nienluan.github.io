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
                'userview' => ['icon' => 'fas fa-users', 'text' => 'Tài khoản', 'admin_only' => true],
                'loaihangview' => ['icon' => 'fas fa-tags', 'text' => 'Loại hàng', 'admin_only' => false],
                'hanghoaview' => ['icon' => 'fas fa-box', 'text' => 'Hàng hóa', 'admin_only' => false],
                'thuoctinhhhview' => ['icon' => 'fas fa-list-ul', 'text' => 'Thuộc tính hàng hóa', 'admin_only' => false],
                'thuoctinhview' => ['icon' => 'fas fa-clipboard-list', 'text' => 'Thuộc tính', 'admin_only' => false],
                'dongiaview' => ['icon' => 'fas fa-dollar-sign', 'text' => 'Đơn giá', 'admin_only' => false],
                'thuonghieuview' => ['icon' => 'fas fa-trademark', 'text' => 'Thương hiệu', 'admin_only' => false],
                'donvitinhview' => ['icon' => 'fas fa-balance-scale', 'text' => 'Đơn vị tính', 'admin_only' => false],
                'nhanvienview' => ['icon' => 'fas fa-user-tie', 'text' => 'Nhân viên', 'admin_only' => false],
                'adminGiohangView' => ['icon' => 'fas fa-shopping-cart', 'text' => 'Giỏ hàng', 'admin_only' => false],
                'hinhanhview' => ['icon' => 'fas fa-images', 'text' => 'Hình ảnh', 'admin_only' => false],
                'userprofile' => ['icon' => 'fas fa-user-circle', 'text' => 'Hồ sơ cá nhân', 'admin_only' => false],
            ];

            foreach ($menu_items as $req => $item) {
                // Kiểm tra quyền truy cập
                if ($item['admin_only'] && !$isAdmin) {
                    continue; // Bỏ qua các mục chỉ dành cho admin
                }

                $active_class = ($current_page === $req) ? 'active' : '';
                echo "<li><a href='index.php?req=$req' class='$active_class'><i class='{$item['icon']}'></i> {$item['text']}</a></li>";
            }
            ?>
            <li><a href="../index.php"><i class="fas fa-store"></i> Trang mua hàng</a></li>
        </ul>
    </div>
</div>
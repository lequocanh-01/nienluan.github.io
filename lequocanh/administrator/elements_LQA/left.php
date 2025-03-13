<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                'userview' => ['icon' => 'fas fa-users', 'text' => 'Tài khoản'],
                'loaihangview' => ['icon' => 'fas fa-tags', 'text' => 'Loại hàng'],
                'hanghoaview' => ['icon' => 'fas fa-box', 'text' => 'Hàng hóa'],
                'thuoctinhhhview' => ['icon' => 'fas fa-list-ul', 'text' => 'Thuộc tính hàng hóa'],
                'thuoctinhview' => ['icon' => 'fas fa-clipboard-list', 'text' => 'Thuộc tính'],
                'dongiaview' => ['icon' => 'fas fa-dollar-sign', 'text' => 'Đơn giá'],
                'thuonghieuview' => ['icon' => 'fas fa-trademark', 'text' => 'Thương hiệu'],
                'donvitinhview' => ['icon' => 'fas fa-balance-scale', 'text' => 'Đơn vị tính'],
                'nhanvienview' => ['icon' => 'fas fa-user-tie', 'text' => 'Nhân viên'],
                'adminGiohangView' => ['icon' => 'fas fa-shopping-cart', 'text' => 'Giỏ hàng'],
                'hinhanhview' => ['icon' => 'fas fa-images', 'text' => 'Hình ảnh'],
              
            ];

            foreach ($menu_items as $req => $item) {
                $active_class = ($current_page === $req) ? 'active' : '';
                echo "<li><a href='index.php?req=$req' class='$active_class'><i class='{$item['icon']}'></i> {$item['text']}</a></li>";
            }
            ?>
            <li><a href="../index.php"><i class="fas fa-store"></i> Trang mua hàng</a></li>
        </ul>
    </div>
</div>
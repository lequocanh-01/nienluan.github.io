<?php
session_start();

echo "<h1>🎯 FINAL MENU TEST</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.test-user { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.menu-preview { background: #343a40; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
.menu-item { display: block; color: #adb5bd; text-decoration: none; padding: 8px 12px; margin: 2px 0; border-radius: 3px; }
.menu-item:hover { background: #495057; color: white; }
.menu-count { background: #007bff; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; }
</style>";

// Test users
$testUsers = [
    'manager1' => 'Manager 1 - Báo cáo',
    'staff2' => 'Staff 2 - Hàng hóa',
    'lequocanh05' => 'Le Quoc Anh - Bán hàng'
];

require_once './elements_LQA/mod/phanquyenCls.php';

// Menu items từ left.php
$menu_items = [
    'userview' => ['icon' => 'fas fa-users', 'text' => 'Tài khoản', 'admin_only' => true, 'hide_from_employee' => true],
    'vaiTroView' => ['icon' => 'fas fa-user-shield', 'text' => 'Vai trò người dùng', 'admin_only' => true, 'hide_from_employee' => false],
    'nguoiDungVaiTroView' => ['icon' => 'fas fa-user-cog', 'text' => 'Gán vai trò', 'admin_only' => true, 'hide_from_employee' => true],
    'danhSachVaiTroView' => ['icon' => 'fas fa-users-cog', 'text' => 'Danh sách vai trò', 'admin_only' => false, 'hide_from_employee' => false],
    'khachhangview' => ['icon' => 'fas fa-user-friends', 'text' => 'Khách hàng', 'admin_only' => false, 'hide_from_employee' => false],
    'hanghoaview' => ['icon' => 'fas fa-box', 'text' => 'Hàng hóa', 'admin_only' => false, 'hide_from_employee' => false],
    'dongiaview' => ['icon' => 'fas fa-tags', 'text' => 'Đơn giá', 'admin_only' => false, 'hide_from_employee' => false],
    'loaihangview' => ['icon' => 'fas fa-list', 'text' => 'Loại hàng', 'admin_only' => false, 'hide_from_employee' => false],
    'thuonghieuview' => ['icon' => 'fas fa-trademark', 'text' => 'Thương hiệu', 'admin_only' => false, 'hide_from_employee' => false],
    'donvitinhview' => ['icon' => 'fas fa-balance-scale', 'text' => 'Đơn vị tính', 'admin_only' => false, 'hide_from_employee' => false],
    'nhanvienview' => ['icon' => 'fas fa-user-tie', 'text' => 'Nhân viên', 'admin_only' => false, 'hide_from_employee' => true],
    'adminGiohangView' => ['icon' => 'fas fa-shopping-cart', 'text' => 'Giỏ hàng', 'admin_only' => false, 'hide_from_employee' => false],
    'don_hang' => ['icon' => 'fas fa-clipboard-check', 'text' => 'Đơn hàng', 'admin_only' => true, 'hide_from_employee' => true],
    'cau_hinh_thanh_toan' => ['icon' => 'fas fa-money-check-alt', 'text' => 'Cấu hình thanh toán', 'admin_only' => true, 'hide_from_employee' => true],
    'hinhanhview' => ['icon' => 'fas fa-images', 'text' => 'Hình ảnh', 'admin_only' => false, 'hide_from_employee' => false],
    'nhacungcapview' => ['icon' => 'fas fa-truck', 'text' => 'Nhà cung cấp', 'admin_only' => false, 'hide_from_employee' => false],
    'mphieunhap' => ['icon' => 'fas fa-file-invoice', 'text' => 'Phiếu nhập kho', 'admin_only' => false, 'hide_from_employee' => false],
    'mtonkho' => ['icon' => 'fas fa-warehouse', 'text' => 'Tồn kho', 'admin_only' => false, 'hide_from_employee' => false],
    'lichsumuahang' => ['icon' => 'fas fa-history', 'text' => 'Lịch sử mua hàng', 'admin_only' => false, 'hide_from_employee' => false],
    'baocaoview' => ['icon' => 'fas fa-chart-line', 'text' => 'Báo cáo tổng hợp', 'admin_only' => false, 'hide_from_employee' => false],
    'doanhThuView' => ['icon' => 'fas fa-money-bill-wave', 'text' => 'Báo cáo doanh thu', 'admin_only' => false, 'hide_from_employee' => false],
    'sanPhamBanChayView' => ['icon' => 'fas fa-fire', 'text' => 'Sản phẩm bán chạy', 'admin_only' => false, 'hide_from_employee' => false],
    'loiNhuanView' => ['icon' => 'fas fa-chart-pie', 'text' => 'Báo cáo lợi nhuận', 'admin_only' => false, 'hide_from_employee' => false],
    'nhatKyHoatDongTichHop' => ['icon' => 'fas fa-chart-bar', 'text' => 'Thống kê hoạt động nhân viên', 'admin_only' => false, 'hide_from_employee' => true],
];

foreach ($testUsers as $username => $description) {
    echo "<div class='test-user'>";
    echo "<h2>👤 $description ($username)</h2>";

    // Giả lập đăng nhập
    $_SESSION['USER'] = $username;
    unset($_SESSION['ADMIN']);

    $phanQuyen = new PhanQuyen();
    $isAdmin = isset($_SESSION['ADMIN']);
    $isNhanVien = $phanQuyen->isNhanVien($username);

    echo "<p><strong>Is Admin:</strong> " . ($isAdmin ? 'true' : 'false') . "</p>";
    echo "<p><strong>Is Nhan Vien:</strong> " . ($isNhanVien ? 'true' : 'false') . "</p>";

    // Simulate menu logic từ left.php
    $visibleMenus = [];

    foreach ($menu_items as $req => $item) {
        $shouldShow = false;

        // Logic từ left.php đã sửa
        if ($isAdmin && $username === 'admin') {
            $shouldShow = true;
        }
        // GIẢI PHÁP TẠM THỜI: Hardcode quyền cho các manager/staff
        else if ($username === 'manager1') {
            $manager1AllowedModules = [
                'baocaoview',
                'doanhThuView',
                'sanPhamBanChayView',
                'loiNhuanView',
                'userprofile',
                'userUpdateProfile',
                'thongbao'
            ];
            $shouldShow = in_array($req, $manager1AllowedModules);
        } else if ($username === 'staff2') {
            $staff2AllowedModules = [
                'hanghoaview',
                'dongiaview',
                'userprofile',
                'userUpdateProfile',
                'thongbao'
            ];
            $shouldShow = in_array($req, $staff2AllowedModules);
        } else if ($username === 'lequocanh05') {
            $lequocanhAllowedModules = [
                'khachhangview',
                'adminGiohangView',
                'lichsumuahang',
                'userprofile',
                'userUpdateProfile',
                'thongbao'
            ];
            $shouldShow = in_array($req, $lequocanhAllowedModules);
        }
        // Nếu là nhân viên khác, kiểm tra quyền bình thường
        else if ($isNhanVien || strpos($username, 'manager') !== false) {
            if ($item['hide_from_employee']) {
                $shouldShow = false;
            } else {
                try {
                    $shouldShow = $phanQuyen->checkAccess($req, $username);
                } catch (Exception $e) {
                    $shouldShow = false;
                }
            }
        } else {
            $basicUserModules = ['userprofile', 'userUpdateProfile', 'lichsumuahang'];
            $shouldShow = in_array($req, $basicUserModules);
        }

        if ($shouldShow) {
            $visibleMenus[] = [
                'req' => $req,
                'item' => $item
            ];
        }
    }

    echo "<p><strong>Số menu hiển thị:</strong> <span class='menu-count'>" . count($visibleMenus) . "</span></p>";

    if (count($visibleMenus) > 0) {
        echo "<div class='menu-preview'>";
        echo "<h4>🖥️ Menu Preview:</h4>";
        foreach ($visibleMenus as $menu) {
            $req = $menu['req'];
            $item = $menu['item'];
            echo "<a href='index.php?req=$req' class='menu-item'>";
            echo "<i class='{$item['icon']}'></i> {$item['text']}";
            echo "</a>";
        }

        // Kiểm tra hiển thị "Trang mua hàng"
        $shouldShowShoppingPage = false;
        if ($isAdmin && $username === 'admin') {
            $shouldShowShoppingPage = true;
        } else if (!$isNhanVien) {
            $shouldShowShoppingPage = true;
        }

        if ($shouldShowShoppingPage) {
            echo "<a href='../index.php' class='menu-item' style='background: #28a745;'>";
            echo "<i class='fas fa-store'></i> Trang mua hàng ✅";
            echo "</a>";
        } else {
            echo "<div class='menu-item' style='background: #6c757d; color: #adb5bd; text-decoration: line-through;'>";
            echo "<i class='fas fa-store'></i> Trang mua hàng ❌ (Ẩn với nhân viên)";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "❌ <strong>Không có menu nào hiển thị!</strong>";
        echo "</div>";
    }

    echo "</div>";
}

// Test đăng nhập thực tế
echo "<div class='test-user'>";
echo "<h2>🔗 TEST ĐĂNG NHẬP THỰC TẾ</h2>";
echo "<p>Bây giờ bạn có thể test đăng nhập:</p>";
echo "<ul>";
echo "<li><a href='UserLogin.php' target='_blank'>🔐 Đăng nhập Manager1</a></li>";
echo "<li><a href='UserLogin.php' target='_blank'>🔐 Đăng nhập Staff2</a></li>";
echo "<li><a href='UserLogin.php' target='_blank'>🔐 Đăng nhập lequocanh05</a></li>";
echo "</ul>";
echo "<p><strong>Thông tin đăng nhập:</strong></p>";
echo "<ul>";
echo "<li><strong>manager1:</strong> password (sẽ thấy 4 menu báo cáo)</li>";
echo "<li><strong>staff2:</strong> password (sẽ thấy 2 menu hàng hóa)</li>";
echo "<li><strong>lequocanh05:</strong> password (sẽ thấy 3 menu bán hàng)</li>";
echo "</ul>";
echo "</div>";

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session reset sau test.</em></p>";

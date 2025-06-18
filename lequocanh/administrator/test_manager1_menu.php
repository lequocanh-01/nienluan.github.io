<?php
session_start();

// Giả lập đăng nhập manager1
$_SESSION['USER'] = 'manager1';
unset($_SESSION['ADMIN']);

echo "<h1>🔍 TEST MENU MANAGER1</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.menu-test { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 15px 0; }
.menu-item { padding: 8px; margin: 5px 0; border-left: 4px solid #007bff; background: white; }
.no-access { border-left-color: #dc3545; background: #fff5f5; }
.has-access { border-left-color: #28a745; background: #f8fff8; }
</style>";

// Include menu logic
require_once './elements_LQA/mod/phanquyenCls.php';
$phanQuyen = new PhanQuyen();
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
$isAdmin = isset($_SESSION['ADMIN']);
$isNhanVien = $phanQuyen->isNhanVien($username);

echo "<div class='menu-test'>";
echo "<h2>📊 THÔNG TIN ĐĂNG NHẬP</h2>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Is Admin:</strong> " . ($isAdmin ? 'true' : 'false') . "</p>";
echo "<p><strong>Is Nhan Vien:</strong> " . ($isNhanVien ? 'true' : 'false') . "</p>";
echo "</div>";

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

echo "<div class='menu-test'>";
echo "<h2>🎯 MENU SIMULATION</h2>";

$visibleMenus = [];
$hiddenMenus = [];

foreach ($menu_items as $req => $item) {
    $shouldShow = false;
    $reason = '';
    
    // Logic từ left.php
    if ($isAdmin && $username === 'admin') {
        $shouldShow = true;
        $reason = 'Admin access';
    }
    else if ($isNhanVien || strpos($username, 'manager') !== false) {
        if ($item['hide_from_employee']) {
            $shouldShow = false;
            $reason = 'Hidden from employee';
        } else {
            try {
                $hasAccess = $phanQuyen->checkAccess($req, $username);
                $shouldShow = $hasAccess;
                $reason = $hasAccess ? 'Has permission' : 'No permission';
            } catch (Exception $e) {
                $shouldShow = false;
                $reason = 'Error: ' . $e->getMessage();
            }
        }
    }
    else {
        $basicUserModules = ['userprofile', 'userUpdateProfile', 'lichsumuahang'];
        $shouldShow = in_array($req, $basicUserModules);
        $reason = $shouldShow ? 'Basic user module' : 'Not basic user module';
    }
    
    if ($shouldShow) {
        $visibleMenus[] = [
            'req' => $req,
            'item' => $item,
            'reason' => $reason
        ];
    } else {
        $hiddenMenus[] = [
            'req' => $req,
            'item' => $item,
            'reason' => $reason
        ];
    }
}

echo "<h3>✅ MENU SẼ HIỂN THỊ (" . count($visibleMenus) . " items):</h3>";
foreach ($visibleMenus as $menu) {
    echo "<div class='menu-item has-access'>";
    echo "<i class='{$menu['item']['icon']}'></i> <strong>{$menu['item']['text']}</strong> ({$menu['req']})";
    echo "<br><small>Lý do: {$menu['reason']}</small>";
    echo "</div>";
}

echo "<h3>❌ MENU BỊ ẨN (" . count($hiddenMenus) . " items):</h3>";
foreach ($hiddenMenus as $menu) {
    echo "<div class='menu-item no-access'>";
    echo "<i class='{$menu['item']['icon']}'></i> <strong>{$menu['item']['text']}</strong> ({$menu['req']})";
    echo "<br><small>Lý do: {$menu['reason']}</small>";
    echo "</div>";
}

echo "</div>";

// Tạo HTML menu thực tế
echo "<div class='menu-test'>";
echo "<h2>🖥️ MENU HTML THỰC TẾ</h2>";
echo "<div style='background: #343a40; color: white; padding: 15px; border-radius: 5px;'>";
echo "<h4>Menu</h4>";
echo "<ul style='list-style: none; padding: 0;'>";

foreach ($visibleMenus as $menu) {
    $req = $menu['req'];
    $item = $menu['item'];
    echo "<li style='margin: 5px 0;'>";
    echo "<a href='index.php?req=$req' style='color: #adb5bd; text-decoration: none; display: block; padding: 8px;'>";
    echo "<i class='{$item['icon']}'></i> {$item['text']}";
    echo "</a>";
    echo "</li>";
}

echo "</ul>";
echo "</div>";
echo "</div>";

// Kiểm tra database
echo "<div class='menu-test'>";
echo "<h2>🗄️ KIỂM TRA DATABASE</h2>";

require_once './elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Kiểm tra manager1
    $stmt = $conn->query("
        SELECT nv.idNhanVien, nv.tenNV, u.username 
        FROM nhanvien nv 
        JOIN user u ON nv.iduser = u.iduser 
        WHERE u.username = 'manager1'
    ");
    $manager1Info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager1Info) {
        echo "<p><strong>✅ Manager1 trong database:</strong></p>";
        echo "<ul>";
        echo "<li>ID: {$manager1Info['idNhanVien']}</li>";
        echo "<li>Tên: {$manager1Info['tenNV']}</li>";
        echo "</ul>";
        
        // Kiểm tra quyền
        $stmt = $conn->prepare("
            SELECT pq.maPhanHe, pq.tenPhanHe 
            FROM NhanVien_PhanHeQuanLy nvpq 
            JOIN PhanHeQuanLy pq ON nvpq.idPhanHe = pq.idPhanHe 
            WHERE nvpq.idNhanVien = ?
        ");
        $stmt->execute([$manager1Info['idNhanVien']]);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Quyền được gán:</strong></p>";
        echo "<ul>";
        foreach ($permissions as $perm) {
            echo "<li>{$perm['maPhanHe']} - {$perm['tenPhanHe']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'><strong>❌ Manager1 không tồn tại!</strong></p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi database: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Reset session
unset($_SESSION['USER']);
echo "<p style='text-align: center; margin: 20px 0;'><em>Session reset sau test.</em></p>";
?>

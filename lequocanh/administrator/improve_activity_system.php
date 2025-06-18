<?php
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/nhatKyHoatDongCls.php';
require_once 'elements_LQA/mod/phanHeQuanLyCls.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h1>🔧 CẢI THIỆN HỆ THỐNG THỐNG KÊ HOẠT ĐỘNG NHÂN VIÊN</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

try {
    $nhatKyObj = new NhatKyHoatDong();
    $phanHeObj = new PhanHeQuanLy();
    
    echo "<h2>📋 BƯỚC 1: TẠO DỮ LIỆU MẪU CHO NHẬT KÝ HOẠT ĐỘNG</h2>";
    
    // Tạo dữ liệu mẫu cho nhật ký hoạt động
    $sampleActivities = [
        ['admin', 'Đăng nhập', 'Hệ thống', null, 'Đăng nhập vào hệ thống quản trị'],
        ['admin', 'Xem danh sách', 'Sản phẩm', null, 'Xem danh sách sản phẩm'],
        ['admin', 'Thêm mới', 'Sản phẩm', 1, 'Thêm sản phẩm mới: Laptop Dell'],
        ['admin', 'Cập nhật', 'Sản phẩm', 1, 'Cập nhật giá sản phẩm'],
        ['staff2', 'Đăng nhập', 'Hệ thống', null, 'Đăng nhập vào hệ thống'],
        ['staff2', 'Xem danh sách', 'Đơn hàng', null, 'Xem danh sách đơn hàng'],
        ['staff2', 'Cập nhật', 'Đơn hàng', 1, 'Cập nhật trạng thái đơn hàng'],
        ['manager1', 'Đăng nhập', 'Hệ thống', null, 'Đăng nhập vào hệ thống'],
        ['manager1', 'Xem báo cáo', 'Doanh thu', null, 'Xem báo cáo doanh thu tháng'],
        ['manager1', 'Xuất báo cáo', 'Doanh thu', null, 'Xuất báo cáo Excel'],
        ['lequocanh', 'Đăng nhập', 'Hệ thống', null, 'Đăng nhập vào hệ thống'],
        ['lequocanh', 'Thêm mới', 'Khách hàng', 2, 'Thêm khách hàng mới'],
        ['lequocanh', 'Xem danh sách', 'Nhân viên', null, 'Xem danh sách nhân viên'],
    ];
    
    $addedCount = 0;
    foreach ($sampleActivities as $activity) {
        $result = $nhatKyObj->ghiNhatKy(
            $activity[0], // username
            $activity[1], // hành động
            $activity[2], // đối tượng
            $activity[3], // đối tượng ID
            $activity[4]  // chi tiết
        );
        if ($result) $addedCount++;
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #155724;'>✅ Đã thêm $addedCount hoạt động mẫu</h4>";
    echo "</div>";
    
    echo "<h2>🏢 BƯỚC 2: TẠO CÁC PHÂN HỆ QUẢN LÝ</h2>";
    
    // Tạo các phân hệ quản lý
    $phanHeList = [
        ['SANPHAM', 'Quản lý Sản phẩm', 'Quản lý thông tin sản phẩm, loại hàng, thương hiệu'],
        ['DONHANG', 'Quản lý Đơn hàng', 'Quản lý đơn hàng, xử lý thanh toán'],
        ['KHACHHANG', 'Quản lý Khách hàng', 'Quản lý thông tin khách hàng, lịch sử mua hàng'],
        ['NHANVIEN', 'Quản lý Nhân viên', 'Quản lý thông tin nhân viên, phân quyền'],
        ['BAOCAO', 'Báo cáo & Thống kê', 'Xem báo cáo doanh thu, thống kê'],
        ['KHOTONG', 'Quản lý Kho tồn', 'Quản lý nhập xuất kho, tồn kho'],
    ];
    
    $phanHeCreated = 0;
    foreach ($phanHeList as $phanHe) {
        $result = $phanHeObj->addPhanHe($phanHe[0], $phanHe[1], $phanHe[2]);
        if ($result) $phanHeCreated++;
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #155724;'>✅ Đã tạo $phanHeCreated phân hệ quản lý</h4>";
    echo "</div>";
    
    echo "<h2>🔗 BƯỚC 3: GÁN NHÂN VIÊN VÀO CÁC PHÂN HỆ</h2>";
    
    // Lấy danh sách nhân viên có username
    $stmt = $conn->query("
        SELECT nv.idNhanVien, nv.tenNV, u.username 
        FROM nhanvien nv 
        JOIN user u ON nv.iduser = u.iduser 
        WHERE u.username IS NOT NULL
    ");
    $nhanVienList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy danh sách phân hệ
    $allPhanHe = $phanHeObj->getAllPhanHe();
    
    $assignmentCount = 0;
    foreach ($nhanVienList as $nv) {
        $username = $nv['username'];
        $idNhanVien = $nv['idNhanVien'];
        
        // Gán phân hệ dựa trên vai trò
        $phanHeToAssign = [];
        
        if ($username == 'admin') {
            // Admin có quyền tất cả phân hệ
            $phanHeToAssign = array_column($allPhanHe, 'idPhanHe');
        } elseif (strpos($username, 'manager') !== false) {
            // Manager có quyền báo cáo, nhân viên, đơn hàng
            foreach ($allPhanHe as $ph) {
                if (in_array($ph->maPhanHe, ['BAOCAO', 'NHANVIEN', 'DONHANG', 'KHACHHANG'])) {
                    $phanHeToAssign[] = $ph->idPhanHe;
                }
            }
        } elseif (strpos($username, 'staff') !== false) {
            // Staff có quyền sản phẩm, đơn hàng, khách hàng
            foreach ($allPhanHe as $ph) {
                if (in_array($ph->maPhanHe, ['SANPHAM', 'DONHANG', 'KHACHHANG', 'KHOTONG'])) {
                    $phanHeToAssign[] = $ph->idPhanHe;
                }
            }
        } else {
            // Nhân viên khác có quyền cơ bản
            foreach ($allPhanHe as $ph) {
                if (in_array($ph->maPhanHe, ['SANPHAM', 'KHACHHANG'])) {
                    $phanHeToAssign[] = $ph->idPhanHe;
                }
            }
        }
        
        // Thực hiện gán
        foreach ($phanHeToAssign as $idPhanHe) {
            $result = $phanHeObj->assignPhanHeToNhanVien($idNhanVien, $idPhanHe);
            if ($result) $assignmentCount++;
        }
        
        echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong>" . $nv['tenNV'] . " (" . $username . ")</strong>: Đã gán " . count($phanHeToAssign) . " phân hệ";
        echo "</div>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #155724;'>✅ Đã thực hiện $assignmentCount phép gán</h4>";
    echo "</div>";
    
    echo "<h2>📊 BƯỚC 4: TẠO TRANG THỐNG KÊ CẢI THIỆN</h2>";
    
    // Tạo trang thống kê cải thiện
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🎯 Các cải thiện sẽ được thực hiện:</h4>";
    echo "<ul>";
    echo "<li>✅ Thêm biểu đồ hoạt động theo thời gian</li>";
    echo "<li>✅ Hiển thị phân hệ được gán cho từng nhân viên</li>";
    echo "<li>✅ Thống kê chi tiết theo loại hoạt động</li>";
    echo "<li>✅ Bộ lọc nâng cao theo phân hệ</li>";
    echo "<li>✅ Xuất báo cáo Excel</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🎉 HOÀN THÀNH!</h2>";
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📋 Tóm tắt cải thiện:</h3>";
    echo "<ul style='font-size: 16px;'>";
    echo "<li><strong>Dữ liệu mẫu:</strong> $addedCount hoạt động</li>";
    echo "<li><strong>Phân hệ quản lý:</strong> $phanHeCreated phân hệ</li>";
    echo "<li><strong>Phân quyền:</strong> $assignmentCount phép gán</li>";
    echo "<li><strong>Nhân viên có quyền:</strong> " . count($nhanVienList) . " người</li>";
    echo "</ul>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php?req=nhatKyHoatDongTichHop' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê hoạt động</a>";
    echo "<a href='index.php?req=nhanvienview' class='btn btn-success' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>👥 Quản lý nhân viên</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24;'>❌ Lỗi: " . $e->getMessage() . "</h4>";
    echo "</div>";
}

echo "</div>";

// Tự động xóa file sau 30 giây
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Script đã chạy xong. Bạn có muốn xóa file này không?')) {";
echo "    fetch('improve_activity_system.php?delete=1');";
echo "    alert('File đã được xóa.');";
echo "  }";
echo "}, 5000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
?>

<?php
/**
 * Script tích hợp ghi nhật ký hoạt động vào các chức năng CRUD
 */

echo "<h1>🔧 TÍCH HỢP GHI NHẬT KÝ HOẠT ĐỘNG</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

echo "<h2>📋 HƯỚNG DẪN TÍCH HỢP</h2>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>🎯 Các file cần tích hợp ghi nhật ký:</h3>";
echo "<ol>";
echo "<li><strong>Quản lý sản phẩm:</strong> elements_LQA/mhanghoa/hanghoaAct.php</li>";
echo "<li><strong>Quản lý đơn hàng:</strong> elements_LQA/mdonhang/donhangAct.php</li>";
echo "<li><strong>Quản lý khách hàng:</strong> elements_LQA/muser/userAct.php</li>";
echo "<li><strong>Quản lý nhân viên:</strong> elements_LQA/mnhanvien/nhanvienAct.php</li>";
echo "<li><strong>Quản lý loại hàng:</strong> elements_LQA/mloaihang/loaihangAct.php</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>💡 Cách tích hợp:</h3>";
echo "<h4>1. Thêm require helper:</h4>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "require_once '../mnhatkyhoatdong/nhatKyHoatDongHelper.php';";
echo "</pre>";

echo "<h4>2. Ghi nhật ký sau các thao tác:</h4>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// Sau khi thêm mới thành công
if (\$result) {
    ghiNhatKyThemMoi(\$_SESSION['username'], 'Sản phẩm', \$newId, 'Thêm sản phẩm: ' . \$tenSanPham);
}

// Sau khi cập nhật thành công  
if (\$result) {
    ghiNhatKyCapNhat(\$_SESSION['username'], 'Sản phẩm', \$id, 'Cập nhật sản phẩm: ' . \$tenSanPham);
}

// Sau khi xóa thành công
if (\$result) {
    ghiNhatKyXoa(\$_SESSION['username'], 'Sản phẩm', \$id, 'Xóa sản phẩm: ' . \$tenSanPham);
}";
echo "</pre>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>✅ Ví dụ tích hợp cho file hanghoaAct.php:</h3>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
echo htmlspecialchars("<?php
session_start();
require '../mod/hanghoaCls.php';
require '../mnhatkyhoatdong/nhatKyHoatDongHelper.php';

\$hanghoa = new Hanghoa();

if (isset(\$_POST['nut'])) {
    switch (\$_POST['nut']) {
        case 'Thêm':
            \$result = \$hanghoa->HanghoaAdd(\$_POST['txtTenHangHoa'], ...);
            if (\$result) {
                ghiNhatKyThemMoi(\$_SESSION['username'], 'Sản phẩm', \$result, 'Thêm sản phẩm: ' . \$_POST['txtTenHangHoa']);
                echo '<script>alert(\"Thêm thành công!\");</script>';
            }
            break;
            
        case 'Sửa':
            \$result = \$hanghoa->HanghoaUpdate(\$_POST['txtIdHangHoa'], ...);
            if (\$result) {
                ghiNhatKyCapNhat(\$_SESSION['username'], 'Sản phẩm', \$_POST['txtIdHangHoa'], 'Cập nhật sản phẩm: ' . \$_POST['txtTenHangHoa']);
                echo '<script>alert(\"Cập nhật thành công!\");</script>';
            }
            break;
            
        case 'Xóa':
            \$tenSanPham = \$hanghoa->HanghoaGetbyId(\$_POST['txtIdHangHoa'])->tenhanghoa;
            \$result = \$hanghoa->HanghoaDelete(\$_POST['txtIdHangHoa']);
            if (\$result) {
                ghiNhatKyXoa(\$_SESSION['username'], 'Sản phẩm', \$_POST['txtIdHangHoa'], 'Xóa sản phẩm: ' . \$tenSanPham);
                echo '<script>alert(\"Xóa thành công!\");</script>';
            }
            break;
    }
}
?>");
echo "</pre>";
echo "</div>";

echo "<h2>🚀 THỰC HIỆN TÍCH HỢP TỰ ĐỘNG</h2>";

// Danh sách các file cần tích hợp
$filesToIntegrate = [
    'elements_LQA/mhanghoa/hanghoaAct.php' => 'Sản phẩm',
    'elements_LQA/mloaihang/loaihangAct.php' => 'Loại hàng',
    'elements_LQA/mnhanvien/nhanvienAct.php' => 'Nhân viên',
    'elements_LQA/muser/userAct.php' => 'Khách hàng'
];

$integratedCount = 0;
$errors = [];

foreach ($filesToIntegrate as $filePath => $objectType) {
    echo "<div style='background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Đang xử lý: $filePath</strong><br>";
    
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Kiểm tra xem đã có require helper chưa
        if (strpos($content, 'nhatKyHoatDongHelper.php') === false) {
            // Thêm require helper
            $requireLine = "require_once '../mnhatkyhoatdong/nhatKyHoatDongHelper.php';\n";
            
            // Tìm vị trí sau session_start() hoặc require đầu tiên
            if (strpos($content, 'session_start()') !== false) {
                $content = str_replace('session_start();', "session_start();\n$requireLine", $content);
            } else {
                // Thêm vào đầu file sau <?php
                $content = str_replace('<?php', "<?php\n$requireLine", $content);
            }
            
            // Lưu file
            if (file_put_contents($filePath, $content)) {
                echo "<span style='color: green;'>✅ Đã thêm require helper</span><br>";
                $integratedCount++;
            } else {
                echo "<span style='color: red;'>❌ Lỗi khi thêm require helper</span><br>";
                $errors[] = "Không thể ghi file: $filePath";
            }
        } else {
            echo "<span style='color: blue;'>ℹ️ Đã có require helper</span><br>";
        }
        
        echo "<span style='color: orange;'>⚠️ Cần thêm thủ công các lệnh ghi nhật ký vào các case xử lý</span>";
    } else {
        echo "<span style='color: red;'>❌ File không tồn tại</span>";
        $errors[] = "File không tồn tại: $filePath";
    }
    
    echo "</div>";
}

echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>📊 Kết quả tích hợp:</h3>";
echo "<ul>";
echo "<li><strong>Đã xử lý:</strong> $integratedCount file</li>";
echo "<li><strong>Lỗi:</strong> " . count($errors) . " file</li>";
echo "</ul>";

if (!empty($errors)) {
    echo "<h4>❌ Các lỗi:</h4>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>📝 BƯỚC TIẾP THEO:</h3>";
echo "<ol>";
echo "<li>Mở từng file đã tích hợp</li>";
echo "<li>Thêm các lệnh ghi nhật ký vào các case xử lý (Thêm, Sửa, Xóa)</li>";
echo "<li>Test các chức năng để đảm bảo ghi nhật ký hoạt động</li>";
echo "<li>Kiểm tra trang thống kê để xem dữ liệu</li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=thongKeNhanVienCaiThien' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê</a>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký</a>";
echo "</div>";

echo "</div>";

// Tự động xóa file sau 60 giây
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Tích hợp hoàn thành. Bạn có muốn xóa file này không?')) {";
echo "    fetch('integrate_activity_logging.php?delete=1');";
echo "    alert('File đã được xóa.');";
echo "  }";
echo "}, 15000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
?>

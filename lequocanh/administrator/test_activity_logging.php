<?php
session_start();
require_once 'elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php';
require_once 'elements_LQA/mod/database.php';

echo "<h1>🧪 TEST HỆ THỐNG GHI NHẬT KÝ HOẠT ĐỘNG</h1>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";

$db = Database::getInstance();
$conn = $db->getConnection();

// Đếm số bản ghi trước khi test
$stmt = $conn->query("SELECT COUNT(*) as count FROM nhat_ky_hoat_dong");
$countBefore = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "<h2>📊 TRƯỚC KHI TEST:</h2>";
echo "<p><strong>Số bản ghi hiện tại:</strong> $countBefore</p>";

echo "<h2>🔬 BẮT ĐẦU TEST CÁC CHỨC NĂNG GHI NHẬT KÝ:</h2>";

$testResults = [];
$testUsername = 'test_user_' . date('His');

// Test 1: Ghi nhật ký đăng nhập
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🔐 Test 1: Ghi nhật ký đăng nhập</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyDangNhap('$testUsername')</p>";

$result1 = ghiNhatKyDangNhap($testUsername);
if ($result1) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Đăng nhập: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Đăng nhập: FAIL";
}
echo "</div>";

// Test 2: Ghi nhật ký thêm mới
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>➕ Test 2: Ghi nhật ký thêm mới</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyThemMoi('$testUsername', 'Sản phẩm', 123, 'Thêm sản phẩm test')</p>";

$result2 = ghiNhatKyThemMoi($testUsername, 'Sản phẩm', 123, 'Thêm sản phẩm test');
if ($result2) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Thêm mới: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Thêm mới: FAIL";
}
echo "</div>";

// Test 3: Ghi nhật ký cập nhật
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>✏️ Test 3: Ghi nhật ký cập nhật</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyCapNhat('$testUsername', 'Khách hàng', 456, 'Cập nhật thông tin khách hàng')</p>";

$result3 = ghiNhatKyCapNhat($testUsername, 'Khách hàng', 456, 'Cập nhật thông tin khách hàng');
if ($result3) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Cập nhật: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Cập nhật: FAIL";
}
echo "</div>";

// Test 4: Ghi nhật ký xóa
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🗑️ Test 4: Ghi nhật ký xóa</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyXoa('$testUsername', 'Đơn hàng', 789, 'Xóa đơn hàng test')</p>";

$result4 = ghiNhatKyXoa($testUsername, 'Đơn hàng', 789, 'Xóa đơn hàng test');
if ($result4) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Xóa: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Xóa: FAIL";
}
echo "</div>";

// Test 5: Ghi nhật ký xem
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>👁️ Test 5: Ghi nhật ký xem</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyXem('$testUsername', 'Báo cáo', null, 'Xem báo cáo doanh thu')</p>";

$result5 = ghiNhatKyXem($testUsername, 'Báo cáo', null, 'Xem báo cáo doanh thu');
if ($result5) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Xem: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Xem: FAIL";
}
echo "</div>";

// Test 6: Ghi nhật ký đăng xuất
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>🚪 Test 6: Ghi nhật ký đăng xuất</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyDangXuat('$testUsername')</p>";

$result6 = ghiNhatKyDangXuat($testUsername);
if ($result6) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Đăng xuất: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Đăng xuất: FAIL";
}
echo "</div>";

// Test 7: Ghi nhật ký trực tiếp
echo "<div style='background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>⚡ Test 7: Ghi nhật ký trực tiếp</h3>";
echo "<p><strong>Đang test:</strong> ghiNhatKyHoatDong('$testUsername', 'Test trực tiếp', 'Hệ thống', null, 'Test function gốc')</p>";

$result7 = ghiNhatKyHoatDong($testUsername, 'Test trực tiếp', 'Hệ thống', null, 'Test function gốc');
if ($result7) {
    echo "<span style='color: green; font-weight: bold;'>✅ THÀNH CÔNG</span>";
    $testResults[] = "✅ Ghi trực tiếp: PASS";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ THẤT BẠI</span>";
    $testResults[] = "❌ Ghi trực tiếp: FAIL";
}
echo "</div>";

// Đếm số bản ghi sau khi test
$stmt = $conn->query("SELECT COUNT(*) as count FROM nhat_ky_hoat_dong");
$countAfter = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "<h2>📊 SAU KHI TEST:</h2>";
echo "<p><strong>Số bản ghi trước test:</strong> $countBefore</p>";
echo "<p><strong>Số bản ghi sau test:</strong> $countAfter</p>";
echo "<p><strong>Số bản ghi đã thêm:</strong> " . ($countAfter - $countBefore) . "</p>";

// Hiển thị dữ liệu vừa ghi
echo "<h2>📋 DỮ LIỆU VỪA GHI:</h2>";
$stmt = $conn->prepare("SELECT * FROM nhat_ky_hoat_dong WHERE username = ? ORDER BY thoi_gian DESC");
$stmt->execute([$testUsername]);
$testData = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($testData) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Đối tượng ID</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    
    foreach ($testData as $row) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $row['id'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['hanh_dong']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['doi_tuong']) . "</td>";
        echo "<td style='padding: 8px;'>" . ($row['doi_tuong_id'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['chi_tiet'] ?? '') . "</td>";
        echo "<td style='padding: 8px;'>" . $row['thoi_gian'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>❌ Không tìm thấy dữ liệu test nào!</strong></p>";
    echo "</div>";
}

// Tóm tắt kết quả
echo "<h2>🎯 TÓM TẮT KẾT QUẢ TEST:</h2>";
echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";

$passCount = 0;
$failCount = 0;

echo "<ul style='font-size: 16px;'>";
foreach ($testResults as $result) {
    echo "<li>$result</li>";
    if (strpos($result, '✅') !== false) {
        $passCount++;
    } else {
        $failCount++;
    }
}
echo "</ul>";

echo "<div style='margin-top: 20px;'>";
echo "<p><strong>📊 Thống kê:</strong></p>";
echo "<ul>";
echo "<li><span style='color: green; font-weight: bold;'>✅ Thành công: $passCount test</span></li>";
echo "<li><span style='color: red; font-weight: bold;'>❌ Thất bại: $failCount test</span></li>";
echo "<li><strong>📈 Tỷ lệ thành công: " . round(($passCount / count($testResults)) * 100, 2) . "%</strong></li>";
echo "</ul>";
echo "</div>";

if ($passCount == count($testResults)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 TẤT CẢ TEST ĐỀU THÀNH CÔNG!</h3>";
    echo "<p>Hệ thống ghi nhật ký hoạt động hoàn toàn bình thường.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>⚠️ CÓ TEST THẤT BẠI!</h3>";
    echo "<p>Cần kiểm tra lại hệ thống ghi nhật ký.</p>";
    echo "</div>";
}

echo "</div>";

// Test cách thức hoạt động
echo "<h2>🔍 CÁCH THỨC HOẠT ĐỘNG CỦA HỆ THỐNG:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>📝 Quy trình ghi nhật ký:</h3>";
echo "<ol>";
echo "<li><strong>Helper Functions:</strong> Các function như ghiNhatKyDangNhap(), ghiNhatKyThemMoi()...</li>";
echo "<li><strong>Core Function:</strong> Tất cả đều gọi ghiNhatKyHoatDong()</li>";
echo "<li><strong>Class NhatKyHoatDong:</strong> Xử lý logic và validation</li>";
echo "<li><strong>Database:</strong> Lưu vào bảng nhat_ky_hoat_dong</li>";
echo "<li><strong>Timestamp:</strong> Tự động ghi thời gian hiện tại</li>";
echo "</ol>";

echo "<h3>🔧 Cách tích hợp vào code:</h3>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// Trong file xử lý (ví dụ: hanghoaAct.php)\n";
echo "require_once '../mnhatkyhoatdong/nhatKyHoatDongHelper.php';\n\n";
echo "// Sau khi thêm sản phẩm thành công\n";
echo "if (\$result) {\n";
echo "    ghiNhatKyThemMoi(\$_SESSION['username'], 'Sản phẩm', \$newId, 'Thêm: ' . \$tenSanPham);\n";
echo "}";
echo "</pre>";
echo "</div>";

// Nút hành động
echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=thongKeNhanVienCaiThien' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê</a>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký</a>";
echo "</div>";

// Nút xóa dữ liệu test
echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h3>🧹 Dọn dẹp dữ liệu test:</h3>";
echo "<p>Dữ liệu test với username '$testUsername' sẽ được xóa tự động sau 30 giây.</p>";
echo "<button onclick='deleteTestData()' style='background: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;'>🗑️ Xóa ngay</button>";
echo "</div>";

echo "</div>";

// JavaScript để xóa dữ liệu test
echo "<script>";
echo "function deleteTestData() {";
echo "  if (confirm('Bạn có chắc muốn xóa dữ liệu test?')) {";
echo "    fetch('test_activity_logging.php?delete_test=1&username=$testUsername')";
echo "      .then(response => response.text())";
echo "      .then(data => {";
echo "        alert('Dữ liệu test đã được xóa!');";
echo "        location.reload();";
echo "      });";
echo "  }";
echo "}";

echo "setTimeout(function() {";
echo "  deleteTestData();";
echo "}, 30000);";
echo "</script>";

// Xử lý xóa dữ liệu test
if (isset($_GET['delete_test']) && isset($_GET['username'])) {
    $stmt = $conn->prepare("DELETE FROM nhat_ky_hoat_dong WHERE username = ?");
    $result = $stmt->execute([$_GET['username']]);
    echo $result ? "Đã xóa dữ liệu test" : "Lỗi khi xóa";
    exit;
}
?>

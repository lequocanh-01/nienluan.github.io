<?php
/**
 * Giải thích vấn đề biểu đồ hiển thị dữ liệu mặc dù bảng không có dữ liệu
 */

require_once '../administrator/elements_LQA/mod/database.php';

echo "<h1>🔍 Giải thích vấn đề biểu đồ hiển thị dữ liệu</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Không thể kết nối database");
    }
    
    echo "<p style='color: green;'>✅ Kết nối database thành công!</p>";
    
    // 1. Mô tả vấn đề
    echo "<h2>❓ 1. Vấn đề gặp phải</h2>";
    
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
    echo "<h3 style='color: #856404;'>🚨 Hiện tượng:</h3>";
    echo "<ul style='color: #856404;'>";
    echo "<li><strong>Bảng thống kê:</strong> Hiển thị tất cả giá trị = 0 (không có hoạt động)</li>";
    echo "<li><strong>Biểu đồ:</strong> Vẫn hiển thị và có thể có đường biểu đồ</li>";
    echo "<li><strong>Kết quả:</strong> Người dùng bối rối vì thấy biểu đồ nhưng không có dữ liệu thực tế</li>";
    echo "</ul>";
    echo "</div>";
    
    // 2. Nguyên nhân
    echo "<h2>🔍 2. Nguyên nhân gốc rễ</h2>";
    
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h3 style='color: #721c24;'>⚠️ Vấn đề trong code:</h3>";
    
    echo "<h4>🔧 Vấn đề 1: Logic tạo dữ liệu biểu đồ</h4>";
    echo "<div style='background: #f1f3f4; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<code style='color: #d63384;'>";
    echo "// Code cũ - LUÔN tạo dữ liệu cho mỗi ngày<br>";
    echo "foreach (\$dateRange as \$date) {<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;\$tongHoatDong = \$nhatKyObj->demTongSoNhatKy(\$filters); // Có thể = 0<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;// Vẫn thêm vào mảng dù = 0<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;\$thongKeNgay[] = [...]; <br>";
    echo "}";
    echo "</code>";
    echo "</div>";
    
    echo "<h4>🔧 Vấn đề 2: JavaScript luôn tạo biểu đồ</h4>";
    echo "<div style='background: #f1f3f4; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<code style='color: #d63384;'>";
    echo "// Code cũ - LUÔN tạo biểu đồ<br>";
    echo "var activityChart = new Chart(ctx, {<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;// Dữ liệu có thể toàn bộ = 0 nhưng vẫn tạo biểu đồ<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;data: { labels: labels, datasets: [...] }<br>";
    echo "});";
    echo "</code>";
    echo "</div>";
    
    echo "<h4>🔧 Vấn đề 3: Không kiểm tra dữ liệu thực tế</h4>";
    echo "<p style='color: #721c24;'>Code cũ không có cơ chế kiểm tra xem có dữ liệu thực tế (> 0) hay không trước khi hiển thị biểu đồ.</p>";
    echo "</div>";
    
    // 3. Giải pháp đã áp dụng
    echo "<h2>✅ 3. Giải pháp đã áp dụng</h2>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3 style='color: #155724;'>🛠️ Các cải tiến:</h3>";
    
    echo "<h4>✅ Cải tiến 1: Thêm biến kiểm tra dữ liệu</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<code style='color: #28a745;'>";
    echo "// Code mới - Kiểm tra có dữ liệu thực tế<br>";
    echo "\$coDataThongKe = false;<br>";
    echo "if (\$tongHoatDong > 0 || \$soLanDangNhap > 0 || ...) {<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;\$coDataThongKe = true;<br>";
    echo "}";
    echo "</code>";
    echo "</div>";
    
    echo "<h4>✅ Cải tiến 2: Hiển thị có điều kiện</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<code style='color: #28a745;'>";
    echo "// Code mới - Chỉ hiển thị biểu đồ khi có dữ liệu<br>";
    echo "&lt;?php if (\$coDataThongKe): ?&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"chart-container\"&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;canvas id=\"activityChart\"&gt;&lt;/canvas&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>";
    echo "&lt;?php else: ?&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"no-data-container\"&gt;Không có dữ liệu&lt;/div&gt;<br>";
    echo "&lt;?php endif; ?&gt;";
    echo "</code>";
    echo "</div>";
    
    echo "<h4>✅ Cải tiến 3: JavaScript thông minh</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "<code style='color: #28a745;'>";
    echo "// Code mới - Chỉ tạo biểu đồ khi có dữ liệu<br>";
    echo "&lt;?php if (\$coDataThongKe): ?&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;var activityChart = new Chart(ctx, {...});<br>";
    echo "&lt;?php else: ?&gt;<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;console.log('Không có dữ liệu để hiển thị biểu đồ');<br>";
    echo "&lt;?php endif; ?&gt;";
    echo "</code>";
    echo "</div>";
    echo "</div>";
    
    // 4. Kiểm tra dữ liệu hiện tại
    echo "<h2>📊 4. Kiểm tra dữ liệu hiện tại</h2>";
    
    $checkTableSql = "SHOW TABLES LIKE 'nhat_ky_hoat_dong'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() > 0) {
        $countSql = "SELECT COUNT(*) as total FROM nhat_ky_hoat_dong";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total > 0) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3 style='color: #155724;'>✅ Có dữ liệu trong bảng nhật ký</h3>";
            echo "<p style='color: #155724;'><strong>Tổng số bản ghi:</strong> $total</p>";
            echo "<p style='color: #155724;'>Biểu đồ sẽ hiển thị bình thường với dữ liệu thực tế.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3 style='color: #856404;'>⚠️ Bảng nhật ký trống</h3>";
            echo "<p style='color: #856404;'>Không có dữ liệu trong bảng nhật ký hoạt động.</p>";
            echo "<p style='color: #856404;'>Biểu đồ sẽ hiển thị thông báo 'Không có dữ liệu' thay vì biểu đồ trống.</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3 style='color: #721c24;'>❌ Bảng nhật ký chưa tồn tại</h3>";
        echo "<p style='color: #721c24;'>Bảng nhat_ky_hoat_dong chưa được tạo.</p>";
        echo "</div>";
    }
    
    // 5. Hướng dẫn test
    echo "<h2>🧪 5. Hướng dẫn test giải pháp</h2>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📋 Các bước test:</h3>";
    
    echo "<h4>🔍 Test 1: Khi không có dữ liệu</h4>";
    echo "<ol>";
    echo "<li>Đảm bảo bảng nhật ký trống hoặc không có dữ liệu trong khoảng thời gian</li>";
    echo "<li>Truy cập trang thống kê hoạt động</li>";
    echo "<li><strong>Kết quả mong đợi:</strong> Hiển thị thông báo 'Không có dữ liệu' thay vì biểu đồ trống</li>";
    echo "</ol>";
    
    echo "<h4>✅ Test 2: Khi có dữ liệu</h4>";
    echo "<ol>";
    echo "<li>Tạo dữ liệu mẫu bằng tool trong thư mục tools/</li>";
    echo "<li>Truy cập trang thống kê hoạt động</li>";
    echo "<li><strong>Kết quả mong đợi:</strong> Hiển thị biểu đồ với dữ liệu thực tế</li>";
    echo "</ol>";
    echo "</div>";
    
    // Links hữu ích
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<h3>🔗 Links test</h3>";
    echo "<a href='../administrator/index.php?req=thongKeHoatDongView' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê hoạt động</a>";
    echo "<a href='../administrator/index.php?req=nhatKyHoatDongView' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký chi tiết</a>";
    echo "<a href='../tools/tao_du_lieu_test_hom_nay.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🧪 Tạo dữ liệu test</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi kết nối database</h3>";
    echo "<p style='color: #721c24;'>Lỗi: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}

h1, h2, h3, h4 {
    color: #2c3e50;
}

code {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    display: block;
    margin: 5px 0;
    line-height: 1.4;
}

button:hover, a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

ol, ul {
    line-height: 1.6;
}

li {
    margin-bottom: 8px;
}
</style>

<?php
/**
 * Script giải thích cách hoạt động của hệ thống thống kê hoạt động nhân viên
 */

require_once '../administrator/elements_LQA/mod/database.php';

echo "<h1>📊 Giải thích hệ thống thống kê hoạt động nhân viên</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Không thể kết nối database");
    }
    
    echo "<p style='color: green;'>✅ Kết nối database thành công!</p>";
    
    // 1. Giải thích cấu trúc bảng nhật ký hoạt động
    echo "<h2>🗃️ 1. Cấu trúc bảng nhật ký hoạt động</h2>";
    
    $checkTableSql = "SHOW TABLES LIKE 'nhat_ky_hoat_dong'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3 style='color: #155724;'>✅ Bảng nhật ký hoạt động tồn tại</h3>";
        
        // Hiển thị cấu trúc bảng
        $structureSql = "DESCRIBE nhat_ky_hoat_dong";
        $structureStmt = $conn->prepare($structureSql);
        $structureStmt->execute();
        $structure = $structureStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📋 Cấu trúc bảng:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f2f2f2;'>";
        echo "<th style='padding: 8px;'>Tên cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "<th style='padding: 8px;'>Mặc định</th>";
        echo "<th style='padding: 8px;'>Mô tả</th>";
        echo "</tr>";
        
        $descriptions = [
            'id' => 'ID tự tăng, khóa chính',
            'username' => 'Tên đăng nhập của người thực hiện',
            'hanh_dong' => 'Loại hành động (đăng nhập, thêm mới, cập nhật, xóa)',
            'doi_tuong' => 'Đối tượng bị tác động (sản phẩm, đơn hàng, nhân viên...)',
            'doi_tuong_id' => 'ID của đối tượng bị tác động',
            'chi_tiet' => 'Mô tả chi tiết về hành động',
            'ip_address' => 'Địa chỉ IP của người thực hiện',
            'thoi_gian' => 'Thời gian thực hiện hành động'
        ];
        
        foreach ($structure as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px;'><strong>" . $column['Field'] . "</strong></td>";
            echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Default'] . "</td>";
            echo "<td style='padding: 8px;'>" . ($descriptions[$column['Field']] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3 style='color: #721c24;'>❌ Bảng nhật ký hoạt động chưa tồn tại</h3>";
        echo "</div>";
    }
    
    // 2. Giải thích cách thống kê hoạt động
    echo "<h2>📊 2. Cách thống kê hoạt động</h2>";
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🔍 Quy trình thống kê:</h3>";
    
    echo "<h4>📅 Thống kê theo ngày:</h4>";
    echo "<ol>";
    echo "<li><strong>Lấy khoảng thời gian:</strong> Từ ngày đến ngày (mặc định 30 ngày gần nhất)</li>";
    echo "<li><strong>Tạo vòng lặp ngày:</strong> Duyệt từng ngày trong khoảng thời gian</li>";
    echo "<li><strong>Đếm hoạt động:</strong> Đếm số lượng từng loại hoạt động trong ngày</li>";
    echo "<li><strong>Tạo dữ liệu biểu đồ:</strong> Chuyển đổi thành format cho Chart.js</li>";
    echo "</ol>";
    
    echo "<h4>👥 Thống kê theo nhân viên:</h4>";
    echo "<ol>";
    echo "<li><strong>Lấy danh sách nhân viên:</strong> Từ bảng nhanvien + user admin</li>";
    echo "<li><strong>Lọc theo username:</strong> Đếm hoạt động của từng nhân viên</li>";
    echo "<li><strong>Phân loại hành động:</strong> Đăng nhập, thêm mới, cập nhật, xóa</li>";
    echo "<li><strong>Hiển thị bảng:</strong> Tổng hợp kết quả trong bảng</li>";
    echo "</ol>";
    echo "</div>";
    
    // 3. Giải thích các loại hành động
    echo "<h2>🎯 3. Các loại hành động được ghi nhận</h2>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📝 Danh sách hành động:</h3>";
    
    $actions = [
        'đăng nhập' => 'Khi người dùng đăng nhập vào hệ thống',
        'thêm mới' => 'Khi tạo mới sản phẩm, đơn hàng, nhân viên...',
        'cập nhật' => 'Khi chỉnh sửa thông tin các đối tượng',
        'xóa' => 'Khi xóa dữ liệu khỏi hệ thống',
        'xem' => 'Khi xem báo cáo, thống kê...',
        'tìm kiếm' => 'Khi thực hiện tìm kiếm dữ liệu'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f2f2f2;'>";
    echo "<th style='padding: 10px;'>Hành động</th>";
    echo "<th style='padding: 10px;'>Mô tả</th>";
    echo "</tr>";
    
    foreach ($actions as $action => $description) {
        echo "<tr>";
        echo "<td style='padding: 10px;'><strong>$action</strong></td>";
        echo "<td style='padding: 10px;'>$description</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 4. Hướng dẫn sử dụng
    echo "<h2>📖 4. Hướng dẫn sử dụng</h2>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🚀 Cách sử dụng hệ thống thống kê:</h3>";
    
    echo "<h4>👀 Xem thống kê:</h4>";
    echo "<ol>";
    echo "<li>Truy cập menu <strong>Nhật ký hoạt động > Thống kê hoạt động</strong></li>";
    echo "<li>Chọn khoảng thời gian muốn xem (từ ngày - đến ngày)</li>";
    echo "<li>Xem biểu đồ hoạt động theo ngày</li>";
    echo "<li>Xem bảng thống kê hoạt động theo nhân viên</li>";
    echo "</ol>";
    
    echo "<h4>🔍 Debug khi có vấn đề:</h4>";
    echo "<ol>";
    echo "<li>Thêm <code>?debug=1</code> vào URL để xem thông tin debug</li>";
    echo "<li>Sử dụng các tool debug trong thư mục <code>tools/</code></li>";
    echo "<li>Tạo dữ liệu test để kiểm tra hệ thống</li>";
    echo "</ol>";
    echo "</div>";
    
    // 5. Tạo dữ liệu mẫu
    echo "<h2>🧪 5. Tạo dữ liệu mẫu</h2>";
    
    if (isset($_POST['create_sample'])) {
        try {
            $sampleData = [
                ['admin', 'đăng nhập', 'hệ thống', null, 'Đăng nhập để kiểm tra hệ thống'],
                ['admin', 'xem', 'thống kê', null, 'Xem thống kê hoạt động nhân viên'],
                ['leuquocanh', 'đăng nhập', 'hệ thống', null, 'Đăng nhập làm việc'],
                ['leuquocanh', 'thêm mới', 'sản phẩm', 1, 'Thêm sản phẩm mới vào kho'],
                ['leuquocanh05', 'cập nhật', 'đơn hàng', 1, 'Cập nhật trạng thái đơn hàng']
            ];
            
            $insertSql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, ip_address, thoi_gian) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            
            $insertedCount = 0;
            foreach ($sampleData as $data) {
                $result = $insertStmt->execute([
                    $data[0], $data[1], $data[2], $data[3], $data[4], '127.0.0.1', date('Y-m-d H:i:s')
                ]);
                if ($result) $insertedCount++;
            }
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3 style='color: #155724;'>✅ Đã tạo $insertedCount bản ghi mẫu</h3>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3 style='color: #721c24;'>❌ Lỗi tạo dữ liệu mẫu</h3>";
            echo "<p style='color: #721c24;'>Lỗi: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    } else {
        echo "<form method='post' style='text-align: center; margin: 30px 0;'>";
        echo "<button type='submit' name='create_sample' style='background: #17a2b8; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;'>";
        echo "🧪 Tạo dữ liệu mẫu";
        echo "</button>";
        echo "</form>";
    }
    
    // Links hữu ích
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<h3>🔗 Links hữu ích</h3>";
    echo "<a href='../administrator/index.php?req=thongKeHoatDongView' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Xem thống kê hoạt động</a>";
    echo "<a href='../administrator/index.php?req=nhatKyHoatDongView' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Xem nhật ký chi tiết</a>";
    echo "<a href='../tools/debug_query_nhat_ky.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔍 Debug query</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi</h3>";
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

table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

th, td {
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
}

tr:hover {
    background-color: #f5f5f5;
}

code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
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

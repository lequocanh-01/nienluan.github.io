<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/nhatKyHoatDongCls.php';

echo "<h1>🔍 DEBUG QUERY NHẬT KÝ</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>1. KIỂM TRA DỮ LIỆU THỰC TẾ:</h2>";
    
    // Đếm tổng số bản ghi
    $stmt = $conn->query("SELECT COUNT(*) as total FROM nhat_ky_hoat_dong");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Tổng số bản ghi trong database:</strong> $total</p>";
    
    // Hiển thị 5 bản ghi gần nhất
    echo "<h3>5 bản ghi gần nhất:</h3>";
    $stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 8px;'>ID</th>";
    echo "<th style='padding: 8px;'>Username</th>";
    echo "<th style='padding: 8px;'>Hành động</th>";
    echo "<th style='padding: 8px;'>Đối tượng</th>";
    echo "<th style='padding: 8px;'>Chi tiết</th>";
    echo "<th style='padding: 8px;'>Thời gian</th>";
    echo "</tr>";
    
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $record['id'] . "</td>";
        echo "<td style='padding: 8px;'>" . $record['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $record['hanh_dong'] . "</td>";
        echo "<td style='padding: 8px;'>" . $record['doi_tuong'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($record['chi_tiet'] ?: $record['noi_dung'] ?: 'N/A') . "</td>";
        echo "<td style='padding: 8px;'>" . $record['thoi_gian'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>2. TEST CLASS NhatKyHoatDong:</h2>";
    
    $nhatKyObj = new NhatKyHoatDong();
    
    // Test không có filter
    echo "<h3>Test layDanhSachNhatKy() không filter:</h3>";
    $result1 = $nhatKyObj->layDanhSachNhatKy([], 10, 0);
    echo "<p><strong>Số bản ghi trả về:</strong> " . count($result1) . "</p>";
    
    if (count($result1) > 0) {
        echo "<p><strong>Bản ghi đầu tiên:</strong></p>";
        echo "<pre>" . print_r($result1[0], true) . "</pre>";
    }
    
    // Test với filter ngày hôm nay
    echo "<h3>Test với filter ngày hôm nay:</h3>";
    $filters = [
        'tu_ngay' => date('Y-m-d'),
        'den_ngay' => date('Y-m-d')
    ];
    $result2 = $nhatKyObj->layDanhSachNhatKy($filters, 10, 0);
    echo "<p><strong>Số bản ghi hôm nay:</strong> " . count($result2) . "</p>";
    
    // Test với filter 7 ngày qua
    echo "<h3>Test với filter 7 ngày qua:</h3>";
    $filters3 = [
        'tu_ngay' => date('Y-m-d', strtotime('-7 days')),
        'den_ngay' => date('Y-m-d')
    ];
    $result3 = $nhatKyObj->layDanhSachNhatKy($filters3, 10, 0);
    echo "<p><strong>Số bản ghi 7 ngày qua:</strong> " . count($result3) . "</p>";
    
    // Test demTongSoNhatKy
    echo "<h3>Test demTongSoNhatKy():</h3>";
    $count1 = $nhatKyObj->demTongSoNhatKy([]);
    echo "<p><strong>Tổng số (không filter):</strong> $count1</p>";
    
    $count2 = $nhatKyObj->demTongSoNhatKy($filters);
    echo "<p><strong>Tổng số (hôm nay):</strong> $count2</p>";
    
    $count3 = $nhatKyObj->demTongSoNhatKy($filters3);
    echo "<p><strong>Tổng số (7 ngày qua):</strong> $count3</p>";
    
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>3. KIỂM TRA FILTER HIỆN TẠI:</h2>";
    
    // Mô phỏng filter từ URL
    $tuNgay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-d', strtotime('-30 days'));
    $denNgay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');
    $selectedUsername = isset($_GET['username']) ? $_GET['username'] : '';
    $selectedHanhDong = isset($_GET['hanh_dong']) ? $_GET['hanh_dong'] : '';
    
    echo "<p><strong>Từ ngày:</strong> $tuNgay</p>";
    echo "<p><strong>Đến ngày:</strong> $denNgay</p>";
    echo "<p><strong>Username:</strong> " . ($selectedUsername ?: 'Tất cả') . "</p>";
    echo "<p><strong>Hành động:</strong> " . ($selectedHanhDong ?: 'Tất cả') . "</p>";
    
    $filters_current = [];
    if (!empty($selectedUsername)) {
        $filters_current['username'] = $selectedUsername;
    }
    if (!empty($selectedHanhDong)) {
        $filters_current['hanh_dong'] = $selectedHanhDong;
    }
    $filters_current['tu_ngay'] = $tuNgay;
    $filters_current['den_ngay'] = $denNgay;
    
    echo "<h3>Test với filter hiện tại:</h3>";
    $result_current = $nhatKyObj->layDanhSachNhatKy($filters_current, 10, 0);
    echo "<p><strong>Số bản ghi với filter hiện tại:</strong> " . count($result_current) . "</p>";
    
    if (count($result_current) > 0) {
        echo "<p><strong>Bản ghi đầu tiên:</strong></p>";
        echo "<pre>" . print_r($result_current[0], true) . "</pre>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi: " . $e->getMessage() . "</h3>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop&tab=chitiet' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Quay lại nhật ký</a>";
echo "<a href='debug_query_nhat_ky.php?tu_ngay=" . date('Y-m-d', strtotime('-7 days')) . "&den_ngay=" . date('Y-m-d') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔍 Test với 7 ngày qua</a>";
echo "</div>";
?>

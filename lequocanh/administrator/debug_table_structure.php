<?php
session_start();
require_once 'elements_LQA/mod/database.php';

echo "<h1>🔍 DEBUG CẤU TRÚC BẢNG</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>1. KIỂM TRA BẢNG NHANVIEN:</h2>";
    
    // Kiểm tra bảng nhanvien có tồn tại không
    $stmt = $conn->query("SHOW TABLES LIKE 'nhanvien'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Bảng nhanvien tồn tại</p>";
        
        // Hiển thị cấu trúc bảng
        echo "<h3>Cấu trúc bảng nhanvien:</h3>";
        $stmt = $conn->query("DESCRIBE nhanvien");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='padding: 8px;'>Tên cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $column['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Hiển thị một vài bản ghi mẫu
        $stmt = $conn->query("SELECT * FROM nhanvien LIMIT 3");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($records) > 0) {
            echo "<h3>Dữ liệu mẫu:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #28a745; color: white;'>";
            foreach (array_keys($records[0]) as $key) {
                echo "<th style='padding: 8px;'>$key</th>";
            }
            echo "</tr>";
            
            foreach ($records as $record) {
                echo "<tr>";
                foreach ($record as $value) {
                    echo "<td style='padding: 8px;'>" . ($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ Bảng nhanvien không có dữ liệu</p>";
        }
        
    } else {
        echo "<p>❌ Bảng nhanvien không tồn tại</p>";
    }
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>2. KIỂM TRA BẢNG USER:</h2>";
    
    // Kiểm tra bảng user
    $stmt = $conn->query("SHOW TABLES LIKE 'user'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Bảng user tồn tại</p>";
        
        // Hiển thị cấu trúc bảng
        echo "<h3>Cấu trúc bảng user:</h3>";
        $stmt = $conn->query("DESCRIBE user");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='padding: 8px;'>Tên cột</th>";
        echo "<th style='padding: 8px;'>Kiểu dữ liệu</th>";
        echo "<th style='padding: 8px;'>Null</th>";
        echo "<th style='padding: 8px;'>Key</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $column['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Hiển thị một vài bản ghi mẫu
        $stmt = $conn->query("SELECT * FROM user LIMIT 3");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($records) > 0) {
            echo "<h3>Dữ liệu mẫu:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #28a745; color: white;'>";
            foreach (array_keys($records[0]) as $key) {
                echo "<th style='padding: 8px;'>$key</th>";
            }
            echo "</tr>";
            
            foreach ($records as $record) {
                echo "<tr>";
                foreach ($record as $value) {
                    echo "<td style='padding: 8px;'>" . ($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ Bảng user không có dữ liệu</p>";
        }
        
    } else {
        echo "<p>❌ Bảng user không tồn tại</p>";
    }
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h2>3. TEST JOIN QUERY:</h2>";
    
    // Test query đơn giản
    echo "<h3>Test query từ bảng nhat_ky_hoat_dong:</h3>";
    $stmt = $conn->query("SELECT * FROM nhat_ky_hoat_dong LIMIT 3");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($records) > 0) {
        echo "<p>✅ Query thành công, có " . count($records) . " bản ghi</p>";
        echo "<pre>" . print_r($records[0], true) . "</pre>";
    } else {
        echo "<p>❌ Không có dữ liệu</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi: " . $e->getMessage() . "</h3>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop&tab=chitiet' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Quay lại nhật ký</a>";
echo "</div>";
?>

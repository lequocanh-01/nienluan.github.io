<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Debug truy vấn SQL cho bảng orders</h2>";

// Kiểm tra xem bảng orders có tồn tại không
try {
    $checkTableSql = "SHOW TABLES LIKE 'orders'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() > 0) {
        echo "<p style='color: green;'>Bảng orders tồn tại!</p>";
        
        // Kiểm tra cấu trúc bảng
        $descTableSql = "DESCRIBE orders";
        $descTableStmt = $conn->prepare($descTableSql);
        $descTableStmt->execute();
        $columns = $descTableStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng orders:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th style='padding: 8px; text-align: left;'>Field</th>";
        echo "<th style='padding: 8px; text-align: left;'>Type</th>";
        echo "<th style='padding: 8px; text-align: left;'>Null</th>";
        echo "<th style='padding: 8px; text-align: left;'>Key</th>";
        echo "<th style='padding: 8px; text-align: left;'>Default</th>";
        echo "<th style='padding: 8px; text-align: left;'>Extra</th>";
        echo "</tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Đếm số lượng bản ghi
        $countSql = "SELECT COUNT(*) as count FROM orders";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute();
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Số lượng đơn hàng: " . $count['count'] . "</p>";
        
        // Thử các truy vấn khác nhau
        echo "<h3>Thử các truy vấn khác nhau:</h3>";
        
        // Truy vấn 1: Lấy tất cả đơn hàng
        echo "<h4>Truy vấn 1: SELECT * FROM orders</h4>";
        $query1 = "SELECT * FROM orders";
        $stmt1 = $conn->prepare($query1);
        $stmt1->execute();
        $results1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Số lượng kết quả: " . count($results1) . "</p>";
        if (count($results1) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            foreach (array_keys($results1[0]) as $key) {
                echo "<th style='padding: 8px; text-align: left;'>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            foreach ($results1 as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>Không có kết quả!</p>";
        }
        
        // Truy vấn 2: Lấy đơn hàng với JOIN bảng user
        echo "<h4>Truy vấn 2: SELECT o.*, u.hoten FROM orders o LEFT JOIN user u ON o.user_id = u.username</h4>";
        
        // Kiểm tra xem bảng user có tồn tại không
        $checkUserTableSql = "SHOW TABLES LIKE 'user'";
        $checkUserTableStmt = $conn->prepare($checkUserTableSql);
        $checkUserTableStmt->execute();
        $hasUserTable = $checkUserTableStmt->rowCount() > 0;
        
        if ($hasUserTable) {
            echo "<p style='color: green;'>Bảng user tồn tại!</p>";
            
            // Kiểm tra cấu trúc bảng user
            $descUserTableSql = "DESCRIBE user";
            $descUserTableStmt = $conn->prepare($descUserTableSql);
            $descUserTableStmt->execute();
            $userColumns = $descUserTableStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h5>Cấu trúc bảng user:</h5>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f2f2f2;'>";
            echo "<th style='padding: 8px; text-align: left;'>Field</th>";
            echo "<th style='padding: 8px; text-align: left;'>Type</th>";
            echo "<th style='padding: 8px; text-align: left;'>Null</th>";
            echo "<th style='padding: 8px; text-align: left;'>Key</th>";
            echo "<th style='padding: 8px; text-align: left;'>Default</th>";
            echo "<th style='padding: 8px; text-align: left;'>Extra</th>";
            echo "</tr>";
            
            foreach ($userColumns as $column) {
                echo "<tr>";
                foreach ($column as $key => $value) {
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Thực hiện truy vấn JOIN
            try {
                $query2 = "SELECT o.*, u.hoten FROM orders o LEFT JOIN user u ON o.user_id = u.username";
                $stmt2 = $conn->prepare($query2);
                $stmt2->execute();
                $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p>Số lượng kết quả: " . count($results2) . "</p>";
                if (count($results2) > 0) {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr style='background-color: #f2f2f2;'>";
                    foreach (array_keys($results2[0]) as $key) {
                        echo "<th style='padding: 8px; text-align: left;'>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($results2 as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td style='padding: 8px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: red;'>Không có kết quả!</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Lỗi khi thực hiện truy vấn JOIN: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Bảng user không tồn tại!</p>";
        }
        
        // Kiểm tra xem cột user_id có tồn tại trong bảng orders không
        $hasUserIdColumn = false;
        foreach ($columns as $column) {
            if ($column['Field'] == 'user_id') {
                $hasUserIdColumn = true;
                break;
            }
        }
        
        echo "<p>Cột user_id trong bảng orders: " . ($hasUserIdColumn ? "<span style='color: green;'>Có</span>" : "<span style='color: red;'>Không</span>") . "</p>";
        
        // Kiểm tra xem có bản ghi nào trong bảng orders có user_id không NULL
        if ($hasUserIdColumn) {
            $countUserIdSql = "SELECT COUNT(*) as count FROM orders WHERE user_id IS NOT NULL";
            $countUserIdStmt = $conn->prepare($countUserIdSql);
            $countUserIdStmt->execute();
            $countUserId = $countUserIdStmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Số lượng đơn hàng có user_id không NULL: " . $countUserId['count'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Bảng orders không tồn tại!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

// Kiểm tra file orders.php
echo "<h3>Kiểm tra file orders.php:</h3>";
$ordersFile = 'administrator/elements_LQA/madmin/orders.php';

if (file_exists($ordersFile)) {
    echo "<p style='color: green;'>File orders.php tồn tại!</p>";
    
    // Kiểm tra quyền đọc
    if (is_readable($ordersFile)) {
        echo "<p style='color: green;'>File orders.php có quyền đọc!</p>";
    } else {
        echo "<p style='color: red;'>File orders.php không có quyền đọc!</p>";
    }
    
    // Hiển thị kích thước và thời gian sửa đổi
    echo "<p>Kích thước: " . filesize($ordersFile) . " bytes</p>";
    echo "<p>Thời gian sửa đổi: " . date("Y-m-d H:i:s", filemtime($ordersFile)) . "</p>";
} else {
    echo "<p style='color: red;'>File orders.php không tồn tại!</p>";
}

// Hiển thị nút truy cập trang quản lý đơn hàng
echo "<p style='margin-top: 20px;'><a href='administrator/index.php?req=orders' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Truy cập trang quản lý đơn hàng</a></p>";
?>

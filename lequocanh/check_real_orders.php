<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Kiểm tra đơn hàng thực tế trong cơ sở dữ liệu</h2>";

// Kiểm tra các bảng có thể chứa đơn hàng
$possibleOrderTables = [
    'orders',
    'order',
    'donhang',
    'don_hang',
    'hoadon',
    'hoa_don',
    'invoice',
    'cart',
    'giohang',
    'gio_hang'
];

echo "<h3>Kiểm tra các bảng có thể chứa đơn hàng:</h3>";
echo "<ul>";

foreach ($possibleOrderTables as $table) {
    try {
        $checkTableSql = "SHOW TABLES LIKE '$table'";
        $checkTableStmt = $conn->prepare($checkTableSql);
        $checkTableStmt->execute();
        
        if ($checkTableStmt->rowCount() > 0) {
            echo "<li style='color: green;'><strong>$table</strong> - Tồn tại</li>";
            
            // Kiểm tra cấu trúc bảng
            $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div style='margin-left: 20px;'>";
            echo "<h4>Cấu trúc bảng:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                foreach ($column as $key => $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Đếm số lượng bản ghi
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "<p>Số lượng bản ghi: " . $count . "</p>";
            
            // Hiển thị dữ liệu mẫu nếu có
            if ($count > 0) {
                $data = $conn->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Dữ liệu mẫu:</h4>";
                echo "<table border='1'>";
                
                // Hiển thị tiêu đề cột
                echo "<tr>";
                foreach (array_keys($data[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                // Hiển thị dữ liệu
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Không có dữ liệu trong bảng này.</p>";
            }
            echo "</div>";
        } else {
            echo "<li style='color: gray;'>$table - Không tồn tại</li>";
        }
    } catch (PDOException $e) {
        echo "<li style='color: red;'>$table - Lỗi: " . $e->getMessage() . "</li>";
    }
}

echo "</ul>";

// Kiểm tra xem có bảng nào khác có thể chứa đơn hàng không
echo "<h3>Tìm kiếm các bảng có thể liên quan đến đơn hàng:</h3>";

try {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $orderRelatedTables = array_filter($tables, function($table) use ($possibleOrderTables) {
        // Kiểm tra xem tên bảng có chứa từ khóa liên quan đến đơn hàng không
        $keywords = ['order', 'don', 'hang', 'hoa', 'invoice', 'cart', 'gio'];
        foreach ($keywords as $keyword) {
            if (stripos($table, $keyword) !== false && !in_array($table, $possibleOrderTables)) {
                return true;
            }
        }
        return false;
    });
    
    if (empty($orderRelatedTables)) {
        echo "<p>Không tìm thấy bảng nào khác có thể liên quan đến đơn hàng.</p>";
    } else {
        echo "<ul>";
        foreach ($orderRelatedTables as $table) {
            echo "<li><strong>$table</strong></li>";
            
            // Kiểm tra cấu trúc bảng
            $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div style='margin-left: 20px;'>";
            echo "<h4>Cấu trúc bảng:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                foreach ($column as $key => $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Đếm số lượng bản ghi
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "<p>Số lượng bản ghi: " . $count . "</p>";
            
            // Hiển thị dữ liệu mẫu nếu có
            if ($count > 0) {
                $data = $conn->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Dữ liệu mẫu:</h4>";
                echo "<table border='1'>";
                
                // Hiển thị tiêu đề cột
                echo "<tr>";
                foreach (array_keys($data[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                // Hiển thị dữ liệu
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Không có dữ liệu trong bảng này.</p>";
            }
            echo "</div>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi tìm kiếm bảng: " . $e->getMessage() . "</p>";
}

// Kiểm tra xem có đơn hàng trong giỏ hàng không
echo "<h3>Kiểm tra giỏ hàng:</h3>";

try {
    $checkGiohangSql = "SHOW TABLES LIKE 'giohang'";
    $checkGiohangStmt = $conn->prepare($checkGiohangSql);
    $checkGiohangStmt->execute();
    
    if ($checkGiohangStmt->rowCount() > 0) {
        echo "<p style='color: green;'>Bảng giỏ hàng tồn tại.</p>";
        
        // Đếm số lượng bản ghi
        $count = $conn->query("SELECT COUNT(*) FROM giohang")->fetchColumn();
        echo "<p>Số lượng bản ghi trong giỏ hàng: " . $count . "</p>";
        
        if ($count > 0) {
            $data = $conn->query("SELECT * FROM giohang LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Dữ liệu mẫu từ giỏ hàng:</h4>";
            echo "<table border='1'>";
            
            // Hiển thị tiêu đề cột
            echo "<tr>";
            foreach (array_keys($data[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            // Hiển thị dữ liệu
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>Bảng giỏ hàng không tồn tại.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra giỏ hàng: " . $e->getMessage() . "</p>";
}

// Kiểm tra xem có bảng nào chứa thông tin thanh toán không
echo "<h3>Kiểm tra thông tin thanh toán:</h3>";

$paymentTables = array_filter($tables, function($table) {
    $keywords = ['payment', 'thanh_toan', 'thanhtoan', 'pay'];
    foreach ($keywords as $keyword) {
        if (stripos($table, $keyword) !== false) {
            return true;
        }
    }
    return false;
});

if (empty($paymentTables)) {
    echo "<p>Không tìm thấy bảng nào liên quan đến thanh toán.</p>";
} else {
    echo "<ul>";
    foreach ($paymentTables as $table) {
        echo "<li><strong>$table</strong></li>";
        
        // Kiểm tra cấu trúc bảng
        $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='margin-left: 20px;'>";
        echo "<h4>Cấu trúc bảng:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Đếm số lượng bản ghi
        $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<p>Số lượng bản ghi: " . $count . "</p>";
        
        if ($count > 0) {
            $data = $conn->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Dữ liệu mẫu:</h4>";
            echo "<table border='1'>";
            
            // Hiển thị tiêu đề cột
            echo "<tr>";
            foreach (array_keys($data[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            // Hiển thị dữ liệu
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</div>";
    }
    echo "</ul>";
}
?>

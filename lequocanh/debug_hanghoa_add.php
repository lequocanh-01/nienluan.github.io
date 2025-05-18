<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối đến cơ sở dữ liệu
require_once 'administrator/elements_LQA/mod/database.php';
$db = Database::getInstance()->getConnection();

echo "<h2>Debug thêm hàng hóa</h2>";

// Kiểm tra kết nối database
echo "<h3>Kiểm tra kết nối database</h3>";
if ($db instanceof PDO) {
    echo "<p style='color: green;'>Kết nối database thành công!</p>";
} else {
    echo "<p style='color: red;'>Kết nối database thất bại!</p>";
    exit;
}

// Kiểm tra quyền truy cập database
echo "<h3>Kiểm tra quyền truy cập database</h3>";
try {
    $stmt = $db->query("SHOW GRANTS FOR CURRENT_USER()");
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Không thể kiểm tra quyền truy cập: " . $e->getMessage() . "</p>";
}

// Kiểm tra cấu trúc bảng hanghoa
echo "<h3>Cấu trúc bảng hanghoa</h3>";
try {
    $stmt = $db->query("DESCRIBE hanghoa");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . ($value === null ? "NULL" : htmlspecialchars($value)) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra cấu trúc bảng hanghoa: " . $e->getMessage() . "</p>";
}

// Kiểm tra các ràng buộc khóa ngoại
echo "<h3>Kiểm tra ràng buộc khóa ngoại</h3>";
try {
    $stmt = $db->query("
        SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_SCHEMA = 'trainingdb'
            AND REFERENCED_TABLE_NAME = 'hanghoa'
    ");
    
    echo "<table border='1'>";
    echo "<tr><th>Bảng</th><th>Cột</th><th>Tên ràng buộc</th><th>Bảng tham chiếu</th><th>Cột tham chiếu</th></tr>";
    $hasConstraints = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasConstraints = true;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['TABLE_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CONSTRAINT_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['REFERENCED_TABLE_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['REFERENCED_COLUMN_NAME']) . "</td>";
        echo "</tr>";
    }
    if (!$hasConstraints) {
        echo "<tr><td colspan='5'>Không tìm thấy ràng buộc khóa ngoại tham chiếu đến bảng hanghoa</td></tr>";
    }
    echo "</table>";
    
    // Kiểm tra các ràng buộc từ bảng hanghoa đến các bảng khác
    $stmt = $db->query("
        SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = 'trainingdb'
            AND TABLE_NAME = 'hanghoa'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    echo "<h4>Ràng buộc từ bảng hanghoa đến các bảng khác</h4>";
    echo "<table border='1'>";
    echo "<tr><th>Bảng</th><th>Cột</th><th>Tên ràng buộc</th><th>Bảng tham chiếu</th><th>Cột tham chiếu</th></tr>";
    $hasConstraints = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasConstraints = true;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['TABLE_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CONSTRAINT_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['REFERENCED_TABLE_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['REFERENCED_COLUMN_NAME']) . "</td>";
        echo "</tr>";
    }
    if (!$hasConstraints) {
        echo "<tr><td colspan='5'>Không tìm thấy ràng buộc khóa ngoại từ bảng hanghoa</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra ràng buộc khóa ngoại: " . $e->getMessage() . "</p>";
}

// Thử thêm hàng hóa với câu lệnh SQL trực tiếp
echo "<h3>Thử thêm hàng hóa với SQL trực tiếp</h3>";
if (isset($_POST['test_direct_insert'])) {
    try {
        // Tạo dữ liệu mẫu
        $tenhanghoa = "Test Hàng Hóa " . date("YmdHis");
        $mota = "Mô tả test";
        $giathamkhao = 100000;
        $hinhanh = 0;
        
        // Lấy ID loại hàng đầu tiên
        $stmt = $db->query("SELECT idloaihang FROM loaihang LIMIT 1");
        $idloaihang = $stmt->fetchColumn();
        
        if (!$idloaihang) {
            echo "<p style='color: red;'>Không tìm thấy loại hàng nào!</p>";
        } else {
            // Thực hiện INSERT trực tiếp
            $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$tenhanghoa, $mota, $giathamkhao, $hinhanh, $idloaihang]);
            
            if ($result) {
                $lastId = $db->lastInsertId();
                echo "<p style='color: green;'>Thêm hàng hóa thành công với ID: " . $lastId . "</p>";
            } else {
                echo "<p style='color: red;'>Thêm hàng hóa thất bại!</p>";
                echo "<pre>";
                print_r($stmt->errorInfo());
                echo "</pre>";
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
    }
}

// Kiểm tra các trigger trên bảng hanghoa
echo "<h3>Kiểm tra các trigger trên bảng hanghoa</h3>";
try {
    $stmt = $db->query("SHOW TRIGGERS WHERE `Table` = 'hanghoa'");
    echo "<table border='1'>";
    echo "<tr><th>Trigger</th><th>Event</th><th>Table</th><th>Statement</th><th>Timing</th></tr>";
    $hasTriggers = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hasTriggers = true;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Trigger']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Event']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Table']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Statement']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Timing']) . "</td>";
        echo "</tr>";
    }
    if (!$hasTriggers) {
        echo "<tr><td colspan='5'>Không tìm thấy trigger nào trên bảng hanghoa</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi khi kiểm tra trigger: " . $e->getMessage() . "</p>";
}

// Form để thử thêm hàng hóa trực tiếp
echo "<form method='post'>";
echo "<input type='submit' name='test_direct_insert' value='Thử thêm hàng hóa trực tiếp'>";
echo "</form>";

// Kiểm tra file log
echo "<h3>Kiểm tra file log</h3>";
$log_files = [
    'administrator/elements_LQA/mhanghoa/hanghoa_debug.log',
    'administrator/elements_LQA/mod/hanghoa_class_debug.log'
];

foreach ($log_files as $log_file) {
    echo "<h4>File: " . htmlspecialchars($log_file) . "</h4>";
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if (!empty($log_content)) {
            echo "<pre style='max-height: 300px; overflow-y: auto; background-color: #f5f5f5; padding: 10px;'>";
            echo htmlspecialchars($log_content);
            echo "</pre>";
        } else {
            echo "<p>File log trống.</p>";
        }
    } else {
        echo "<p>File log không tồn tại.</p>";
    }
}
?>

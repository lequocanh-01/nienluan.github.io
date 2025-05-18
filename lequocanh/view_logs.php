<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "pw";
$dbname = "trainingdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra xem bảng system_logs có tồn tại không
    $checkTableSql = "SHOW TABLES LIKE 'system_logs'";
    $checkTableStmt = $conn->prepare($checkTableSql);
    $checkTableStmt->execute();
    
    if ($checkTableStmt->rowCount() == 0) {
        // Bảng chưa tồn tại, tạo bảng system_logs
        $createSystemLogsTableSql = "CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($createSystemLogsTableSql);
        echo "<p>Đã tạo bảng system_logs.</p>";
    }
    
    // Lấy danh sách log
    $logsSql = "SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100";
    $logsStmt = $conn->prepare($logsSql);
    $logsStmt->execute();
    $logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hiển thị danh sách log
    echo "<h2>Danh sách log hệ thống (100 log gần nhất)</h2>";
    
    if (count($logs) > 0) {
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th style='padding: 8px; text-align: left;'>ID</th>";
        echo "<th style='padding: 8px; text-align: left;'>Thông điệp</th>";
        echo "<th style='padding: 8px; text-align: left;'>Thời gian</th>";
        echo "</tr>";
        
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $log['id'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $log['message'] . "</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $log['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Không có log nào trong hệ thống.</p>";
    }
    
    // Hiển thị nút xóa log
    echo "<form method='post' style='margin-top: 20px;'>";
    echo "<button type='submit' name='clear_logs' style='padding: 10px; background-color: #f44336; color: white; border: none; cursor: pointer;'>Xóa tất cả log</button>";
    echo "</form>";
    
    // Xử lý khi người dùng nhấn nút xóa log
    if (isset($_POST['clear_logs'])) {
        $clearLogsSql = "TRUNCATE TABLE system_logs";
        $conn->exec($clearLogsSql);
        
        echo "<p style='color: green;'>Đã xóa tất cả log.</p>";
        echo "<script>window.location.href = 'view_logs.php';</script>";
    }
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

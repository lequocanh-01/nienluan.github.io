<?php
//tao ket noi CSDL

$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

// Cấu hình kết nối database - có thể thay đổi tùy theo môi trường
$host = "localhost"; // Hoặc "mysql" nếu chạy trong Docker
$dbname = "qlsanpham"; // Hoặc "android_db" tùy theo database thực tế
$username = "root";
$password = "android123";

try {
    $connect = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $opt);
    echo "Kết nối database thành công!\n";

    // Test kết nối bằng cách lấy thông tin database
    $stmt = $connect->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "Database hiện tại: " . $result['current_db'] . "\n";
} catch (PDOException $e) {
    echo "Lỗi kết nối database: " . $e->getMessage() . "\n";
    exit(1);
}

// Test tạo bảng và insert dữ liệu
try {
    // Tạo bảng loaihang nếu chưa tồn tại
    $createTable = "CREATE TABLE IF NOT EXISTS loaihang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenloai VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $connect->exec($createTable);
    echo "Tạo bảng loaihang thành công!\n";

    // Test insert dữ liệu
    $insert = $connect->prepare("INSERT INTO loaihang(tenloai) VALUES(?)");
    $data = array("Điện máy");
    $kq = $insert->execute($data);

    if ($kq) {
        echo "Insert dữ liệu thành công!\n";
        echo "ID của record vừa insert: " . $connect->lastInsertId() . "\n";
    } else {
        echo "Insert dữ liệu thất bại!\n";
    }

    // Test select dữ liệu
    $select = $connect->prepare("SELECT * FROM loaihang ORDER BY id DESC LIMIT 5");
    $select->execute();
    $results = $select->fetchAll();

    echo "Dữ liệu trong bảng loaihang:\n";
    foreach ($results as $row) {
        echo "ID: " . $row['id'] . ", Tên loại: " . $row['tenloai'] . ", Ngày tạo: " . $row['created_at'] . "\n";
    }
} catch (PDOException $e) {
    echo "Lỗi thao tác database: " . $e->getMessage() . "\n";
}
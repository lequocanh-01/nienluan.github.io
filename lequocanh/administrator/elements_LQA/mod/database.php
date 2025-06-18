<?php
class Database
{
  private static $instance = null;
  private $conn = null;

  private function __construct()
  {
    // Kiểm tra file config.ini có tồn tại không
    $configFile = __DIR__ . '/config.ini';
    if (!file_exists($configFile)) {
      error_log("File config.ini không tồn tại tại: $configFile");
      // Tạo config mặc định
      $this->createDefaultConfig($configFile);
    }

    $config = parse_ini_file($configFile, true);
    if (!$config || !isset($config['section'])) {
      error_log("Không thể đọc file config.ini hoặc thiếu section");
      throw new Exception("Lỗi cấu hình database");
    }

    $servername = $config['section']['servername'] ?? 'localhost';
    $dbname = $config['section']['dbname'] ?? 'trainingdb';
    $username = $config['section']['username'] ?? 'root';
    $password = $config['section']['password'] ?? 'pw';
    $port = $config['section']['port'] ?? 3306;

    // Thử kết nối với nhiều cấu hình khác nhau
    $connectionConfigs = [
      ['host' => $servername, 'port' => $port, 'user' => $username, 'pass' => $password],
      ['host' => 'mysql', 'port' => 3306, 'user' => 'root', 'pass' => 'pw'],
      ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'pw'],
      ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
      ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'pw'],
      ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => '']
    ];

    $connected = false;
    foreach ($connectionConfigs as $connConfig) {
      try {
        $dsn = "mysql:host={$connConfig['host']};port={$connConfig['port']};dbname=$dbname;charset=utf8mb4";
        $this->conn = new PDO($dsn, $connConfig['user'], $connConfig['pass']);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Test connection
        $this->conn->query("SELECT 1");

        error_log("Kết nối thành công đến MySQL: {$connConfig['host']}:{$connConfig['port']}");
        $connected = true;
        break;
      } catch (PDOException $e) {
        error_log("Không thể kết nối đến MySQL {$connConfig['host']}:{$connConfig['port']}: " . $e->getMessage());
        continue;
      }
    }

    // Nếu tất cả cấu hình đều thất bại
    if (!$connected || !$this->conn) {
      $error_msg = "Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra:\n";
      $error_msg .= "1. MySQL server đã được khởi động chưa\n";
      $error_msg .= "2. Thông tin kết nối trong config.ini có đúng không\n";
      $error_msg .= "3. Docker containers đã chạy chưa\n";
      $error_msg .= "4. XAMPP/WAMP MySQL service đã khởi động chưa";

      error_log($error_msg);
      throw new Exception($error_msg);
    }
  }

  /**
   * Tạo file config mặc định
   */
  private function createDefaultConfig($configFile)
  {
    $defaultConfig = "[section]\n";
    $defaultConfig .= "; Cấu hình kết nối database\n";
    $defaultConfig .= "servername = localhost\n";
    $defaultConfig .= "port = 3306\n";
    $defaultConfig .= "dbname = trainingdb\n";
    $defaultConfig .= "username = root\n";
    $defaultConfig .= "password = pw\n\n";
    $defaultConfig .= "[local]\n";
    $defaultConfig .= "servername = localhost\n";
    $defaultConfig .= "port = 3306\n";
    $defaultConfig .= "dbname = trainingdb\n";
    $defaultConfig .= "username = root\n";
    $defaultConfig .= "password = pw\n";

    try {
      file_put_contents($configFile, $defaultConfig);
      error_log("Đã tạo file config.ini mặc định");
    } catch (Exception $e) {
      error_log("Không thể tạo file config.ini: " . $e->getMessage());
    }
  }

  public static function getInstance()
  {
    if (self::$instance == null) {
      self::$instance = new Database();
    }
    return self::$instance;
  }

  public function getConnection()
  {
    return $this->conn;
  }

  public function deleteAndUpdateID($userIdToDelete)
  {
    try {
      // Bắt đầu một giao dịch
      $this->conn->beginTransaction();

      // Xóa người dùng
      $sql = "DELETE FROM users WHERE id = :idToDelete";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute(['idToDelete' => $userIdToDelete]);

      // Cập nhật lại ID
      $sql = "SET @count = 0";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "UPDATE users SET id = @count:= @count + 1";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      $sql = "ALTER TABLE users AUTO_INCREMENT = 1";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();

      // Hoàn tất giao dịch
      $this->conn->commit();

      return true;
    } catch (PDOException $e) {
      // Hủy giao dịch nếu có lỗi
      $this->conn->rollBack();
      echo "Lỗi: " . $e->getMessage();
      return false;
    }
  }

  public function addProduct($tenHangHoa, $giaHangHoa, $moTa, $hinhAnh)
  {
    try {
      // Bắt đầu một giao dịch
      $this->conn->beginTransaction();

      // Chuẩn bị câu lệnh SQL để thêm hàng hóa
      $sql = "INSERT INTO hang_hoa (ten_hang_hoa, gia_tham_khao, mo_ta, hinh_anh)
              VALUES (:ten_hang_hoa, :gia_tham_khao, :mo_ta, :hinh_anh)";

      $stmt = $this->conn->prepare($sql);

      // Thực thi với dữ liệu hàng hóa
      $stmt->execute([
        'ten_hang_hoa' => $tenHangHoa,
        'gia_tham_khao' => $giaHangHoa,
        'mo_ta' => $moTa,
        'hinh_anh' => $hinhAnh
      ]);

      // Lấy ID của hàng hóa vừa thêm
      $hangHoaId = $this->conn->lastInsertId();

      // Hoàn tất giao dịch
      $this->conn->commit();

      return $hangHoaId;
    } catch (PDOException $e) {
      // Hủy giao dịch nếu có lỗi
      $this->conn->rollBack();
      return false;
    }
  }
}

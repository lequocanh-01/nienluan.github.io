<?php
class Database
{
  private static $instance = null;
  private $conn = null;

  private function __construct()
  {
    $config = parse_ini_file(__DIR__ . '/config.ini', true);

    $servername = $config['section']['servername'];
    $dbname = $config['section']['dbname'];
    $username = $config['section']['username'];
    $password = $config['section']['password'];
    $port = $config['section']['port'];

    try {
      $this->conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
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

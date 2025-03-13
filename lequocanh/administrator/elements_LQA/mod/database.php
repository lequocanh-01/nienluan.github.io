<?php
class Database
{
  public $connect;

  public function __construct()
  {
    $init = parse_ini_file("config.ini");
    $servername = $init["servername"];
    $dbname = $init["dbname"];
    $username = $init["username"];
    $password = $init["password"];
    $opt = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    );
    $this->connect = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password, $opt);
  }

  public function deleteAndUpdateID($userIdToDelete)
  {
    try {
      // Bắt đầu một giao dịch
      $this->connect->beginTransaction();

      // Xóa người dùng
      $sql = "DELETE FROM users WHERE id = :idToDelete";
      $stmt = $this->connect->prepare($sql);
      $stmt->execute(['idToDelete' => $userIdToDelete]);

      // Cập nhật lại ID
      $sql = "SET @count = 0";
      $stmt = $this->connect->prepare($sql);
      $stmt->execute();

      $sql = "UPDATE users SET id = @count:= @count + 1";
      $stmt = $this->connect->prepare($sql);
      $stmt->execute();

      $sql = "ALTER TABLE users AUTO_INCREMENT = 1";
      $stmt = $this->connect->prepare($sql);
      $stmt->execute();

      // Hoàn tất giao dịch
      $this->connect->commit();

      return true;
    } catch (PDOException $e) {
      // Hủy giao dịch nếu có lỗi
      $this->connect->rollBack();
      echo "Lỗi: " . $e->getMessage();
      return false;
    }
  }
}
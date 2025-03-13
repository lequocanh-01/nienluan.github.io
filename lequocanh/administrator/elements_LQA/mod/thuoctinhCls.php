<?php
$s = '../../elements_LQA/mod/database.php';
if (file_exists($s)) {
    $f = $s;
} else {
    $f = './elements_LQA/mod/database.php';
    if (!file_exists($f)) {
        $f = './administrator/elements_LQA/mod/database.php';
    }
}
require_once $f;

class ThuocTinh extends Database
{
    // Lấy tất cả thuộc tính
    public function thuoctinhGetAll()
    {
        try {
            $sql = 'SELECT * FROM thuoctinh';
            $getAll = $this->connect->prepare($sql);
            $getAll->setFetchMode(PDO::FETCH_OBJ);
            $getAll->execute();

            return $getAll->fetchAll();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    // Thêm thuộc tính
    public function thuoctinhAdd($tenThuocTinh,  $ghiChu, $hinhanh)
    {
        try {
            $sql = "INSERT INTO thuoctinh (tenThuocTinh, ghiChu, hinhanh) VALUES (?, ?, ?)";
            $data = array($tenThuocTinh, $ghiChu, $hinhanh);
            $add = $this->connect->prepare($sql);
            $add->execute($data);
            return $add->rowCount();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }

    // Xóa thuộc tính theo ID
    public function thuoctinhDelete($idThuocTinh)
    {
        try {
            $sql = "DELETE FROM thuoctinh WHERE idThuocTinh = ?";
            $data = array($idThuocTinh);
            $del = $this->connect->prepare($sql);
            $del->execute($data);
            return $del->rowCount();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }

    // Cập nhật thông tin thuộc tính
    public function thuoctinhUpdate($tenThuocTinh, $ghiChu, $hinhanh, $idThuocTinh)
    {
        try {
            $sql = "UPDATE thuoctinh SET tenThuocTinh = ?, ghiChu = ?, hinhanh = ? WHERE idThuocTinh = ?";
            $data = array($tenThuocTinh, $ghiChu, $hinhanh, $idThuocTinh);
            $update = $this->connect->prepare($sql);
            $update->execute($data);
            return $update->rowCount();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }

    // Lấy thông tin thuộc tính theo ID
    public function thuoctinhGetById($idThuocTinh)
    {
        try {
            $sql = 'SELECT * FROM thuoctinh WHERE idThuocTinh = ?';
            $data = array($idThuocTinh);
            $getOne = $this->connect->prepare($sql);
            $getOne->setFetchMode(PDO::FETCH_OBJ);
            $getOne->execute($data);

            return $getOne->fetch();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}

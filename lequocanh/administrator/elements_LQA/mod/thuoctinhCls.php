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

class ThuocTinh
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy tất cả các thuộc tính
    public function thuoctinhGetAll()
    {
        $sql = 'SELECT * FROM thuoctinh';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);

        if (!$getAll->execute()) {
            error_log(print_r($getAll->errorInfo(), true));
            return false;
        }

        return $getAll->fetchAll();
    }

    // Thêm thuộc tính mới
    public function thuoctinhAdd($tenThuocTinh, $ghiChu, $hinhanh)
    {
        $sql = "INSERT INTO thuoctinh (tenThuocTinh, ghiChu, hinhanh) VALUES (?, ?, ?)";
        $data = array($tenThuocTinh, $ghiChu, $hinhanh);

        $add = $this->db->prepare($sql);

        if (!$add->execute($data)) {
            error_log(print_r($add->errorInfo(), true));
            return false;
        }

        return $add->rowCount();
    }

    // Xóa thuộc tính theo ID
    public function thuoctinhDelete($idThuocTinh)
    {
        $sql = "DELETE FROM thuoctinh WHERE idThuocTinh = ?";
        $data = array($idThuocTinh);

        $del = $this->db->prepare($sql);

        if (!$del->execute($data)) {
            error_log(print_r($del->errorInfo(), true));
            return false;
        }

        return $del->rowCount();
    }

    // Cập nhật thông tin thuộc tính
    public function thuoctinhUpdate($tenThuocTinh, $ghiChu, $hinhanh, $idThuocTinh)
    {
        $sql = "UPDATE thuoctinh 
                SET tenThuocTinh = ?, ghiChu = ?, hinhanh = ? 
                WHERE idThuocTinh = ?";
        $data = array($tenThuocTinh, $ghiChu, $hinhanh, $idThuocTinh);

        $update = $this->db->prepare($sql);

        if (!$update->execute($data)) {
            error_log(print_r($update->errorInfo(), true));
            return false;
        }

        return $update->rowCount();
    }

    // Lấy thông tin thuộc tính theo ID
    public function thuoctinhGetById($idThuocTinh)
    {
        $sql = 'SELECT * FROM thuoctinh WHERE idThuocTinh = ?';
        $data = array($idThuocTinh);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);

        if (!$getOne->execute($data)) {
            error_log(print_r($getOne->errorInfo(), true));
            return false;
        }

        return $getOne->fetch();
    }
}

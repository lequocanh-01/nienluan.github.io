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

class NhanVien
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy tất cả các nhân viên
    public function nhanvienGetAll()
    {
        $sql = 'SELECT * FROM nhanvien';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);

        if (!$getAll->execute()) {
            error_log(print_r($getAll->errorInfo(), true));
            return false;
        }

        return $getAll->fetchAll();
    }

    // Thêm nhân viên mới
    public function nhanvienAdd($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu)
    {
        $sql = "INSERT INTO nhanvien (tenNV, SDT, email, luongCB, phuCap, chucVu) VALUES (?, ?, ?, ?, ?, ?)";
        $data = array($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu);

        $add = $this->db->prepare($sql);

        if (!$add->execute($data)) {
            error_log(print_r($add->errorInfo(), true));
            return false;
        }

        return $add->rowCount();
    }

    // Xóa nhân viên theo ID
    public function nhanvienDelete($idNhanVien)
    {
        $sql = "DELETE FROM nhanvien WHERE idNhanVien = ?";
        $data = array($idNhanVien);

        $del = $this->db->prepare($sql);

        if (!$del->execute($data)) {
            error_log(print_r($del->errorInfo(), true));
            return false;
        }

        return $del->rowCount();
    }

    // Cập nhật thông tin nhân viên
    public function nhanvienUpdate($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien)
    {
        $sql = "UPDATE nhanvien 
                SET tenNV = ?, SDT = ?, email = ?, luongCB = ?, phuCap = ?, chucVu = ? 
                WHERE idNhanVien = ?";
        $data = array($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien);

        $update = $this->db->prepare($sql);

        if (!$update->execute($data)) {
            error_log(print_r($update->errorInfo(), true));
            return false;
        }

        return $update->rowCount();
    }

    // Lấy thông tin nhân viên theo ID
    public function nhanvienGetById($idNhanVien)
    {
        $sql = 'SELECT * FROM nhanvien WHERE idNhanVien = ?';
        $data = array($idNhanVien);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);

        if (!$getOne->execute($data)) {
            error_log(print_r($getOne->errorInfo(), true));
            return false;
        }

        return $getOne->fetch();
    }
}

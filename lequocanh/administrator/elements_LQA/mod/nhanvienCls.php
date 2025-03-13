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

class NhanVien extends Database
{
    // Lấy tất cả nhân viên
    public function nhanvienGetAll()
    {
        $sql = 'SELECT * FROM nhanvien';
        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }

    // Thêm nhân viên
    public function nhanvienAdd($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu)
    {
        $sql = "INSERT INTO nhanvien (tenNV, SDT, email, luongCB, phuCap, chucVu) VALUES (?, ?, ?, ?, ?, ?)";
        $data = array($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu);
        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }

    // Xóa nhân viên theo ID
    public function nhanvienDelete($idNhanVien)
    {
        $sql = "DELETE FROM nhanvien WHERE idNhanVien = ?";
        $data = array($idNhanVien);
        $del = $this->connect->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }

    // Cập nhật thông tin nhân viên
    public function nhanvienUpdate($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien)
    {
        $sql = "UPDATE nhanvien SET tenNV = ?, SDT = ?, email = ?, luongCB = ?, phuCap = ?, chucVu = ? WHERE idNhanVien = ?";
        $data = array($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien);
        $update = $this->connect->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }

    // Lấy thông tin nhân viên theo ID
    public function nhanvienGetById($idNhanVien)
    {
        $sql = 'SELECT * FROM nhanvien WHERE idNhanVien = ?';
        $data = array($idNhanVien);
        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
}

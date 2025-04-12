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

class Dongia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy tất cả các đơn giá
    public function dongiaGetAll()
    {
        $sql = 'SELECT * FROM dongia';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);

        if (!$getAll->execute()) {
            error_log(print_r($getAll->errorInfo(), true));
            return false;
        }

        return $getAll->fetchAll();
    }

    // Thêm đơn giá mới
    public function dongiaAdd($idhanghoa, $ngaycapnhat, $dongia)
    {
        $sql = "INSERT INTO dongia (idhanghoa, ngaycapnhat, dongia) VALUES (?, ?, ?)";
        $data = array($idhanghoa, $ngaycapnhat, $dongia);

        $add = $this->db->prepare($sql);

        if (!$add->execute($data)) {
            error_log(print_r($add->errorInfo(), true));
            return false;
        }

        return $add->rowCount();
    }

    // Xóa đơn giá theo ID
    public function dongiaDelete($idDongia)
    {
        $sql = "DELETE FROM dongia WHERE idDongia = ?";
        $data = array($idDongia);

        $del = $this->db->prepare($sql);

        if (!$del->execute($data)) {
            error_log(print_r($del->errorInfo(), true));
            return false;
        }

        return $del->rowCount();
    }

    // Cập nhật thông tin đơn giá
    public function dongiaUpdate($idhanghoa, $ngaycapnhat, $dongia, $idDongia)
    {
        $sql = "UPDATE dongia 
                SET idhanghoa = ?, ngaycapnhat = ?, dongia = ? 
                WHERE idDongia = ?";
        $data = array($idhanghoa, $ngaycapnhat, $dongia, $idDongia);

        $update = $this->db->prepare($sql);

        if (!$update->execute($data)) {
            error_log(print_r($update->errorInfo(), true));
            return false;
        }

        return $update->rowCount();
    }

    // Lấy thông tin đơn giá theo ID
    public function dongiaGetbyId($idDongia)
    {
        $sql = 'SELECT * FROM dongia WHERE idDongia = ?';
        $data = array($idDongia);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);

        if (!$getOne->execute($data)) {
            error_log(print_r($getOne->errorInfo(), true));
            return false;
        }

        return $getOne->fetch();
    }

    // Lấy đơn giá theo ID hàng hóa
    public function dongiaGetbyIdHanghoa($idhanghoa)
    {
        $sql = 'SELECT * FROM dongia WHERE idhanghoa = ? ORDER BY ngaycapnhat DESC';
        $data = array($idhanghoa);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);

        if (!$getOne->execute($data)) {
            error_log(print_r($getOne->errorInfo(), true));
            return false;
        }

        return $getOne->fetchAll();
    }
}

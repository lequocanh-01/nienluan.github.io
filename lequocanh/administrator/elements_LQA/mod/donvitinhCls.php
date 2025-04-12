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

class DonViTinh
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy tất cả các đơn vị tính
    public function donvitinhGetAll()
    {
        $sql = 'SELECT * FROM donvitinh';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);

        if (!$getAll->execute()) {
            error_log(print_r($getAll->errorInfo(), true));
            return false;
        }

        return $getAll->fetchAll();
    }

    // Thêm đơn vị tính mới
    public function donvitinhAdd($tenDonViTinh, $moTa, $ghiChu)
    {
        $sql = "INSERT INTO donvitinh (tenDonViTinh, moTa, ghiChu) VALUES (?, ?, ?)";
        $data = array($tenDonViTinh, $moTa, $ghiChu);

        $add = $this->db->prepare($sql);

        if (!$add->execute($data)) {
            error_log(print_r($add->errorInfo(), true));
            return false;
        }

        return $add->rowCount();
    }

    // Xóa đơn vị tính theo ID
    public function donvitinhDelete($idDonViTinh)
    {
        $sql = "DELETE FROM donvitinh WHERE idDonViTinh = ?";
        $data = array($idDonViTinh);

        $del = $this->db->prepare($sql);

        if (!$del->execute($data)) {
            error_log(print_r($del->errorInfo(), true));
            return false;
        }

        return $del->rowCount();
    }

    // Cập nhật thông tin đơn vị tính
    public function donvitinhUpdate($tenDonViTinh, $moTa, $ghiChu, $idDonViTinh)
    {
        $sql = "UPDATE donvitinh 
                SET tenDonViTinh = ?, moTa = ?, ghiChu = ? 
                WHERE idDonViTinh = ?";
        $data = array($tenDonViTinh, $moTa, $ghiChu, $idDonViTinh);

        $update = $this->db->prepare($sql);

        // Debug: Log SQL and parameters
        error_log("SQL: " . $sql);
        error_log("Params: " . json_encode($data));

        // Execute query
        $result = $update->execute($data);

        // Log result
        error_log("Update result: " . ($result ? "success" : "failed") . ", rows affected: " . $update->rowCount());

        if (!$result) {
            error_log("SQL Error: " . json_encode($update->errorInfo()));
            return false;
        }

        return $update->rowCount();
    }

    // Lấy thông tin đơn vị tính theo ID
    public function donvitinhGetbyId($idDonViTinh)
    {
        $sql = 'SELECT * FROM donvitinh WHERE idDonViTinh = ?';
        $data = array($idDonViTinh);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);

        if (!$getOne->execute($data)) {
            error_log(print_r($getOne->errorInfo(), true));
            return false;
        }

        return $getOne->fetch();
    }

    public function donvitinhGetbyIdloaihang($idloaihang)
    {
        $sql = 'select * from donvitinh where idloaihang=?';
        $data = array($idloaihang);


        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetchAll();
    }
}

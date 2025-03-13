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

class ThuongHieu extends Database
{
    // Lấy tất cả các thương hiệu
    public function thuonghieuGetAll()
    {
        $sql = 'SELECT * FROM thuonghieu';
        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }

    // Thêm mới một thương hiệu
    public function thuonghieuAdd($tenTH, $SDT, $email, $diaChi, $hinhanh)
    {
        $sql = "INSERT INTO thuonghieu (tenTH, SDT, email, diaChi, hinhanh) VALUES (?, ?, ?, ?, ?)";
        $data = array($tenTH, $SDT, $email, $diaChi, $hinhanh);
        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }

    // Xóa thương hiệu theo ID
    public function thuonghieuDelete($idThuongHieu)
    {
        try {
            $sql = "DELETE FROM thuonghieu WHERE idThuongHieu = ?";
            $data = array($idThuongHieu);
            $del = $this->connect->prepare($sql);
            $del->execute($data);
            return $del->rowCount();
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
            return 0;
        }
    }

    // Cập nhật thông tin thương hiệu
    public function thuonghieuUpdate($tenTH, $SDT, $email, $diaChi, $hinhanh, $idThuongHieu)
    {
        try {
            $sql = "UPDATE thuonghieu SET tenTH = ?, SDT = ?, email = ?, diaChi = ?, hinhanh = ? WHERE idThuongHieu = ?";
            $data = array($tenTH, $SDT, $email, $diaChi, $hinhanh, $idThuongHieu);
            $update = $this->connect->prepare($sql);
            $update->execute($data);
            return $update->rowCount();
        } catch (PDOException $e) {
            echo "Lỗi: " . $e->getMessage();
            return 0;
        }
    }

    // Lấy thông tin thương hiệu theo ID
    public function thuonghieuGetById($idThuongHieu)
    {
        $sql = 'SELECT * FROM thuonghieu WHERE idThuongHieu = ?';
        $data = array($idThuongHieu);
        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
}

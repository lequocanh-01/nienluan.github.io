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
class donvitinh extends Database
{
    public function donvitinhGetAll()
    {
        $sql = 'select * from donvitinh';

        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }
    public function donvitinhAdd($tenDonViTinh, $moTa, $ghiChu)
    {
        $sql = "INSERT INTO donvitinh (tenDonViTinh, moTa, ghiChu) VALUES (?,?,?)";
        $data = array($tenDonViTinh, $moTa, $ghiChu);
        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }
    public function donvitinhDelete($iddonvitinh)
    {
        $sql = "DELETE from donvitinh where iddonvitinh = ?";
        $data = array($iddonvitinh);

        $del = $this->connect->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }
    public function donvitinhUpdate($tenDonViTinh, $moTa, $ghiChu, $idDonViTinh)
    {
        $sql = "UPDATE donvitinh set tenDonViTinh=?, moTa=?, ghiChu=? WHERE idDonViTinh =?";
        $data = array($tenDonViTinh, $moTa, $ghiChu, $idDonViTinh);

        $update = $this->connect->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }
    public function donvitinhGetbyId($iddonvitinh)
    {
        $sql = 'select * from donvitinh where iddonvitinh=?';
        $data = array($iddonvitinh);


        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }

    public function donvitinhGetbyIdloaihang($idloaihang)
    {
        $sql = 'select * from donvitinh where idloaihang=?';
        $data = array($idloaihang);


        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetchAll();
    }
    
}

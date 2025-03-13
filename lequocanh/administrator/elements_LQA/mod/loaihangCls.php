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
class loaihang extends Database
{
    public function LoaihangGetAll()
    {
        $sql = 'select * from loaihang';

        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }
    public function LoaihangAdd($tenloaihang, $hinhanh, $mota)
    {
        $sql = "INSERT INTO loaihang (tenloaihang, hinhanh, mota) VALUES (?,?,?)";
        $data = array($tenloaihang, $hinhanh, $mota);
        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }
    public function LoaihangDelete($idloaihang)
    {
        $sql = "DELETE from loaihang where idloaihang = ?";
        $data = array($idloaihang);

        $del = $this->connect->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }
    public function LoaihangUpdate($tenloaihang, $hinhanh, $mota, $idloaihang)
    {
        $sql = "UPDATE loaihang set tenloaihang=?, hinhanh=?, mota=? WHERE idloaihang =?";
        $data = array($tenloaihang, $hinhanh, $mota, $idloaihang);

        $update = $this->connect->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }
    public function LoaihangGetbyId($idloaihang)
    {
        $sql = 'select * from loaihang where idloaihang=?';
        $data = array($idloaihang);


        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
}

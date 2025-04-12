<?php
require_once 'database.php';

class loaihang
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function LoaihangGetAll()
    {
        $sql = 'SELECT * FROM loaihang';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function LoaihangAdd($tenloaihang, $hinhanh, $mota)
    {
        $sql = "INSERT INTO loaihang (tenloaihang, hinhanh, mota) VALUES (?,?,?)";
        $data = array($tenloaihang, $hinhanh, $mota);
        $add = $this->db->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }

    public function LoaihangDelete($idloaihang)
    {
        $sql = "DELETE FROM loaihang WHERE idloaihang = ?";
        $data = array($idloaihang);
        $del = $this->db->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }

    public function LoaihangUpdate($tenloaihang, $hinhanh, $mota, $idloaihang)
    {
        $sql = "UPDATE loaihang SET tenloaihang=?, hinhanh=?, mota=? WHERE idloaihang=?";
        $data = array($tenloaihang, $hinhanh, $mota, $idloaihang);
        $update = $this->db->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }

    public function LoaihangGetbyId($idloaihang)
    {
        $sql = "SELECT * FROM loaihang WHERE idloaihang=?";
        $data = array($idloaihang);
        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);
        return $getOne->fetch();
    }
}

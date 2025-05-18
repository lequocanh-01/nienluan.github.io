<?php
$s = '../../elements_LQA/mod/database.php';
if (file_exists($s)) {
    $f = $s;
} else {
    $f = './elements_LQA/mod/database.php';
}
require_once $f;

class user
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function UserCheckLogin($username, $password)
    {
        $sql = 'select * from user where username = ? and password = ? and setlock = 1';
        $data = array($username, $password);

        $select = $this->db->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);

        $get_obj = count($select->fetchAll());

        if ($get_obj === 1) {
            return true;
        } else {
            return false;
        }
    }
    public function UserCheckUsername($username)
    {
        $sql = 'select * from user where username = ?';
        $data = array($username);

        $select = $this->db->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);

        $get_obj = count($select->fetchAll());

        if ($get_obj === 1) {
            return true;
        } else {
            return false;
        }
    }
    public function UserGetAll()
    {
        $sql = 'select * from user';

        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }
    public function UserAdd($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai)
    {
        $sql = "INSERT INTO user (username, password, hoten, gioitinh, ngaysinh, diachi, dienthoai) VALUES (?,?,?,?,?,?,?)";
        $data = array($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai);

        $add = $this->db->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }
    public function UserDelete($iduser)
    {
        $sql = "DELETE from user where iduser = ?";
        $data = array($iduser);

        $del = $this->db->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }
    public function UserUpdate($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, $iduser)
    {
        try {
            $sql = "UPDATE user SET
                    username=?,
                    password=?,
                    hoten=?,
                    gioitinh=?,
                    ngaysinh=?,
                    diachi=?,
                    dienthoai=?
                    WHERE iduser=?";

            $data = array($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, $iduser);
            $update = $this->db->prepare($sql);
            $update->execute($data);
            return $update->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function UserGetbyId($iduser)
    {
        $sql = 'select * from user where iduser=?';
        $data = array($iduser);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
    public function UserSetPassword($iduser, $password)
    {
        $sql = "UPDATE user set password = ? WHERE iduser =? ";
        $data = array($password, $iduser);

        $update_pass = $this->db->prepare($sql);
        $update_pass->execute($data);
        return $update_pass->rowCount();
    }
    public function UserSetActive($iduser, $setlock)
    {
        $sql = "UPDATE user set setlock = ? WHERE iduser =? ";
        $data = array($setlock, $iduser);

        $update_lock = $this->db->prepare($sql);
        $update_lock->execute($data);
        return $update_lock->rowCount();
    }
    public function UserChangePassword($iduser, $passwordold, $passwordnew)
    {
        $sql = 'select * from user where iduser = ? and password = ?';
        $data = array($iduser, $passwordold);

        $select = $this->db->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);

        $get_obj = count($select->fetchAll());
        if ($get_obj === 1) {
            $sql = "UPDATE user set password = ? WHERE iduser =? ";
            $data = array($passwordnew, $iduser);

            $update_pass = $this->db->prepare($sql);
            $update_pass->execute($data);
            return $update_pass->rowCount();
        } else {
            return false;
        }
    }
    public function UserGetAllExceptAdmin()
    {
        $sql = "SELECT * FROM user WHERE username != 'admin'";

        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }

    public function UserGetbyUsername($username)
    {
        $sql = 'SELECT * FROM user WHERE username = ?';
        $data = array($username);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
}
// Removed direct instantiation of user class
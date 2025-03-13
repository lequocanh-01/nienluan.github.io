<?php
$s = '../../elements_LQA/mod/database.php';
if (file_exists($s)) {
    $f = $s;
} else {
    $f = './elements_LQA/mod/database.php';
}
require_once $f;

class user extends Database
{
    public function UserCheckLogin($username, $password)
    {
        $sql = 'select * from user where username = ? and password = ? and setlock = 1';
        $data = array($username, $password);

        $select = $this->connect->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);

        $get_obj = count($select->fetchAll());

        if($get_obj === 1){
            return true;
        } else{
            return false;
        }
        
    }
    public function UserCheckUsername($username)
    {
        $sql = 'select * from user where username = ?';
        $data = array($username);

        $select = $this->connect->prepare($sql);
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

        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        return $getAll->fetchAll();
    }
    public function UserAdd($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai)
    {
        $sql = "INSERT INTO user (username, password, hoten, gioitinh, ngaysinh, diachi, dienthoai) VALUES (?,?,?,?,?,?,?)";
        $data = array($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai);

        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }
    public function UserDelete($iduser)
    {
        $sql = "DELETE from user where iduser = ?";
        $data = array($iduser);

        $del = $this->connect->prepare($sql);
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
            $update = $this->connect->prepare($sql);
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

        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }
    public function UserSetPassword($iduser, $password)
    {
        $sql = "UPDATE user set password = ? WHERE iduser =? ";
        $data = array($password, $iduser);

        $update_pass = $this->connect->prepare($sql);
        $update_pass->execute($data);
        return $update_pass->rowCount();
    }
    public function UserSetActive($iduser, $setlock)
    {
        $sql = "UPDATE user set setlock = ? WHERE iduser =? ";
        $data = array($setlock, $iduser);

        $update_lock = $this->connect->prepare($sql);
        $update_lock->execute($data);
        return $update_lock->rowCount();
    }
    public function UserChangePassword($iduser, $passwordold, $passwordnew)
    {
        $sql = 'select * from user where iduser = ? and password = ?';
        $data = array($iduser, $passwordold);

        $select = $this->connect->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);

        $get_obj = count($select->fetchAll());
        if ($get_obj === 1) {
            $sql = "UPDATE user set password = ? WHERE iduser =? ";
            $data = array($passwordnew, $iduser);

            $update_pass = $this->connect->prepare($sql);
            $update_pass->execute($data);
            return $update_pass->rowCount();
        } else {
            return false;
        }
    }
}
$user = new user();
// $row = $user->UserAdd('sir','sir1234','Lê Quản Lý','1','1976-88-82','Lê Bình, Ninh Kiều, Cần Thơ','0893333332');
// echo $row;

// $list = $user->UserGetAll(); // trả về là 1 danh sách đối tượng
// foreach ($list as $l) {
// echo $l->username . '<br/>';}

// echo $user->UserDelete(6);

// $row = $user->UserUpdate('quocanh','quocanh1234','Lê Quản Lý','1','1976-88-82','Lê Bình, Ninh Kiều, Cần Thơ','0893333332',4);
// echo $row;

// $obj = $user->UserGetByid(5);
// echo $obj->username .'<br/>';
// echo $obj->hoten.'<br/>';
// echo $obj->gioitinh .'<br/>';
// echo $obj->ngaysinh .'<br/>';

// echo $user->UserSetPassword(4,'124');

// echo $user->UserSetACtive(4,1);

// echo $user->UserChangePassword(4,'124','quocanh');
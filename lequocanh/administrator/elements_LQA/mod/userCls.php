<?php
$s = '../../elements_LQA/mod/database.php';
if (file_exists($s)) {
    $f = $s;
} else {
    $f = './elements_LQA/mod/database.php';
}
require_once $f;

// Kiểm tra xem class user đã được định nghĩa chưa
if (!class_exists('user')) {
    class user
    {
        private $db;

        public function __construct()
        {
            $this->db = Database::getInstance()->getConnection();
        }

        public function UserCheckLogin($username, $password)
        {
            // Loại bỏ khoảng trắng thừa từ username
            $username = trim($username);

            // Ghi log để debug
            error_log("UserCheckLogin - Username: '$username', Password length: " . strlen($password));

            // Kiểm tra trực tiếp trong cơ sở dữ liệu
            $sql = 'SELECT * FROM user WHERE username = ?';
            $data = array($username);

            $select = $this->db->prepare($sql);
            $select->setFetchMode(PDO::FETCH_OBJ);
            $select->execute($data);

            $user = $select->fetch();

            if ($user) {
                error_log("UserCheckLogin - Tìm thấy user: " . json_encode($user));

                // Kiểm tra mật khẩu
                if ($user->password === $password) {
                    error_log("UserCheckLogin - Mật khẩu khớp");

                    // Kiểm tra trạng thái tài khoản
                    if ($user->setlock == 1) {
                        error_log("UserCheckLogin - Tài khoản đã kích hoạt (setlock=1)");
                        return true;
                    } else {
                        error_log("UserCheckLogin - Tài khoản chưa kích hoạt (setlock=" . $user->setlock . ")");

                        // Tự động kích hoạt tài khoản
                        $update_sql = "UPDATE user SET setlock = 1 WHERE iduser = ?";
                        $update = $this->db->prepare($update_sql);
                        $update->execute(array($user->iduser));
                        error_log("UserCheckLogin - Đã tự động kích hoạt tài khoản");

                        return true; // Cho phép đăng nhập sau khi kích hoạt
                    }
                } else {
                    error_log("UserCheckLogin - Mật khẩu không khớp. DB: '" . $user->password . "', Input: '$password'");
                    return false;
                }
            } else {
                error_log("UserCheckLogin - Không tìm thấy user với username: '$username'");

                // Kiểm tra xem có user nào gần giống không
                $sql_like = "SELECT * FROM user WHERE username LIKE ?";
                $stmt_like = $this->db->prepare($sql_like);
                $stmt_like->execute(array('%' . $username . '%'));
                $similar_users = $stmt_like->fetchAll(PDO::FETCH_OBJ);

                if (count($similar_users) > 0) {
                    error_log("UserCheckLogin - Tìm thấy các user tương tự: " . json_encode($similar_users));
                }

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
            // Loại bỏ khoảng trắng thừa từ username
            $username = trim($username);

            $sql = "INSERT INTO user (username, password, hoten, gioitinh, ngaysinh, diachi, dienthoai, setlock) VALUES (?,?,?,?,?,?,?,?)";
            $data = array($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, 1); // Thêm setlock=1 để kích hoạt tài khoản

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
    } // Đóng class user
} // Đóng if (!class_exists('user'))
// Removed direct instantiation of user class
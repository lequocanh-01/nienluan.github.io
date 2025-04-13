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

class PhanQuyen
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Kiểm tra xem username có phải là nhân viên không
    public function isNhanVien($username)
    {
        $sql = 'SELECT nv.* FROM nhanvien nv 
                INNER JOIN user u ON nv.iduser = u.iduser
                WHERE u.username = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);

        return $stmt->rowCount() > 0;
    }

    // Kiểm tra quyền truy cập vào một chức năng cụ thể
    public function checkAccess($module, $username)
    {
        // Nếu là Admin thì có quyền truy cập toàn bộ
        if (isset($_SESSION['ADMIN'])) {
            return true;
        }

        // Nếu là user thông thường (không phải admin và không phải nhân viên)
        if (isset($_SESSION['USER']) && !$this->isNhanVien($username)) {
            // Chỉ cho phép xem hồ sơ cá nhân
            $userAllowedModules = [
                'userprofile',
                'userUpdateProfile'
            ];

            return in_array($module, $userAllowedModules);
        }

        // Nếu là nhân viên
        if (isset($_SESSION['USER']) && $this->isNhanVien($username)) {
            // Các bảng nhân viên KHÔNG được phép truy cập
            $restrictedModules = [
                'userview',
                'userupdate',
                'updateuser',  // Các module liên quan đến user
                'nhanvienview'                          // Module liên quan đến nhân viên
            ];

            if (in_array($module, $restrictedModules)) {
                return false;
            }

            // Các module được phép truy cập cho nhân viên
            $allowedModules = [
                'loaihangview',
                'hanghoaview',
                'dongiaview',
                'thuonghieuview',
                'donvitinhview',
                'thuoctinhview',
                'thuoctinhhhview',
                'adminGiohangView',
                'hinhanhview',
                'userprofile',
                'userUpdateProfile'
            ];

            return in_array($module, $allowedModules);
        }

        // Mặc định không có quyền truy cập
        return false;
    }
}

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
        // Log để debug
        error_log("Kiểm tra quyền truy cập - Module: $module, Username: $username");

        // Nếu là Admin thì có quyền truy cập toàn bộ
        if (isset($_SESSION['ADMIN'])) {
            error_log("User là admin, cho phép truy cập");
            return true;
        }

        // Nếu là user thông thường (không phải admin và không phải nhân viên)
        if (isset($_SESSION['USER']) && !$this->isNhanVien($username)) {
            // Chỉ cho phép xem hồ sơ cá nhân
            $userAllowedModules = [
                'userprofile',
                'userUpdateProfile'
            ];

            $hasAccess = in_array($module, $userAllowedModules);
            error_log("User thông thường - Module: $module, Cho phép: " . ($hasAccess ? 'Có' : 'Không'));
            return $hasAccess;
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
                error_log("Nhân viên không được phép truy cập module hạn chế: $module");
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
                'mphieunhap',
                'mchitietphieunhap',
                'mphieunhapedit',
                'mchitietphieunhapedit',
                'mtonkho',
                'mtonkhoedit',
                'mphieunhapfixtonkho',
                'userprofile',
                'userUpdateProfile'
            ];

            $hasAccess = in_array($module, $allowedModules);
            error_log("Nhân viên - Module: $module, Cho phép: " . ($hasAccess ? 'Có' : 'Không'));
            return $hasAccess;
        }

        // Mặc định không có quyền truy cập
        error_log("Không có quyền truy cập mặc định cho module: $module");
        return false;
    }
}

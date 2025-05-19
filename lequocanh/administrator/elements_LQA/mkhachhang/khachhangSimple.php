<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require_once '../mod/simple_db.php';

/**
 * Class KhachHang
 * Lớp xử lý thông tin khách hàng
 */
class KhachHang {
    private $db;
    
    /**
     * Khởi tạo đối tượng KhachHang
     */
    public function __construct() {
        try {
            $this->db = SimpleDB::getInstance();
        } catch (Exception $e) {
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }
    
    /**
     * Kiểm tra xem username có phải là nhân viên không
     * @param string $username Username cần kiểm tra
     * @return bool True nếu là nhân viên, False nếu không phải
     */
    private function isNhanVien($username) {
        $sql = 'SELECT nv.* FROM nhanvien nv
                INNER JOIN user u ON nv.iduser = u.iduser
                WHERE u.username = ?';
        $result = $this->db->query($sql, [$username]);
        return count($result) > 0;
    }
    
    /**
     * Lấy danh sách tất cả khách hàng (người dùng không phải admin và không phải nhân viên)
     * @return array Danh sách khách hàng
     */
    public function getAll() {
        // Lấy danh sách tất cả người dùng không phải admin
        $sql = "SELECT u.iduser as id, u.username, u.hoten, u.gioitinh, u.ngaysinh, u.diachi, u.dienthoai, u.email, 
                       u.ngaydangki as ngaytao, u.setlock
                FROM user u 
                WHERE u.username != 'admin'
                ORDER BY u.hoten ASC";
        
        $users = $this->db->query($sql);
        
        // Lọc ra những người không phải nhân viên
        $customers = [];
        foreach ($users as $user) {
            if (!$this->isNhanVien($user['username'])) {
                $customers[] = $user;
            }
        }
        
        return $customers;
    }
    
    /**
     * Lấy thông tin khách hàng theo ID
     * @param int $id ID của khách hàng
     * @return array|false Thông tin khách hàng hoặc false nếu không tìm thấy
     */
    public function getById($id) {
        $sql = "SELECT u.iduser as id, u.username, u.hoten, u.gioitinh, u.ngaysinh, u.diachi, u.dienthoai, u.email, 
                       u.ngaydangki as ngaytao, u.setlock
                FROM user u 
                WHERE u.iduser = ? AND u.username != 'admin'";
        $user = $this->db->queryOne($sql, [$id]);
        
        // Kiểm tra xem người dùng có phải là nhân viên không
        if ($user && $this->isNhanVien($user['username'])) {
            return false; // Không trả về thông tin nếu là nhân viên
        }
        
        return $user;
    }
    
    /**
     * Lấy thông tin khách hàng theo username
     * @param string $username Username của khách hàng
     * @return array|false Thông tin khách hàng hoặc false nếu không tìm thấy
     */
    public function getByUsername($username) {
        $sql = "SELECT u.iduser as id, u.username, u.hoten, u.gioitinh, u.ngaysinh, u.diachi, u.dienthoai, u.email, 
                       u.ngaydangki as ngaytao, u.setlock
                FROM user u 
                WHERE u.username = ? AND u.username != 'admin'";
        $user = $this->db->queryOne($sql, [$username]);
        
        // Kiểm tra xem người dùng có phải là nhân viên không
        if ($user && $this->isNhanVien($user['username'])) {
            return false; // Không trả về thông tin nếu là nhân viên
        }
        
        return $user;
    }
    
    /**
     * Tìm kiếm khách hàng theo từ khóa
     * @param string $keyword Từ khóa tìm kiếm
     * @param string $field Trường cần tìm kiếm (all, hoten, dienthoai, email, diachi)
     * @return array Danh sách khách hàng tìm thấy
     */
    public function search($keyword, $field = 'all') {
        // Lấy danh sách tất cả người dùng không phải admin
        if ($field == 'all') {
            $sql = "SELECT u.iduser as id, u.username, u.hoten, u.gioitinh, u.ngaysinh, u.diachi, u.dienthoai, u.email, 
                           u.ngaydangki as ngaytao, u.setlock
                    FROM user u 
                    WHERE u.username != 'admin' AND (u.hoten LIKE ? OR u.dienthoai LIKE ? OR u.email LIKE ? OR u.diachi LIKE ?)
                    ORDER BY u.hoten ASC";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"];
        } else {
            $sql = "SELECT u.iduser as id, u.username, u.hoten, u.gioitinh, u.ngaysinh, u.diachi, u.dienthoai, u.email, 
                           u.ngaydangki as ngaytao, u.setlock
                    FROM user u 
                    WHERE u.username != 'admin' AND u.$field LIKE ?
                    ORDER BY u.hoten ASC";
            $params = ["%$keyword%"];
        }
        
        $users = $this->db->query($sql, $params);
        
        // Lọc ra những người không phải nhân viên
        $customers = [];
        foreach ($users as $user) {
            if (!$this->isNhanVien($user['username'])) {
                $customers[] = $user;
            }
        }
        
        return $customers;
    }
    
    /**
     * Thêm khách hàng mới
     * @param array $data Dữ liệu khách hàng
     * @return int|false ID của khách hàng mới hoặc false nếu thất bại
     */
    public function add($data) {
        // Kiểm tra xem username đã tồn tại chưa
        $checkSql = "SELECT iduser FROM user WHERE username = ?";
        $existingUser = $this->db->queryOne($checkSql, [$data['username']]);
        
        if ($existingUser) {
            // Username đã tồn tại
            return false;
        }
        
        // Thêm người dùng mới vào bảng user
        $sql = "INSERT INTO user (username, password, hoten, gioitinh, ngaysinh, diachi, dienthoai, email, setlock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        // Tạo mật khẩu mặc định (có thể thay đổi theo yêu cầu)
        $defaultPassword = md5('123456');
        
        $params = [
            $data['username'],
            $defaultPassword,
            $data['hoten'],
            $data['gioitinh'],
            $data['ngaysinh'],
            $data['diachi'],
            $data['dienthoai'],
            $data['email']
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật thông tin khách hàng
     * @param int $id ID của khách hàng
     * @param array $data Dữ liệu cập nhật
     * @return bool Kết quả cập nhật
     */
    public function update($id, $data) {
        // Kiểm tra xem người dùng có phải là nhân viên không
        $user = $this->getById($id);
        if (!$user) {
            return false;
        }
        
        $sql = "UPDATE user 
                SET hoten = ?, gioitinh = ?, ngaysinh = ?, diachi = ?, dienthoai = ?, email = ? 
                WHERE iduser = ?";
        
        $params = [
            $data['hoten'],
            $data['gioitinh'],
            $data['ngaysinh'],
            $data['diachi'],
            $data['dienthoai'],
            $data['email'],
            $id
        ];
        
        $result = $this->db->execute($sql, $params);
        return $result > 0;
    }
    
    /**
     * Xóa khách hàng
     * @param int $id ID của khách hàng
     * @return bool Kết quả xóa
     */
    public function delete($id) {
        // Không thực sự xóa người dùng, chỉ vô hiệu hóa tài khoản
        $sql = "UPDATE user SET setlock = 0 WHERE iduser = ?";
        $result = $this->db->execute($sql, [$id]);
        return $result > 0;
    }
    
    /**
     * Lấy lịch sử mua hàng của khách hàng
     * @param string $username Username của khách hàng
     * @param int $limit Số lượng đơn hàng tối đa
     * @return array Danh sách đơn hàng
     */
    public function getOrderHistory($username, $limit = 5) {
        // Kiểm tra xem bảng orders có tồn tại không
        try {
            $sql = "SELECT o.*,
                        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                   FROM orders o
                   WHERE o.user_id = ?
                   ORDER BY o.created_at DESC
                   LIMIT ?";
            return $this->db->query($sql, [$username, $limit]);
        } catch (Exception $e) {
            error_log("Lỗi khi lấy lịch sử mua hàng: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm đã mua của khách hàng
     * @param string $username Username của khách hàng
     * @param int $limit Số lượng sản phẩm tối đa
     * @return array Danh sách sản phẩm
     */
    public function getPurchasedProducts($username, $limit = 5) {
        try {
            $sql = "SELECT h.idhanghoa, h.tenhanghoa, h.hinhanh, h.gia,
                           COUNT(DISTINCT o.id) as order_count,
                           SUM(oi.quantity) as total_quantity,
                           MAX(o.created_at) as last_purchase_date
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    JOIN hanghoa h ON oi.product_id = h.idhanghoa
                    WHERE o.user_id = ? AND o.status = 'approved'
                    GROUP BY h.idhanghoa
                    ORDER BY last_purchase_date DESC
                    LIMIT ?";
            return $this->db->query($sql, [$username, $limit]);
        } catch (Exception $e) {
            error_log("Lỗi khi lấy sản phẩm đã mua: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy tổng chi tiêu của khách hàng
     * @param string $username Username của khách hàng
     * @return float Tổng chi tiêu
     */
    public function getTotalSpent($username) {
        try {
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_spent
                    FROM orders
                    WHERE user_id = ? AND status = 'approved'";
            return $this->db->queryValue($sql, [$username]);
        } catch (Exception $e) {
            error_log("Lỗi khi lấy tổng chi tiêu: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lấy số lượng đơn hàng của khách hàng
     * @param string $username Username của khách hàng
     * @return int Số lượng đơn hàng
     */
    public function getOrderCount($username) {
        try {
            $sql = "SELECT COUNT(*) as order_count
                    FROM orders
                    WHERE user_id = ?";
            return $this->db->queryValue($sql, [$username]);
        } catch (Exception $e) {
            error_log("Lỗi khi lấy số lượng đơn hàng: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Định dạng giới tính
     * @param int $gioitinh Giới tính (0: Nữ, 1: Nam, 2: Khác)
     * @return string Giới tính đã định dạng
     */
    public static function formatGender($gioitinh) {
        switch ($gioitinh) {
            case 0:
                return 'Nữ';
            case 1:
                return 'Nam';
            case 2:
                return 'Khác';
            default:
                return 'Không xác định';
        }
    }
}
?>

<?php

/**
 * File: nhatKyHoatDongCls.php
 * Lớp quản lý nhật ký hoạt động của nhân viên
 */

// Xác định đường dẫn tới file database.php
$possible_paths = array(
    dirname(__FILE__) . '/database.php',                    // Cùng thư mục
    dirname(dirname(dirname(__FILE__))) . '/elements_LQA/mod/database.php',  // Từ thư mục administrator
    dirname(dirname(dirname(dirname(__FILE__)))) . '/administrator/elements_LQA/mod/database.php'  // Từ thư mục gốc
);

$database_file = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $database_file = $path;
        break;
    }
}

if ($database_file === null) {
    die("Không thể tìm thấy file database.php");
}

require_once $database_file;

class NhatKyHoatDong
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->createTableIfNotExists();
    }

    /**
     * Tạo bảng nhat_ky_hoat_dong nếu chưa tồn tại
     */
    private function createTableIfNotExists()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS nhat_ky_hoat_dong (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                hanh_dong VARCHAR(100) NOT NULL,
                doi_tuong VARCHAR(50) NOT NULL,
                doi_tuong_id INT,
                chi_tiet TEXT,
                ip_address VARCHAR(50),
                thoi_gian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_hanh_dong (hanh_dong),
                INDEX idx_doi_tuong (doi_tuong, doi_tuong_id),
                INDEX idx_thoi_gian (thoi_gian)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi khi tạo bảng nhat_ky_hoat_dong: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ghi nhật ký hoạt động
     *
     * @param string $username Tên đăng nhập của người dùng
     * @param string $hanhDong Hành động thực hiện (thêm, sửa, xóa, đăng nhập, v.v.)
     * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
     * @param int $doiTuongId ID của đối tượng (nếu có)
     * @param string $chiTiet Chi tiết về hành động
     * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
     */
    public function ghiNhatKy($username, $hanhDong, $doiTuong, $doiTuongId = null, $chiTiet = '')
    {
        try {
            $ipAddress = $this->getClientIP();

            // Xác định module dựa trên username
            $moDun = 'Hệ thống';
            if (strpos($username, 'manager') !== false) {
                $moDun = 'Quản lý';
            } elseif (strpos($username, 'staff') !== false) {
                $moDun = 'Nhân viên';
            } elseif ($username === 'admin') {
                $moDun = 'Quản trị';
            }

            // Cập nhật SQL để phù hợp với cấu trúc bảng hiện tại
            $sql = "INSERT INTO nhat_ky_hoat_dong (username, hanh_dong, doi_tuong, doi_tuong_id, chi_tiet, mo_dun, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $hanhDong, $doiTuong, $doiTuongId, $chiTiet, $moDun, $ipAddress]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Lỗi khi ghi nhật ký hoạt động: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách nhật ký hoạt động theo điều kiện
     *
     * @param array $filters Các điều kiện lọc (username, username_in, hanh_dong, doi_tuong, tu_ngay, den_ngay)
     * @param int $limit Số lượng bản ghi tối đa
     * @param int $offset Vị trí bắt đầu
     * @return array Danh sách nhật ký hoạt động
     */
    public function layDanhSachNhatKy($filters = [], $limit = 100, $offset = 0)
    {
        try {
            // Kiểm tra xem bảng có tồn tại không
            $checkTableSql = "SHOW TABLES LIKE 'nhat_ky_hoat_dong'";
            $checkTableStmt = $this->db->prepare($checkTableSql);
            $checkTableStmt->execute();

            if ($checkTableStmt->rowCount() == 0) {
                // Bảng không tồn tại, tạo bảng
                $this->createTableIfNotExists();
                return []; // Trả về mảng rỗng vì bảng vừa được tạo
            }

            $whereClause = [];
            $params = [];

            if (isset($filters['username']) && !empty($filters['username'])) {
                $whereClause[] = "nk.username = ?";
                $params[] = $filters['username'];
            } elseif (isset($filters['username_in']) && is_array($filters['username_in']) && !empty($filters['username_in'])) {
                // Lọc theo danh sách username
                $placeholders = implode(',', array_fill(0, count($filters['username_in']), '?'));
                $whereClause[] = "nk.username IN ($placeholders)";
                $params = array_merge($params, $filters['username_in']);
            }

            if (isset($filters['hanh_dong']) && !empty($filters['hanh_dong'])) {
                $whereClause[] = "nk.hanh_dong = ?";
                $params[] = $filters['hanh_dong'];
            }

            if (isset($filters['doi_tuong']) && !empty($filters['doi_tuong'])) {
                $whereClause[] = "nk.doi_tuong = ?";
                $params[] = $filters['doi_tuong'];
            }

            if (isset($filters['doi_tuong_id']) && !empty($filters['doi_tuong_id'])) {
                $whereClause[] = "nk.doi_tuong_id = ?";
                $params[] = $filters['doi_tuong_id'];
            }

            if (isset($filters['tu_ngay']) && !empty($filters['tu_ngay'])) {
                $whereClause[] = "nk.thoi_gian >= ?";
                $params[] = $filters['tu_ngay'] . ' 00:00:00';
            }

            if (isset($filters['den_ngay']) && !empty($filters['den_ngay'])) {
                $whereClause[] = "nk.thoi_gian <= ?";
                $params[] = $filters['den_ngay'] . ' 23:59:59';
            }

            $where = count($whereClause) > 0 ? "WHERE " . implode(" AND ", $whereClause) : "";

            // Kiểm tra xem bảng user và nhanvien có tồn tại không
            $checkUserTableSql = "SHOW TABLES LIKE 'user'";
            $checkUserTableStmt = $this->db->prepare($checkUserTableSql);
            $checkUserTableStmt->execute();

            $checkNhanVienTableSql = "SHOW TABLES LIKE 'nhanvien'";
            $checkNhanVienTableStmt = $this->db->prepare($checkNhanVienTableSql);
            $checkNhanVienTableStmt->execute();

            // Sử dụng query đơn giản để tránh lỗi collation
            $sql = "SELECT nk.*
                    FROM nhat_ky_hoat_dong nk
                    $where
                    ORDER BY nk.thoi_gian DESC
                    LIMIT $limit OFFSET $offset";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy danh sách nhật ký hoạt động: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số bản ghi nhật ký theo điều kiện
     *
     * @param array $filters Các điều kiện lọc (username, username_in, hanh_dong, doi_tuong, tu_ngay, den_ngay)
     * @return int Tổng số bản ghi
     */
    public function demTongSoNhatKy($filters = [])
    {
        try {
            // Kiểm tra xem bảng có tồn tại không
            $checkTableSql = "SHOW TABLES LIKE 'nhat_ky_hoat_dong'";
            $checkTableStmt = $this->db->prepare($checkTableSql);
            $checkTableStmt->execute();

            if ($checkTableStmt->rowCount() == 0) {
                // Bảng không tồn tại, tạo bảng
                $this->createTableIfNotExists();
                return 0; // Trả về 0 vì bảng vừa được tạo
            }

            $whereClause = [];
            $params = [];

            if (isset($filters['username']) && !empty($filters['username'])) {
                $whereClause[] = "username = ?";
                $params[] = $filters['username'];
            } elseif (isset($filters['username_in']) && is_array($filters['username_in']) && !empty($filters['username_in'])) {
                // Lọc theo danh sách username
                $placeholders = implode(',', array_fill(0, count($filters['username_in']), '?'));
                $whereClause[] = "username IN ($placeholders)";
                $params = array_merge($params, $filters['username_in']);
            }

            if (isset($filters['hanh_dong']) && !empty($filters['hanh_dong'])) {
                $whereClause[] = "hanh_dong = ?";
                $params[] = $filters['hanh_dong'];
            }

            if (isset($filters['doi_tuong']) && !empty($filters['doi_tuong'])) {
                $whereClause[] = "doi_tuong = ?";
                $params[] = $filters['doi_tuong'];
            }

            if (isset($filters['doi_tuong_id']) && !empty($filters['doi_tuong_id'])) {
                $whereClause[] = "doi_tuong_id = ?";
                $params[] = $filters['doi_tuong_id'];
            }

            if (isset($filters['tu_ngay']) && !empty($filters['tu_ngay'])) {
                $whereClause[] = "thoi_gian >= ?";
                $params[] = $filters['tu_ngay'] . ' 00:00:00';
            }

            if (isset($filters['den_ngay']) && !empty($filters['den_ngay'])) {
                $whereClause[] = "thoi_gian <= ?";
                $params[] = $filters['den_ngay'] . ' 23:59:59';
            }

            $where = count($whereClause) > 0 ? "WHERE " . implode(" AND ", $whereClause) : "";

            $sql = "SELECT COUNT(*) as total FROM nhat_ky_hoat_dong $where";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Lỗi khi đếm tổng số nhật ký hoạt động: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy thông tin chi tiết một nhật ký hoạt động theo ID
     *
     * @param int $id ID của nhật ký hoạt động
     * @return array|false Thông tin chi tiết nhật ký hoặc false nếu không tìm thấy
     */
    public function getActivityById($id)
    {
        try {
            // Kiểm tra xem bảng có tồn tại không
            $checkTableSql = "SHOW TABLES LIKE 'nhat_ky_hoat_dong'";
            $checkTableStmt = $this->db->prepare($checkTableSql);
            $checkTableStmt->execute();

            if ($checkTableStmt->rowCount() == 0) {
                // Bảng không tồn tại, tạo bảng
                $this->createTableIfNotExists();
                return false;
            }

            // Query đơn giản để tránh lỗi collation
            $sql = "SELECT * FROM nhat_ky_hoat_dong WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy chi tiết nhật ký hoạt động: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy IP của client
     *
     * @return string IP của client
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
        return $ip;
    }
}

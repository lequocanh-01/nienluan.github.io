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

class MTonKho
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy thông tin tồn kho theo ID hàng hóa
    public function getTonKhoByIdHangHoa($idhanghoa)
    {
        $sql = "SELECT * FROM tonkho WHERE idhanghoa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute([$idhanghoa]);
        return $stmt->fetch();
    }

    // Lấy tất cả thông tin tồn kho
    public function getAllTonKho()
    {
        $sql = "SELECT t.*, h.tenhanghoa, h.mota, dvt.tenDonViTinh
                FROM tonkho t
                LEFT JOIN hanghoa h ON t.idhanghoa = h.idhanghoa
                LEFT JOIN donvitinh dvt ON h.idDonViTinh = dvt.idDonViTinh
                ORDER BY t.idTonKho";
        $stmt = $this->db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cập nhật số lượng tồn kho
    public function updateSoLuong($idhanghoa, $soLuongThayDoi, $isIncrement = true)
    {
        try {
            // Ghi log để debug
            error_log("Updating tonkho for idhanghoa: " . $idhanghoa . ", soLuongThayDoi: " . $soLuongThayDoi . ", isIncrement: " . ($isIncrement ? "true" : "false"));

            // Kiểm tra xem hàng hóa đã có trong bảng tồn kho chưa
            $tonkho = $this->getTonKhoByIdHangHoa($idhanghoa);

            if ($tonkho) {
                // Nếu đã có, cập nhật số lượng
                $newSoLuong = $isIncrement
                    ? $tonkho->soLuong + $soLuongThayDoi
                    : $tonkho->soLuong - $soLuongThayDoi;

                // Đảm bảo số lượng không âm
                $newSoLuong = max(0, $newSoLuong);

                error_log("Updating existing tonkho: old soLuong = " . $tonkho->soLuong . ", new soLuong = " . $newSoLuong);

                $sql = "UPDATE tonkho SET soLuong = ?, ngayCapNhat = CURRENT_TIMESTAMP WHERE idhanghoa = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$newSoLuong, $idhanghoa]);

                error_log("Update result: " . ($result ? "success" : "failed") . ", rows affected: " . $stmt->rowCount());
                return $result;
            } else {
                // Nếu chưa có, thêm mới vào bảng tồn kho
                error_log("Creating new tonkho entry for idhanghoa: " . $idhanghoa . " with soLuong: " . $soLuongThayDoi);

                // Kiểm tra xem bảng tonkho có tồn tại không
                try {
                    $checkTable = $this->db->query("SHOW TABLES LIKE 'tonkho'");
                    if ($checkTable->rowCount() == 0) {
                        // Tạo bảng tonkho nếu chưa tồn tại
                        error_log("Table tonkho does not exist, creating it");
                        $createTable = "CREATE TABLE IF NOT EXISTS tonkho (
                            idTonKho INT AUTO_INCREMENT PRIMARY KEY,
                            idhanghoa INT NOT NULL,
                            soLuong INT NOT NULL DEFAULT 0,
                            soLuongToiThieu INT NOT NULL DEFAULT 0,
                            viTri VARCHAR(255),
                            ngayCapNhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (idhanghoa) REFERENCES hanghoa(idhanghoa)
                        )";
                        $this->db->exec($createTable);
                    }
                } catch (PDOException $e) {
                    error_log("Error checking/creating tonkho table: " . $e->getMessage());
                }

                $sql = "INSERT INTO tonkho (idhanghoa, soLuong, soLuongToiThieu, viTri) VALUES (?, ?, 0, '')";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$idhanghoa, $soLuongThayDoi]);

                error_log("Insert result: " . ($result ? "success" : "failed") . ", last insert ID: " . $this->db->lastInsertId());
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error updating tonkho: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật thông tin tồn kho
    public function updateTonKho($idTonKho, $soLuong, $soLuongToiThieu, $viTri)
    {
        try {
            $sql = "UPDATE tonkho
                    SET soLuong = ?, soLuongToiThieu = ?, viTri = ?, ngayCapNhat = CURRENT_TIMESTAMP
                    WHERE idTonKho = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$soLuong, $soLuongToiThieu, $viTri, $idTonKho]);
        } catch (PDOException $e) {
            error_log("Error updating tonkho: " . $e->getMessage());
            return false;
        }
    }

    // Kiểm tra hàng hóa có tồn tại trong bảng tồn kho không
    public function checkHangHoaExists($idhanghoa)
    {
        $sql = "SELECT COUNT(*) FROM tonkho WHERE idhanghoa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idhanghoa]);
        return $stmt->fetchColumn() > 0;
    }

    // Lấy danh sách hàng hóa sắp hết (số lượng dưới mức tối thiểu)
    public function getHangHoaSapHet()
    {
        $sql = "SELECT t.*, h.tenhanghoa, h.mota, dvt.tenDonViTinh
                FROM tonkho t
                LEFT JOIN hanghoa h ON t.idhanghoa = h.idhanghoa
                LEFT JOIN donvitinh dvt ON h.idDonViTinh = dvt.idDonViTinh
                WHERE t.soLuong <= t.soLuongToiThieu AND t.soLuongToiThieu > 0
                ORDER BY t.soLuong ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy danh sách hàng hóa hết hàng (số lượng = 0)
    public function getHangHoaHetHang()
    {
        $sql = "SELECT t.*, h.tenhanghoa, h.mota, dvt.tenDonViTinh
                FROM tonkho t
                LEFT JOIN hanghoa h ON t.idhanghoa = h.idhanghoa
                LEFT JOIN donvitinh dvt ON h.idDonViTinh = dvt.idDonViTinh
                WHERE t.soLuong = 0
                ORDER BY t.idTonKho";
        $stmt = $this->db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy thông tin tồn kho theo ID tồn kho
    public function getTonKhoById($idTonKho)
    {
        try {
            $sql = "SELECT * FROM tonkho WHERE idTonKho = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $stmt->execute([$idTonKho]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting tonkho by ID: " . $e->getMessage());
            return null;
        }
    }
}

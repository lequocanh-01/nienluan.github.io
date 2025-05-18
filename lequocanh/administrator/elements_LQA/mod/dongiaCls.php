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

class Dongia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy tất cả các đơn giá
    public function DongiaGetAll()
    {
        try {
            $sql = 'SELECT d.*, h.tenhanghoa
                   FROM dongia d
                   LEFT JOIN hanghoa h ON d.idHangHoa = h.idhanghoa
                   ORDER BY d.idHangHoa, d.apDung DESC, d.ngayApDung DESC';
            $getAll = $this->db->prepare($sql);
            $getAll->setFetchMode(PDO::FETCH_OBJ);
            $getAll->execute();
            return $getAll->fetchAll();
        } catch (PDOException $e) {
            error_log("DongiaGetAll Error: " . $e->getMessage());
            return [];
        }
    }

    // Thêm đơn giá mới
    public function DongiaAdd($idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien = '', $ghiChu = '')
    {
        try {
            // Đặt tất cả các đơn giá hiện tại của sản phẩm này thành không áp dụng
            $this->DongiaSetAllToFalse($idHangHoa);

            // Thêm đơn giá mới và đặt nó thành đang áp dụng
            $sql = "INSERT INTO dongia (idHangHoa, giaBan, ngayApDung, ngayKetThuc, dieuKien, ghiChu, apDung)
                   VALUES (?, ?, ?, ?, ?, ?, 1)";
            $data = array($idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu);

            $add = $this->db->prepare($sql);
            $add->execute($data);

            // Cập nhật giá tham khảo trong bảng hanghoa
            $this->HanghoaUpdatePrice($idHangHoa, $giaBan);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("DongiaAdd Error: " . $e->getMessage());
            return false;
        }
    }

    // Xóa đơn giá theo ID
    public function DongiaDelete($idDonGia)
    {
        try {
            // Lấy thông tin đơn giá trước khi xóa
            $dongia = $this->DongiaGetbyId($idDonGia);
            if (!$dongia) {
                return false;
            }

            $sql = "DELETE FROM dongia WHERE idDonGia = ?";
            $data = array($idDonGia);

            $del = $this->db->prepare($sql);
            $result = $del->execute($data);

            // Nếu đơn giá đang được áp dụng, tìm đơn giá mới nhất để áp dụng
            if ($dongia->apDung) {
                $this->UpdateLatestPriceForProduct($dongia->idHangHoa);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("DongiaDelete Error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật thông tin đơn giá
    public function DongiaUpdate($idDonGia, $idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien = '', $ghiChu = '')
    {
        try {
            $sql = "UPDATE dongia
                   SET idHangHoa = ?, giaBan = ?, ngayApDung = ?, ngayKetThuc = ?, dieuKien = ?, ghiChu = ?
                   WHERE idDonGia = ?";
            $data = array($idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu, $idDonGia);

            $update = $this->db->prepare($sql);
            $result = $update->execute($data);

            // Nếu đơn giá đang được áp dụng, cập nhật giá tham khảo trong bảng hanghoa
            $dongia = $this->DongiaGetbyId($idDonGia);
            if ($dongia && $dongia->apDung) {
                $this->HanghoaUpdatePrice($idHangHoa, $giaBan);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("DongiaUpdate Error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy thông tin đơn giá theo ID
    public function DongiaGetbyId($idDonGia)
    {
        try {
            $sql = 'SELECT d.*, h.tenhanghoa
                   FROM dongia d
                   LEFT JOIN hanghoa h ON d.idHangHoa = h.idhanghoa
                   WHERE d.idDonGia = ?';
            $data = array($idDonGia);

            $getOne = $this->db->prepare($sql);
            $getOne->setFetchMode(PDO::FETCH_OBJ);
            $getOne->execute($data);

            return $getOne->fetch();
        } catch (PDOException $e) {
            error_log("DongiaGetbyId Error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy đơn giá theo ID hàng hóa
    public function DongiaGetbyIdHanghoa($idHangHoa)
    {
        try {
            $sql = 'SELECT d.*, h.tenhanghoa
                   FROM dongia d
                   LEFT JOIN hanghoa h ON d.idHangHoa = h.idhanghoa
                   WHERE d.idHangHoa = ?
                   ORDER BY d.apDung DESC, d.ngayApDung DESC';
            $data = array($idHangHoa);

            $getAll = $this->db->prepare($sql);
            $getAll->setFetchMode(PDO::FETCH_OBJ);
            $getAll->execute($data);

            return $getAll->fetchAll();
        } catch (PDOException $e) {
            error_log("DongiaGetbyIdHanghoa Error: " . $e->getMessage());
            return [];
        }
    }

    // Đặt tất cả đơn giá của một sản phẩm thành không áp dụng
    public function DongiaSetAllToFalse($idHangHoa)
    {
        try {
            $sql = "UPDATE dongia SET apDung = 0 WHERE idHangHoa = ?";
            $data = array($idHangHoa);

            $update = $this->db->prepare($sql);
            return $update->execute($data);
        } catch (PDOException $e) {
            error_log("DongiaSetAllToFalse Error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật trạng thái áp dụng của đơn giá
    public function DongiaUpdateStatus($idDonGia, $apDung)
    {
        try {
            $sql = "UPDATE dongia SET apDung = ? WHERE idDonGia = ?";
            $data = array($apDung ? 1 : 0, $idDonGia);

            $update = $this->db->prepare($sql);
            return $update->execute($data);
        } catch (PDOException $e) {
            error_log("DongiaUpdateStatus Error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật giá tham khảo trong bảng hanghoa
    public function HanghoaUpdatePrice($idHangHoa, $giaBan)
    {
        try {
            $sql = "UPDATE hanghoa SET giathamkhao = ? WHERE idhanghoa = ?";
            $data = array($giaBan, $idHangHoa);

            $update = $this->db->prepare($sql);
            return $update->execute($data);
        } catch (PDOException $e) {
            error_log("HanghoaUpdatePrice Error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật đơn giá mới nhất cho sản phẩm
    public function UpdateLatestPriceForProduct($idHangHoa)
    {
        try {
            // Tìm đơn giá mới nhất
            $sql = "SELECT * FROM dongia WHERE idHangHoa = ? ORDER BY ngayApDung DESC LIMIT 1";
            $data = array($idHangHoa);

            $getLatest = $this->db->prepare($sql);
            $getLatest->setFetchMode(PDO::FETCH_OBJ);
            $getLatest->execute($data);

            $latestPrice = $getLatest->fetch();

            if ($latestPrice) {
                // Đặt đơn giá mới nhất thành đang áp dụng
                $this->DongiaUpdateStatus($latestPrice->idDonGia, true);

                // Cập nhật giá tham khảo trong bảng hanghoa
                $this->HanghoaUpdatePrice($idHangHoa, $latestPrice->giaBan);

                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("UpdateLatestPriceForProduct Error: " . $e->getMessage());
            return false;
        }
    }
}

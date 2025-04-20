<?php

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

class hanghoa
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function HanghoaGetAll()
    {
        $sql = 'SELECT h.*, 
                t.tenTH AS ten_thuonghieu, 
                d.tenDonViTinh AS ten_donvitinh, 
                n.tenNV AS ten_nhanvien 
                FROM hanghoa h 
                LEFT JOIN thuonghieu t ON h.idThuongHieu = t.idThuongHieu 
                LEFT JOIN donvitinh d ON h.idDonViTinh = d.idDonViTinh 
                LEFT JOIN nhanvien n ON h.idNhanVien = n.idNhanVien';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $soLuong = 0)
    {
        // Convert empty strings to NULL for integer fields
        $idThuongHieu = $idThuongHieu === '' ? null : $idThuongHieu;
        $idDonViTinh = $idDonViTinh === '' ? null : $idDonViTinh;
        $idNhanVien = $idNhanVien === '' ? null : $idNhanVien;
        $soLuong = intval($soLuong);

        $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang, idThuongHieu, idDonViTinh, idNhanVien, soLuong) VALUES (?,?,?,?,?,?,?,?,?)";
        $data = array($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $soLuong);
        $add = $this->db->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }

    public function HanghoaDelete($idhanghoa)
    {
        $sql = "DELETE from hanghoa where idhanghoa = ?";
        $data = array($idhanghoa);

        $del = $this->db->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }

    public function HanghoaUpdate($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa, $soLuong = 0)
    {
        // Convert empty strings to NULL for integer fields
        $idThuongHieu = $idThuongHieu === '' ? null : $idThuongHieu;
        $idDonViTinh = $idDonViTinh === '' ? null : $idDonViTinh;
        $idNhanVien = $idNhanVien === '' ? null : $idNhanVien;
        $soLuong = intval($soLuong);

        $sql = "UPDATE hanghoa SET tenhanghoa=?, hinhanh=?, mota=?, giathamkhao=?, idloaihang=?, idThuongHieu=?, idDonViTinh=?, idNhanVien=?, soLuong=? WHERE idhanghoa =?";
        $data = array($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $soLuong, $idhanghoa);

        $update = $this->db->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }

    public function HanghoaGetbyId($idhanghoa)
    {
        $sql = 'select * from hanghoa where idhanghoa=?';
        $data = array($idhanghoa);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetch();
    }

    public function HanghoaGetbyIdloaihang($idloaihang)
    {
        $sql = 'select * from hanghoa where idloaihang=?';
        $data = array($idloaihang);

        $getOne = $this->db->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetchAll();
    }

    public function HanghoaUpdatePrice($idhanghoa, $giaban)
    {
        $sql = "UPDATE hanghoa SET giathamkhao = ? WHERE idhanghoa = ?";
        $data = array($giaban, $idhanghoa);

        $update = $this->db->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }

    public function searchHanghoa($keyword)
    {
        try {
            $select = "SELECT * FROM hanghoa 
                       WHERE LOWER(tenhanghoa) LIKE LOWER(:keyword)
                       ORDER BY tenhanghoa ASC 
                       LIMIT 10";
            $stmt = $this->db->prepare($select);
            $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Search error: " . $e->getMessage());
            return [];
        }
    }

    public function CheckRelations($idhanghoa)
    {
        $tablesWithRelations = []; // Mảng để lưu tên các bảng có liên kết

        // Kiểm tra liên kết với bảng thuộc tính hàng hóa (ví dụ)
        $sql = "SELECT COUNT(*) FROM thuoctinhhh WHERE idhanghoa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idhanghoa]);
        if ($stmt->fetchColumn() > 0) {
            $tablesWithRelations[] = 'thuoctinhhh';
        }

        // Kiểm tra liên kết với bảng đơn giá (ví dụ)
        $sql = "SELECT COUNT(*) FROM dongia WHERE idhanghoa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idhanghoa]);
        if ($stmt->fetchColumn() > 0) {
            $tablesWithRelations[] = 'dongia';
        }

        // Thêm các bảng khác mà bạn muốn kiểm tra ở đây

        return $tablesWithRelations; // Trả về danh sách các bảng có liên kết
    }

    public function GetAllThuongHieu()
    {
        $sql = 'SELECT * FROM thuonghieu';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function GetAllDonViTinh()
    {
        $sql = 'SELECT * FROM donvitinh';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function GetAllNhanVien()
    {
        $sql = 'SELECT * FROM nhanvien';
        $getAll = $this->db->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    // Lấy thông tin thương hiệu theo ID
    public function GetThuongHieuById($idThuongHieu)
    {
        $sql = 'SELECT * FROM thuonghieu WHERE idThuongHieu = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idThuongHieu]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function GetAllHinhAnh()
    {
        try {
            $sql = 'SELECT h.*, 
                    (SELECT COUNT(*) FROM hanghoa WHERE hinhanh = h.id) as usage_count 
                    FROM hinhanh h 
                    ORDER BY h.ngay_tao DESC';
            $getAll = $this->db->prepare($sql);
            $getAll->setFetchMode(PDO::FETCH_OBJ);
            $getAll->execute();
            return $getAll->fetchAll();
        } catch (Exception $e) {
            error_log("Error in GetAllHinhAnh: " . $e->getMessage());
            return array();
        }
    }

    public function GetHinhAnhById($id)
    {
        if (!$id) return null;

        try {
            $sql = 'SELECT * FROM hinhanh WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $hinhanh = $stmt->fetch(PDO::FETCH_OBJ);

            if ($hinhanh) {
                // Log đường dẫn để debug
                error_log("GetHinhAnhById - ID: " . $id . ", đường dẫn gốc: " . $hinhanh->duong_dan);

                // Xử lý đường dẫn hình ảnh
                if (strpos($hinhanh->duong_dan, 'data:image') === 0) {
                    // Nếu là base64, giữ nguyên
                    error_log("GetHinhAnhById - Đường dẫn là base64");
                    return $hinhanh;
                } else {
                    // Đường dẫn chính xác từ gốc web app
                    if (!empty($hinhanh->duong_dan)) {
                        // Chuẩn hóa đường dẫn
                        $hinhanh->duong_dan = str_replace('\\', '/', $hinhanh->duong_dan);

                        // Nếu đường dẫn chưa có "administrator/" ở đầu và bắt đầu bằng "uploads/"
                        if (
                            strpos($hinhanh->duong_dan, 'administrator/') !== 0 &&
                            strpos($hinhanh->duong_dan, 'uploads/') === 0
                        ) {
                            $hinhanh->duong_dan = 'administrator/' . $hinhanh->duong_dan;
                            error_log("GetHinhAnhById - Đường dẫn sau khi thêm tiền tố: " . $hinhanh->duong_dan);
                        }
                    }
                    error_log("GetHinhAnhById - Đường dẫn cuối cùng: " . $hinhanh->duong_dan);
                    return $hinhanh;
                }
            }
            error_log("GetHinhAnhById - Không tìm thấy hình ảnh với ID: " . $id);
            return null;
        } catch (PDOException $e) {
            error_log("Error in GetHinhAnhById: " . $e->getMessage());
            return null;
        }
    }

    // Phương thức tạo bảng hanghoa_hinhanh nếu chưa tồn tại
    public function CreateHanghoaHinhanhTable()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS hanghoa_hinhanh (
                id INT AUTO_INCREMENT PRIMARY KEY,
                idhanghoa INT NOT NULL,
                idhinhanh INT NOT NULL,
                UNIQUE KEY (idhanghoa, idhinhanh)
            )";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in CreateHanghoaHinhanhTable: " . $e->getMessage());
            return false;
        }
    }

    public function ThemHinhAnh($ten_file, $loai_file, $duong_dan, $file_hash = null)
    {
        try {
            // Kiểm tra xem đã có cột file_hash trong bảng hinhanh chưa
            $checkColumnSql = "SHOW COLUMNS FROM hinhanh LIKE 'file_hash'";
            $checkColumnStmt = $this->db->prepare($checkColumnSql);
            $checkColumnStmt->execute();

            // Nếu chưa có cột file_hash, thêm cột này vào bảng
            if ($checkColumnStmt->rowCount() == 0) {
                $addColumnSql = "ALTER TABLE hinhanh ADD COLUMN file_hash VARCHAR(32) NULL";
                $this->db->exec($addColumnSql);
            }

            if ($file_hash) {
                $sql = "INSERT INTO hinhanh (ten_file, loai_file, duong_dan, trang_thai, ngay_tao, file_hash) 
                        VALUES (?, ?, ?, 0, CURRENT_TIMESTAMP, ?)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$ten_file, $loai_file, $duong_dan, $file_hash]);
            } else {
                $sql = "INSERT INTO hinhanh (ten_file, loai_file, duong_dan, trang_thai, ngay_tao) 
                        VALUES (?, ?, ?, 0, CURRENT_TIMESTAMP)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$ten_file, $loai_file, $duong_dan]);
            }
        } catch (PDOException $e) {
            error_log("Error in ThemHinhAnh: " . $e->getMessage());
            return false;
        }
    }

    public function XoaHinhAnh($id)
    {
        try {
            // Kiểm tra xem hình ảnh có đang được sử dụng không
            $products = $this->GetProductsByImageId($id);
            if (!empty($products)) {
                return false;
            }

            // Xóa record trong database
            $sql = "DELETE FROM hinhanh WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error in XoaHinhAnh: " . $e->getMessage());
            return false;
        }
    }

    // Áp dụng hình ảnh cho sản phẩm
    public function ApplyImageToProduct($idhanghoa, $id_hinhanh)
    {
        try {
            // Đảm bảo kết nối đang hoạt động
            if (!$this->db || !($this->db instanceof PDO)) {
                error_log("Không có kết nối database hợp lệ");
                return false;
            }

            // Kiểm tra trạng thái transaction hiện tại
            try {
                // Bắt đầu giao dịch mới
                $this->db->beginTransaction();
            } catch (PDOException $e) {
                // Nếu có lỗi "There is no active transaction", thử commit trước khi bắt đầu mới
                if (strpos($e->getMessage(), 'There is no active transaction') !== false) {
                    error_log("Đang thử phục hồi transaction: " . $e->getMessage());
                    try {
                        // Thử commit transaction hiện tại nếu có
                        $this->db->commit();
                    } catch (Exception $ex) {
                        // Bỏ qua lỗi nếu không có transaction để commit
                    }
                    // Bắt đầu transaction mới
                    $this->db->beginTransaction();
                } else {
                    // Lỗi khác, ghi log và trả về false
                    error_log("Lỗi transaction: " . $e->getMessage());
                    return false;
                }
            }

            // Đảm bảo bảng hanghoa_hinhanh đã được tạo
            $this->CreateHanghoaHinhanhTable();

            // Cập nhật hình ảnh chính cho sản phẩm
            $sql = 'UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id_hinhanh, $idhanghoa]);

            if (!$result) {
                throw new Exception("Không thể cập nhật hình ảnh chính");
            }

            // Thêm quan hệ vào bảng hanghoa_hinhanh nếu chưa tồn tại
            $checkSql = 'SELECT COUNT(*) FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh = ?';
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$idhanghoa, $id_hinhanh]);
            $exists = $checkStmt->fetchColumn() > 0;

            if (!$exists) {
                $insertSql = 'INSERT INTO hanghoa_hinhanh (idhanghoa, idhinhanh) VALUES (?, ?)';
                $insertStmt = $this->db->prepare($insertSql);
                $insertResult = $insertStmt->execute([$idhanghoa, $id_hinhanh]);

                if (!$insertResult) {
                    throw new Exception("Không thể thêm quan hệ hình ảnh");
                }
            }

            // Cập nhật trạng thái hình ảnh thành đang sử dụng
            $this->UpdateImageStatus($id_hinhanh);

            // Hoàn tất giao dịch
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            try {
                $this->db->rollBack();
            } catch (PDOException $rollbackException) {
                error_log("Lỗi khi rollback: " . $rollbackException->getMessage());
            }
            error_log("Error in ApplyImageToProduct: " . $e->getMessage());
            return false;
        }
    }

    public function GetProductsByImageId($imageId)
    {
        $sql = "SELECT idhanghoa, tenhanghoa FROM hanghoa WHERE hinhanh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function UpdateProductImages($oldImageId, $newImageId)
    {
        try {
            $sql = "UPDATE hanghoa SET hinhanh = ? WHERE hinhanh = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$newImageId, $oldImageId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function GetImagePath($id)
    {
        $sql = 'SELECT duong_dan FROM hinhanh WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->duong_dan : null;
    }

    public function UpdateImageStatus($id)
    {
        $sql = 'UPDATE hinhanh SET trang_thai = 1 WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Tìm sản phẩm theo tên
    public function FindProductsByName($name)
    {
        $sql = 'SELECT * FROM hanghoa WHERE tenhanghoa LIKE ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%" . $name . "%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy ID được insert cuối cùng
    public function GetLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function FindProductsByExactName($productName)
    {
        try {
            $sql = "SELECT * FROM hanghoa WHERE tenhanghoa = :productName";
            $cmd = $this->db->prepare($sql);
            $cmd->bindValue(":productName", $productName);
            $cmd->execute();
            $result = $cmd->fetchAll(PDO::FETCH_OBJ);
            return $result;
        } catch (PDOException $e) {
            error_log("Error in FindProductsByExactName: " . $e->getMessage());
            return array();
        }
    }

    // Kiểm tra xem hình ảnh đã tồn tại chưa
    public function CheckImageExists($fileName)
    {
        try {
            $sql = "SELECT COUNT(*) FROM hinhanh WHERE ten_file = :fileName";
            $cmd = $this->db->prepare($sql);
            $cmd->bindValue(":fileName", $fileName);
            $cmd->execute();
            return $cmd->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error in CheckImageExists: " . $e->getMessage());
            return false;
        }
    }

    // Thêm phương thức kiểm tra trùng lặp ảnh bằng hash MD5
    public function CheckImageExistsByHash($fileHash)
    {
        try {
            // Kiểm tra xem đã có cột file_hash trong bảng hinhanh chưa
            $checkColumnSql = "SHOW COLUMNS FROM hinhanh LIKE 'file_hash'";
            $checkColumnStmt = $this->db->prepare($checkColumnSql);
            $checkColumnStmt->execute();

            // Nếu chưa có cột file_hash, thêm cột này vào bảng
            if ($checkColumnStmt->rowCount() == 0) {
                $addColumnSql = "ALTER TABLE hinhanh ADD COLUMN file_hash VARCHAR(32) NULL";
                $this->db->exec($addColumnSql);
                return false; // Vì vừa thêm cột, chắc chắn chưa có dữ liệu
            }

            $sql = "SELECT id FROM hinhanh WHERE file_hash = :fileHash";
            $cmd = $this->db->prepare($sql);
            $cmd->bindValue(":fileHash", $fileHash);
            $cmd->execute();
            $result = $cmd->fetch(PDO::FETCH_OBJ);

            return $result ? $result->id : false;
        } catch (PDOException $e) {
            error_log("Error in CheckImageExistsByHash: " . $e->getMessage());
            return false;
        }
    }

    // Đếm số lượng hình ảnh đã áp dụng cho từng sản phẩm
    public function CountImagesForProduct($idhanghoa)
    {
        try {
            $sql = "SELECT COUNT(*) FROM hanghoa_hinhanh WHERE idhanghoa = :idhanghoa";
            $cmd = $this->db->prepare($sql);
            $cmd->bindValue(":idhanghoa", $idhanghoa);
            $cmd->execute();
            return $cmd->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in CountImagesForProduct: " . $e->getMessage());
            return 0;
        }
    }

    // Lấy tất cả thông tin về hình ảnh đã áp dụng cho một sản phẩm
    public function GetAllImagesForProduct($idhanghoa)
    {
        try {
            $sql = "SELECT h.* FROM hinhanh h 
                    INNER JOIN hanghoa_hinhanh hh ON h.id = hh.idhinhanh 
                    WHERE hh.idhanghoa = :idhanghoa";
            $cmd = $this->db->prepare($sql);
            $cmd->bindValue(":idhanghoa", $idhanghoa);
            $cmd->execute();
            return $cmd->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in GetAllImagesForProduct: " . $e->getMessage());
            return [];
        }
    }

    // Thêm phương thức để cập nhật hình ảnh của sản phẩm
    public function CapNhatHinhAnhSanPham($idhanghoa, $id_hinhanh_moi)
    {
        try {
            $sql = "UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id_hinhanh_moi, $idhanghoa]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Kiểm tra hình ảnh không khớp tên với sản phẩm
    public function GetMismatchedProductImages()
    {
        try {
            $sql = "SELECT h.idhanghoa, h.tenhanghoa, ha.id, ha.ten_file 
                   FROM hanghoa h 
                   JOIN hinhanh ha ON h.hinhanh = ha.id 
                   WHERE ha.ten_file NOT LIKE CONCAT('%', h.tenhanghoa, '%') 
                   AND ha.ten_file NOT LIKE CONCAT('%', REPLACE(h.tenhanghoa, ' ', ''), '%')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in GetMismatchedProductImages: " . $e->getMessage());
            return [];
        }
    }

    // Kiểm tra và tìm hình ảnh bị thiếu
    public function FindMissingImages()
    {
        try {
            // Tìm các hình ảnh được tham chiếu trong bảng hanghoa nhưng không tồn tại trong bảng hinhanh
            $sql = "SELECT h.idhanghoa, h.tenhanghoa, h.hinhanh 
                   FROM hanghoa h 
                   LEFT JOIN hinhanh ha ON h.hinhanh = ha.id 
                   WHERE h.hinhanh > 0 AND ha.id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in FindMissingImages: " . $e->getMessage());
            return [];
        }
    }

    // Tìm hình ảnh có tên khớp chính xác với tên sản phẩm
    public function FindExactMatchImage($idhanghoa)
    {
        try {
            // Lấy thông tin sản phẩm
            $sql = "SELECT tenhanghoa FROM hanghoa WHERE idhanghoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idhanghoa]);
            $product = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$product) {
                return null;
            }

            // Lấy tất cả hình ảnh
            $sql = "SELECT * FROM hinhanh";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $images = $stmt->fetchAll(PDO::FETCH_OBJ);

            // Tìm hình ảnh khớp chính xác
            foreach ($images as $image) {
                if ($this->IsExactImageNameMatch($product->tenhanghoa, $image->ten_file)) {
                    return $image;
                }
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error in FindExactMatchImage: " . $e->getMessage());
            return null;
        }
    }

    // Áp dụng tất cả hình ảnh khớp chính xác cho sản phẩm
    public function ApplyAllExactMatchImages()
    {
        try {
            $this->db->beginTransaction();

            // Lấy tất cả sản phẩm
            $sqlProducts = "SELECT idhanghoa, tenhanghoa FROM hanghoa";
            $stmtProducts = $this->db->prepare($sqlProducts);
            $stmtProducts->execute();
            $products = $stmtProducts->fetchAll(PDO::FETCH_OBJ);

            // Lấy tất cả hình ảnh
            $sqlImages = "SELECT id, ten_file FROM hinhanh";
            $stmtImages = $this->db->prepare($sqlImages);
            $stmtImages->execute();
            $images = $stmtImages->fetchAll(PDO::FETCH_OBJ);

            $matchesCount = 0;

            foreach ($products as $product) {
                foreach ($images as $image) {
                    if ($this->IsExactImageNameMatch($product->tenhanghoa, $image->ten_file)) {
                        // Cập nhật hình ảnh chính cho sản phẩm
                        $sqlUpdate = "UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?";
                        $stmtUpdate = $this->db->prepare($sqlUpdate);
                        $stmtUpdate->execute([$image->id, $product->idhanghoa]);

                        // Thêm liên kết vào bảng hanghoa_hinhanh
                        $checkSql = "SELECT COUNT(*) FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh =?";
                        $checkStmt = $this->db->prepare($checkSql);
                        $checkStmt->execute([$product->idhanghoa, $image->id]);
                        $exists = $checkStmt->fetchColumn() > 0;

                        if (!$exists) {
                            $insertSql = "INSERT INTO hanghoa_hinhanh (idhanghoa, idhinhanh) VALUES (?, ?)";
                            $insertStmt = $this->db->prepare($insertSql);
                            $insertStmt->execute([$product->idhanghoa, $image->id]);
                        }

                        $matchesCount++;
                        break; // Chỉ áp dụng hình ảnh đầu tiên khớp
                    }
                }
            }

            $this->db->commit();
            return $matchesCount;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in ApplyAllExactMatchImages: " . $e->getMessage());
            return 0;
        }
    }

    // Gỡ bỏ hình ảnh khỏi sản phẩm
    public function RemoveImageFromProduct($idhanghoa)
    {
        try {
            // Log cho việc debug
            error_log("RemoveImageFromProduct - Bắt đầu gỡ bỏ hình ảnh cho sản phẩm ID: " . $idhanghoa);

            // Kiểm tra xem sản phẩm có tồn tại không
            $checkProduct = "SELECT hinhanh FROM hanghoa WHERE idhanghoa = ?";
            $stmtCheckProduct = $this->db->prepare($checkProduct);
            $stmtCheckProduct->execute([$idhanghoa]);
            $currentImageId = $stmtCheckProduct->fetchColumn();

            if ($currentImageId === false) {
                error_log("RemoveImageFromProduct - Sản phẩm không tồn tại: " . $idhanghoa);
                return false;
            }

            error_log("RemoveImageFromProduct - Hình ảnh hiện tại của sản phẩm: " . ($currentImageId ?: 'NULL'));

            // Bắt đầu giao dịch
            $this->db->beginTransaction();

            // Đặt hình ảnh về NULL cho sản phẩm
            $sqlUpdate = "UPDATE hanghoa SET hinhanh = NULL WHERE idhanghoa = ?";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $result = $stmtUpdate->execute([$idhanghoa]);

            if (!$result) {
                error_log("RemoveImageFromProduct - Lỗi khi cập nhật sản phẩm: " . implode(", ", $stmtUpdate->errorInfo()));
                $this->db->rollBack();
                return false;
            }

            error_log("RemoveImageFromProduct - Đã cập nhật sản phẩm thành NULL");

            // Xóa quan hệ trong bảng hanghoa_hinhanh nếu có hình ảnh cũ
            if ($currentImageId) {
                try {
                    // Kiểm tra xem bảng hanghoa_hinhanh có tồn tại không
                    $checkTableSql = "SHOW TABLES LIKE 'hanghoa_hinhanh'";
                    $checkTableStmt = $this->db->prepare($checkTableSql);
                    $checkTableStmt->execute();
                    $tableExists = $checkTableStmt->rowCount() > 0;

                    if (!$tableExists) {
                        error_log("RemoveImageFromProduct - Bảng hanghoa_hinhanh chưa tồn tại, đang tạo mới");
                        $this->CreateHanghoaHinhanhTable();
                    }

                    $sqlDeleteRelation = "DELETE FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh = ?";
                    $stmtDeleteRelation = $this->db->prepare($sqlDeleteRelation);
                    $resultDelete = $stmtDeleteRelation->execute([$idhanghoa, $currentImageId]);

                    if (!$resultDelete) {
                        error_log("RemoveImageFromProduct - Lỗi khi xóa quan hệ: " . implode(", ", $stmtDeleteRelation->errorInfo()));
                    } else {
                        error_log("RemoveImageFromProduct - Đã xóa quan hệ thành công");
                    }
                } catch (Exception $e) {
                    error_log("RemoveImageFromProduct - Lỗi khi xử lý bảng hanghoa_hinhanh: " . $e->getMessage());
                    // Không rollback ở đây vì việc xóa quan hệ không quan trọng bằng việc cập nhật hình ảnh chính
                }
            }

            // Hoàn tất giao dịch
            $this->db->commit();
            error_log("RemoveImageFromProduct - Gỡ bỏ hình ảnh hoàn tất thành công");

            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in RemoveImageFromProduct: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Unexpected error in RemoveImageFromProduct: " . $e->getMessage());
            return false;
        }
    }

    // Gỡ bỏ tất cả hình ảnh không khớp tên với sản phẩm
    public function RemoveAllMismatchedImages()
    {
        try {
            error_log("RemoveAllMismatchedImages - Bắt đầu gỡ bỏ tất cả hình ảnh không khớp");

            // Bắt đầu giao dịch
            $this->db->beginTransaction();

            // Lấy danh sách sản phẩm có hình ảnh không khớp
            $mismatched = $this->GetMismatchedProductImages();
            $count = 0;

            if (empty($mismatched)) {
                error_log("RemoveAllMismatchedImages - Không tìm thấy hình ảnh không khớp nào");
                $this->db->commit();
                return 0;
            }

            error_log("RemoveAllMismatchedImages - Tìm thấy " . count($mismatched) . " hình ảnh không khớp");

            foreach ($mismatched as $item) {
                error_log("RemoveAllMismatchedImages - Đang xử lý sản phẩm ID: " . $item->idhanghoa . ", Tên: " . $item->tenhanghoa . ", Hình ảnh ID: " . $item->id);

                // Đặt hình ảnh về NULL cho sản phẩm
                $sqlUpdate = "UPDATE hanghoa SET hinhanh = NULL WHERE idhanghoa = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $resultUpdate = $stmtUpdate->execute([$item->idhanghoa]);

                if (!$resultUpdate) {
                    error_log("RemoveAllMismatchedImages - Lỗi khi cập nhật sản phẩm ID " . $item->idhanghoa . ": " . implode(", ", $stmtUpdate->errorInfo()));
                    continue;
                }

                // Xóa quan hệ trong bảng hanghoa_hinhanh
                $sqlDeleteRelation = "DELETE FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh = ?";
                $stmtDeleteRelation = $this->db->prepare($sqlDeleteRelation);
                $resultDelete = $stmtDeleteRelation->execute([$item->idhanghoa, $item->id]);

                if (!$resultDelete) {
                    error_log("RemoveAllMismatchedImages - Lỗi khi xóa quan hệ cho sản phẩm ID " . $item->idhanghoa . ": " . implode(", ", $stmtDeleteRelation->errorInfo()));
                }

                $count++;
                error_log("RemoveAllMismatchedImages - Đã gỡ bỏ hình ảnh cho sản phẩm ID: " . $item->idhanghoa);
            }

            // Hoàn tất giao dịch
            $this->db->commit();

            error_log("RemoveAllMismatchedImages - Hoàn tất gỡ bỏ " . $count . " hình ảnh");
            return $count;
        } catch (PDOException $e) {
            // Rollback nếu có lỗi
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in RemoveAllMismatchedImages: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Bắt các exception khác
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Unexpected error in RemoveAllMismatchedImages: " . $e->getMessage());
            return false;
        }
    }

    // Kiểm tra xem tên file hình ảnh có khớp chính xác với tên sản phẩm không (phân biệt hoa thường)
    public function IsExactImageNameMatch($tenhanghoa, $ten_file)
    {
        // Tách tên file không có phần mở rộng
        $imageNameWithoutExt = pathinfo($ten_file, PATHINFO_FILENAME);

        // So sánh chính xác giữa tên sản phẩm và tên file, chỉ loại bỏ khoảng trắng đầu/cuối
        // Giữ nguyên phân biệt chữ hoa/thường để so khớp tuyệt đối
        if (trim($tenhanghoa) === trim($imageNameWithoutExt)) {
            return true;
        }

        return false;
    }
}

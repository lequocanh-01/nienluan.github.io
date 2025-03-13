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

class hanghoa extends Database
{
    public function HanghoaGetAll()
    {
        $sql = 'SELECT h.*, 
                i.duong_dan as hinhanh, 
                i.ten_file as ten_file, 
                i.id as hinhanh_id,
                th.tenTH,
                dvt.tenDonViTinh,
                nv.tenNV
                FROM hanghoa h
                LEFT JOIN hinhanh i ON h.hinhanh = i.id
                LEFT JOIN thuonghieu th ON h.idThuongHieu = th.idThuongHieu
                LEFT JOIN donvitinh dvt ON h.idDonViTinh = dvt.idDonViTinh
                LEFT JOIN nhanvien nv ON h.idNhanVien = nv.idNhanVien';

        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();

        $results = $getAll->fetchAll();

        // Process image paths
        foreach ($results as $result) {
            if ($result->hinhanh) {
                if (strpos($result->hinhanh, 'data:image') === 0) {
                    continue; // Keep base64 images as is
                }

                // Convert absolute path to relative path
                $result->hinhanh = 'uploads/' . basename($result->hinhanh);
            }
        }

        return $results;
    }
    public function HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien)
    {
        $sql = "INSERT INTO hanghoa (tenhanghoa, mota, giathamkhao, hinhanh, idloaihang, idThuongHieu, idDonViTinh, idNhanVien) VALUES (?,?,?,?,?,?,?,?)";
        $data = array($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien);
        $add = $this->connect->prepare($sql);
        $add->execute($data);
        return $add->rowCount();
    }
    public function HanghoaDelete($idhanghoa)
    {
        $sql = "DELETE from hanghoa where idhanghoa = ?";
        $data = array($idhanghoa);

        $del = $this->connect->prepare($sql);
        $del->execute($data);
        return $del->rowCount();
    }
    public function HanghoaUpdate($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa)
    {
        try {
            // Debug information
            error_log("HanghoaUpdate - ID hàng hóa: $idhanghoa, ID hình ảnh: " . ($id_hinhanh ? $id_hinhanh : "null"));

            // Validate image ID if provided
            if ($id_hinhanh) {
                $image = $this->GetHinhAnhById($id_hinhanh);
                if (!$image) {
                    error_log("HanghoaUpdate - Không tìm thấy hình ảnh với ID: $id_hinhanh");
                    // Lấy ID hình ảnh hiện tại nếu ID mới không hợp lệ
                    $currentProduct = $this->HanghoaGetbyId($idhanghoa);
                    if ($currentProduct && $currentProduct->hinhanh) {
                        $id_hinhanh = $currentProduct->hinhanh;
                        error_log("HanghoaUpdate - Sử dụng ID hình ảnh hiện tại: $id_hinhanh");
                    }
                }
            }

            // Thực hiện cập nhật
            $sql = "UPDATE hanghoa SET tenhanghoa=?, hinhanh=?, mota=?, giathamkhao=?, idloaihang=?, idThuongHieu=?, idDonViTinh=?, idNhanVien=? WHERE idhanghoa =?";
            $data = array($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa);

            error_log("HanghoaUpdate - Executing SQL update with params: " . print_r($data, true));
            $update = $this->connect->prepare($sql);
            $update->execute($data);

            if ($update->rowCount() > 0) {
                error_log("HanghoaUpdate - Cập nhật thành công, số dòng ảnh hưởng: " . $update->rowCount());
                // Update image status
                if ($id_hinhanh) {
                    $this->UpdateImageStatus($id_hinhanh);
                    error_log("HanghoaUpdate - Đã cập nhật trạng thái hình ảnh ID: $id_hinhanh");
                }
                return $update->rowCount();
            } else {
                error_log("HanghoaUpdate - Không có dòng nào được cập nhật");
                // Kiểm tra xem hàng hóa có tồn tại không
                $check = $this->HanghoaGetbyId($idhanghoa);
                if ($check) {
                    error_log("HanghoaUpdate - Hàng hóa ID:$idhanghoa tồn tại nhưng không có thay đổi");
                    return 1; // Trả về 1 nếu không có thay đổi nhưng hàng hóa tồn tại
                }
                return 0;
            }
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            return 0;
        }
    }
    public function HanghoaGetbyId($idhanghoa)
    {
        $sql = 'SELECT h.*, 
                i.duong_dan as hinhanh, 
                i.ten_file as ten_file, 
                i.id as hinhanh_id,
                th.tenTH,
                dvt.tenDonViTinh,
                nv.tenNV
                FROM hanghoa h
                LEFT JOIN hinhanh i ON h.hinhanh = i.id
                LEFT JOIN thuonghieu th ON h.idThuongHieu = th.idThuongHieu
                LEFT JOIN donvitinh dvt ON h.idDonViTinh = dvt.idDonViTinh
                LEFT JOIN nhanvien nv ON h.idNhanVien = nv.idNhanVien
                WHERE h.idhanghoa = ?';
        $data = array($idhanghoa);

        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        $result = $getOne->fetch();

        // Process image path
        if ($result && $result->hinhanh) {
            if (strpos($result->hinhanh, 'data:image') === 0) {
                return $result; // Keep base64 images as is
            }

            // Convert absolute path to relative path
            $result->hinhanh = 'uploads/' . basename($result->hinhanh);
        }

        return $result;
    }

    public function HanghoaGetbyIdloaihang($idloaihang)
    {
        $sql = 'SELECT h.*, i.duong_dan as hinhanh, i.ten_file as ten_file FROM hanghoa h
                LEFT JOIN hinhanh i ON h.hinhanh = i.id
                WHERE h.idloaihang = ?';
        $data = array($idloaihang);

        $getOne = $this->connect->prepare($sql);
        $getOne->setFetchMode(PDO::FETCH_OBJ);
        $getOne->execute($data);

        return $getOne->fetchAll();
    }
    public function HanghoaUpdatePrice($idhanghoa, $giaban)
    {
        $sql = "UPDATE hanghoa SET giathamkhao = ? WHERE idhanghoa = ?";
        $data = array($giaban, $idhanghoa);

        $update = $this->connect->prepare($sql);
        $update->execute($data);
        return $update->rowCount();
    }
    public function searchHanghoa($keyword)
    {
        try {
            $select = "SELECT h.*, i.duong_dan as hinhanh FROM hanghoa h
                       LEFT JOIN hinhanh i ON h.hinhanh = i.id
                       WHERE LOWER(h.tenhanghoa) LIKE LOWER(:keyword)
                       ORDER BY h.tenhanghoa ASC 
                       LIMIT 10";
            $stmt = $this->connect->prepare($select);
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
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([$idhanghoa]);
        if ($stmt->fetchColumn() > 0) {
            $tablesWithRelations[] = 'thuoctinhhh';
        }

        // Kiểm tra liên kết với bảng đơn giá (ví dụ)
        $sql = "SELECT COUNT(*) FROM dongia WHERE idhanghoa = ?";
        $stmt = $this->connect->prepare($sql);
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
        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function GetAllDonViTinh()
    {
        $sql = 'SELECT * FROM donvitinh';
        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    public function GetAllNhanVien()
    {
        $sql = 'SELECT * FROM nhanvien';
        $getAll = $this->connect->prepare($sql);
        $getAll->setFetchMode(PDO::FETCH_OBJ);
        $getAll->execute();
        return $getAll->fetchAll();
    }

    // Lấy thông tin thương hiệu theo ID
    public function GetThuongHieuById($idThuongHieu)
    {
        $sql = 'SELECT * FROM thuonghieu WHERE idThuongHieu = ?';
        $stmt = $this->connect->prepare($sql);
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
            $getAll = $this->connect->prepare($sql);
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
            $stmt = $this->connect->prepare($sql);
            $stmt->execute([$id]);
            $hinhanh = $stmt->fetch(PDO::FETCH_OBJ);

            if ($hinhanh) {
                // Xử lý đường dẫn hình ảnh
                if (strpos($hinhanh->duong_dan, 'data:image') === 0) {
                    // Nếu là base64, giữ nguyên
                    return $hinhanh;
                } else {
                    // Đảm bảo đường dẫn bắt đầu với 'uploads/'
                    if (!empty($hinhanh->duong_dan) && strpos($hinhanh->duong_dan, 'uploads/') === false) {
                        // Nếu đường dẫn không chứa 'uploads/', thêm tiền tố
                        if (basename($hinhanh->duong_dan) == $hinhanh->duong_dan) {
                            // Nếu đường dẫn chỉ là tên file, thêm tiền tố 'uploads/'
                            $hinhanh->duong_dan = 'uploads/' . $hinhanh->duong_dan;
                        }
                    }
                    // Thêm thông tin debug
                    error_log("Truy vấn hình ảnh ID: " . $id . ", Đường dẫn: " . $hinhanh->duong_dan);
                    return $hinhanh;
                }
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error in GetHinhAnhById: " . $e->getMessage());
            return null;
        }
    }

    public function ThemHinhAnh($ten_file, $loai_file, $duong_dan)
    {
        try {
            $sql = "INSERT INTO hinhanh (ten_file, loai_file, duong_dan, trang_thai, ngay_tao) 
                    VALUES (?, ?, ?, 0, CURRENT_TIMESTAMP)";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$ten_file, $loai_file, $duong_dan]);
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
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error in XoaHinhAnh: " . $e->getMessage());
            return false;
        }
    }

    // Thêm phương thức để cập nhật hình ảnh của sản phẩm
    public function CapNhatHinhAnhSanPham($idhanghoa, $id_hinhanh_moi)
    {
        try {
            $sql = "UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$id_hinhanh_moi, $idhanghoa]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function GetProductsByImageId($imageId)
    {
        $sql = "SELECT idhanghoa, tenhanghoa FROM hanghoa WHERE hinhanh = ?";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function UpdateProductImages($oldImageId, $newImageId)
    {
        try {
            $sql = "UPDATE hanghoa SET hinhanh = ? WHERE hinhanh = ?";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$newImageId, $oldImageId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function GetImagePath($id)
    {
        $sql = "SELECT duong_dan FROM hinhanh WHERE id = ?";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['duong_dan'] : null;
    }

    public function UpdateImageStatus($id)
    {
        try {
            if (!$id) {
                error_log("UpdateImageStatus - ID hình ảnh không hợp lệ");
                return false;
            }

            $products = $this->GetProductsByImageId($id);
            $status = !empty($products) ? 1 : 0;

            error_log("UpdateImageStatus - ID hình ảnh: $id, Số lượng sản phẩm sử dụng: " . count($products) . ", Cập nhật trạng thái: $status");

            $sql = "UPDATE hinhanh SET trang_thai = ? WHERE id = ?";
            $stmt = $this->connect->prepare($sql);
            $result = $stmt->execute([$status, $id]);

            if ($result) {
                error_log("UpdateImageStatus - Cập nhật trạng thái thành công cho hình ảnh ID: $id");
            } else {
                error_log("UpdateImageStatus - Cập nhật trạng thái thất bại cho hình ảnh ID: $id");
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error updating image status: " . $e->getMessage());
            return false;
        }
    }

    public function AddHinhAnh($fileName)
    {
        $sql = "INSERT INTO hinhanh (ten_file, duong_dan) VALUES (?, ?)";
        $data = array($fileName, './uploads/' . $fileName);
        $insert = $this->connect->prepare($sql);
        $insert->execute($data);
        return $this->connect->lastInsertId();
    }
}

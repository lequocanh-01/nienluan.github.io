<?php
require_once '../mod/hanghoaCls.php';
$hanghoa = new hanghoa();
if (isset($_REQUEST['reqact'])) {
    $requestAction = $_REQUEST['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $tenhanghoa = $_REQUEST['tenhanghoa'];
            $mota = $_REQUEST['mota'];
            $giathamkhao = $_REQUEST['giathamkhao'];
            $id_hinhanh = $_REQUEST['id_hinhanh'];
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = $_REQUEST['idThuongHieu'];
            $idDonViTinh = $_REQUEST['idDonViTinh'];
            $idNhanVien = $_REQUEST['idNhanVien'];

            $hanghoa->HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien);
            if ($hanghoa) {
                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                header('location: ../../index.php?req=hanghoaview&result=notok');
            }
            break;

        case 'deletehanghoa':
            $idhanghoa = $_REQUEST['idhanghoa'];
            $hanghoa->HanghoaDelete($idhanghoa);
            if ($hanghoa) {
                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                header('location: ../../index.php?req=hanghoaview&result=notok');
            }
            break;

        case 'updatehanghoa':
            $idhanghoa = $_REQUEST['idhanghoa'];
            $id_hinhanh = isset($_REQUEST['id_hinhanh']) ? $_REQUEST['id_hinhanh'] : null;
            $current_image_id = isset($_REQUEST['current_image_id']) ? $_REQUEST['current_image_id'] : null;
            $tenhanghoa = $_REQUEST['tenhanghoa'];
            $mota = $_REQUEST['mota'];
            $giathamkhao = $_REQUEST['giathamkhao'];
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = $_REQUEST['idThuongHieu'];
            $idDonViTinh = $_REQUEST['idDonViTinh'];
            $idNhanVien = isset($_REQUEST['idNhanVien']) && !empty($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;

            if (!$idNhanVien) {
                header('location: ../../index.php?req=hanghoaview&result=missing_employee');
                exit();
            }

            // Debug information
            error_log("Update Hanghoa - ID: $idhanghoa, New Image ID: " . ($id_hinhanh ? $id_hinhanh : "null") . ", Current Image ID: " . ($current_image_id ? $current_image_id : "null"));

            // Lấy thông tin sản phẩm hiện tại nếu không có ID hình ảnh mới
            if (empty($id_hinhanh) || $id_hinhanh === "") {
                // Nếu không chọn hình ảnh mới, giữ nguyên hình ảnh hiện tại
                $currentProduct = $hanghoa->HanghoaGetbyId($idhanghoa);
                $id_hinhanh = $currentProduct->hinhanh;
                error_log("Keeping current image ID: " . ($id_hinhanh ? $id_hinhanh : "null"));
            } else {
                // Kiểm tra xem ID hình ảnh mới có tồn tại trong CSDL không
                $image = $hanghoa->GetHinhAnhById($id_hinhanh);
                if (!$image) {
                    error_log("Selected image ID $id_hinhanh not found in database!");
                    $id_hinhanh = $current_image_id; // Sử dụng ID hình ảnh hiện tại nếu ID mới không hợp lệ
                }
            }

            $result = $hanghoa->HanghoaUpdate($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa);

            if ($result) {
                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                error_log("Failed to update product with ID: $idhanghoa, Image ID: $id_hinhanh");
                header('location: ../../index.php?req=hanghoaview&result=notok');
            }
            break;

        default:
            header('location:../../index.php?req=hanghoaview');
            break;
    }
}

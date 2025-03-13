<?php
session_start();
require '../../elements_LQA/mod/thuoctinhCls.php';

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    $lh = new ThuocTinh();
    
    switch ($requestAction) {
        case 'addnew': // Thêm mới
            $tenThuocTinh = isset($_POST['tenThuocTinh']) ? $_POST['tenThuocTinh'] : '';
            $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
            if (empty($_FILES['fileimage']['tmp_name'])) {
                echo "<script>alert('Vui lòng nhập ảnh trước khi thêm loại hàng.'); window.history.back();</script>";
                exit; 
            }
            $hinhanh_file = $_FILES['fileimage']['tmp_name'];
            $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));

            $kq = $lh->thuoctinhAdd($tenThuocTinh,  $ghiChu, $hinhanh);
            header('location: ../../index.php?req=thuoctinhview&result=' . ($kq ? 'ok' : 'notok'));
            break;

        case 'deletethuoctinh': // Xóa
            $idThuocTinh = isset($_GET['idThuocTinh']) ? $_GET['idThuocTinh'] : null;
            if ($idThuocTinh) {
                $kq = $lh->thuoctinhDelete($idThuocTinh);
                header('location: ../../index.php?req=thuoctinhview&result=' . ($kq ? 'ok' : 'notok'));
            } else {
                header('location: ../../index.php?req=thuoctinhview&result=error');
            }
            break;

        case 'updatethuoctinh': // Cập nhật
            $idThuocTinh = isset($_POST['idThuocTinh']) ? $_POST['idThuocTinh'] : null;
            $tenThuocTinh = isset($_POST['tenThuocTinh']) ? $_POST['tenThuocTinh'] : '';
            $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
            if (file_exists($_FILES['fileimage']['tmp_name'])) {
                $hinhanh_file = $_FILES['fileimage']['tmp_name'];
                $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));
            } else {
                $hinhanh = $_REQUEST['hinhanh'];
            }

            if ($idThuocTinh) {
                $kq = $lh->thuoctinhUpdate($tenThuocTinh,  $ghiChu, $hinhanh, $idThuocTinh);
                header('location: ../../index.php?req=thuoctinhview&result=' . ($kq ? 'ok' : 'notok'));
            } else {
                header('location: ../../index.php?req=thuoctinhview&result=error');
            }
            break;

        default:
            header('location: ../../index.php?req=thuoctinhview');
            break;
    }
} else {
    header('location: ../../index.php?req=thuoctinhview');
}

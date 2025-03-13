<?php
session_start();
require '../../elements_LQA/mod/thuonghieuCls.php';

// Kiểm tra biến yêu cầu, nếu không có thì đẩy về trang chủ
if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew': // Thêm mới
            // Nhập dữ liệu với kiểm tra giá trị
            $tenTH = isset($_REQUEST['tenTH']) ? $_REQUEST['tenTH'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $diaChi = isset($_REQUEST['diaChi']) ? $_REQUEST['diaChi'] : null;
            if (empty($_FILES['fileimage']['tmp_name'])) {
                // Nếu không có ảnh, hiển thị alert và quay lại trang thêm loại hàng
                echo "<script>alert('Vui lòng nhập ảnh trước khi thêm loại hàng.'); window.history.back();</script>";
                exit; // Dừng thực thi mã
            }

            $hinhanh_file = $_FILES['fileimage']['tmp_name'];
            $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));

            $lh = new ThuongHieu();
            $kq = $lh->thuonghieuAdd($tenTH, $SDT, $email, $diaChi, $hinhanh);
            if ($kq) {
                header('location: ../../index.php?req=thuonghieuview&result=ok');
                exit;
            } else {
                header('location: ../../index.php?req=thuonghieuview&result=notok');
                exit;
            }
            break;

        case 'deletethuonghieu':
            $idThuongHieu = isset($_REQUEST['idThuongHieu']) ? $_REQUEST['idThuongHieu'] : null;
            $lh = new ThuongHieu();
            $kq = $lh->thuonghieuDelete($idThuongHieu);
            if ($kq) {
                header('location: ../../index.php?req=thuonghieuview&result=ok');
                exit;
            } else {
                header('location: ../../index.php?req=thuonghieuview&result=notok');
                exit;
            }
            break;

        case 'updatethuonghieu':
            $tenTH = isset($_REQUEST['tenTH']) ? $_REQUEST['tenTH'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $diaChi = isset($_REQUEST['diaChi']) ? $_REQUEST['diaChi'] : null;
            $idThuongHieu = isset($_REQUEST['idThuongHieu']) ? $_REQUEST['idThuongHieu'] : null;

            // Check if a new image is uploaded
            if (file_exists($_FILES['fileimage']['tmp_name']) && !empty($_FILES['fileimage']['tmp_name'])) {
                $hinhanh_file = $_FILES['fileimage']['tmp_name'];
                $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));
            } else {
                // Use the old image if no new image is uploaded
                $hinhanh = $_REQUEST['hinhanh']; // Assuming hinhanh is passed in the request
            }

            $lh = new ThuongHieu();
            $kq = $lh->thuonghieuUpdate($tenTH, $SDT, $email, $diaChi, $hinhanh, $idThuongHieu);
            if ($kq) {
                header('location: ../../index.php?req=thuonghieuview&result=ok');
                exit;
            } else {
                header('location: ../../index.php?req=thuonghieuview&result=notok');
                exit;
            }
            break;

        default:
            // Nếu không có hành động hợp lệ, quay lại trang chính
            header('location: ../../index.php?req=thuonghieuview');
            exit;
    }
} else {
    // Nhảy lại địa chỉ index.php nếu không có yêu cầu cụ thể
    header('location: ../../index.php?req=thuonghieuview');
    exit;
}
<?php
session_start();
require '../../elements_LQA/mod/nhanvienCls.php';

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew': // Thêm mới
            // Nhập dữ liệu
            $tenNV = isset($_REQUEST['tenNV']) ? $_REQUEST['tenNV'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $luongCB = isset($_REQUEST['luongCB']) ? $_REQUEST['luongCB'] : null;
            $phuCap = isset($_REQUEST['phuCap']) ? $_REQUEST['phuCap'] : null;
            $chucVu = isset($_REQUEST['chucVu']) ? $_REQUEST['chucVu'] : null;

            $lh = new NhanVien();
            $kq = $lh->nhanvienAdd($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu);
            if ($kq) {
                header('location: ../../index.php?req=nhanvienview&result=ok');
            } else {
                header('location: ../../index.php?req=nhanvienview&result=notok');
            }
            break;

        case 'deletenhanvien':
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;
            if ($idNhanVien) {
                $lh = new NhanVien();
                $kq = $lh->nhanvienDelete($idNhanVien);
                if ($kq) {
                    header('location: ../../index.php?req=nhanvienview&result=ok');
                } else {
                    header('location: ../../index.php?req=nhanvienview&result=notok');
                }
            } else {
                header('location: ../../index.php?req=nhanvienview&result=error');
            }
            break;

        case 'updatenhanvien':
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;
            $tenNV = isset($_REQUEST['tenNV']) ? $_REQUEST['tenNV'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $luongCB = isset($_REQUEST['luongCB']) ? $_REQUEST['luongCB'] : null;
            $phuCap = isset($_REQUEST['phuCap']) ? $_REQUEST['phuCap'] : null;
            $chucVu = isset($_REQUEST['chucVu']) ? $_REQUEST['chucVu'] : null;

            if ($idNhanVien) {
                $lh = new NhanVien();
                $kq = $lh->nhanvienUpdate($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien);
                if ($kq) {
                    header('location: ../../index.php?req=nhanvienview&result=ok');
                } else {
                    header('location: ../../index.php?req=nhanvienview&result=notok');
                }
            } else {
                header('location: ../../index.php?req=nhanvienview&result=error');
            }
            break;

        default:
            header('location: ../../index.php?req=nhanvienview');
            break;
    }
} else {
    // Nhảy lại địa chỉ index.php
    header('location: ../../index.php?req=nhanvienview');
}

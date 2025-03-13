<?php
session_start();
require '../../elements_LQA/mod/thuoctinhhhCls.php';

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    $thuocTinhHHObj = new ThuocTinhHH();

    switch ($requestAction) {
        case 'addnew':
            // Lấy dữ liệu từ form
            $idhanghoa = $_POST['idhanghoa'] ?? null;
            $idThuocTinh = $_POST['idThuocTinh'] ?? null;
            $tenThuocTinhHH = $_POST['tenThuocTinhHH'] ?? null;
            $ghiChu = $_POST['ghiChu'] ?? null;

            // Kiểm tra dữ liệu đầu vào
            if ($idhanghoa && $idThuocTinh && $tenThuocTinhHH ) {
                $result = $thuocTinhHHObj->thuoctinhhhAdd($idhanghoa, $idThuocTinh, $tenThuocTinhHH,  $ghiChu);
                header("Location: ../../index.php?req=thuoctinhhhview&result=" . ($result ? 'ok' : 'notok'));
            } else {
                // Nếu thiếu dữ liệu
                header("Location: ../../index.php?req=thuoctinhhhview&result=notok&error=missing_data");
            }
            break;

        case 'deletethuoctinhhh':
            // Lấy ID thuộc tính hàng hóa cần xóa
            $idThuocTinhHH = $_GET['idThuocTinhHH'] ?? null;
            if ($idThuocTinhHH) {
                $result = $thuocTinhHHObj->thuoctinhhhDelete($idThuocTinhHH);
                header("Location: ../../index.php?req=thuoctinhhhview&result=" . ($result ? 'ok' : 'notok'));
            } else {
                // Nếu thiếu ID
                header("Location: ../../index.php?req=thuoctinhhhview&result=notok&error=missing_id");
            }
            break;

        case 'updatethuoctinhhh':
            // Lấy dữ liệu từ form
            $idThuocTinhHH = $_POST['idThuocTinhHH'] ?? null;
            $tenThuocTinhHH = $_POST['tenThuocTinhHH'] ?? null;
            $ghiChu = $_POST['ghiChu'] ?? null;

            // echo $hinhanh . '<br/>';
            $lh = new thuoctinhhh();
            $kq = $lh->thuoctinhhhUpdate($tenThuocTinhHH,  $ghiChu,  $idThuocTinhHH);
            if ($kq) {
                header('location: ../../index.php?req=thuoctinhhhview&result=ok');
            } else {
                header('location: ../../index.php?req=thuoctinhhhview&result=notok');
            }
            break;
        default:
            header('Location: ../../index.php?req=thuoctinhhhview');
            break;
    }
} else {
    header('Location: ../../index.php?req=thuoctinhhhview');
}

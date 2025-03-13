<?php
session_start();
require '../../elements_LQA/mod/dongiaCls.php';
require '../../elements_LQA/mod/hanghoaCls.php';

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $idHangHoa = $_REQUEST['idhanghoa'];
            $giaBan = $_REQUEST['giaban'];
            $ngayApDung = $_REQUEST['ngayapdung'];
            $ngayKetThuc = $_REQUEST['ngayketthuc'];
            $dieuKien = $_REQUEST['dieukien'];
            $ghiChu = $_REQUEST['ghichu'];
            $tenHangHoa  = $_REQUEST['tenHangHoa'];
            $dongiaObj = new Dongia();
            $kq = $dongiaObj->DongiaAdd($idHangHoa, $tenHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu, $apDung);
            if ($kq) {
                $hanghoaObj = new Hanghoa();
                $hanghoaObj->HanghoaUpdatePrice($idHangHoa, $giaBan);
                header('location: ../../index.php?req=dongiaview&result=ok');
            } else {
                header('location: ../../index.php?req=dongiaview&result=notok');
            }
            break;

        // case 'deletedongia':
        //     $idDonGia = $_REQUEST['idDonGia'];
        //     $dongiaObj = new Dongia();
        //     $kq = $dongiaObj->DongiaDelete($idDonGia);
        //     header('location: ../../index.php?req=dongiaview&result=' . ($kq ? 'ok' : 'notok'));
        //     break;

        case 'updateStatus':
            $idDonGia = $_REQUEST['idDonGia'];
            $apDung = ($_REQUEST['apDung'] === 'true');

            $dongiaObj = new Dongia();
            $dongia = $dongiaObj->DongiaGetbyId($idDonGia);

            $kq = $dongiaObj->DongiaUpdateStatus($idDonGia, $apDung);

            if ($kq) {
                if ($apDung) {
                    $dongiaObj->HanghoaUpdatePrice($dongia->idHangHoa, $dongia->giaBan);
                }
                header('location: ../../index.php?req=dongiaView&result=ok');
            } else {
                header('location: ../../index.php?req=dongiaView&result=notok');
            }
            break;

        // case 'updatedongia':
        //     $idDonGia = $_REQUEST['idDonGia'];
        //     $idHangHoa = $_REQUEST['idHangHoa'];
        //     $tenHangHoa = $_REQUEST['tenHangHoa'];
        //     $giaBan = $_REQUEST['giaBan'];
        //     $ngayApDung = $_REQUEST['ngayApDung'];
        //     $ngayKetThuc = $_REQUEST['ngayKetThuc'];
        //     $dieuKien = $_REQUEST['dieuKien'];
        //     $ghiChu = $_REQUEST['ghiChu'];

        //     $dongiaObj = new Dongia();
        //     $kq = $dongiaObj->DongiaUpdate($idDonGia, $idHangHoa, $tenHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu, $apDung);

        //     if ($kq) {
        //         $hanghoaObj = new Hanghoa();
        //         $hanghoaObj->HanghoaUpdatePrice($idHangHoa, $giaBan);
        //     }

        //     header('location: ../../index.php?req=dongiaview&result=' . ($kq ? 'ok' : 'notok'));
        //     break;

        default:
            header('location: ../../index.php?req=dongiaView');
            break;
    }
} else {
    header('location: ../../index.php?req=dongiaView');
}

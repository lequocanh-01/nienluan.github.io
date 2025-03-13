<?php
session_start();
require_once __DIR__ . '/../mod/dongiaCls.php';

if (isset($_REQUEST['idDonGia'])) {
    $idDonGia = $_REQUEST['idDonGia'];
    $apDung = ($_REQUEST['apDung'] === 'true');
    
    $dongiaObj = new Dongia();
    $dongia = $dongiaObj->DongiaGetbyId($idDonGia);
    
    if ($apDung) {
        $dongiaObj->DongiaSetAllToFalse($dongia->idHangHoa);
    }
    
    $kq = $dongiaObj->DongiaUpdateStatus($idDonGia, $apDung);
    
    if ($kq) {
        if ($apDung) {
            $dongiaObj->HanghoaUpdatePrice($dongia->idHangHoa, $dongia->giaBan);
        }
        header('location: ../../index.php?req=dongiaview&result=ok');
    } else {
        header('location: ../../index.php?req=dongiaView&result=notok');
    }
} else {
    header('location: ../../index.php?req=dongiaView');
}
?> 
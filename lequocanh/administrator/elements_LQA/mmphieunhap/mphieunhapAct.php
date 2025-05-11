<?php
session_start();
require_once '../mod/mphieunhapCls.php';
require_once '../mod/mchitietphieunhapCls.php';

$phieunhap = new MPhieuNhap();
$chitietphieunhap = new MChiTietPhieuNhap();

if (isset($_GET['reqact'])) {
    $reqact = $_GET['reqact'];
    
    switch ($reqact) {
        case 'addnew':
            // Thêm mới phiếu nhập
            if (isset($_POST['maPhieuNhap']) && isset($_POST['idNCC']) && isset($_POST['idNhanVien'])) {
                $maPhieuNhap = $_POST['maPhieuNhap'];
                $idNCC = $_POST['idNCC'];
                $idNhanVien = $_POST['idNhanVien'];
                $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
                
                $result = $phieunhap->addPhieuNhap($maPhieuNhap, $idNhanVien, $idNCC, $ghiChu);
                
                if ($result) {
                    // Chuyển đến trang chi tiết phiếu nhập để thêm sản phẩm
                    header("Location: ../../index.php?req=mchitietphieunhap&idpn=" . $result);
                } else {
                    header("Location: ../../index.php?req=mphieunhap&result=fail");
                }
            } else {
                header("Location: ../../index.php?req=mphieunhap&result=fail");
            }
            break;
            
        case 'update':
            // Cập nhật phiếu nhập
            if (isset($_POST['idPhieuNhap']) && isset($_POST['maPhieuNhap']) && isset($_POST['idNCC']) && isset($_POST['idNhanVien'])) {
                $idPhieuNhap = $_POST['idPhieuNhap'];
                $maPhieuNhap = $_POST['maPhieuNhap'];
                $idNCC = $_POST['idNCC'];
                $idNhanVien = $_POST['idNhanVien'];
                $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
                
                $result = $phieunhap->updatePhieuNhap($idPhieuNhap, $maPhieuNhap, $idNhanVien, $idNCC, $ghiChu);
                
                if ($result) {
                    header("Location: ../../index.php?req=mphieunhap&result=success");
                } else {
                    header("Location: ../../index.php?req=mphieunhap&result=fail");
                }
            } else {
                header("Location: ../../index.php?req=mphieunhap&result=fail");
            }
            break;
            
        case 'delete':
            // Xóa phiếu nhập
            if (isset($_GET['idpn'])) {
                $idPhieuNhap = $_GET['idpn'];
                $result = $phieunhap->deletePhieuNhap($idPhieuNhap);
                
                if ($result) {
                    header("Location: ../../index.php?req=mphieunhap&result=success");
                } else {
                    header("Location: ../../index.php?req=mphieunhap&result=fail");
                }
            } else {
                header("Location: ../../index.php?req=mphieunhap&result=fail");
            }
            break;
            
        case 'approve':
            // Duyệt phiếu nhập
            if (isset($_GET['idpn'])) {
                $idPhieuNhap = $_GET['idpn'];
                $result = $phieunhap->approvePhieuNhap($idPhieuNhap);
                
                if ($result) {
                    header("Location: ../../index.php?req=mphieunhap&result=success");
                } else {
                    header("Location: ../../index.php?req=mphieunhap&result=fail");
                }
            } else {
                header("Location: ../../index.php?req=mphieunhap&result=fail");
            }
            break;
            
        case 'cancel':
            // Hủy phiếu nhập
            if (isset($_GET['idpn'])) {
                $idPhieuNhap = $_GET['idpn'];
                $result = $phieunhap->cancelPhieuNhap($idPhieuNhap);
                
                if ($result) {
                    header("Location: ../../index.php?req=mphieunhap&result=success");
                } else {
                    header("Location: ../../index.php?req=mphieunhap&result=fail");
                }
            } else {
                header("Location: ../../index.php?req=mphieunhap&result=fail");
            }
            break;
            
        default:
            header("Location: ../../index.php?req=mphieunhap");
            break;
    }
} else {
    header("Location: ../../index.php?req=mphieunhap");
}
?>

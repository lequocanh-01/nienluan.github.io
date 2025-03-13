<?php
session_start();
require '../../elements_LQA/mod/donvitinhCls.php';

// Nếu có biến yêu cầu đúng tên biến thì vào, nếu không đẩy về index.php ngăn truy cập mục đích không rõ ràng
if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew': // Thêm mới
            // Nhập dữ liệu
            $tenDonViTinh = isset($_REQUEST['tenDonViTinh']) ? $_REQUEST['tenDonViTinh'] : null;
            $moTa = isset($_REQUEST['moTa']) ? $_REQUEST['moTa'] : null;
            $ghiChu = isset($_REQUEST['ghiChu']) ? $_REQUEST['ghiChu'] : null;
   
            // echo $tenDonViTinh . '<br/>';
            // echo $moTa . '<br/>';
            // echo $ghiChu . '<br/>';
            

                $lh = new donvitinh();
                $kq = $lh->donvitinhAdd($tenDonViTinh, $moTa, $ghiChu); // Cập nhật tham số cho phù hợp
                if ($kq) { 
                    header('location: ../../index.php?req=donvitinhview&result=ok');
                } else {
                    header('location: ../../index.php?req=donvitinhView&result=notok');
                }
                break;
            
           
        

        case 'deletedonvitinh':
            $iddonvitinh = $_REQUEST['iddonvitinh'];
            $lh = new donvitinh();
            $kq = $lh->donvitinhDelete($iddonvitinh);
            if ($kq) {
                header('location: ../../index.php?req=donvitinhview&result=ok');
            } else {
                header('location: ../../index.php?req=donvitinhView&result=notok');
            }
            break;
        

        case 'updatedonvitinh':
            $idDonViTinh = isset($_REQUEST['idDonViTinh']) ? $_REQUEST['idDonViTinh'] : null;
            $tenDonViTinh = isset($_REQUEST['tenDonViTinh']) ? $_REQUEST['tenDonViTinh'] : null;
            $moTa = isset($_REQUEST['moTa']) ? $_REQUEST['moTa'] : null;
            $ghiChu = isset($_REQUEST['ghiChu']) ? $_REQUEST['ghiChu'] : null;
           



            $lh = new donvitinh();
            $kq = $lh->donvitinhUpdate($tenDonViTinh, $moTa, $ghiChu,$idDonViTinh); // Cập nhật tham số cho phù hợp
            if ($kq) {
                header('location: ../../index.php?req=donvitinhview&result=ok');
            } else {
                header('location: ../../index.php?req=donvitinhView&result=notok');
            }
            break;

        default:
            header('location: ../../index.php?req=donvitinhview');
            break;
        }
    
} else { // Correctly placed else statement
    // Nhảy lại địa chỉ index.php
    header('location: ../../index.php?req=donvitinhview');
}
<?php
session_start();
require '../../elements_LQA/mod/loaihangCls.php';
//nếu có biến yêu cầu đlúng tên biến thì cbo vô nếu không đẩy về index.php ngăn truy cập mục đichs không rõ ràng
if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew': //them moi
            //xu lys them moi 
            //nhabn du lieu
            $tenloaihang = $_REQUEST['tenloaihang'];
            $mota = $_REQUEST['mota'];
            
            // Kiểm tra xem người dùng đã chọn ảnh hay chưa
            if (empty($_FILES['fileimage']['tmp_name'])) {
                // Nếu không có ảnh, hiển thị alert và quay lại trang thêm loại hàng
                echo "<script>alert('Vui lòng nhập ảnh trước khi thêm loại hàng.'); window.history.back();</script>";
                exit; // Dừng thực thi mã
            }

            $hinhanh_file = $_FILES['fileimage']['tmp_name'];
            $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));
            //kiem thu lieu da nhan du khong
            // echo $tenloaihang . '<br/>';
            // echo $mota . '<br/>';
            // echo $hinhanh . '<br/>';
            $lh = new loaihang();
            $kq = $lh->LoaihangAdd($tenloaihang, $hinhanh, $mota);
            if ($kq) {
                header('location: ../../index.php?req=loaihangview&result=ok');
            } else {
                header('location: ../../index.php?req=loaihangView&result=notok');
            }
            break;

        case 'deleteloaihang':
            $idloaihang = $_REQUEST['idloaihang'];
            //  echo $idloaihang;
            $lh = new loaihang();
            $kq = $lh->LoaihangDelete($idloaihang);
            if ($kq) {
                header('location: ../../index.php?req=loaihangview&result=ok');
            } else {
                header('location: ../../index.php?req=loaihangView&result=notok');
            }
            break;

        case 'updateloaihang':
            $idloaihang = $_REQUEST['idloaihang'];
            $tenloaihang = $_REQUEST['tenloaihang'];
            $mota = $_REQUEST['mota'];

            if (file_exists($_FILES['fileimage']['tmp_name'])) {
                $hinhanh_file = $_FILES['fileimage']['tmp_name'];
                $hinhanh = base64_encode(file_get_contents(addslashes($hinhanh_file)));
            } else {
                $hinhanh = $_REQUEST['hinhanh'];
            }
            // echo $idloaihang . '<br/>';
            // echo $tenloaihang . '<br/>';
            // echo $mota . '<br/>';
            // echo $hinhanh . '<br/>';
            
            $lh = new loaihang();
            $kq = $lh->LoaihangUpdate($tenloaihang, $hinhanh, $mota, $idloaihang);
            if ($kq) {
                header('location: ../../index.php?req=loaihangview&result=ok');
            } else {
                header('location: ../../index.php?req=loaihangView&result=notok');
            }
            break;

        default:
            //danh cho truong hop khong gan thi dai gia tri nao do trong cau truc xu ly 
            header('location: ../../index.php?req=loaihangview');
            break;
    }
} else {
    //nhay lai dia chi index.php
    header('location: ../../index.php?req=loaihangview');
}

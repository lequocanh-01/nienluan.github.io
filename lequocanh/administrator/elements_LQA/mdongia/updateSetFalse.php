<?php
session_start();
require_once __DIR__ . '/../mod/dongiaCls.php';

// Hàm chuyển hướng với thông báo
function redirectWithMessage($success, $message = '')
{
    $_SESSION['dongia_message'] = $message;
    $_SESSION['dongia_success'] = $success;
    header('location: ../../index.php?req=dongiaview');
    exit;
}

if (isset($_REQUEST['idDonGia'])) {
    $idDonGia = $_REQUEST['idDonGia'];
    $apDung = ($_REQUEST['apDung'] === 'true');

    $dongiaObj = new Dongia();
    $dongia = $dongiaObj->DongiaGetbyId($idDonGia);

    if (!$dongia) {
        redirectWithMessage(false, 'Không tìm thấy đơn giá');
    }

    // Nếu đang chuyển trạng thái thành "Đang áp dụng"
    if ($apDung) {
        // Đặt tất cả các đơn giá khác của sản phẩm này thành không áp dụng
        $dongiaObj->DongiaSetAllToFalse($dongia->idHangHoa);

        // Cập nhật trạng thái của đơn giá này thành đang áp dụng
        $kq = $dongiaObj->DongiaUpdateStatus($idDonGia, true);

        if ($kq) {
            // Cập nhật giá tham khảo trong bảng hanghoa
            $dongiaObj->HanghoaUpdatePrice($dongia->idHangHoa, $dongia->giaBan);
            redirectWithMessage(true, 'Đã chuyển đơn giá thành đang áp dụng và cập nhật giá sản phẩm');
        } else {
            redirectWithMessage(false, 'Không thể áp dụng đơn giá này');
        }
    } else {
        // Nếu đang chuyển trạng thái thành "Ngừng áp dụng"
        $kq = $dongiaObj->DongiaUpdateStatus($idDonGia, false);

        if ($kq) {
            // Tìm đơn giá mới nhất để áp dụng
            $dongiaObj->UpdateLatestPriceForProduct($dongia->idHangHoa);
            redirectWithMessage(true, 'Đã ngừng áp dụng đơn giá này và cập nhật giá mới nhất');
        } else {
            redirectWithMessage(false, 'Không thể ngừng áp dụng đơn giá này');
        }
    }
} else {
    redirectWithMessage(false, 'Không có thông tin đơn giá');
}
?>
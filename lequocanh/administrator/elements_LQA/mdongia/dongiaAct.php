<?php
session_start();
require '../../elements_LQA/mod/dongiaCls.php';

function sendJsonResponse($success, $message = '')
{
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $idhanghoa = $_REQUEST['idhanghoa'];
            $ngaycapnhat = $_REQUEST['ngaycapnhat'];
            $dongia = $_REQUEST['dongia'];

            $dg = new Dongia();
            $kq = $dg->dongiaAdd($idhanghoa, $ngaycapnhat, $dongia);
            if ($kq) {
                sendJsonResponse(true, 'Thêm đơn giá thành công');
            } else {
                sendJsonResponse(false, 'Thêm đơn giá thất bại');
            }
            break;

        case 'deletedongia':
            $idDongia = $_REQUEST['idDongia'];
            $dg = new Dongia();
            $kq = $dg->dongiaDelete($idDongia);
            if ($kq) {
                sendJsonResponse(true, 'Xóa đơn giá thành công');
            } else {
                sendJsonResponse(false, 'Xóa đơn giá thất bại');
            }
            break;

        case 'updatedongia':
            $idDongia = $_REQUEST['idDongia'];
            $idhanghoa = $_REQUEST['idhanghoa'];
            $ngaycapnhat = $_REQUEST['ngaycapnhat'];
            $dongia = $_REQUEST['dongia'];

            $dg = new Dongia();
            $kq = $dg->dongiaUpdate($idhanghoa, $ngaycapnhat, $dongia, $idDongia);
            if ($kq) {
                sendJsonResponse(true, 'Cập nhật đơn giá thành công');
            } else {
                sendJsonResponse(false, 'Cập nhật đơn giá thất bại');
            }
            break;

        default:
            sendJsonResponse(false, 'Yêu cầu không hợp lệ');
            break;
    }
} else {
    sendJsonResponse(false, 'Yêu cầu không hợp lệ');
}

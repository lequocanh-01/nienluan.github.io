<?php
session_start();
require '../../elements_LQA/mod/dongiaCls.php';

function sendJsonResponse($success, $message = '')
{
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function redirectWithMessage($success, $message = '')
{
    $_SESSION['dongia_message'] = $message;
    $_SESSION['dongia_success'] = $success;
    header('location: ../../index.php?req=dongiaview');
    exit;
}

// Kiểm tra xem yêu cầu là AJAX hay form thông thường
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew':
            // Lấy dữ liệu từ form
            $idHangHoa = isset($_REQUEST['idhanghoa']) ? $_REQUEST['idhanghoa'] : '';
            $giaBan = isset($_REQUEST['giaban']) ? $_REQUEST['giaban'] : 0;
            $ngayApDung = isset($_REQUEST['ngayapdung']) ? $_REQUEST['ngayapdung'] : '';
            $ngayKetThuc = isset($_REQUEST['ngayketthuc']) ? $_REQUEST['ngayketthuc'] : '';
            $dieuKien = isset($_REQUEST['dieukien']) ? $_REQUEST['dieukien'] : '';
            $ghiChu = isset($_REQUEST['ghichu']) ? $_REQUEST['ghichu'] : '';

            // Kiểm tra dữ liệu đầu vào
            if (empty($idHangHoa) || empty($giaBan) || empty($ngayApDung) || empty($ngayKetThuc)) {
                if ($isAjax) {
                    sendJsonResponse(false, 'Vui lòng điền đầy đủ thông tin bắt buộc');
                } else {
                    redirectWithMessage(false, 'Vui lòng điền đầy đủ thông tin bắt buộc');
                }
            }

            // Thêm đơn giá mới
            $dg = new Dongia();
            $kq = $dg->DongiaAdd($idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu);

            if ($kq) {
                if ($isAjax) {
                    sendJsonResponse(true, 'Thêm đơn giá thành công');
                } else {
                    redirectWithMessage(true, 'Thêm đơn giá thành công');
                }
            } else {
                if ($isAjax) {
                    sendJsonResponse(false, 'Thêm đơn giá thất bại');
                } else {
                    redirectWithMessage(false, 'Thêm đơn giá thất bại');
                }
            }
            break;

        case 'deletedongia':
            $idDonGia = isset($_REQUEST['idDonGia']) ? $_REQUEST['idDonGia'] : '';

            if (empty($idDonGia)) {
                if ($isAjax) {
                    sendJsonResponse(false, 'ID đơn giá không hợp lệ');
                } else {
                    redirectWithMessage(false, 'ID đơn giá không hợp lệ');
                }
            }

            $dg = new Dongia();
            $kq = $dg->DongiaDelete($idDonGia);

            if ($kq) {
                if ($isAjax) {
                    sendJsonResponse(true, 'Xóa đơn giá thành công');
                } else {
                    redirectWithMessage(true, 'Xóa đơn giá thành công');
                }
            } else {
                if ($isAjax) {
                    sendJsonResponse(false, 'Xóa đơn giá thất bại');
                } else {
                    redirectWithMessage(false, 'Xóa đơn giá thất bại');
                }
            }
            break;

        case 'updatedongia':
            $idDonGia = isset($_REQUEST['idDonGia']) ? $_REQUEST['idDonGia'] : '';
            $idHangHoa = isset($_REQUEST['idhanghoa']) ? $_REQUEST['idhanghoa'] : '';
            $giaBan = isset($_REQUEST['giaban']) ? $_REQUEST['giaban'] : 0;
            $ngayApDung = isset($_REQUEST['ngayapdung']) ? $_REQUEST['ngayapdung'] : '';
            $ngayKetThuc = isset($_REQUEST['ngayketthuc']) ? $_REQUEST['ngayketthuc'] : '';
            $dieuKien = isset($_REQUEST['dieukien']) ? $_REQUEST['dieukien'] : '';
            $ghiChu = isset($_REQUEST['ghichu']) ? $_REQUEST['ghichu'] : '';

            if (empty($idDonGia) || empty($idHangHoa) || empty($giaBan) || empty($ngayApDung) || empty($ngayKetThuc)) {
                if ($isAjax) {
                    sendJsonResponse(false, 'Vui lòng điền đầy đủ thông tin bắt buộc');
                } else {
                    redirectWithMessage(false, 'Vui lòng điền đầy đủ thông tin bắt buộc');
                }
            }

            $dg = new Dongia();
            $kq = $dg->DongiaUpdate($idDonGia, $idHangHoa, $giaBan, $ngayApDung, $ngayKetThuc, $dieuKien, $ghiChu);

            if ($kq) {
                if ($isAjax) {
                    sendJsonResponse(true, 'Cập nhật đơn giá thành công');
                } else {
                    redirectWithMessage(true, 'Cập nhật đơn giá thành công');
                }
            } else {
                if ($isAjax) {
                    sendJsonResponse(false, 'Cập nhật đơn giá thất bại');
                } else {
                    redirectWithMessage(false, 'Cập nhật đơn giá thất bại');
                }
            }
            break;

        default:
            if ($isAjax) {
                sendJsonResponse(false, 'Yêu cầu không hợp lệ');
            } else {
                redirectWithMessage(false, 'Yêu cầu không hợp lệ');
            }
            break;
    }
} else {
    if ($isAjax) {
        sendJsonResponse(false, 'Yêu cầu không hợp lệ');
    } else {
        redirectWithMessage(false, 'Yêu cầu không hợp lệ');
    }
}

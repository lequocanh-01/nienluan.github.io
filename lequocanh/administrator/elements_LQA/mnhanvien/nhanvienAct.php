<?php
session_start();
require '../../elements_LQA/mod/nhanvienCls.php';

function sendJsonResponse($success, $message = '')
{
    // Clear any previous output that might corrupt JSON
    if (ob_get_contents()) ob_clean();

    // Set proper headers
    header('Content-Type: application/json');
    header("Cache-Control: no-cache, must-revalidate");

    // Return simple JSON
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (isset($_GET['reqact'])) {
    $requestAction = $_GET['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $tenNV = isset($_REQUEST['tenNV']) ? $_REQUEST['tenNV'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $luongCB = isset($_REQUEST['luongCB']) ? $_REQUEST['luongCB'] : null;
            $phuCap = isset($_REQUEST['phuCap']) ? $_REQUEST['phuCap'] : null;
            $chucVu = isset($_REQUEST['chucVu']) ? $_REQUEST['chucVu'] : null;
            $iduser = isset($_REQUEST['iduser']) ? $_REQUEST['iduser'] : null;

            $nv = new NhanVien();
            $kq = $nv->nhanvienAdd($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $iduser);

            // Check if it's an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                sendJsonResponse($kq, $kq ? 'Thêm nhân viên thành công' : 'Thêm nhân viên thất bại');
            } else {
                // Redirect for regular form submit
                header("location:../../index.php?req=nhanvienview&result=" . ($kq ? "ok" : "notok"));
            }
            break;

        case 'deletenhanvien':
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;
            if ($idNhanVien) {
                $nv = new NhanVien();
                $kq = $nv->nhanvienDelete($idNhanVien);

                // Check if it's an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    sendJsonResponse($kq, $kq ? 'Xóa nhân viên thành công' : 'Xóa nhân viên thất bại');
                } else {
                    // Redirect for regular form submit
                    header("location:../../index.php?req=nhanvienview&result=" . ($kq ? "ok" : "notok"));
                }
            } else {
                sendJsonResponse(false, 'Không tìm thấy ID nhân viên');
            }
            break;

        case 'updatenhanvien':
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;
            $tenNV = isset($_REQUEST['tenNV']) ? $_REQUEST['tenNV'] : null;
            $SDT = isset($_REQUEST['SDT']) ? $_REQUEST['SDT'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
            $luongCB = isset($_REQUEST['luongCB']) ? $_REQUEST['luongCB'] : 0;
            $phuCap = isset($_REQUEST['phuCap']) ? $_REQUEST['phuCap'] : 0;
            $chucVu = isset($_REQUEST['chucVu']) ? $_REQUEST['chucVu'] : null;
            $iduser = isset($_REQUEST['iduser']) && $_REQUEST['iduser'] !== '' ? $_REQUEST['iduser'] : null;

            if ($idNhanVien) {
                $nv = new NhanVien();
                $kq = $nv->nhanvienUpdate($tenNV, $SDT, $email, $luongCB, $phuCap, $chucVu, $idNhanVien, $iduser);

                // Always send JSON for updatenhanvien
                sendJsonResponse(true, 'Cập nhật nhân viên thành công');
            } else {
                sendJsonResponse(false, 'Không tìm thấy ID nhân viên');
            }
            break;

        default:
            sendJsonResponse(false, 'Yêu cầu không hợp lệ');
            break;
    }
} else {
    sendJsonResponse(false, 'Yêu cầu không hợp lệ');
}

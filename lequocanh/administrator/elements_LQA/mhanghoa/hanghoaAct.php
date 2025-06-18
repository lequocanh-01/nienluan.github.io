<?php
session_start();
require_once '../mod/hanghoaCls.php';
$hanghoa = new hanghoa();

// Tìm đường dẫn đúng đến nhatKyHoatDongHelper.php
$nhatKyHelperPaths = [
    __DIR__ . '/../mnhatkyhoatdong/nhatKyHoatDongHelper.php',
    __DIR__ . '/../../elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php',
    __DIR__ . '/../../../administrator/elements_LQA/mnhatkyhoatdong/nhatKyHoatDongHelper.php'
];

$foundNhatKyHelper = false;
foreach ($nhatKyHelperPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $foundNhatKyHelper = true;
        break;
    }
}

if (!$foundNhatKyHelper) {
    error_log("Không thể tìm thấy file nhatKyHoatDongHelper.php");
}

// Lấy username từ session
$username = isset($_SESSION['USER']) ? $_SESSION['USER'] : (isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : '');
if (isset($_REQUEST['reqact'])) {
    $requestAction = $_REQUEST['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $tenhanghoa = $_REQUEST['tenhanghoa'];
            $mota = $_REQUEST['mota'];
            $giathamkhao = $_REQUEST['giathamkhao'];
            $id_hinhanh = isset($_REQUEST['id_hinhanh']) ? $_REQUEST['id_hinhanh'] : 0;
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = isset($_REQUEST['idThuongHieu']) ? $_REQUEST['idThuongHieu'] : '';
            $idDonViTinh = isset($_REQUEST['idDonViTinh']) ? $_REQUEST['idDonViTinh'] : '';
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : '';
            $ghichu = isset($_REQUEST['ghichu']) ? $_REQUEST['ghichu'] : '';

            // Tạo file log
            $log_file = __DIR__ . '/hanghoa_debug.log';

            // Ghi log chi tiết
            $log_data = date('Y-m-d H:i:s') . " - Thêm hàng hóa mới:\n";
            $log_data .= "tenhanghoa: $tenhanghoa\n";
            $log_data .= "mota: $mota\n";
            $log_data .= "giathamkhao: $giathamkhao\n";
            $log_data .= "id_hinhanh: $id_hinhanh\n";
            $log_data .= "idloaihang: $idloaihang\n";
            $log_data .= "idThuongHieu: " . ($idThuongHieu ?: "NULL") . "\n";
            $log_data .= "idDonViTinh: " . ($idDonViTinh ?: "NULL") . "\n";
            $log_data .= "idNhanVien: " . ($idNhanVien ?: "NULL") . "\n";
            file_put_contents($log_file, $log_data, FILE_APPEND);

            try {
                // Lưu kết quả trả về từ hàm HanghoaAdd
                $result = $hanghoa->HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $ghichu);

                // Ghi log kết quả
                $log_result = date('Y-m-d H:i:s') . " - Kết quả thêm hàng hóa: " . ($result ? "thành công" : "thất bại") . "\n";
                file_put_contents($log_file, $log_result, FILE_APPEND);

                // Kiểm tra kết quả trả về từ hàm HanghoaAdd
                if ($result) {
                    // Nếu result là số nguyên > 0, đó là ID của hàng hóa mới thêm
                    if (is_numeric($result) && $result > 0) {
                        $log_success = date('Y-m-d H:i:s') . " - Thêm hàng hóa thành công với ID: $result\n";
                        file_put_contents($log_file, $log_success, FILE_APPEND);

                        // Ghi nhật ký thêm mới hàng hóa
                        if ($foundNhatKyHelper && !empty($username)) {
                            ghiNhatKyThemMoi($username, 'hàng hóa', $result, "Thêm hàng hóa mới: $tenhanghoa");
                        }
                    }
                    header('location: ../../index.php?req=hanghoaview&result=ok');
                } else {
                    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Thêm hàng hóa thất bại, không có lỗi cụ thể\n", FILE_APPEND);
                    header('location: ../../index.php?req=hanghoaview&result=notok');
                }
            } catch (Exception $e) {
                // Ghi log lỗi
                $log_error = date('Y-m-d H:i:s') . " - Lỗi: " . $e->getMessage() . "\n";
                file_put_contents($log_file, $log_error, FILE_APPEND);

                header('location: ../../index.php?req=hanghoaview&result=notok&error=' . urlencode($e->getMessage()));
            }
            break;

        case 'deletehanghoa':
            $idhanghoa = $_REQUEST['idhanghoa'];

            // Lấy thông tin hàng hóa trước khi xóa để ghi nhật ký
            $hanghoaInfo = $hanghoa->HanghoaGetbyId($idhanghoa);
            $tenhanghoa = $hanghoaInfo ? $hanghoaInfo->tenhanghoa : "Không xác định";

            $result = $hanghoa->HanghoaDelete($idhanghoa);
            if ($result) {
                // Ghi nhật ký xóa hàng hóa
                if ($foundNhatKyHelper && !empty($username)) {
                    ghiNhatKyXoa($username, 'hàng hóa', $idhanghoa, "Xóa hàng hóa: $tenhanghoa");
                }

                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                header('location: ../../index.php?req=hanghoaview&result=notok');
            }
            break;

        case 'updatehanghoa':
            $idhanghoa = $_REQUEST['idhanghoa'];
            $tenhanghoa = $_REQUEST['tenhanghoa'];
            $mota = $_REQUEST['mota'];
            $giathamkhao = $_REQUEST['giathamkhao'];
            $id_hinhanh = isset($_REQUEST['id_hinhanh']) ? $_REQUEST['id_hinhanh'] : 0;
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = isset($_REQUEST['idThuongHieu']) ? $_REQUEST['idThuongHieu'] : '';
            $idDonViTinh = isset($_REQUEST['idDonViTinh']) ? $_REQUEST['idDonViTinh'] : '';
            $idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : '';
            $ghichu = isset($_REQUEST['ghichu']) ? $_REQUEST['ghichu'] : '';

            // Debug log si se solicita
            $debug_log = isset($_REQUEST['debug_log']) && $_REQUEST['debug_log'] === 'true';
            if ($debug_log) {
                $log_file = __DIR__ . '/debug_log.txt';
                $log_data = date('Y-m-d H:i:s') . " - Hanghoa Update request:\n";
                $log_data .= "idhanghoa: $idhanghoa\n";
                $log_data .= "tenhanghoa: $tenhanghoa\n";
                $log_data .= "POST: " . print_r($_POST, true) . "\n";
                $log_data .= "GET: " . print_r($_GET, true) . "\n";
                $log_data .= "REQUEST: " . print_r($_REQUEST, true) . "\n";
                $log_data .= "--------------------------------------\n";
                file_put_contents($log_file, $log_data, FILE_APPEND);
            }

            try {
                $result = $hanghoa->HanghoaUpdate($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa, $ghichu);

                if ($debug_log) {
                    $log_data = date('Y-m-d H:i:s') . " - Update result: " . ($result ? "Success" : "Failed") . "\n";
                    file_put_contents(__DIR__ . '/debug_log.txt', $log_data, FILE_APPEND);
                }

                // Ghi nhật ký cập nhật hàng hóa
                if ($result && $foundNhatKyHelper && !empty($username)) {
                    ghiNhatKyCapNhat($username, 'hàng hóa', $idhanghoa, "Cập nhật hàng hóa: $tenhanghoa");
                }

                // Verificar si la solicitud es AJAX
                if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                    echo json_encode([
                        'success' => $result ? true : false,
                        'message' => $result ? 'Cập nhật hàng hóa thành công!' : 'Cập nhật thất bại!'
                    ]);
                    exit;
                } else {
                    header('location: ../../index.php?req=hanghoaview&result=' . ($result ? 'ok' : 'notok'));
                }
            } catch (Exception $e) {
                if ($debug_log) {
                    $log_data = date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n";
                    file_put_contents(__DIR__ . '/debug_log.txt', $log_data, FILE_APPEND);
                }

                if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi: ' . $e->getMessage()
                    ]);
                    exit;
                } else {
                    header('location: ../../index.php?req=hanghoaview&result=notok&error=' . urlencode($e->getMessage()));
                }
            }
            break;

        case 'applyimage':
            if (isset($_GET['idhanghoa']) && isset($_GET['id_hinhanh'])) {
                $idhanghoa = intval($_GET['idhanghoa']);
                $id_hinhanh = intval($_GET['id_hinhanh']);

                if ($hanghoa->ApplyImageToProduct($idhanghoa, $id_hinhanh)) {
                    // Thành công
                    header("location: ../../index.php?req=hanghoaview&result=ok&msg=image_applied");
                } else {
                    // Thất bại
                    header("location: ../../index.php?req=hanghoaview&result=notok&msg=image_not_applied");
                }
            } else {
                header("location: ../../index.php?req=hanghoaview&result=notok");
            }
            break;

        case 'applyallimages':
            if (isset($_GET['matches'])) {
                $matches = json_decode(urldecode($_GET['matches']), true);

                if (empty($matches)) {
                    header("location: ../../index.php?req=hanghoaview&result=notok&msg=no_matches");
                    break;
                }

                $successCount = 0;

                foreach ($matches as $match) {
                    if ($hanghoa->ApplyImageToProduct($match['product_id'], $match['image_id'])) {
                        $successCount++;
                    }
                }

                if ($successCount > 0) {
                    if ($successCount == count($matches)) {
                        // Tất cả đều thành công
                        header("location: ../../index.php?req=hanghoaview&result=ok&msg=all_images_applied&count=" . $successCount);
                    } else {
                        // Một số thành công, một số thất bại
                        header("location: ../../index.php?req=hanghoaview&result=notok&msg=some_images_not_applied");
                    }
                } else {
                    // Tất cả đều thất bại
                    header("location: ../../index.php?req=hanghoaview&result=notok&msg=no_images_applied");
                }
            } else {
                header("location: ../../index.php?req=hanghoaview&result=notok");
            }
            break;

        case "remove_mismatched_images":
            // Gỡ bỏ tất cả hình ảnh không khớp tên sản phẩm
            $count = $hanghoa->RemoveAllMismatchedImages();

            if ($count === false) {
                // Có lỗi xảy ra
                header("location: ../../index.php?req=hanghoaview&result=notok&msg=remove_failed");
            } else if ($count > 0) {
                // Đã gỡ bỏ thành công một số hình ảnh
                header("location: ../../index.php?req=hanghoaview&result=ok&msg=removed_mismatched&count=" . $count);
            } else {
                // Không có hình ảnh nào bị gỡ bỏ
                header("location: ../../index.php?req=hanghoaview&result=notok&msg=no_images_removed");
            }
            break;

        case "remove_image":
            // Gỡ bỏ hình ảnh khỏi một sản phẩm cụ thể
            if (isset($_GET['idhanghoa'])) {
                $idhanghoa = intval($_GET['idhanghoa']);
                $result = $hanghoa->RemoveImageFromProduct($idhanghoa);

                // Kiểm tra xem request có phải là AJAX không
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($isAjax) {
                    // Trả về kết quả dạng JSON cho AJAX request
                    header('Content-Type: application/json');
                    if ($result) {
                        http_response_code(200); // OK
                        echo json_encode(['success' => true, 'message' => 'Đã xóa hình ảnh thành công']);
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(['success' => false, 'message' => 'Không thể xóa hình ảnh']);
                    }
                    exit;
                } else {
                    // Xử lý cho non-AJAX request
                    if ($result) {
                        // Gỡ bỏ thành công
                        header("location: ../../index.php?req=hanghoaview&result=ok&msg=image_removed");
                    } else {
                        // Gỡ bỏ thất bại
                        header("location: ../../index.php?req=hanghoaview&result=notok&msg=image_removal_failed");
                    }
                }
            } else {
                if (
                    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                ) {
                    header('Content-Type: application/json');
                    http_response_code(400); // Bad Request
                    echo json_encode(['success' => false, 'message' => 'Thiếu ID hàng hóa']);
                    exit;
                } else {
                    header("location: ../../index.php?req=hanghoaview&result=notok");
                }
            }
            break;

        default:
            header('location:../../index.php?req=hanghoaview');
            break;
    }
}

<?php
require_once '../mod/hanghoaCls.php';
$hanghoa = new hanghoa();
if (isset($_REQUEST['reqact'])) {
    $requestAction = $_REQUEST['reqact'];
    switch ($requestAction) {
        case 'addnew':
            $tenhanghoa = $_REQUEST['tenhanghoa'];
            $mota = $_REQUEST['mota'];
            $giathamkhao = $_REQUEST['giathamkhao'];
            $id_hinhanh = $_REQUEST['id_hinhanh'];
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = $_REQUEST['idThuongHieu'];
            $idDonViTinh = $_REQUEST['idDonViTinh'];
            $idNhanVien = $_REQUEST['idNhanVien'];

            $hanghoa->HanghoaAdd($tenhanghoa, $mota, $giathamkhao, $id_hinhanh, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien);
            if ($hanghoa) {
                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                header('location: ../../index.php?req=hanghoaview&result=notok');
            }
            break;

        case 'deletehanghoa':
            $idhanghoa = $_REQUEST['idhanghoa'];
            $hanghoa->HanghoaDelete($idhanghoa);
            if ($hanghoa) {
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
            $id_hinhanh = $_REQUEST['id_hinhanh'];
            $idloaihang = $_REQUEST['idloaihang'];
            $idThuongHieu = $_REQUEST['idThuongHieu'];
            $idDonViTinh = $_REQUEST['idDonViTinh'];
            $idNhanVien = $_REQUEST['idNhanVien'];

            $hanghoa->HanghoaUpdate($tenhanghoa, $id_hinhanh, $mota, $giathamkhao, $idloaihang, $idThuongHieu, $idDonViTinh, $idNhanVien, $idhanghoa);
            if ($hanghoa) {
                header('location: ../../index.php?req=hanghoaview&result=ok');
            } else {
                header('location: ../../index.php?req=hanghoaview&result=notok');
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

                if ($result) {
                    // Gỡ bỏ thành công
                    header("location: ../../index.php?req=hanghoaview&result=ok&msg=image_removed");
                } else {
                    // Gỡ bỏ thất bại
                    header("location: ../../index.php?req=hanghoaview&result=notok&msg=image_removal_failed");
                }
            } else {
                header("location: ../../index.php?req=hanghoaview&result=notok");
            }
            break;

        default:
            header('location:../../index.php?req=hanghoaview');
            break;
    }
}

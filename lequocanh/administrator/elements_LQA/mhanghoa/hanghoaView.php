<div class="admin-title">Quản lý hàng hóa</div>
<hr>
<?php
require_once './elements_LQA/mod/loaihangCls.php';
require_once './elements_LQA/mod/hanghoaCls.php';

$lhobj = new loaihang();
$hanghoaObj = new hanghoa();

$list_lh = $lhobj->LoaihangGetAll();
$list_thuonghieu = $hanghoaObj->GetAllThuongHieu();
$list_donvitinh = $hanghoaObj->GetAllDonViTinh();
$list_nhanvien = $hanghoaObj->GetAllNhanVien();
$list_hinhanh = $hanghoaObj->GetAllHinhAnh();

// Tạo bảng hanghoa_hinhanh nếu chưa tồn tại
$hanghoaObj->CreateHanghoaHinhanhTable();

// Hiển thị thông báo nếu có
if (isset($_GET['result'])) {
    if ($_GET['result'] == 'ok') {
        echo '<div class="alert alert-success">';
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] == 'removed_mismatched' && isset($_GET['count'])) {
                echo '<strong>Thành công!</strong> Đã gỡ bỏ ' . $_GET['count'] . ' hình ảnh không khớp tên.';
            } else if ($_GET['msg'] == 'image_removed') {
                echo '<strong>Thành công!</strong> Đã gỡ bỏ hình ảnh khỏi sản phẩm.';
            } else if ($_GET['msg'] == 'image_applied') {
                echo '<strong>Thành công!</strong> Đã áp dụng hình ảnh cho sản phẩm.';
            } else if ($_GET['msg'] == 'all_images_applied' && isset($_GET['count'])) {
                echo '<strong>Thành công!</strong> Đã áp dụng ' . $_GET['count'] . ' hình ảnh cho các sản phẩm.';
            } else {
                echo '<strong>Thành công!</strong> Thao tác đã được thực hiện.';
            }
        } else {
            echo '<strong>Thành công!</strong> Thao tác đã được thực hiện.';
        }
        echo '</div>';
    } else if ($_GET['result'] == 'notok') {
        echo '<div class="alert alert-danger">';
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] == 'remove_failed') {
                echo '<strong>Lỗi!</strong> Không thể gỡ bỏ hình ảnh. Vui lòng thử lại.';
            } else if ($_GET['msg'] == 'no_images_removed') {
                echo '<strong>Thông báo:</strong> Không có hình ảnh nào được gỡ bỏ.';
            } else if ($_GET['msg'] == 'image_removal_failed') {
                echo '<strong>Lỗi!</strong> Không thể gỡ bỏ hình ảnh khỏi sản phẩm. Vui lòng thử lại.';
            } else if ($_GET['msg'] == 'image_not_applied') {
                echo '<strong>Lỗi!</strong> Không thể áp dụng hình ảnh cho sản phẩm. Vui lòng thử lại.';
            } else if ($_GET['msg'] == 'some_images_not_applied') {
                echo '<strong>Cảnh báo:</strong> Một số hình ảnh không thể được áp dụng.';
            } else {
                echo '<strong>Lỗi!</strong> Thao tác thất bại. Vui lòng thử lại.';
            }
        } else {
            echo '<strong>Lỗi!</strong> Thao tác thất bại. Vui lòng thử lại.';
        }
        echo '</div>';
    }
}

// Hiển thị thông báo nếu có hình ảnh mới khớp với sản phẩm
if (isset($_SESSION['matched_images']) && !empty($_SESSION['matched_images'])) {
    echo '<div class="alert-success">';
    echo '<strong>Phát hiện hình ảnh phù hợp với sản phẩm:</strong><br>';
    foreach ($_SESSION['matched_images'] as $match) {
        echo 'Hình ảnh <strong>' . htmlspecialchars($match['image_name']) . '</strong> phù hợp với sản phẩm <strong>' . htmlspecialchars($match['product_name']) . '</strong><br>';
    }
    echo 'Bạn có thể nhấn nút "Áp dụng" ở cột hình ảnh tương ứng để áp dụng hình ảnh cho sản phẩm.';
    echo '</div>';

    // Xóa session sau khi đã hiển thị
    unset($_SESSION['matched_images']);
}

// Kiểm tra hình ảnh không khớp tên sản phẩm
$mismatched_images = $hanghoaObj->GetMismatchedProductImages();
$missing_images = $hanghoaObj->FindMissingImages();
?>

<head>
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<div class="admin-form">
    <h3>Thêm hàng hóa mới</h3>
    <form name="newhanghoa" id="formaddhanghoa" method="post"
        action='./elements_LQA/mhanghoa/hanghoaAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
            <tr>
                <td>Tên hàng hóa</td>
                <td><input type="text" name="tenhanghoa" required /></td>
            </tr>
            <tr>
                <td>Giá tham khảo</td>
                <td><input type="number" name="giathamkhao" required /></td>
            </tr>
            <tr>
                <td>Mô tả</td>
                <td><input type="text" name="mota" /></td>
            </tr>
            <tr>
                <td>Hình ảnh</td>
                <td>
                    <select name="id_hinhanh" id="imageSelector" required>
                        <option value="">-- Chọn hình ảnh --</option>
                        <?php
                        foreach ($list_hinhanh as $img) {
                        ?>
                            <option value="<?php echo $img->id; ?>">
                                <?php echo htmlspecialchars($img->ten_file); ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                    <div class="image-preview">
                        <?php
                        foreach ($list_hinhanh as $img) {
                        ?>
                            <div class="preview-item" onclick="selectImage(<?php echo $img->id; ?>)">
                                <?php
                                // Sử dụng displayImage.php để hiển thị ảnh theo ID
                                $imageSrc = "./elements_LQA/mhanghoa/displayImage.php?id=" . $img->id;
                                ?>
                                <img src="<?php echo $imageSrc; ?>"
                                    class="preview-img"
                                    data-id="<?php echo $img->id; ?>"
                                    alt="<?php echo htmlspecialchars($img->ten_file); ?>"
                                    title="<?php echo htmlspecialchars($img->ten_file); ?>"
                                    onerror="this.src='./img_LQA/no-image.png'">
                                <div class="preview-info">
                                    <span class="preview-name"><?php echo htmlspecialchars($img->ten_file); ?></span>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Chọn loại hàng:</td>
                <td>
                    <?php
                    if (!empty($list_lh)) {
                        foreach ($list_lh as $l) {
                    ?>
                            <input type="radio" name="idloaihang" value="<?php echo $l->idloaihang; ?>" required>
                            <img class="iconbutton" src="data:image/png;base64,<?php echo $l->hinhanh; ?>">
                            <br>
                    <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Chọn thương hiệu:</td>
                <td>
                    <select name="idThuongHieu">
                        <option value="">-- Chọn thương hiệu --</option>
                        <?php
                        foreach ($list_thuonghieu as $th) {
                        ?>
                            <option value="<?php echo $th->idThuongHieu; ?>"><?php echo $th->tenTH; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Chọn đơn vị tính:</td>
                <td>
                    <select name="idDonViTinh">
                        <option value="">-- Chọn đơn vị tính --</option>
                        <?php
                        foreach ($list_donvitinh as $dvt) {
                        ?>
                            <option value="<?php echo $dvt->idDonViTinh; ?>"><?php echo $dvt->tenDonViTinh; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Chọn nhân viên:</td>
                <td>
                    <select name="idNhanVien">
                        <option value="">-- Chọn nhân viên --</option>
                        <?php
                        foreach ($list_nhanvien as $nv) {
                        ?>
                            <option value="<?php echo $nv->idNhanVien; ?>"><?php echo $nv->tenNV; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<?php
$list_hanghoa = $hanghoaObj->HanghoaGetAll();
$l = count($list_hanghoa);
?>
<div class="content_hanghoa">
    <div class="admin-info">
        Tổng số hàng hóa: <b><?php echo $l; ?></b>

        <?php
        // Đếm tổng số hình ảnh đã áp dụng
        $totalImages = 0;
        $productsWithImages = 0;
        foreach ($list_hanghoa as $product) {
            $imageCount = $hanghoaObj->CountImagesForProduct($product->idhanghoa);
            $totalImages += $imageCount;
            if ($imageCount > 0) {
                $productsWithImages++;
            }
        }
        echo ' | Tổng số hình ảnh đã áp dụng: <b>' . $totalImages . '</b>';
        echo ' | Số sản phẩm có hình ảnh: <b>' . $productsWithImages . '/' . $l . '</b>';
        ?>

        <?php
        // Kiểm tra xem có sản phẩm nào chưa có ảnh và có ảnh phù hợp không
        $productsWithMatchingImages = [];
        foreach ($list_hanghoa as $product) {
            if (empty($product->hinhanh) || $product->hinhanh == 0) {
                $matchFound = false;
                foreach ($list_hinhanh as $img) {
                    if ($hanghoaObj->IsExactImageNameMatch($product->tenhanghoa, $img->ten_file)) {
                        $matchFound = true;
                        $productsWithMatchingImages[] = [
                            'product_id' => $product->idhanghoa,
                            'image_id' => $img->id
                        ];
                        break; // Chỉ lấy ảnh đầu tiên khớp
                    }
                }
            }
        }

        // Hiển thị nút áp dụng tất cả nếu có sản phẩm nào chưa có ảnh và có ảnh phù hợp
        if (!empty($productsWithMatchingImages)) {
            echo '<a href="./elements_LQA/mhanghoa/hanghoaAct.php?reqact=applyallimages&matches=' .
                urlencode(json_encode($productsWithMatchingImages)) .
                '" class="btn-apply-all">
                 <i class="fas fa-images"></i> Áp dụng tất cả hình ảnh phù hợp
                 </a>';
        }
        ?>
    </div>

    <?php
    // Hiển thị thông báo về hình ảnh không khớp tên
    if (!empty($mismatched_images)) {
        echo '<div class="alert alert-warning">';
        echo '<div class="alert-header">';
        echo '<h4><i class="fas fa-exclamation-triangle"></i> Lưu ý: Có ' . count($mismatched_images) . ' sản phẩm có hình ảnh không khớp với tên sản phẩm</h4>';
        echo '<a href="javascript:void(0)" onclick="confirmRemoveAll()" class="btn btn-danger remove-mismatched-btn"><i class="fas fa-unlink"></i> Gỡ bỏ tất cả hình ảnh không khớp</a>';
        echo '</div>';
        echo '<ul class="mismatched-list">';
        foreach ($mismatched_images as $item) {
            echo '<li>';
            echo 'Sản phẩm "' . htmlspecialchars($item->tenhanghoa) . '" (ID: ' . $item->idhanghoa . ') ';
            echo 'đang sử dụng hình ảnh "' . htmlspecialchars($item->ten_file) . '" (ID: ' . $item->id . ') ';
            echo '<a href="javascript:void(0)" onclick="confirmRemoveSingle(' . $item->idhanghoa . ', \'' . htmlspecialchars($item->tenhanghoa) . '\')" class="btn-remove-image" title="Gỡ bỏ hình ảnh này"><i class="fas fa-times-circle"></i></a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '<p><em>Lưu ý: Đây chỉ là thông báo, bạn có thể kiểm tra và sửa thủ công nếu cần hoặc sử dụng nút gỡ bỏ để loại bỏ hình ảnh không khớp.</em></p>';
        echo '</div>';
    }

    // Hiển thị thông báo về hình ảnh bị mất
    if (!empty($missing_images)) {
        echo '<div class="alert alert-danger">';
        echo '<h4><i class="fas fa-exclamation-circle"></i> Cảnh báo: Có ' . count($missing_images) . ' sản phẩm đang tham chiếu đến hình ảnh không tồn tại</h4>';
        echo '<ul class="missing-list">';
        foreach ($missing_images as $item) {
            echo '<li>';
            echo 'Sản phẩm "' . htmlspecialchars($item->tenhanghoa) . '" (ID: ' . $item->idhanghoa . ') ';
            echo 'đang tham chiếu đến hình ảnh không tồn tại (ID: ' . $item->hinhanh . ')';
            echo '</li>';
        }
        echo '</ul>';
        echo '<p><em>Khuyến nghị: Hãy chọn hình ảnh khác cho các sản phẩm này.</em></p>';
        echo '</div>';
    }
    ?>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên hàng hóa</th>
                <th>Giá tham khảo</th>
                <th>Mô tả</th>
                <th>Hình ảnh</th>
                <th>Thương Hiệu</th>
                <th>Đơn Vị Tính</th>
                <th>Nhân Viên</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($l > 0) {
                foreach ($list_hanghoa as $u) {
            ?>
                    <tr>
                        <td><?php echo $u->idhanghoa; ?></td>
                        <td><?php echo htmlspecialchars($u->tenhanghoa); ?></td>
                        <td><?php echo number_format($u->giathamkhao, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($u->mota); ?></td>
                        <td align="center">
                            <?php
                            if (is_numeric($u->hinhanh) && $u->hinhanh > 0) {
                                // Sử dụng script displayImage khi hinhanh là ID
                                $imageSrc = "./elements_LQA/mhanghoa/displayImage.php?id=" . $u->hinhanh;
                            ?>
                                <img class="iconbutton" src="<?php echo $imageSrc; ?>"
                                    alt="Product Image"
                                    onerror="this.src='./img_LQA/no-image.png'">
                                <?php
                                // Hiển thị số lượng hình ảnh
                                $imageCount = $hanghoaObj->CountImagesForProduct($u->idhanghoa);
                                if ($imageCount > 0) {
                                    echo '<div class="image-count">';
                                    echo '<span class="badge">' . $imageCount . ' ảnh</span>';
                                    echo '</div>';
                                }
                                ?>
                            <?php
                            } else {
                                echo '<img class="iconbutton" src="./img_LQA/no-image.png" alt="No image">';

                                // Chỉ hiển thị nút áp dụng khi sản phẩm chưa có hình ảnh
                                // Kiểm tra nếu có hình ảnh KHỚP CHÍNH XÁC (tên hình ảnh phải giống hệt tên sản phẩm)
                                $matchingImages = [];
                                foreach ($list_hinhanh as $img) {
                                    if ($hanghoaObj->IsExactImageNameMatch($u->tenhanghoa, $img->ten_file)) {
                                        $matchingImages[] = $img;
                                    }
                                }

                                if (!empty($matchingImages)) {
                                    echo '<div class="matching-images">';
                                    foreach ($matchingImages as $img) {
                                        echo '<a href="./elements_LQA/mhanghoa/hanghoaAct.php?reqact=applyimage&idhanghoa=' . $u->idhanghoa . '&id_hinhanh=' . $img->id . '" 
                                                 class="btn btn-apply-image" 
                                                 title="Áp dụng hình ảnh: ' . $img->ten_file . '">
                                                 <i class="fas fa-check"></i> Áp dụng
                                              </a>';
                                    }
                                    echo '</div>';
                                }

                                // Hiển thị số lượng hình ảnh nếu có
                                $imageCount = $hanghoaObj->CountImagesForProduct($u->idhanghoa);
                                if ($imageCount > 0) {
                                    echo '<div class="image-count">';
                                    echo '<span class="badge">' . $imageCount . ' ảnh</span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($u->idThuongHieu ?? 'Chưa chọn'); ?></td>
                        <td><?php echo htmlspecialchars($u->idDonViTinh ?? 'Chưa chọn'); ?></td>
                        <td><?php echo htmlspecialchars($u->idNhanVien ?? 'Chưa chọn'); ?></td>
                        <td align="center">
                            <?php
                            if (isset($_SESSION['ADMIN'])) {
                            ?>
                                <a
                                    href="./elements_LQA/mhanghoa/hanghoaAct.php?reqact=deletehanghoa&idhanghoa=<?php echo $u->idhanghoa; ?>">
                                    <img src="./img_LQA/delete.png">
                                </a>
                            <?php
                            } else {
                            ?>
                                <img src="./img_LQA/delete.png">
                            <?php
                            }
                            ?>
                            <img src="./img_LQA/Update.png" class="w_update_btn_open_hh" value="<?php echo $u->idhanghoa; ?>">
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<div id="w_update_hh">
    <div id="w_update_form_hh"></div>
    <input type="button" value="close" id="w_close_btn_hh">
</div>

<div id="replace-image-dialog" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Chọn hình ảnh thay thế</h3>
        <p>Hình ảnh này đang được sử dụng bởi một số sản phẩm. Vui lòng chọn hình ảnh thay thế:</p>
        <select id="replace-image-select">
            <option value="">-- Chọn hình ảnh thay thế --</option>
        </select>
        <div class="modal-buttons">
            <button id="confirm-replace">Xác nhận</button>
            <button id="cancel-replace">Hủy</button>
        </div>
    </div>
</div>

<script src="../../js_LQA/jscript.js"></script>

<script>
    // Xác nhận gỡ bỏ một hình ảnh
    function confirmRemoveSingle(idhanghoa, tenhanghoa) {
        if (confirm('Bạn có chắc chắn muốn gỡ bỏ hình ảnh khỏi sản phẩm "' + tenhanghoa + '" không?')) {
            window.location.href = './elements_LQA/mhanghoa/hanghoaAct.php?reqact=remove_image&idhanghoa=' + idhanghoa;
        }
    }

    // Xác nhận gỡ bỏ tất cả hình ảnh không khớp
    function confirmRemoveAll() {
        if (confirm('Bạn có chắc chắn muốn gỡ bỏ TẤT CẢ hình ảnh không khớp tên ra khỏi sản phẩm không?\nHành động này không thể hoàn tác!')) {
            window.location.href = './elements_LQA/mhanghoa/hanghoaAct.php?reqact=remove_mismatched_images';
        }
    }

    // Javascript xử lý chọn hình ảnh
    function selectImage(imageId) {
        // Lấy đối tượng select
        const imageSelector = document.getElementById('imageSelector');

        // Đặt giá trị select thành imageId
        imageSelector.value = imageId;

        // Đánh dấu tất cả các item là không được chọn
        const allPreviewItems = document.querySelectorAll('.preview-item');
        allPreviewItems.forEach(item => {
            item.classList.remove('selected');
        });

        // Thêm class selected cho item được chọn
        const selectedItem = document.querySelector(`.preview-item img[data-id="${imageId}"]`).parentNode;
        selectedItem.classList.add('selected');
    }

    // Khi trang đã load xong
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý khi người dùng thay đổi select box
        document.getElementById('imageSelector').addEventListener('change', function() {
            const selectedValue = this.value;

            // Xóa highlight tất cả các item
            const allPreviewItems = document.querySelectorAll('.preview-item');
            allPreviewItems.forEach(item => {
                item.classList.remove('selected');
            });

            // Nếu đã chọn một giá trị, highlight item tương ứng
            if (selectedValue) {
                const selectedItem = document.querySelector(`.preview-item img[data-id="${selectedValue}"]`).parentNode;
                selectedItem.classList.add('selected');
            }
        });
    });
</script>

<hr />
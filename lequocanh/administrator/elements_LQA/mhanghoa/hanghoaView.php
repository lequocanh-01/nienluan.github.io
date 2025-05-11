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
                                <img src="<?php echo $imageSrc; ?>" class="preview-img" data-id="<?php echo $img->id; ?>"
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
        // Lấy danh sách tất cả hình ảnh - số lượng thực tế trong DB
        $allImages = $hanghoaObj->GetAllHinhAnh();
        $totalImages = count($allImages);

        // Đếm số sản phẩm có hình ảnh (có giá trị hinhanh > 0)
        $productsWithImages = 0;
        foreach ($list_hanghoa as $product) {
            if (isset($product->hinhanh) && $product->hinhanh > 0) {
                $productsWithImages++;
            }
        }

        echo ' | Tổng số hình ảnh đã áp dụng: <b>' . $totalImages . '</b>';
        echo ' | Số sản phẩm có hình ảnh: <b>' . $productsWithImages . '/' . $l . '</b>';
        ?>
    </div>

    <?php
    // Include search box
    $searchFormId = 'product-search';
    $tableBodyId = 'product-list';
    $placeholderText = 'Tìm kiếm hàng hóa...';
    include './elements_LQA/includes/search-box.php';
    ?>

    <?php
    // Hiển thị thông báo về hình ảnh không khớp tên
    if (!empty($mismatched_images)) {
        echo '<div class="alert alert-warning">';
        echo '<div class="alert-header">';
        echo '<h4><i class="fas fa-exclamation-triangle"></i> Lưu ý: Có ' . count($mismatched_images) . ' sản phẩm có hình ảnh không khớp với tên sản phẩm</h4>';
        echo '</div>';
        echo '<ul class="mismatched-list">';
        foreach ($mismatched_images as $item) {
            echo '<li>';
            echo 'Sản phẩm "' . htmlspecialchars($item->tenhanghoa) . '" (ID: ' . $item->idhanghoa . ') ';
            echo 'đang sử dụng hình ảnh "' . htmlspecialchars($item->ten_file) . '" (ID: ' . $item->id . ') ';
            echo '</li>';
        }
        echo '</ul>';
        echo '<p><em>Lưu ý: Đây chỉ là thông báo, bạn có thể kiểm tra và sửa thủ công nếu cần.</em></p>';
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
        <tbody id="product-list">
            <?php
            if ($l > 0) {
                foreach ($list_hanghoa as $u) {
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u->idhanghoa); ?></td>
                        <td><?php echo htmlspecialchars($u->tenhanghoa); ?></td>
                        <td><?php echo number_format($u->giathamkhao, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($u->mota); ?></td>
                        <td align="center">
                            <?php
                            if (is_numeric($u->hinhanh) && $u->hinhanh > 0) {
                                // Sử dụng script displayImage khi hinhanh là ID
                                $imageSrc = "./elements_LQA/mhanghoa/displayImage.php?id=" . $u->hinhanh;
                            ?>
                                <img class="iconbutton" src="<?php echo $imageSrc; ?>" alt="Product Image"
                                    onerror="this.src='./img_LQA/no-image.png'">
                                <?php
                                // Không hiển thị số lượng hình ảnh ở đây vì mỗi sản phẩm chỉ có một hình ảnh chính
                                ?>
                            <?php
                            } else {
                                echo '<img class="iconbutton" src="./img_LQA/no-image.png" alt="No image">';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($u->ten_thuonghieu ?? 'Chưa chọn'); ?></td>
                        <td><?php echo htmlspecialchars($u->ten_donvitinh ?? 'Chưa chọn'); ?></td>
                        <td><?php echo htmlspecialchars($u->ten_nhanvien ?? 'Chưa chọn'); ?></td>
                        <td align="center">
                            <?php
                            if (isset($_SESSION['ADMIN'])) {
                            ?>
                                <a
                                    href="./elements_LQA/mhanghoa/hanghoaAct.php?reqact=deletehanghoa&idhanghoa=<?php echo $u->idhanghoa; ?>">
                                    <img src="./img_LQA/Delete.png" class="iconimg">
                                </a>
                            <?php
                            } else {
                            ?>
                                <img src="./img_LQA/Delete.png" class="iconimg">
                            <?php
                            }
                            ?>
                            <img src="./img_LQA/Update.png"
                                class="iconimg generic-update-btn"
                                data-module="mhanghoa"
                                data-update-url="./elements_LQA/mhanghoa/hanghoaUpdate.php"
                                data-id-param="idhanghoa"
                                data-title="Cập nhật Hàng hóa"
                                data-id="<?php echo htmlspecialchars($u->idhanghoa); ?>"
                                alt="Update">
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<hr />

<!-- Popup container cho cập nhật hàng hóa -->
<div id="w_update_hh">
    <div class="update-popup-wrapper">
        <span id="w_close_btn_hh">X</span>
        <div id="w_update_form_hh"></div>
    </div>
</div>

<style>
    #w_update_hh {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border: 2px solid #3498db;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border-radius: 5px;
        padding: 15px;
        z-index: 9999;
        display: none;
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .update-popup-wrapper {
        position: relative;
    }

    #w_close_btn_hh {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #f44336;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        text-align: center;
        line-height: 25px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
    }
</style>

<script>
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
                const selectedItem = document.querySelector(`.preview-item img[data-id="${selectedValue}"]`)
                    .parentNode;
                selectedItem.classList.add('selected');
            }
        });
    });
</script>

<script src="./js_LQA/test-search.js"></script>

<hr />
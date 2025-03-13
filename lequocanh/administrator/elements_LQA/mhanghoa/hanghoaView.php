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
?>

<head>
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
    <style>
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .content-table th {
            background-color: #15326b;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .content-table td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .content-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .iconbutton {
            max-width: 60px;
            max-height: 60px;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .update-form input,
        .update-form select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        /* Debug button */
        .debug-btn {
            background: #ff7700;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<div class="admin-form">
    <h3>Thêm hàng hóa mới</h3>
    <form name="newhanghoa" id="formaddhanghoa" method="post" action='./elements_LQA/mhanghoa/hanghoaAct.php?reqact=addnew' enctype="multipart/form-data">
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
                    <select name="id_hinhanh" id="addnew-image-select" required>
                        <option value="">-- Chọn hình ảnh --</option>
                        <?php
                        foreach ($list_hinhanh as $img) {
                        ?>
                            <option value="<?php echo $img->id; ?>" data-path="<?php echo './' . $img->duong_dan; ?>">
                                <?php echo htmlspecialchars($img->ten_file); ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                    <div id="add-image-preview" style="margin-top: 10px; max-width: 200px;"></div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const imageSelect = document.getElementById('addnew-image-select');
                            const imagePreview = document.getElementById('add-image-preview');

                            imageSelect.addEventListener('change', function() {
                                const selectedOption = this.options[this.selectedIndex];
                                if (selectedOption.value) {
                                    const imagePath = selectedOption.getAttribute('data-path');

                                    // Tạo phần tử img với xử lý lỗi
                                    const imgElement = document.createElement('img');
                                    imgElement.style.maxWidth = '200px';
                                    imgElement.style.border = '1px solid #ddd';
                                    imgElement.style.padding = '5px';
                                    imgElement.alt = 'Hình ảnh preview';

                                    // Xử lý khi hình ảnh không tải được
                                    imgElement.onerror = function() {
                                        console.log('Không thể tải hình ảnh từ:', imagePath);

                                        // Thử các đường dẫn khác
                                        if (this.src !== './img_LQA/' + imagePath.split('/').pop()) {
                                            this.src = './img_LQA/' + imagePath.split('/').pop();
                                        } else if (this.src !== './uploads/' + imagePath.split('/').pop()) {
                                            this.src = './uploads/' + imagePath.split('/').pop();
                                        } else {
                                            // Nếu tất cả các đường dẫn đều không tồn tại, hiển thị hình ảnh mặc định
                                            this.src = './img_LQA/no-image.png';
                                            console.log('Sử dụng hình ảnh mặc định cho:', imagePath);
                                        }
                                    };

                                    // Gán đường dẫn hình ảnh
                                    imgElement.src = imagePath;

                                    // Xóa nội dung cũ và thêm phần tử img mới
                                    imagePreview.innerHTML = '';
                                    imagePreview.appendChild(imgElement);
                                } else {
                                    imagePreview.innerHTML = '';
                                }
                            });

                            // Kích hoạt sự kiện change nếu có hình ảnh được chọn sẵn
                            if (imageSelect.selectedIndex > 0) {
                                imageSelect.dispatchEvent(new Event('change'));
                            }
                        });
                    </script>
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
    </div>

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
            foreach ($list_hanghoa as $hh) {
            ?>
                <tr>
                    <td><?php echo $hh->idhanghoa; ?></td>
                    <td><?php echo $hh->tenhanghoa; ?></td>
                    <td><?php echo number_format($hh->giathamkhao); ?> đ</td>
                    <td><?php echo $hh->mota; ?></td>
                    <td>
                        <?php if ($hh->hinhanh): ?>
                            <img src="<?php echo $hh->hinhanh; ?>" alt="<?php echo $hh->tenhanghoa; ?>" style="max-width: 100px; max-height: 100px; object-fit: contain;" onerror="this.src='img_LQA/no-image.png'">
                        <?php else: ?>
                            <img src="img_LQA/no-image.png" alt="No Image" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo $hh->tenTH; ?></td>
                    <td><?php echo $hh->tenDonViTinh; ?></td>
                    <td><?php echo $hh->tenNV; ?></td>
                    <td>
                        <a href="index.php?req=hanghoaupdate&idhanghoa=<?php echo $hh->idhanghoa; ?>">
                            <img src="img_LQA/edit.png" class="iconbutton" />
                        </a>
                        <a href="index.php?req=hanghoadelete&idhanghoa=<?php echo $hh->idhanghoa; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                            <img src="img_LQA/delete.png" class="iconbutton" />
                        </a>
                    </td>
                </tr>
            <?php
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
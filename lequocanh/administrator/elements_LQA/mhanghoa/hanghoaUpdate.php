<?php
require '../../elements_LQA/mod/hanghoaCls.php';
require '../../elements_LQA/mod/loaihangCls.php';
$idhanghoa = $_REQUEST['idhanghoa'];

$hanghoaObj = new hanghoa();
$getLhUpdate = $hanghoaObj->HanghoaGetbyId($idhanghoa);
$obj = new loaihang();
$list_lh = $obj->LoaihangGetAll();

// Fetch lists for employees, units of measurement, brands and images
$list_nhanvien = $hanghoaObj->GetAllNhanVien();
$list_donvitinh = $hanghoaObj->GetAllDonViTinh();
$list_thuonghieu = $hanghoaObj->GetAllThuongHieu();
$list_hinhanh = $hanghoaObj->GetAllHinhAnh();

// Get current image
$current_image = $hanghoaObj->GetHinhAnhById($getLhUpdate->hinhanh);
?>

<div class="update-form">
    <h3>Cập nhật hàng hóa</h3>
    <form name="updatehanghoa" id="formupdatehh" method="post"
        action='./elements_LQA/mhanghoa/hanghoaAct.php?reqact=updatehanghoa'>
        <input type="hidden" name="idhanghoa" value="<?php echo $getLhUpdate->idhanghoa; ?>" />

        <div class="form-group">
            <label>Tên hàng hóa:</label>
            <input type="text" name="tenhanghoa" value="<?php echo htmlspecialchars($getLhUpdate->tenhanghoa); ?>"
                required />
        </div>

        <div class="form-group">
            <label>Giá tham khảo:</label>
            <input type="number" name="giathamkhao" value="<?php echo $getLhUpdate->giathamkhao; ?>" required />
        </div>

        <div class="form-group">
            <label>Mô tả:</label>
            <input type="text" name="mota" value="<?php echo htmlspecialchars($getLhUpdate->mota); ?>" />
        </div>

        <div class="form-group">
            <label>Hình ảnh:</label>
            <div class="image-select-container">
                <select name="id_hinhanh" required id="imageSelector">
                    <option value="">-- Chọn hình ảnh --</option>
                    <?php foreach ($list_hinhanh as $img): ?>
                        <option value="<?php echo $img->id; ?>"
                            <?php echo ($img->id == $getLhUpdate->hinhanh) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($img->ten_file); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="selected-image-preview">
                    <?php
                    $imageSrc = "./elements_LQA/mhanghoa/displayImage.php?id=" . $getLhUpdate->hinhanh;
                    ?>
                    <img id="imagePreview" src="<?php echo $imageSrc; ?>"
                        alt="Hình ảnh sản phẩm"
                        onerror="this.src='./img_LQA/no-image.png'"
                        style="max-width: 100px; max-height: 100px; margin-top: 10px;">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Loại hàng:</label>
            <div class="radio-group">
                <?php foreach ($list_lh as $l): ?>
                    <label class="radio-item">
                        <input type="radio" name="idloaihang" value="<?php echo $l->idloaihang; ?>"
                            <?php echo ($l->idloaihang == $getLhUpdate->idloaihang) ? 'checked' : ''; ?> required>
                        <img class="iconbutton" src="data:image/png;base64,<?php echo $l->hinhanh; ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Thương hiệu:</label>
            <select name="idThuongHieu">
                <option value="">-- Chọn thương hiệu --</option>
                <?php foreach ($list_thuonghieu as $th): ?>
                    <option value="<?php echo $th->idThuongHieu; ?>"
                        <?php echo ($th->idThuongHieu == $getLhUpdate->idThuongHieu) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($th->tenTH); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Đơn vị tính:</label>
            <select name="idDonViTinh">
                <option value="">-- Chọn đơn vị tính --</option>
                <?php foreach ($list_donvitinh as $dvt): ?>
                    <option value="<?php echo $dvt->idDonViTinh; ?>"
                        <?php echo ($dvt->idDonViTinh == $getLhUpdate->idDonViTinh) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dvt->tenDonViTinh); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nhân viên:</label>
            <select name="idNhanVien">
                <option value="">-- Chọn nhân viên --</option>
                <?php foreach ($list_nhanvien as $nv): ?>
                    <option value="<?php echo $nv->idNhanVien; ?>"
                        <?php echo ($nv->idNhanVien == $getLhUpdate->idNhanVien) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nv->tenNV); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <input type="submit" value="Cập nhật" class="btn-update" />
            <input type="reset" value="Làm lại" class="btn-reset" />
        </div>
    </form>
</div>

<style>
    .update-form {
        padding: 20px;
        background: white;
        border-radius: 8px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .radio-item {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }

    .form-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .btn-update,
    .btn-reset {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-update {
        background-color: #007bff;
        color: white;
    }

    .btn-reset {
        background-color: #6c757d;
        color: white;
    }

    .btn-update:hover {
        background-color: #0056b3;
    }

    .btn-reset:hover {
        background-color: #5a6268;
    }

    .image-preview {
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 10px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
        max-height: 300px;
        overflow-y: auto;
    }

    .preview-item {
        border: 1px solid #eee;
        padding: 5px;
        border-radius: 4px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .preview-item:hover {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .preview-item.selected {
        border-color: #007bff;
        background-color: #f0f7ff;
    }

    .preview-img {
        width: 100%;
        height: 80px;
        object-fit: contain;
        border-radius: 4px;
    }

    .preview-info {
        margin-top: 5px;
        font-size: 12px;
    }

    .preview-name {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .preview-size {
        color: #666;
        font-size: 11px;
    }
</style>

<script>
    document.getElementById('imageSelector').addEventListener('change', function() {
        const imageId = this.value;
        if (imageId) {
            document.getElementById('imagePreview').src = "./elements_LQA/mhanghoa/displayImage.php?id=" + imageId;
        } else {
            document.getElementById('imagePreview').src = "./img_LQA/no-image.png";
        }
    });
</script>
<?php
require_once("./elements_LQA/mod/hanghoaCls.php");
$hanghoa = new hanghoa();
$list_hinhanh = $hanghoa->GetAllHinhAnh();
$total = count($list_hinhanh);
?>

<head>
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
</head>

<div class="admin-title">
    <h1>Quản lý hình ảnh</h1>
</div>

<div class="admin-content">
    <!-- Form upload hình ảnh -->
    <div class="upload-form">
        <h3>Thêm hình ảnh mới</h3>
        <form action="./elements_LQA/mhinhanh/hinhanhAct.php?reqact=addnew"
            method="post"
            enctype="multipart/form-data"
            class="mb-3">
            <div class="input-group">
                <input type="file"
                    name="files[]"
                    multiple
                    accept="image/*"
                    required
                    class="form-control">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload
                </button>
            </div>
        </form>
    </div>

    <!-- Hiển thị danh s��ch hình ảnh -->
    <div class="image-list">
        <div class="list-header">
            <h3>Danh sách hình ảnh (<?php echo $total; ?> hình ảnh)</h3>
            <button id="delete-selected" class="btn btn-danger" style="display: none;">
                <i class="fas fa-trash me-2"></i>Xóa đã chọn
            </button>
        </div>

        <table class="image-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Hình ảnh</th>
                    <th>Tên file</th>
                    <th>Đường dẫn</th>
                    <th>Loại file</th>
                    <th>Trạng thái</th>
                    <th>Ngày thêm</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_hinhanh as $img): ?>
                    <tr>
                        <td>
                            <input type="checkbox"
                                class="image-checkbox form-check-input"
                                data-id="<?php echo $img->id; ?>"
                                <?php
                                $products = $hanghoa->GetProductsByImageId($img->id);
                                echo !empty($products) ? 'disabled' : '';
                                ?>>
                        </td>
                        <td>
                            <?php
                            $image_src = $img->duong_dan;
                            // Kiểm tra các trường hợp đường dẫn
                            if (strpos($image_src, 'data:image') === 0) {
                                // Nếu là base64, giữ nguyên
                                $display_src = $image_src;
                            } else {
                                // Thử các đường dẫn khác nhau
                                $possible_paths = [
                                    "./uploads/" . basename($image_src),
                                    "../uploads/" . basename($image_src),
                                    "../../uploads/" . basename($image_src),
                                    $image_src,
                                    "./img_LQA/" . basename($image_src)
                                ];

                                $display_src = "./img_LQA/no-image.png"; // Đường dẫn mặc định
                                foreach ($possible_paths as $path) {
                                    if (file_exists($path)) {
                                        $display_src = $path;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <img src="<?php echo $display_src; ?>"
                                alt="<?php echo htmlspecialchars($img->ten_file); ?>"
                                class="preview-image"
                                onerror="this.src='./img_LQA/no-image.png'">
                        </td>
                        <td><?php echo htmlspecialchars($img->ten_file); ?></td>
                        <td><?php echo htmlspecialchars($img->duong_dan); ?></td>
                        <td><?php echo htmlspecialchars($img->loai_file); ?></td>
                        <td>
                            <?php
                            $products = $hanghoa->GetProductsByImageId($img->id);
                            if (!empty($products)) {
                                echo '<span class="badge bg-success">';
                                echo htmlspecialchars($products[0]['tenhanghoa']);
                                echo '</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Không</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($img->ngay_tao)); ?></td>
                        <td>
                            <button class="delete-btn" data-id="<?php echo $img->id; ?>"
                                <?php echo !empty($products) ? 'disabled' : ''; ?>>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="../../js_LQA/jscript.js"></script>
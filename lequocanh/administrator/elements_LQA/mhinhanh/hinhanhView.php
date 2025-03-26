<?php
require_once("./elements_LQA/mod/hanghoaCls.php");
$hanghoa = new hanghoa();
$list_hinhanh = $hanghoa->GetAllHinhAnh();
$total = count($list_hinhanh);

// Kiểm tra môi trường và cài đặt đường dẫn phù hợp
$isDocker = (getenv('DOCKER_ENV') !== false) || file_exists('/.dockerenv');
$uploadDirAbsolute = $isDocker ? '/var/www/html/administrator/uploads/' : 'D:/PHP_WS/lequocanh/administrator/uploads/';
$uploadPermissionOk = true;
$permissionWarning = "";

// Kiểm tra nếu thư mục không tồn tại
if (!file_exists($uploadDirAbsolute)) {
    // Kiểm tra nếu có thể tạo thư mục
    if (!is_writable(dirname($uploadDirAbsolute))) {
        $uploadPermissionOk = false;
        $permissionWarning = "Không thể tạo thư mục upload. Vui lòng đảm bảo thư mục <code>" . dirname($uploadDirAbsolute) . "</code> có quyền ghi.";
    } else {
        // Thử tạo thư mục
        if (!mkdir($uploadDirAbsolute, 0777, true)) {
            $uploadPermissionOk = false;
            $permissionWarning = "Không thể tạo thư mục upload ngay cả khi có quyền ghi trên thư mục cha.";
        } else {
            chmod($uploadDirAbsolute, 0777); // Cấp quyền đủ cho thư mục vừa tạo
        }
    }
} else {
    // Kiểm tra quyền ghi vào thư mục upload
    if (!is_writable($uploadDirAbsolute)) {
        $uploadPermissionOk = false;
        $permissionWarning = "Thư mục upload không có quyền ghi. Vui lòng cấp quyền ghi cho thư mục <code>" . $uploadDirAbsolute . "</code>";
    }
}
?>

<head>
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
</head>

<div class="admin-title">
    <h1>Quản lý hình ảnh</h1>
</div>

<div class="admin-content">
    <?php if (!$uploadPermissionOk): ?>
        <div class="alert alert-warning">
            <p><strong>Cảnh báo về quyền truy cập:</strong> <?php echo $permissionWarning; ?></p>
            <p>Việc tải lên hình ảnh có thể không hoạt động cho đến khi vấn đề này được giải quyết.</p>
        </div>
    <?php endif; ?>

    <!-- Hiển thị thông báo kết quả -->
    <?php if (isset($_GET['result'])): ?>
        <?php if ($_GET['result'] == 'ok'): ?>
            <div class="alert alert-success">
                <?php
                if (isset($_GET['count'])) {
                    echo 'Đã tải lên ' . $_GET['count'] . ' hình ảnh thành công.';
                } else {
                    echo 'Tải hình ảnh thành công.';
                }
                ?>
            </div>
        <?php elseif ($_GET['result'] == 'partial'): ?>
            <div class="alert alert-warning">
                Tải hình ảnh hoàn tất với một số cảnh báo:
                <?php echo $_GET['success']; ?> thành công,
                <?php echo $_GET['failed']; ?> thất bại.
                <?php if (isset($_SESSION['upload_errors']) && !empty($_SESSION['upload_errors'])): ?>
                    <ul>
                        <?php foreach ($_SESSION['upload_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php unset($_SESSION['upload_errors']); ?>
                <?php endif; ?>
            </div>
        <?php elseif ($_GET['result'] == 'notok'): ?>
            <div class="alert alert-danger">
                <p><strong>Tải hình ảnh không thành công.</strong> Vui lòng kiểm tra lỗi dưới đây và thử lại:</p>
                <?php if (isset($_SESSION['upload_errors']) && !empty($_SESSION['upload_errors'])): ?>
                    <ul>
                        <?php foreach ($_SESSION['upload_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php unset($_SESSION['upload_errors']); ?>
                <?php else: ?>
                    <p>Không có thông tin chi tiết về lỗi.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($_GET['result'] == 'nofiles'): ?>
            <div class="alert alert-warning">
                <p><strong>Không có file nào được tải lên.</strong></p>
                <?php if (isset($_SESSION['upload_errors']) && !empty($_SESSION['upload_errors'])): ?>
                    <ul>
                        <?php foreach ($_SESSION['upload_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php unset($_SESSION['upload_errors']); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['auto_applied_images']) && !empty($_SESSION['auto_applied_images'])): ?>
        <div class="alert alert-success">
            <p><strong>Đã tự động áp dụng hình ảnh cho các sản phẩm sau:</strong></p>
            <ul>
                <?php foreach ($_SESSION['auto_applied_images'] as $applied): ?>
                    <li>Hình <strong><?php echo htmlspecialchars($applied['image_name']); ?></strong>
                        đã áp dụng cho sản phẩm <strong><?php echo htmlspecialchars($applied['product_name']); ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['auto_applied_images']); ?>
    <?php endif; ?>

    <!-- Form upload hình ảnh -->
    <div class="admin-form">
        <h3>Upload hình ảnh</h3>

        <!-- Tùy chọn hiển thị -->
        <div class="option-panel">
            <div class="option-title">Tùy chọn áp dụng hình ảnh</div>
            <div class="option-item">
                <input type="checkbox" id="auto_apply_images" name="auto_apply_images" <?php echo (isset($_SESSION['auto_apply_images']) && $_SESSION['auto_apply_images']) ? 'checked' : ''; ?>>
                <label for="auto_apply_images">Tự động áp dụng hình ảnh cho sản phẩm khi tải lên</label>
                <div class="option-description">
                    <div>Khi bật: Hình ảnh sẽ tự động áp dụng cho sản phẩm có tên trùng khớp.</div>
                    <div>Khi tắt: Bạn cần nhấn nút "Áp dụng" để liên kết hình ảnh với sản phẩm.</div>
                </div>
                <button id="save_options" class="btn btn-primary">Lưu tùy chọn</button>
            </div>
        </div>

        <form method="post" action="elements_LQA/mhinhanh/hinhanhAct.php?reqact=addnew" enctype="multipart/form-data" id="uploadForm">
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

    <!-- Hiển thị danh sách hình ảnh -->
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
                            // Sử dụng đường dẫn chính xác đến file hiển thị ảnh
                            $display_src = './elements_LQA/mhanghoa/displayImage.php?id=' . $img->id;
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

<script>
    // Xử lý lưu tùy chọn
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý sự kiện lưu tùy chọn
        document.getElementById('save_options').addEventListener('click', function(e) {
            e.preventDefault();
            const autoApply = document.getElementById('auto_apply_images').checked;

            // Gửi AJAX để lưu tùy chọn
            fetch('elements_LQA/mhinhanh/saveOptions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'auto_apply_images=' + (autoApply ? '1' : '0')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hiển thị thông báo thành công
                        alert('Đã lưu tùy chọn thành công!');
                    } else {
                        // Hiển thị thông báo lỗi
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi lưu tùy chọn!');
                });
        });
    });
</script>
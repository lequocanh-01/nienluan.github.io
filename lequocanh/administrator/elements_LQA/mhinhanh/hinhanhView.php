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
        <?php elseif ($_GET['result'] == 'duplicates'): ?>
            <?php if (isset($_SESSION['duplicate_images']) && !empty($_SESSION['duplicate_images'])): ?>
                <div class="alert alert-warning alert-with-icon">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-content">
                        <h4 class="alert-heading">Phát hiện ảnh trùng lặp!</h4>
                        <p>Hệ thống đã phát hiện ảnh trùng lặp cho một số sản phẩm. Vui lòng chọn giữa việc sử dụng ảnh mới hoặc giữ nguyên ảnh hiện tại.</p>
                    </div>
                </div>

                <div class="duplicate-images-container">
                    <?php foreach ($_SESSION['duplicate_images'] as $index => $duplicate): ?>
                        <?php
                        // Lấy thông tin ảnh hiện có
                        $existingImage = $hanghoa->GetHinhAnhById($duplicate['existing_image_id']);
                        ?>
                        <div class="duplicate-image-item" data-index="<?php echo $index; ?>">
                            <h5>
                                <i class="fas fa-image"></i>
                                Ảnh cho sản phẩm: <span class="product-name"><?php echo htmlspecialchars($duplicate['product_name']); ?></span>
                            </h5>

                            <div class="image-comparison">
                                <div class="existing-image">
                                    <h6><i class="fas fa-history"></i> Ảnh hiện tại</h6>
                                    <div class="image-wrapper">
                                        <?php if (!empty($duplicate['existing_image_id'])): ?>
                                            <img src="./elements_LQA/mhanghoa/displayImage.php?id=<?php echo $duplicate['existing_image_id']; ?>&t=<?php echo time(); ?>"
                                                alt="Ảnh hiện tại"
                                                class="preview-image"
                                                onerror="this.onerror=null; this.src='./elements_LQA/img_LQA/no-image.png';">
                                        <?php else: ?>
                                            <div class="no-image">Không có ảnh</div>
                                        <?php endif; ?>
                                    </div>
                                    <p><i class="fas fa-file"></i>
                                        <?php
                                        if (isset($duplicate['existing_image_info']) && $duplicate['existing_image_info']) {
                                            echo htmlspecialchars($duplicate['existing_image_info']->ten_file);
                                        } else if (isset($existingImage->ten_file)) {
                                            echo htmlspecialchars($existingImage->ten_file);
                                        } else {
                                            echo "Không có thông tin";
                                        }
                                        ?>
                                    </p>
                                </div>

                                <div class="new-image">
                                    <h6><i class="fas fa-upload"></i> Ảnh mới tải lên</h6>
                                    <div class="image-wrapper">
                                        <?php if (!empty($duplicate['relative_path'])): ?>
                                            <!-- Debug data -->
                                            <div class="debug-info" style="display: none;">
                                                <pre><?php print_r($duplicate); ?></pre>
                                            </div>
                                            <?php
                                            // Thử nhiều cách khác nhau để định vị ảnh
                                            $relativePath = $duplicate['relative_path'];
                                            $ts = isset($duplicate['upload_timestamp']) ? $duplicate['upload_timestamp'] : time();
                                            $imagePath1 = $duplicate['new_image_path'] . '?t=' . $ts;  // Đường dẫn gốc + timestamp
                                            $imagePath2 = '../../' . $relativePath . '?t=' . $ts;      // Đường dẫn tương đối + timestamp
                                            $imagePath3 = '../../../' . $relativePath . '?t=' . $ts;   // Thử đường dẫn khác
                                            ?>
                                            <img src="<?php echo $imagePath1; ?>"
                                                data-alt-src1="<?php echo $imagePath2; ?>"
                                                data-alt-src2="<?php echo $imagePath3; ?>"
                                                alt="Ảnh mới"
                                                class="preview-image dynamic-image"
                                                onerror="this.onerror=null; handleImageError(this);">
                                            <button type="button" class="btn btn-sm btn-info show-debug">
                                                <i class="fas fa-bug"></i> Debug
                                            </button>
                                        <?php else: ?>
                                            <div class="no-image">Không có ảnh</div>
                                        <?php endif; ?>
                                    </div>
                                    <p><i class="fas fa-file"></i> <?php echo htmlspecialchars($duplicate['new_image_name']); ?></p>
                                </div>
                            </div>

                            <div class="image-actions">
                                <button class="btn btn-primary use-new-image" data-index="<?php echo $index; ?>">
                                    <i class="fas fa-check"></i> Sử dụng ảnh mới
                                </button>
                                <button class="btn btn-secondary use-existing-image" data-index="<?php echo $index; ?>">
                                    <i class="fas fa-undo"></i> Giữ ảnh hiện tại
                                </button>
                            </div>

                            <div class="processing-overlay">
                                <div class="spinner-container">
                                    <div class="spinner"></div>
                                    <p>Đang xử lý...</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['matched_images']) && !empty($_SESSION['matched_images'])): ?>
        <div class="alert alert-info">
            <p><strong>Kết quả khớp hình ảnh:</strong></p>
            <ul>
                <?php foreach ($_SESSION['matched_images'] as $matched): ?>
                    <li>
                        <?php if (isset($matched['duplicate']) && $matched['duplicate']): ?>
                            Hình ảnh <strong><?php echo htmlspecialchars($matched['image_name']); ?></strong>
                            đã tồn tại trong hệ thống và được áp dụng cho sản phẩm
                            <strong><?php echo htmlspecialchars($matched['product_name']); ?></strong>
                        <?php else: ?>
                            Hình ảnh <strong><?php echo htmlspecialchars($matched['image_name']); ?></strong>
                            được khớp với sản phẩm <strong><?php echo htmlspecialchars($matched['product_name']); ?></strong>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['matched_images']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['resolved_images']) && !empty($_SESSION['resolved_images'])): ?>
        <div class="alert alert-success">
            <p><strong>Đã xử lý ảnh trùng lặp:</strong></p>
            <ul>
                <?php foreach ($_SESSION['resolved_images'] as $resolved): ?>
                    <li>
                        <?php if ($resolved['action'] === 'used_new'): ?>
                            Đã sử dụng ảnh mới <strong><?php echo htmlspecialchars($resolved['image_name']); ?></strong>
                            cho sản phẩm <strong><?php echo htmlspecialchars($resolved['product_name']); ?></strong>
                        <?php else: ?>
                            Đã giữ nguyên ảnh hiện tại cho sản phẩm <strong><?php echo htmlspecialchars($resolved['product_name']); ?></strong>
                            (bỏ qua ảnh mới <strong><?php echo htmlspecialchars($resolved['image_name']); ?></strong>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['resolved_images']); ?>
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
                    <th>Hash</th>
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
                                onerror="this.src='./elements_LQA/img_LQA/no-image.png'">
                        </td>
                        <td><?php echo htmlspecialchars($img->ten_file); ?></td>
                        <td><?php echo htmlspecialchars($img->duong_dan); ?></td>
                        <td><?php echo htmlspecialchars($img->loai_file); ?></td>
                        <td><?php echo htmlspecialchars($img->file_hash); ?></td>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo tải ảnh động
        document.querySelectorAll('.dynamic-image').forEach(function(img) {
            // Thêm sự kiện để xử lý lỗi tải ảnh
            img.addEventListener('error', function() {
                if (!this.dataset.autoHandled) {
                    this.dataset.autoHandled = 'true';
                    handleImageError(this);
                }
            });

            // Log khi ảnh tải thành công
            img.addEventListener('load', function() {
                console.log('Image successfully loaded: ' + this.src);
            });
        });

        // Xử lý nút debug
        document.querySelectorAll('.show-debug').forEach(function(button) {
            button.addEventListener('click', function() {
                const debugInfo = this.closest('.image-wrapper').querySelector('.debug-info');
                if (debugInfo.style.display === 'none') {
                    debugInfo.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Debug';
                } else {
                    debugInfo.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-bug"></i> Debug';
                }
            });
        });

        // Xử lý nút sử dụng ảnh mới
        document.querySelectorAll('.use-new-image').forEach(function(button) {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const item = this.closest('.duplicate-image-item');

                // Hiển thị overlay loading
                item.classList.add('processing');

                processDuplicateImage('use_new', index, this);
            });
        });

        // Xử lý nút giữ ảnh hiện tại
        document.querySelectorAll('.use-existing-image').forEach(function(button) {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const item = this.closest('.duplicate-image-item');

                // Hiển thị overlay loading
                item.classList.add('processing');

                processDuplicateImage('use_existing', index, this);
            });
        });

        // Hàm xử lý ảnh trùng lặp
        function processDuplicateImage(action, index, button) {
            // Gửi yêu cầu AJAX để xử lý
            fetch(`./elements_LQA/mhinhanh/hinhanhAct.php?reqact=resolve_duplicate&action=${action}&index=${index}`)
                .then(response => response.json())
                .then(data => {
                    const item = button.closest('.duplicate-image-item');

                    // Ẩn overlay loading
                    item.classList.remove('processing');

                    if (data.success) {
                        // Nếu thành công, hiển thị kết quả
                        item.classList.add('processed');

                        // Thêm badge thông báo
                        const badge = document.createElement('div');
                        badge.className = 'result-badge ' + (action === 'use_new' ? 'success' : 'info');
                        badge.innerHTML = `<i class="fas ${action === 'use_new' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${data.message}`;
                        item.appendChild(badge);

                        // Kiểm tra nếu không còn ảnh trùng lặp nào, tải lại trang
                        const remainingItems = document.querySelectorAll('.duplicate-image-item:not(.processed)').length;

                        if (remainingItems === 0) {
                            setTimeout(() => {
                                const processingComplete = document.createElement('div');
                                processingComplete.className = 'alert alert-success mt-4';
                                processingComplete.innerHTML = '<p><i class="fas fa-check-circle"></i> <strong>Đã xử lý tất cả ảnh trùng lặp!</strong> Đang chuyển hướng...</p>';
                                document.querySelector('.duplicate-images-container').appendChild(processingComplete);

                                setTimeout(() => {
                                    window.location.href = '../../index.php?req=hinhanhview&result=ok';
                                }, 1500);
                            }, 500);
                        }
                    } else {
                        // Nếu thất bại, hiển thị thông báo lỗi
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'alert alert-danger mt-2';
                        errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> <strong>Lỗi:</strong> ${data.message}`;
                        item.appendChild(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    const item = button.closest('.duplicate-image-item');

                    // Ẩn overlay loading
                    item.classList.remove('processing');

                    // Hiển thị thông báo lỗi
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger mt-2';
                    errorMsg.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <strong>Lỗi kết nối:</strong> Vui lòng thử lại`;
                    item.appendChild(errorMsg);
                });
        }

        function handleImageError(img) {
            console.error('Failed to load image: ' + img.src);

            // Thử tải với đường dẫn thay thế 1
            if (img.dataset.altSrc1 && !img.dataset.tried1) {
                img.dataset.tried1 = 'true';
                console.log('Trying alternative path 1: ' + img.dataset.altSrc1);
                img.src = img.dataset.altSrc1;
                return;
            }

            // Thử tải với đường dẫn thay thế 2
            if (img.dataset.altSrc2 && !img.dataset.tried2) {
                img.dataset.tried2 = 'true';
                console.log('Trying alternative path 2: ' + img.dataset.altSrc2);
                img.src = img.dataset.altSrc2;
                return;
            }

            // Nếu tất cả các đường dẫn đều thất bại, hiển thị ảnh mặc định
            console.log('All paths failed, showing default image');
            img.src = './elements_LQA/img_LQA/no-image.png';

            // Hiển thị thông báo lỗi
            const errorMsg = document.createElement('div');
            errorMsg.className = 'image-error-message';
            errorMsg.innerHTML = '<small class="text-danger">Không thể tải hình ảnh. Vui lòng kiểm tra đường dẫn.</small>';
            img.parentNode.appendChild(errorMsg);
        }
    });
</script>
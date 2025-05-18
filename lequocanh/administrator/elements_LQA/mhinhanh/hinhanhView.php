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
    <style>
    /* Cải thiện giao diện xử lý ảnh trùng lặp */
    .duplicate-image-item {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        position: relative;
        background-color: #f9f9f9;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .duplicate-image-item.processed {
        background-color: #f0f8ff;
        border-color: #b8daff;
    }

    .image-comparison {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin: 15px 0;
    }

    .existing-image,
    .new-image {
        flex: 1;
        min-width: 250px;
        border: 1px solid #eee;
        border-radius: 5px;
        padding: 10px;
        background-color: white;
    }

    .image-wrapper {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        position: relative;
        overflow: hidden;
    }

    .preview-image {
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
    }

    .image-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .processing-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .duplicate-image-item.processing .processing-overlay {
        display: flex;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .result-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
    }

    .result-badge.success {
        background-color: #d4edda;
        color: #155724;
    }

    .result-badge.info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .result-badge.error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .duplicate-actions {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #eee;
    }

    .debug-info {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        padding: 5px;
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }

    /* Ẩn các phần tử đã xử lý */
    .all-processed .duplicate-warning-alert {
        display: none !important;
    }

    .all-processed .duplicate-image-item {
        display: none !important;
    }

    .all-processed .duplicate-images-container {
        display: none !important;
    }

    /* Đảm bảo thông báo "Đã xử lý ảnh trùng lặp" vẫn hiển thị */
    .all-processed .alert-success {
        display: block !important;
    }

    .all-processed .resolved-images-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
    }

    /* Cải thiện giao diện nút xóa */
    .delete-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .delete-btn:hover {
        background-color: #c82333;
    }

    .delete-btn:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
    }

    /* Cải thiện giao diện checkbox */
    .image-checkbox {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    /* Cải thiện giao diện nút xóa đã chọn */
    #delete-selected {
        margin-left: 10px;
        transition: all 0.3s ease;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Hiển thị hàng chứa hình ảnh đang được sử dụng */
    .image-in-use {
        background-color: rgba(255, 243, 205, 0.3);
    }

    /* Hiển thị checkbox của hình ảnh đang được sử dụng */
    .image-in-use .image-checkbox {
        cursor: not-allowed;
        opacity: 0.6;
    }
    </style>
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
    <div class="alert alert-warning alert-with-icon" id="duplicate-warning-alert">
        <i class="fas fa-exclamation-triangle alert-icon"></i>
        <div class="alert-content">
            <h4 class="alert-heading">Phát hiện ảnh trùng lặp!</h4>
            <p>Hệ thống đã phát hiện ảnh trùng lặp cho một số sản phẩm. Vui lòng chọn giữa việc sử dụng ảnh mới hoặc giữ
                nguyên ảnh hiện tại.</p>
        </div>
    </div>

    <div class="duplicate-actions">
        <button id="process-all-new" class="btn btn-success mb-3">
            <i class="fas fa-check-double"></i> Sử dụng tất cả ảnh mới
        </button>
        <button id="process-all-existing" class="btn btn-secondary mb-3 ml-2" style="margin-left: 10px;">
            <i class="fas fa-undo-alt"></i> Giữ tất cả ảnh hiện tại
        </button>
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
                Ảnh cho sản phẩm: <span
                    class="product-name"><?php echo htmlspecialchars($duplicate['product_name']); ?></span>
            </h5>

            <div class="image-comparison">
                <div class="existing-image">
                    <h6><i class="fas fa-history"></i> Ảnh hiện tại</h6>
                    <div class="image-wrapper">
                        <?php if (!empty($duplicate['existing_image_id'])): ?>
                        <img src="./elements_LQA/mhanghoa/displayImage.php?id=<?php echo $duplicate['existing_image_id']; ?>&t=<?php echo time(); ?>"
                            alt="Ảnh hiện tại" class="preview-image"
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
                        <img src="<?php echo $imagePath1; ?>" data-alt-src1="<?php echo $imagePath2; ?>"
                            data-alt-src2="<?php echo $imagePath3; ?>" alt="Ảnh mới" class="preview-image dynamic-image"
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
                Đã giữ nguyên ảnh hiện tại cho sản phẩm
                <strong><?php echo htmlspecialchars($resolved['product_name']); ?></strong>
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

        <form method="post" action="elements_LQA/mhinhanh/hinhanhAct.php?reqact=addnew" enctype="multipart/form-data"
            id="uploadForm">
            <div class="input-group">
                <input type="file" name="files[]" multiple accept="image/*" required class="form-control">
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
                        <?php
                            $products = $hanghoa->GetProductsByImageId($img->id);
                            $isInUse = !empty($products);
                        ?>
                        <input type="checkbox" class="image-checkbox form-check-input"
                            data-id="<?php echo $img->id; ?>"
                            <?php echo $isInUse ? 'data-in-use="true"' : ''; ?>>
                    </td>
                    <td>
                        <?php
                            // Sử dụng đường dẫn chính xác đến file hiển thị ảnh
                            $display_src = './elements_LQA/mhanghoa/displayImage.php?id=' . $img->id . '&t=' . time();

                            // Kiểm tra xem hình ảnh có đường dẫn hợp lệ không
                            if (!empty($img->duong_dan)) {
                                // Hiển thị hình ảnh từ displayImage.php
                                echo '<img src="' . $display_src . '"
                                    alt="' . htmlspecialchars($img->ten_file) . '"
                                    class="preview-image"
                                    onerror="this.onerror=null; handleImageError(this);"
                                    data-id="' . $img->id . '">';
                            } else {
                                // Hiển thị hình ảnh mặc định nếu không có đường dẫn
                                echo '<img src="./elements_LQA/img_LQA/no-image.png"
                                    alt="No image"
                                    class="preview-image">';
                            }
                            ?>
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
                        <button class="delete-btn" data-id="<?php echo $img->id; ?>">
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
    // Kiểm tra nếu không còn ảnh trùng lặp nào nhưng vẩn có thông báo đã xử lý
    const duplicateImages = document.querySelectorAll('.duplicate-image-item');
    const resolvedImagesElements = document.querySelectorAll('.alert-success strong');
    let hasResolvedImages = false;

    // Kiểm tra nếu có thông báo "Đã xử lý ảnh trùng lặp"
    resolvedImagesElements.forEach(function(el) {
        if (el.textContent.includes('Đã xử lý ảnh trùng lặp')) {
            hasResolvedImages = true;
        }
    });

    if (duplicateImages.length === 0 && hasResolvedImages) {
        // Thêm class để ẩn các phần tử đã xử lý
        document.querySelector('.admin-content').classList.add('all-processed');

        // Ẩn thông báo cảnh báo trùng lặp
        const warningAlert = document.getElementById('duplicate-warning-alert');
        if (warningAlert) {
            warningAlert.style.display = 'none';
        }
    }

    // Xử lý nút "Sử dụng tất cả ảnh mới"
    const processAllNewBtn = document.getElementById('process-all-new');
    if (processAllNewBtn) {
        processAllNewBtn.addEventListener('click', function() {
            // Vô hiệu hóa nút để tránh nhấn nhiều lần
            this.disabled = true;
            document.getElementById('process-all-existing').disabled = true;

            // Thêm lớp loading cho nút
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            // Lấy tất cả các item chưa được xử lý
            const items = document.querySelectorAll('.duplicate-image-item:not(.processed)');
            const totalItems = items.length;

            if (totalItems === 0) return;

            // Hiển thị thông báo đang xử lý
            const processingMsg = document.createElement('div');
            processingMsg.className = 'alert alert-info mt-3';
            processingMsg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý tất cả ảnh...';
            document.querySelector('.duplicate-actions').appendChild(processingMsg);

            // Xử lý từng item bằng Promise.all để tăng tốc
            const promises = [];

            items.forEach(item => {
                const index = item.getAttribute('data-index');
                const url =
                    `./elements_LQA/mhinhanh/hinhanhAct.php?reqact=resolve_duplicate&action=use_new&index=${index}&ajax=1`;

                // Thêm hiệu ứng loading cho item
                item.classList.add('processing');

                // Tạo promise cho mỗi request
                const promise = fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Cập nhật UI cho item
                        item.classList.remove('processing');
                        item.classList.add('processed');

                        // Thêm badge thông báo
                        const badge = document.createElement('div');
                        badge.className = 'result-badge success';
                        badge.innerHTML =
                            `<i class="fas fa-check-circle"></i> ${data.message}`;
                        item.appendChild(badge);

                        return data;
                    })
                    .catch(error => {
                        console.error('Error processing item:', error);
                        item.classList.remove('processing');

                        // Thêm badge lỗi
                        const badge = document.createElement('div');
                        badge.className = 'result-badge error';
                        badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> Lỗi`;
                        item.appendChild(badge);

                        return {
                            success: false,
                            error: error.message
                        };
                    });

                promises.push(promise);
            });

            // Xử lý khi tất cả các promise hoàn thành
            Promise.all(promises)
                .then(results => {
                    // Xóa thông báo đang xử lý
                    processingMsg.remove();

                    // Đếm số lượng thành công
                    const successCount = results.filter(r => r.success).length;

                    // Hiển thị thông báo kết quả
                    const resultMsg = document.createElement('div');
                    resultMsg.className = 'alert alert-success mt-3';
                    resultMsg.innerHTML =
                        `<i class="fas fa-check-circle"></i> Đã xử lý ${successCount}/${totalItems} ảnh thành công.`;
                    document.querySelector('.duplicate-actions').appendChild(resultMsg);

                    // Ẩn các phần tử đã xử lý
                    document.querySelector('.admin-content').classList.add('all-processed');

                    // Ẩn thông báo cảnh báo trùng lặp
                    const warningAlert = document.getElementById('duplicate-warning-alert');
                    if (warningAlert) {
                        warningAlert.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error processing all items:', error);

                    // Hiển thị thông báo lỗi
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger mt-3';
                    errorMsg.innerHTML =
                        `<i class="fas fa-exclamation-circle"></i> Lỗi khi xử lý tất cả ảnh: ${error.message}`;
                    document.querySelector('.duplicate-actions').appendChild(errorMsg);

                    // Kích hoạt lại các nút
                    this.disabled = false;
                    document.getElementById('process-all-existing').disabled = false;
                    this.innerHTML = '<i class="fas fa-check-double"></i> Sử dụng tất cả ảnh mới';
                });
        });
    }

    // Xử lý nút "Giữ tất cả ảnh hiện tại"
    const processAllExistingBtn = document.getElementById('process-all-existing');
    if (processAllExistingBtn) {
        processAllExistingBtn.addEventListener('click', function() {
            // Vô hiệu hóa nút để tránh nhấn nhiều lần
            this.disabled = true;
            document.getElementById('process-all-new').disabled = true;

            // Thêm lớp loading cho nút
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            // Lấy tất cả các item chưa được xử lý
            const items = document.querySelectorAll('.duplicate-image-item:not(.processed)');
            const totalItems = items.length;

            if (totalItems === 0) return;

            // Hiển thị thông báo đang xử lý
            const processingMsg = document.createElement('div');
            processingMsg.className = 'alert alert-info mt-3';
            processingMsg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý tất cả ảnh...';
            document.querySelector('.duplicate-actions').appendChild(processingMsg);

            // Xử lý từng item bằng Promise.all để tăng tốc
            const promises = [];

            items.forEach(item => {
                const index = item.getAttribute('data-index');
                const url =
                    `./elements_LQA/mhinhanh/hinhanhAct.php?reqact=resolve_duplicate&action=use_existing&index=${index}&ajax=1`;

                // Thêm hiệu ứng loading cho item
                item.classList.add('processing');

                // Tạo promise cho mỗi request
                const promise = fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Cập nhật UI cho item
                        item.classList.remove('processing');
                        item.classList.add('processed');

                        // Thêm badge thông báo
                        const badge = document.createElement('div');
                        badge.className = 'result-badge info';
                        badge.innerHTML =
                            `<i class="fas fa-info-circle"></i> ${data.message}`;
                        item.appendChild(badge);

                        return data;
                    })
                    .catch(error => {
                        console.error('Error processing item:', error);
                        item.classList.remove('processing');

                        // Thêm badge lỗi
                        const badge = document.createElement('div');
                        badge.className = 'result-badge error';
                        badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> Lỗi`;
                        item.appendChild(badge);

                        return {
                            success: false,
                            error: error.message
                        };
                    });

                promises.push(promise);
            });

            // Xử lý khi tất cả các promise hoàn thành
            Promise.all(promises)
                .then(results => {
                    // Xóa thông báo đang xử lý
                    processingMsg.remove();

                    // Đếm số lượng thành công
                    const successCount = results.filter(r => r.success).length;

                    // Hiển thị thông báo kết quả
                    const resultMsg = document.createElement('div');
                    resultMsg.className = 'alert alert-success mt-3';
                    resultMsg.innerHTML =
                        `<i class="fas fa-check-circle"></i> Đã xử lý ${successCount}/${totalItems} ảnh thành công.`;
                    document.querySelector('.duplicate-actions').appendChild(resultMsg);

                    // Ẩn các phần tử đã xử lý
                    document.querySelector('.admin-content').classList.add('all-processed');

                    // Ẩn thông báo cảnh báo trùng lặp
                    const warningAlert = document.getElementById('duplicate-warning-alert');
                    if (warningAlert) {
                        warningAlert.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error processing all items:', error);

                    // Hiển thị thông báo lỗi
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger mt-3';
                    errorMsg.innerHTML =
                        `<i class="fas fa-exclamation-circle"></i> Lỗi khi xử lý tất cả ảnh: ${error.message}`;
                    document.querySelector('.duplicate-actions').appendChild(errorMsg);

                    // Kích hoạt lại các nút
                    this.disabled = false;
                    document.getElementById('process-all-new').disabled = false;
                    this.innerHTML = '<i class="fas fa-undo-alt"></i> Giữ tất cả ảnh hiện tại';
                });
        });
    }

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

    // Hàm xử lý ảnh trùng lặp - phiên bản tối ưu
    function processDuplicateImage(action, index, button) {
        console.log(`Xử lý ảnh trùng lặp: action=${action}, index=${index}`);

        // Hiển thị thông báo đang xử lý
        const item = button.closest('.duplicate-image-item');

        // Hiển thị ngay lập tức phản hồi UI để người dùng biết đã nhấn nút
        item.classList.add('processing');

        // Vô hiệu hóa các nút để tránh nhấn nhiều lần
        const allButtons = item.querySelectorAll('button');
        allButtons.forEach(btn => btn.disabled = true);

        // Thêm badge thông báo ngay lập tức để cải thiện UX
        const badge = document.createElement('div');
        badge.className = 'result-badge ' + (action === 'use_new' ? 'success' : 'info');
        badge.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Đang xử lý...`;
        item.appendChild(badge);

        // Gửi yêu cầu AJAX để xử lý - sử dụng timeout ngắn hơn
        const url =
            `./elements_LQA/mhinhanh/hinhanhAct.php?reqact=resolve_duplicate&action=${action}&index=${index}&ajax=1`;

        // Sử dụng AbortController để có thể hủy request nếu mất quá nhiều thời gian
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 giây timeout

        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Cập nhật UI ngay lập tức
                item.classList.remove('processing');
                item.classList.add('processed');

                // Cập nhật badge thông báo
                badge.innerHTML =
                    `<i class="fas ${action === 'use_new' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${data.message}`;

                // Kiểm tra nếu không còn ảnh trùng lặp nào, ẩn thông báo và khung xử lý trùng lặp
                const remainingItems = document.querySelectorAll('.duplicate-image-item:not(.processed)')
                    .length;

                if (remainingItems === 0) {
                    // Thêm class để ẩn các phần tử đã xử lý ngay lập tức
                    document.querySelector('.admin-content').classList.add('all-processed');

                    // Ẩn thông báo cảnh báo trùng lặp ngay lập tức
                    const warningAlert = document.getElementById('duplicate-warning-alert');
                    if (warningAlert) {
                        warningAlert.style.display = 'none';
                    }

                    // Thêm thông báo hoàn tất
                    const processingComplete = document.createElement('div');
                    processingComplete.className = 'alert alert-success mt-4';
                    processingComplete.innerHTML =
                        '<p><i class="fas fa-check-circle"></i> <strong>Đã xử lý tất cả ảnh trùng lặp!</strong></p>';
                    document.querySelector('.duplicate-images-container').appendChild(processingComplete);
                }
            })
            .catch(error => {
                // Xóa timeout nếu có lỗi
                clearTimeout(timeoutId);

                // Ẩn overlay loading
                item.classList.remove('processing');

                // Kích hoạt lại các nút
                allButtons.forEach(btn => btn.disabled = false);

                // Cập nhật badge thành thông báo lỗi
                badge.className = 'result-badge error';
                badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> Lỗi`;

                // Hiển thị thông báo lỗi chi tiết
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger mt-2';
                errorMsg.innerHTML =
                    `<i class="fas fa-exclamation-circle"></i> <strong>Lỗi:</strong> ${error.message || 'Không thể xử lý yêu cầu'}`;
                item.appendChild(errorMsg);

                // Thêm nút thử lại
                const retryBtn = document.createElement('button');
                retryBtn.className = 'btn btn-sm btn-warning mt-2';
                retryBtn.innerHTML = '<i class="fas fa-redo"></i> Thử lại';
                retryBtn.addEventListener('click', function() {
                    // Xóa thông báo lỗi và badge
                    errorMsg.remove();
                    badge.remove();
                    this.remove();

                    // Thử lại
                    processDuplicateImage(action, index, button);
                });
                item.appendChild(retryBtn);
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

        // Nếu có ID hình ảnh, thử tải trực tiếp từ displayImage.php
        if (img.dataset.id && !img.dataset.triedDisplay) {
            img.dataset.triedDisplay = 'true';
            const newSrc = './elements_LQA/mhanghoa/displayImage.php?id=' + img.dataset.id + '&t=' + new Date()
                .getTime();
            console.log('Trying direct displayImage path: ' + newSrc);
            img.src = newSrc;
            return;
        }

        // Nếu tất cả các đường dẫn đều thất bại, hiển thị ảnh mặc định
        console.log('All paths failed, showing default image');
        img.src = './elements_LQA/img_LQA/no-image.png';

        // Hiển thị thông báo lỗi
        const errorMsg = document.createElement('div');
        errorMsg.className = 'image-error-message';
        errorMsg.innerHTML =
            '<small class="text-danger">Không thể tải hình ảnh. Vui lòng kiểm tra đường dẫn.</small>';
        img.parentNode.appendChild(errorMsg);
    }

    // Xử lý nút xóa hình ảnh
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-id');
            if (!imageId) return;

            if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này không?')) {
                // Hiển thị loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;

                // Gửi yêu cầu xóa
                const formData = new FormData();
                formData.append('id', imageId);

                fetch('./elements_LQA/mhinhanh/hinhanhAct.php?reqact=deleteimage', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa thành công, xóa hàng khỏi bảng
                            const row = this.closest('tr');
                            row.style.backgroundColor = '#ffdddd';
                            setTimeout(() => {
                                row.remove();

                                // Cập nhật số lượng hình ảnh
                                const totalElement = document.querySelector(
                                    '.list-header h3');
                                if (totalElement) {
                                    const currentCount = document.querySelectorAll(
                                        '.image-table tbody tr').length;
                                    totalElement.textContent =
                                        `Danh sách hình ảnh (${currentCount} hình ảnh)`;
                                }

                                // Hiển thị thông báo thành công
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success';
                                alertDiv.innerHTML =
                                    '<i class="fas fa-check-circle"></i> Đã xóa hình ảnh thành công.';
                                document.querySelector('.admin-content')
                                    .insertBefore(
                                        alertDiv, document.querySelector(
                                            '.admin-content')
                                        .firstChild);

                                // Tự động ẩn thông báo sau 3 giây
                                setTimeout(() => {
                                    alertDiv.remove();
                                }, 3000);
                            }, 500);
                        } else {
                            // Xóa thất bại, hiển thị thông báo lỗi
                            if (data.inUse) {
                                // Hiển thị thông báo lỗi chi tiết nếu hình ảnh đang được sử dụng
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-danger';
                                alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> <strong>Không thể xóa:</strong> ${data.message}`;
                                document.querySelector('.admin-content')
                                    .insertBefore(
                                        alertDiv, document.querySelector(
                                            '.admin-content')
                                        .firstChild);

                                // Đánh dấu hàng có hình ảnh đang được sử dụng
                                const row = this.closest('tr');
                                row.style.backgroundColor = '#fff3cd';

                                // Tự động ẩn thông báo sau 5 giây
                                setTimeout(() => {
                                    alertDiv.remove();
                                    row.style.backgroundColor = '';
                                }, 5000);
                            } else {
                                // Thông báo lỗi thông thường
                                alert('Lỗi: ' + data.message);
                            }
                            this.innerHTML = '<i class="fas fa-trash"></i>';
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Đã xảy ra lỗi khi xóa hình ảnh. Vui lòng thử lại.');
                        this.innerHTML = '<i class="fas fa-trash"></i>';
                        this.disabled = false;
                    });
            }
        });
    });

    // Xử lý checkbox "Chọn tất cả"
    const selectAllCheckbox = document.getElementById('select-all');
    const deleteSelectedButton = document.getElementById('delete-selected');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;

            // Chọn/bỏ chọn tất cả các checkbox không bị disabled và không đang được sử dụng bởi sản phẩm
            document.querySelectorAll('.image-checkbox:not([disabled])').forEach(function(checkbox) {
                // Kiểm tra xem hình ảnh có đang được sử dụng không
                const isInUse = checkbox.getAttribute('data-in-use') === 'true';

                // Chỉ tích chọn những hình ảnh không đang được sử dụng
                if (!isInUse) {
                    checkbox.checked = isChecked;
                } else if (isChecked) {
                    // Nếu đang chọn tất cả, đánh dấu hàng chứa hình ảnh đang sử dụng
                    const row = checkbox.closest('tr');
                    row.style.backgroundColor = '#fff3cd';

                    // Tự động xóa màu nền sau 2 giây
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 2000);
                }
            });

            // Hiển thị/ẩn nút "Xóa đã chọn"
            updateDeleteSelectedButton();

            // Hiển thị thông báo nếu có hình ảnh đang được sử dụng
            if (isChecked) {
                const inUseImages = document.querySelectorAll('.image-checkbox[data-in-use="true"]');
                if (inUseImages.length > 0) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info';
                    alertDiv.innerHTML = `<i class="fas fa-info-circle"></i> <strong>Lưu ý:</strong> ${inUseImages.length} hình ảnh đang được sử dụng bởi sản phẩm sẽ không được chọn để xóa.`;
                    document.querySelector('.admin-content').insertBefore(alertDiv, document.querySelector('.admin-content').firstChild);

                    // Tự động ẩn thông báo sau 4 giây
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 4000);
                }
            }
        });
    }

    // Xử lý các checkbox riêng lẻ
    document.querySelectorAll('.image-checkbox').forEach(function(checkbox) {
        // Kiểm tra xem hình ảnh có đang được sử dụng không
        const isInUse = checkbox.getAttribute('data-in-use') === 'true';

        // Nếu hình ảnh đang được sử dụng, thêm CSS để hiển thị khác biệt
        if (isInUse) {
            const row = checkbox.closest('tr');
            row.classList.add('image-in-use');

            // Thêm sự kiện click để hiển thị thông báo khi người dùng cố gắng chọn hình ảnh đang sử dụng
            checkbox.addEventListener('click', function(e) {
                // Ngăn chặn hành động mặc định (tích chọn)
                e.preventDefault();

                // Hiển thị thông báo
                const row = this.closest('tr');
                row.style.backgroundColor = '#fff3cd';

                // Hiển thị thông báo
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-warning';
                alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <strong>Không thể chọn:</strong> Hình ảnh này đang được sử dụng bởi sản phẩm và không thể xóa.`;
                document.querySelector('.admin-content').insertBefore(alertDiv, document.querySelector('.admin-content').firstChild);

                // Tự động ẩn thông báo và màu nền sau 3 giây
                setTimeout(() => {
                    alertDiv.remove();
                    row.style.backgroundColor = '';
                }, 3000);

                return false;
            });
        } else {
            // Chỉ thêm sự kiện change cho những hình ảnh không đang được sử dụng
            checkbox.addEventListener('change', function() {
                // Kiểm tra nếu tất cả các checkbox không đang được sử dụng đều được chọn
                const availableCheckboxes = document.querySelectorAll('.image-checkbox:not([disabled]):not([data-in-use="true"])');
                const allAvailableChecked = Array.from(availableCheckboxes).every(cb => cb.checked);

                // Cập nhật trạng thái của checkbox "Chọn tất cả"
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allAvailableChecked;
                }

                // Hiển thị/ẩn nút "Xóa đã chọn"
                updateDeleteSelectedButton();
            });
        }
    });

    // Hàm cập nhật trạng thái nút "Xóa đã chọn"
    function updateDeleteSelectedButton() {
        if (deleteSelectedButton) {
            const checkedCount = document.querySelectorAll('.image-checkbox:checked').length;
            deleteSelectedButton.style.display = checkedCount > 0 ? 'block' : 'none';

            if (checkedCount > 0) {
                deleteSelectedButton.textContent = `Xóa đã chọn (${checkedCount})`;
            }
        }
    }

    // Xử lý nút "Xóa đã chọn"
    if (deleteSelectedButton) {
        deleteSelectedButton.addEventListener('click', function() {
            const checkedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
            const checkedCount = checkedCheckboxes.length;

            if (checkedCount === 0) return;

            if (confirm(`Bạn có chắc chắn muốn xóa ${checkedCount} hình ảnh đã chọn không?`)) {
                // Lấy danh sách ID hình ảnh đã chọn
                const imageIds = Array.from(checkedCheckboxes).map(cb => cb.getAttribute('data-id'));

                // Hiển thị loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
                this.disabled = true;

                // Gửi yêu cầu xóa nhiều hình ảnh
                fetch('./elements_LQA/mhinhanh/hinhanhAct.php?reqact=deletemultiple', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: imageIds
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa thành công, xóa các hàng khỏi bảng
                            checkedCheckboxes.forEach(cb => {
                                const row = cb.closest('tr');
                                row.style.backgroundColor = '#ffdddd';
                                setTimeout(() => {
                                    row.remove();
                                }, 500);
                            });

                            // Cập nhật số lượng hình ảnh sau 500ms
                            setTimeout(() => {
                                const totalElement = document.querySelector(
                                    '.list-header h3');
                                if (totalElement) {
                                    const currentCount = document.querySelectorAll(
                                        '.image-table tbody tr').length;
                                    totalElement.textContent =
                                        `Danh sách hình ảnh (${currentCount} hình ảnh)`;
                                }

                                // Hiển thị thông báo thành công
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success';
                                alertDiv.innerHTML =
                                    `<i class="fas fa-check-circle"></i> ${data.message}`;
                                document.querySelector('.admin-content').insertBefore(
                                    alertDiv, document.querySelector('.admin-content').firstChild);

                                // Tự động ẩn thông báo sau 3 giây
                                setTimeout(() => {
                                    alertDiv.remove();
                                }, 3000);
                            }, 500);
                        } else {
                            // Xóa thất bại
                            if (data.inUse) {
                                // Hiển thị thông báo lỗi chi tiết nếu hình ảnh đang được sử dụng
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-danger';

                                let alertContent = `<i class="fas fa-exclamation-circle"></i> <strong>Không thể xóa:</strong> ${data.message}<br>`;

                                // Hiển thị chi tiết về các hình ảnh đang được sử dụng
                                if (data.inUseDetails && data.inUseDetails.length > 0) {
                                    alertContent += '<ul>';
                                    data.inUseDetails.forEach(detail => {
                                        alertContent += `<li>${detail}</li>`;
                                    });
                                    alertContent += '</ul>';
                                }

                                alertDiv.innerHTML = alertContent;
                                document.querySelector('.admin-content').insertBefore(
                                    alertDiv, document.querySelector('.admin-content').firstChild);

                                // Đánh dấu các hàng có hình ảnh đang được sử dụng
                                if (data.inUseImages && data.inUseImages.length > 0) {
                                    data.inUseImages.forEach(imageId => {
                                        const checkbox = document.querySelector(`.image-checkbox[data-id="${imageId}"]`);
                                        if (checkbox) {
                                            const row = checkbox.closest('tr');
                                            row.style.backgroundColor = '#fff3cd';

                                            // Bỏ chọn checkbox
                                            checkbox.checked = false;
                                        }
                                    });
                                }
                            } else {
                                // Thông báo lỗi thông thường
                                alert('Lỗi: ' + data.message);
                            }

                            // Cập nhật lại nút xóa
                            this.innerHTML = 'Xóa đã chọn';
                            this.disabled = false;
                            updateDeleteSelectedButton();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Đã xảy ra lỗi khi xóa hình ảnh. Vui lòng thử lại.');
                        this.innerHTML = 'Xóa đã chọn';
                        this.disabled = false;
                    });
            }
        });
    }
});
</script>
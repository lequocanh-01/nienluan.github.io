<?php
require '../../elements_LQA/mod/hanghoaCls.php';
require '../../elements_LQA/mod/loaihangCls.php';

// Validate idhanghoa
$idhanghoa = isset($_REQUEST['idhanghoa']) ? $_REQUEST['idhanghoa'] : null;
if (!$idhanghoa) {
    echo "ID hàng hóa không hợp lệ.";
    exit;
}

$hanghoaObj = new hanghoa();
$getLhUpdate = $hanghoaObj->HanghoaGetbyId($idhanghoa);
if (!$getLhUpdate) {
    echo "Không tìm thấy hàng hóa.";
    exit;
}

$obj = new loaihang();
$list_lh = $obj->LoaihangGetAll();

// Fetch lists for employees, units of measurement, brands and images
$list_nhanvien = $hanghoaObj->GetAllNhanVien();
$list_donvitinh = $hanghoaObj->GetAllDonViTinh();
$list_thuonghieu = $hanghoaObj->GetAllThuongHieu();
$list_hinhanh = $hanghoaObj->GetAllHinhAnh();

// Get current image
$current_image = $getLhUpdate->hinhanh ? $hanghoaObj->GetHinhAnhById($getLhUpdate->hinhanh) : null;

if (!function_exists('htmlspecialchars')) {
    function htmlspecialchars($string)
    {
        return $string; // Fallback to return the string as is
    }
}
?>

<div class="update-form">
    <h3>Cập nhật hàng hóa</h3>
    <form name="updatehanghoa" id="formupdatehh" method="post"
        action='./elements_LQA/mhanghoa/hanghoaAct.php?reqact=updatehanghoa' enctype="multipart/form-data">
        <input type="hidden" name="idhanghoa" value="<?php echo htmlspecialchars($getLhUpdate->idhanghoa ?? ''); ?>" />
        <input type="hidden" name="idNhanVien"
            value="<?php echo htmlspecialchars($getLhUpdate->idNhanVien ?? ''); ?>" />

        <div class="form-group">
            <label>Tên hàng hóa:</label>
            <input type="text" name="tenhanghoa" value="<?php echo htmlspecialchars($getLhUpdate->tenhanghoa ?? ''); ?>"
                required />
        </div>

        <div class="form-group">
            <label>Giá tham khảo:</label>
            <input type="number" name="giathamkhao"
                value="<?php echo htmlspecialchars($getLhUpdate->giathamkhao ?? 0); ?>" required />
        </div>

        <div class="form-group">
            <label>Mô tả:</label>
            <input type="text" name="mota" value="<?php echo htmlspecialchars($getLhUpdate->mota ?? ''); ?>" />
        </div>

        <div class="form-group">
            <label>Hình ảnh hiện tại:</label>
            <?php if ($current_image && isset($current_image->duong_dan)): ?>
            <?php
                // Kiểm tra các đường dẫn có thể có của hình ảnh
                $possible_paths = [
                    '../../' . $current_image->duong_dan,
                    '../' . $current_image->duong_dan,
                    $current_image->duong_dan,
                    '../../uploads/' . basename($current_image->duong_dan),
                    './uploads/' . basename($current_image->duong_dan),
                    '../uploads/' . basename($current_image->duong_dan)
                ];

                $image_found = false;
                $display_path = '';

                // Debug info
                echo '<!-- Debug image path: ' . print_r($current_image, true) . ' -->';

                // Kiểm tra từng đường dẫn
                foreach ($possible_paths as $path) {
                    $relative_path = str_replace(['./'], '', $path);
                    echo '<!-- Checking path: ' . $relative_path . ' -->';

                    if (file_exists($relative_path)) {
                        $display_path = $relative_path;
                        $image_found = true;
                        echo '<!-- Image found at: ' . $relative_path . ' -->';
                        break;
                    }
                }

                if ($image_found) {
                    // Hiển thị hình ảnh tìm thấy
                    echo '<img src="' . $display_path . '" alt="Current Image" style="max-width: 200px; display: block; margin-top: 10px; border: 1px solid #ddd; padding: 5px;" id="currentImage" />';
                } else {
                    // Sử dụng hình ảnh base64 mặc định
                    echo '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEIUlEQVR4nO2dW4hNURjHf8cld3K/lDzwQDwgeSJ5kCgpJUoeUG7JZV4oL7wRRbnMvFHKhQcPJPGglBQPbiEPRkpukaRmxqH5OHuaYZg5s9dee+1v/3/1rc44e6/v/M7MXnut9a2CIAiCIAiCIAiCIAiCEHdagRlAC9AI1AENDdAA1AObgPkYztAV8kCt/kEdZbk4YWpUlw9KyKnzEz1dEoQZUS3vlxDTlT1GdEkQ/WLwAuWUlH5GdJ2SINrF4LfLKSmnjejaLEFcFGOnu6DLfV2XJIhLYuiEuyAlp3WNlCCWi5ET7qKU5JccGSSI+WLkuLs2JadNbWy5IVNWF8TIsXZ9SnKaNmL+WfNWjBz7Fkta7rPGIBoFuC0Gftm8kJL7vPbhGlBuHNArxrUqpSQv7zWvjSsI49KQ/8DaAcuAl+of0qosFyesgS9TcuuAM0AXihDdYsyKsiilZJcRXceAchLAdBLO15TcOp1mJcl0JMF8SMltL8nsQoxZlJLbWZLRkwQzOyW3rSQjUaOsANNdgrAkCObfJJixEsSXJJh1EsT7JJgWCeJVEsx2CeJREsw+CeJ+Eky7BHErCeaCBHEtCeayBNGdBLNHgtidBLMsCWZkEkx/w91VK4LyMNDuLEiCmWpiuR/4wtwzMkc1GVN6i+kKyqfAODImq4l4WKgYUUZHjkgQOQ3i+CDQlcwwXqY9CeJeIMnXyAxV+ry7BHGY4HQnM9TpGisJ4hDBWZFI46j5FEgQuwjOxESaR81Iu+NKED0IztBEGkeNXLtCVpIZJIgVBGdaIo2jZpwEsZjgzEqkcdR0lSBmE5w5OXWOe9NXghCEjHEScotMfxJAmdOhb4y1ik2CIAiCIAiCIAiCIAgJpzdQA7QBj9VWlJOqWe6oquxOQAWBGQlsAT4BvyrsJXYCVepFIvxHX+AQ8DOAMP5cfoigJ7BLb7mWQhhFEZPVFpOk0k4JAykK43oi3ZKjCggj7yfxKjkvQXRBxfcS9Cbum8HEcUr6AhyVP3ww9gQURXGZrSZ6I5Qs7wmwOsrH7c5VYp5SDfwqQRjFJnCj1X5lJzkVJehTxlXUh3aqGp6mDKcokkbVJfU3f/Kg69RcR2dF7kUZbMU1Jea9MqXsdnSy+rl76rRFVTsrM9zZ3d9QTVQvRbUGrBLzPgX2qtnSi38MyimgGaOXAj7o7uo7fxdpIp5YobYlVRpfDT3K0Dc+V9sSJPm/r6iXUodFbkE5MRi1zLN3fCXGwB3AZOPbZuxRKs31zCBDK4xQH/Eeqi3prnqRvPJ8Pu9P5ZTqL8b6LyMIQU31dqpz/4f6vRj4qpo3q+z4r8rQNNQONQXuVdOa0vy7JfWb8p45vEetVl/5S0GnUf+NHjLdEQRBEARBEARBEARBEHDeL9qMsYfZY/CjAAAAAElFTkSuQmCC" alt="No Image Available" style="max-width: 200px; display: block; margin-top: 10px; border: 1px solid #ddd; padding: 5px;" id="currentImage" />';
                    echo '<!-- No image found, using default base64 image -->';
                }
                ?>
            <?php else: ?>
            <p>Không có hình ảnh</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Chọn hình ảnh mới từ danh sách:</label>
            <select name="id_hinhanh" class="image-select" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                <option value="">-- Chọn hình ảnh --</option>
                <?php
                echo "<!-- DEBUG: ID hình ảnh hiện tại: " . ($getLhUpdate->hinhanh ?? 'không có') . " -->\n";
                if (!empty($list_hinhanh)) {
                    foreach ($list_hinhanh as $img):
                        $is_selected = ($current_image && isset($current_image->id) && $img->id == $current_image->id);
                        echo "<!-- DEBUG: Kiểm tra hình ảnh " . $img->id . ", selected: " . ($is_selected ? 'true' : 'false') . " -->\n";
                ?>
                <option value="<?php echo htmlspecialchars($img->id ?? ''); ?>"
                    <?php echo $is_selected ? 'selected' : ''; ?>
                    data-preview="<?php echo htmlspecialchars($img->duong_dan ?? ''); ?>">
                    <?php echo htmlspecialchars($img->ten_file ?? ''); ?> (ID: <?php echo $img->id; ?>)
                </option>
                <?php
                    endforeach;
                } else {
                    echo "<!-- DEBUG: Không có hình ảnh nào trong danh sách -->\n";
                }
                ?>
            </select>
            <!-- Thêm giá trị ẩn để lưu ID hình ảnh hiện tại -->
            <input type="hidden" name="current_image_id"
                value="<?php echo ($current_image && isset($current_image->id)) ? $current_image->id : ''; ?>">
            <div id="imagePreview" style="margin-top: 10px; border: 1px solid #eee; padding: 10px; max-width: 220px;">
            </div>
        </div>

        <div class="form-group">
            <label>Loại hàng:</label>
            <select name="idloaihang" required>
                <?php foreach ($list_lh as $l): ?>
                <option value="<?php echo htmlspecialchars($l->idloaihang ?? ''); ?>"
                    <?php echo (isset($l->idloaihang) && $l->idloaihang == $getLhUpdate->idloaihang) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($l->tenloaihang ?? ''); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Thương hiệu:</label>
            <select name="idThuongHieu" required>
                <option value="">-- Chọn thương hiệu --</option>
                <?php foreach ($list_thuonghieu as $th): ?>
                <option value="<?php echo htmlspecialchars($th->idThuongHieu ?? ''); ?>"
                    <?php echo (isset($th->idThuongHieu) && $th->idThuongHieu == $getLhUpdate->idThuongHieu) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($th->tenTH ?? ''); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Đơn vị tính:</label>
            <select name="idDonViTinh" required>
                <option value="">-- Chọn đơn vị tính --</option>
                <?php foreach ($list_donvitinh as $dvt): ?>
                <option value="<?php echo htmlspecialchars($dvt->idDonViTinh ?? ''); ?>"
                    <?php echo (isset($dvt->idDonViTinh) && $dvt->idDonViTinh == $getLhUpdate->idDonViTinh) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dvt->tenDonViTinh ?? ''); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()">Hủy</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageSelect = document.querySelector('.image-select');
    const imagePreview = document.getElementById('imagePreview');
    const currentImage = document.getElementById('currentImage');
    const form = document.getElementById('formupdatehh');

    console.log('Debug - Form cập nhật hàng hóa đã được tải');
    console.log('Debug - ID hình ảnh đang chọn: ' + (imageSelect.value || 'Không có'));
    console.log('Debug - Số lượng hình ảnh trong danh sách: ' + imageSelect.options.length);

    // Kiểm tra và hiển thị lỗi hình ảnh nếu có
    if (currentImage) {
        console.log('Debug - Current image src: ' + currentImage.src);
        currentImage.onerror = function() {
            console.error('Không thể tải hình ảnh hiện tại:', this.src);
            this.onerror = null; // Tránh lặp vô hạn
            this.src =
                'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEIUlEQVR4nO2dW4hNURjHf8cld3K/lDzwQDwgeSJ5kCgpJUoeUG7JZV4oL7wRRbnMvFHKhQcPJPGglBQPbiEPRkpukaRmxqH5OHuaYZg5s9dee+1v/3/1rc44e6/v/M7MXnut9a2CIAiCIAiCIAiCIAiCEHdagRlAC9AI1AENDdAA1AObgPkYztAV8kCt/kEdZbk4YWpUlw9KyKnzEz1dEoQZUS3vlxDTlT1GdEkQ/WLwAuWUlH5GdJ2SINrF4LfLKSmnjejaLEFcFGOnu6DLfV2XJIhLYuiEuyAlp3WNlCCWi5ET7qKU5JccGSSI+WLkuLs2JadNbWy5IVNWF8TIsXZ9SnKaNmL+WfNWjBz7Fkta7rPGIBoFuC0Gftm8kJL7vPbhGlBuHNArxrUqpSQv7zWvjSsI49KQ/8DaAcuAl+of0qosFyesgS9TcuuAM0AXihDdYsyKsiilZJcRXceAchLAdBLO15TcOp1mJcl0JMF8SMltL8nsQoxZlJLbWZLRkwQzOyW3rSQjUaOsANNdgrAkCObfJJixEsSXJJh1EsT7JJgWCeJVEsx2CeJREsw+CeJ+Eky7BHErCeaCBHEtCeayBNGdBLNHgtidBLMsCWZkEkx/w91VK4LyMNDuLEiCmWpiuR/4wtwzMkc1GVN6i+kKyqfAODImq4l4WKgYUUZHjkgQOQ3i+CDQlcwwXqY9CeJeIMnXyAxV+ry7BHGY4HQnM9TpGisJ4hDBWZFI46j5FEgQuwjOxESaR81Iu+NKED0IztBEGkeNXLtCVpIZJIgVBGdaIo2jZpwEsZjgzEqkcdR0lSBmE5w5OXWOe9NXghCEjHEScotMfxJAmdOhb4y1ik2CIAiCIAiCIAiCIAgJpzdQA7QBj9VWlJOqWe6oquxOQAWBGQlsAT4BvyrsJXYCVepFIvxHX+AQ8DOAMP5cfoigJ7BLb7mWQhhFEZPVFpOk0k4JAykK43oi3ZKjCggj7yfxKjkvQXRBxfcS9Cbum8HEcUr6AhyVP3ww9gQURXGZrSZ6I5Qs7wmwOsrH7c5VYp5SDfwqQRjFJnCj1X5lJzkVJehTxlXUh3aqGp6mDKcokkbVJfU3f/Kg69RcR2dF7kUZbMU1Jea9MqXsdnSy+rl76rRFVTsrM9zZ3d9QTVQvRbUGrBLzPgX2qtnSi38MyimgGaOXAj7o7uo7fxdpIp5YobYlVRpfDT3K0Dc+V9sSJPm/r6iXUodFbkE5MRi1zLN3fCXGwB3AZOPbZuxRKs31zCBDK4xQH/Eeqi3prnqRvPJ8Pu9P5ZTqL8b6LyMIQU31dqpz/4f6vRj4qpo3q+z4r8rQNNQONQXuVdOa0vy7JfWb8p45vEetVl/5S0GnUf+NHjLdEQRBEARBEARBEARBEHDeL9qMsYfZY/CjAAAAAElFTkSuQmCC';
        };
    }

    // Hiển thị hình ảnh preview khi trang được tải
    const initialOption = imageSelect.options[imageSelect.selectedIndex];
    if (initialOption && initialOption.getAttribute('data-preview')) {
        const previewUrl = initialOption.getAttribute('data-preview');
        console.log('Debug - Initial preview URL: ' + previewUrl);
        showImagePreview(previewUrl);
    }

    // Handle image preview
    imageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const previewUrl = selectedOption.getAttribute('data-preview');
        console.log('Debug - Đã chọn hình ảnh mới với ID: ' + this.value);
        console.log('Debug - Preview URL: ' + previewUrl);

        if (previewUrl) {
            showImagePreview(previewUrl);
        } else {
            imagePreview.innerHTML = '<p>Không có hình ảnh preview</p>';
        }
    });

    function showImagePreview(url) {
        // Sanitize URL and make sure it's a valid path before rendering
        if (!url) {
            console.log('Debug - Không có URL hình ảnh để hiển thị');
            return;
        }

        console.log('Debug - Hiển thị hình ảnh preview: ' + url);
        const sanitizedUrl = url.replace(/['"<>]/g, '');

        // Thử các đường dẫn khác nhau
        const imgElement = document.createElement('img');
        imgElement.style.maxWidth = '200px';
        imgElement.style.border = '1px solid #ddd';
        imgElement.style.padding = '3px';
        imgElement.alt = 'Preview';

        // Đặt hàm xử lý lỗi trước khi gán src để bắt lỗi tải
        imgElement.onerror = function() {
            console.log('Không thể tải hình ảnh từ:', sanitizedUrl);

            // Thử các đường dẫn thay thế
            const relativePath = sanitizedUrl.split('/').pop();
            const alternativePaths = [
                './uploads/' + relativePath,
                '../uploads/' + relativePath,
                '../../uploads/' + relativePath,
                '../elements_LQA/uploads/' + relativePath
            ];

            let pathIndex = 0;
            tryNextPath();

            function tryNextPath() {
                if (pathIndex >= alternativePaths.length) {
                    // Nếu tất cả đường dẫn đều thất bại, hiển thị hình ảnh mặc định
                    imgElement.src =
                        'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEIUlEQVR4nO2dW4hNURjHf8cld3K/lDzwQDwgeSJ5kCgpJUoeUG7JZV4oL7wRRbnMvFHKhQcPJPGglBQPbiEPRkpukaRmxqH5OHuaYZg5s9dee+1v/3/1rc44e6/v/M7MXnut9a2CIAiCIAiCIAiCIAiCEHdagRlAC9AI1AENDdAA1AObgPkYztAV8kCt/kEdZbk4YWpUlw9KyKnzEz1dEoQZUS3vlxDTlT1GdEkQ/WLwAuWUlH5GdJ2SINrF4LfLKSmnjejaLEFcFGOnu6DLfV2XJIhLYuiEuyAlp3WNlCCWi5ET7qKU5JccGSSI+WLkuLs2JadNbWy5IVNWF8TIsXZ9SnKaNmL+WfNWjBz7Fkta7rPGIBoFuC0Gftm8kJL7vPbhGlBuHNArxrUqpSQv7zWvjSsI49KQ/8DaAcuAl+of0qosFyesgS9TcuuAM0AXihDdYsyKsiilZJcRXceAchLAdBLO15TcOp1mJcl0JMF8SMltL8nsQoxZlJLbWZLRkwQzOyW3rSQjUaOsANNdgrAkCObfJJixEsSXJJh1EsT7JJgWCeJVEsx2CeJREsw+CeJ+Eky7BHErCeaCBHEtCeayBNGdBLNHgtidBLMsCWZkEkx/w91VK4LyMNDuLEiCmWpiuR/4wtwzMkc1GVN6i+kKyqfAODImq4l4WKgYUUZHjkgQOQ3i+CDQlcwwXqY9CeJeIMnXyAxV+ry7BHGY4HQnM9TpGisJ4hDBWZFI46j5FEgQuwjOxESaR81Iu+NKED0IztBEGkeNXLtCVpIZJIgVBGdaIo2jZpwEsZjgzEqkcdR0lSBmE5w5OXWOe9NXghCEjHEScotMfxJAmdOhb4y1ik2CIAiCIAiCIAiCIAgJpzdQA7QBj9VWlJOqWe6oquxOQAWBGQlsAT4BvyrsJXYCVepFIvxHX+AQ8DOAMP5cfoigJ7BLb7mWQhhFEZPVFpOk0k4JAykK43oi3ZKjCggj7yfxKjkvQXRBxfcS9Cbum8HEcUr6AhyVP3ww9gQURXGZrSZ6I5Qs7wmwOsrH7c5VYp5SDfwqQRjFJnCj1X5lJzkVJehTxlXUh3aqGp6mDKcokkbVJfU3f/Kg69RcR2dF7kUZbMU1Jea9MqXsdnSy+rl76rRFVTsrM9zZ3d9QTVQvRbUGrBLzPgX2qtnSi38MyimgGaOXAj7o7uo7fxdpIp5YobYlVRpfDT3K0Dc+V9sSJPm/r6iXUodFbkE5MRi1zLN3fCXGwB3AZOPbZuxRKs31zCBDK4xQH/Eeqi3prnqRvPJ8Pu9P5ZTqL8b6LyMIQU31dqpz/4f6vRj4qpo3q+z4r8rQNNQONQXuVdOa0vy7JfWb8p45vEetVl/5S0GnUf+NHjLdEQRBEARBEARBEARBEHDeL9qMsYfZY/CjAAAAAElFTkSuQmCC';
                    console.log(
                        'Debug - Đã thử tất cả các đường dẫn nhưng không thành công, sử dụng hình ảnh mặc định'
                    );
                    return;
                }

                console.log('Debug - Thử đường dẫn thay thế: ' + alternativePaths[pathIndex]);
                imgElement.src = alternativePaths[pathIndex];
                pathIndex++;

                // Nếu đường dẫn này cũng thất bại, thử đường dẫn tiếp theo
                imgElement.onerror = tryNextPath;
            }
        };

        // Thiết lập đường dẫn ban đầu
        imgElement.src = sanitizedUrl;
        imagePreview.innerHTML = '';
        imagePreview.appendChild(imgElement);
    }

    // Form validation
    if (form) {
        form.onsubmit = function(e) {
            // Log submission for debugging
            console.log('Form đang được gửi đi - Thông tin chi tiết:');

            // Hiển thị thông tin form để debug
            const formData = {};
            for (let i = 0; i < this.elements.length; i++) {
                const element = this.elements[i];
                if (element.name) {
                    formData[element.name] = element.value;
                    console.log(`${element.name}: ${element.value}`);
                }
            }

            // Kiểm tra cụ thể cho hình ảnh
            console.log('Debug - ID hình ảnh TRƯỚC KHI gửi: ' + formData.id_hinhanh);

            return true;
        };
    }
});
</script>
<?php
require_once __DIR__ . '/../mod/thuoctinhCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idThuocTinh = isset($_POST['idThuocTinh']) ? $_POST['idThuocTinh'] : (isset($_GET['idThuocTinh']) ? $_GET['idThuocTinh'] : (isset($_REQUEST['idThuocTinh']) ? $_REQUEST['idThuocTinh'] : null));

if (!$idThuocTinh) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID thuộc tính",
        'debug' => $debug
    ]);
    exit;
}

$thuocTinhObj = new ThuocTinh();
$item = $thuocTinhObj->thuoctinhGetbyId($idThuocTinh);

if (!$item) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy thuộc tính với ID: " . htmlspecialchars($idThuocTinh),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form-container">
    <div class="update-header">
        <h3>Cập nhật thuộc tính</h3>
        <span class="close-btn" id="close-btn">X</span>
    </div>

    <form id="updatethuoctinh" method="post" action="./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=updatethuoctinh" enctype="multipart/form-data">
        <input type="hidden" name="idThuocTinh" value="<?php echo $item->idThuocTinh; ?>" />
        <input type="hidden" name="hinhanh" value="<?php echo $item->hinhanh; ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idThuocTinh); ?></div>
        </div>

        <div class="form-group">
            <label>Tên Thuộc Tính:</label>
            <input type="text" class="form-control" name="tenThuocTinh" value="<?php echo htmlspecialchars($item->tenThuocTinh); ?>" required />
        </div>

        <div class="form-group">
            <label>Ghi Chú:</label>
            <input type="text" class="form-control" name="ghiChu" value="<?php echo htmlspecialchars($item->ghiChu); ?>" />
        </div>

        <div class="form-group">
            <label>Hình ảnh:</label>
            <?php if ($item->hinhanh): ?>
                <div class="mt-2">
                    <img src="data:image/png;base64,<?php echo $item->hinhanh; ?>" alt="Current image" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                </div>
            <?php endif; ?>
            <label>Chọn hình ảnh mới (nếu muốn thay đổi):</label>
            <input type="file" class="form-control" name="fileimage" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('close-btn').addEventListener('click', function() {
        // Emit custom event for parent window to handle closing
        if (window.parent) {
            window.parent.postMessage('closeUpdateForm', '*');
        }
        // Also handle close if this is used in a native popup
        var parentElement = window.frameElement && window.frameElement.parentElement;
        if (parentElement) {
            parentElement.style.display = 'none';
        }
    });

    document.getElementById('updatethuoctinh').addEventListener('submit', function(e) {
        e.preventDefault();

        console.log("Form cập nhật thuộc tính được submit");

        var formData = new FormData(this);

        // Gửi request ajax
        $.ajax({
            url: "./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=updatethuoctinh",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Phản hồi từ server:", response);

                // Đóng popup và tải lại trang
                $("#w_update_tt").hide();

                // Tải lại trang với tham số cache-busting
                window.location.href = "index.php?req=thuoctinhview&t=" + new Date().getTime();
            },
            error: function(xhr, status, error) {
                console.error("Lỗi khi cập nhật:", error);
                alert("Có lỗi xảy ra khi cập nhật thuộc tính: " + error);
            }
        });
    });
</script>
<?php
require_once __DIR__ . '/../mod/nhacungcapCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idNCC = isset($_POST['idNCC']) ? $_POST['idNCC'] : (isset($_GET['idNCC']) ? $_GET['idNCC'] : (isset($_REQUEST['idNCC']) ? $_REQUEST['idNCC'] : null));

$debug['ID detected'] = $idNCC;

// Output debug if requested
if (isset($_GET['debug']) || isset($_POST['debug'])) {
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
}

if (!$idNCC) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID nhà cung cấp",
        'debug' => $debug
    ]);
    exit;
}

$nccObj = new nhacungcap();
$getNccUpdate = $nccObj->NhacungcapGetbyId($idNCC);

if (!$getNccUpdate) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy nhà cung cấp với ID: " . htmlspecialchars($idNCC),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form-container">
    <div class="update-header">
        <h3>Cập nhật thông tin nhà cung cấp</h3>
        <button id="close-btn" class="close-btn">X</button>
    </div>

    <form name="updatenhacungcap" id="formupdate" method="post" action="./elements_LQA/mnhacungcap/nhacungcapAct.php?reqact=updatenhacungcap">
        <input type="hidden" name="idNCC" value="<?php echo htmlspecialchars($getNccUpdate->idNCC); ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idNCC); ?></div>
        </div>

        <div class="form-group">
            <label>Tên nhà cung cấp:</label>
            <input type="text" name="tenNCC" class="form-control" value="<?php echo htmlspecialchars($getNccUpdate->tenNCC); ?>" required />
        </div>

        <div class="form-group">
            <label>Người liên hệ:</label>
            <input type="text" name="nguoiLienHe" class="form-control" value="<?php echo htmlspecialchars($getNccUpdate->nguoiLienHe ?? ''); ?>" />
        </div>

        <div class="form-group">
            <label>Số điện thoại:</label>
            <input type="text" name="soDienThoai" class="form-control" value="<?php echo htmlspecialchars($getNccUpdate->soDienThoai ?? ''); ?>" />
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($getNccUpdate->email ?? ''); ?>" />
        </div>

        <div class="form-group">
            <label>Địa chỉ:</label>
            <textarea name="diaChi" class="form-control" rows="3"><?php echo htmlspecialchars($getNccUpdate->diaChi ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label>Mã số thuế:</label>
            <input type="text" name="maSoThue" class="form-control" value="<?php echo htmlspecialchars($getNccUpdate->maSoThue ?? ''); ?>" />
        </div>

        <div class="form-group">
            <label>Ghi chú:</label>
            <textarea name="ghiChu" class="form-control" rows="3"><?php echo htmlspecialchars($getNccUpdate->ghiChu ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label>Trạng thái:</label>
            <select name="trangThai" class="form-control">
                <option value="1" <?php echo ($getNccUpdate->trangThai == 1) ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="0" <?php echo ($getNccUpdate->trangThai == 0) ? 'selected' : ''; ?>>Không hoạt động</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Cập nhật</button>
            <div id="noteForm" style="margin-top: 10px;"></div>
        </div>
    </form>
</div>

<style>
    .update-form-container {
        padding: 15px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        position: relative;
        z-index: 9999;
        pointer-events: auto;
    }

    .update-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        position: relative;
        z-index: 9999;
    }

    .update-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .close-btn {
        color: #fff;
        background-color: #dc3545;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: bold;
        border: none;
        pointer-events: auto !important;
    }

    .form-group {
        position: relative;
        z-index: 9999;
        margin-bottom: 15px;
        pointer-events: auto;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: text !important;
        pointer-events: auto !important;
    }

    .form-actions {
        text-align: center;
        margin-top: 15px;
        pointer-events: auto;
    }

    .btn-primary {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        pointer-events: auto !important;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }
</style>

<script>
    document.getElementById('close-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Close the popup
        if (window.parent && typeof window.parent.closeModal === 'function') {
            window.parent.closeModal();
        } else {
            const modal = document.getElementById('w_update_ncc');
            if (modal) modal.style.display = 'none';

            // Or try to notify parent window to close modal
            window.parent.postMessage('closeUpdateForm', '*');
        }
    });

    document.getElementById('formupdate').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show submitting state
        const submitBtn = document.querySelector('.btn-primary');
        submitBtn.textContent = "Đang gửi...";
        submitBtn.disabled = true;

        // Get form data
        const formData = new FormData(this);

        // Send form data
        fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log("Response:", data);

                if (data.success) {
                    // Reload the page on success
                    window.top.location.href = 'index.php?req=nhacungcapview&result=ok';
                } else {
                    // Show error message
                    document.getElementById('noteForm').innerHTML =
                        '<div style="color: red; font-weight: bold;">Lỗi: ' + data.message + '</div>';

                    // Reset button state
                    submitBtn.textContent = "Cập nhật";
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error("Error:", error);

                // Show error message
                document.getElementById('noteForm').innerHTML =
                    '<div style="color: red; font-weight: bold;">Lỗi kết nối. Vui lòng thử lại.</div>';

                // Reset button state
                submitBtn.textContent = "Cập nhật";
                submitBtn.disabled = false;
            });
    });
</script>
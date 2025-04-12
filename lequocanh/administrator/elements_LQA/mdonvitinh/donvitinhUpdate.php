<?php
require_once __DIR__ . '/../mod/donvitinhCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idDonViTinh = isset($_POST['idDonViTinh']) ? $_POST['idDonViTinh'] : (isset($_GET['idDonViTinh']) ? $_GET['idDonViTinh'] : (isset($_REQUEST['idDonViTinh']) ? $_REQUEST['idDonViTinh'] : null));

// If still no ID, try alternative forms
if (!$idDonViTinh) {
    if (isset($_POST['data-id'])) {
        $idDonViTinh = $_POST['data-id'];
    } elseif (isset($_GET['data-id'])) {
        $idDonViTinh = $_GET['data-id'];
    }
}

$debug['ID detected'] = $idDonViTinh;

// Output debug if requested
if (isset($_GET['debug']) || isset($_POST['debug'])) {
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
}

if (!$idDonViTinh) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID đơn vị tính",
        'debug' => $debug
    ]);
    exit;
}

$donViTinhObj = new DonViTinh();
$getDonViTinhUpdate = $donViTinhObj->donvitinhGetbyId($idDonViTinh);

if (!$getDonViTinhUpdate) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy đơn vị tính với ID: " . htmlspecialchars($idDonViTinh),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form-container">
    <div class="update-header">
        <h3>Cập nhật đơn vị tính</h3>
        <span class="close-btn" id="close-btn">X</span>
    </div>

    <form name="updatedonvitinh" id="updatedonvitinh" method="post" action="./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh">
        <input type="hidden" name="idDonViTinh" value="<?php echo $getDonViTinhUpdate->idDonViTinh; ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idDonViTinh); ?></div>
        </div>

        <div class="form-group">
            <label>Tên Đơn Vị Tính:</label>
            <input type="text" class="form-control" name="tenDonViTinh" value="<?php echo htmlspecialchars($getDonViTinhUpdate->tenDonViTinh); ?>" required />
        </div>

        <div class="form-group">
            <label>Mô Tả:</label>
            <textarea class="form-control" name="moTa" rows="3"><?php echo htmlspecialchars($getDonViTinhUpdate->moTa); ?></textarea>
        </div>

        <div class="form-group">
            <label>Ghi Chú:</label>
            <textarea class="form-control" name="ghiChu" rows="3"><?php echo htmlspecialchars($getDonViTinhUpdate->ghiChu); ?></textarea>
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
</script>
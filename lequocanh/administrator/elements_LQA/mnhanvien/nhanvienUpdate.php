<?php
require_once __DIR__ . '/../mod/nhanvienCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idNhanVien = isset($_POST['idNhanVien']) ? $_POST['idNhanVien'] : (isset($_GET['idNhanVien']) ? $_GET['idNhanVien'] : (isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null));

// If still no ID, try alternative forms
if (!$idNhanVien) {
    if (isset($_POST['data-id'])) {
        $idNhanVien = $_POST['data-id'];
    } elseif (isset($_GET['data-id'])) {
        $idNhanVien = $_GET['data-id'];
    }
}

$debug['ID detected'] = $idNhanVien;

// Output debug if requested
if (isset($_GET['debug']) || isset($_POST['debug'])) {
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
}

if (!$idNhanVien) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID nhân viên",
        'debug' => $debug
    ]);
    exit;
}

$nhanVienObj = new NhanVien();
$getNhanVienUpdate = $nhanVienObj->nhanvienGetbyId($idNhanVien);

if (!$getNhanVienUpdate) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy nhân viên với ID: " . htmlspecialchars($idNhanVien),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form">
    <h3>Cập nhật nhân viên</h3>
    <form name="updatenhanvien" id="updatenhanvien" method="post">
        <input type="hidden" name="idNhanVien" value="<?php echo $getNhanVienUpdate->idNhanVien; ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idNhanVien); ?></div>
        </div>

        <div class="form-group">
            <label>Tên Nhân Viên:</label>
            <input type="text" name="tenNV" value="<?php echo htmlspecialchars($getNhanVienUpdate->tenNV); ?>" required />
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($getNhanVienUpdate->email); ?>" />
        </div>

        <div class="form-group">
            <label>Số Điện Thoại:</label>
            <input type="text" name="SDT" value="<?php echo htmlspecialchars($getNhanVienUpdate->SDT); ?>" />
        </div>

        <div class="form-group">
            <label>Lương Cơ Bản:</label>
            <input type="number" name="luongCB" value="<?php echo htmlspecialchars($getNhanVienUpdate->luongCB); ?>" />
        </div>

        <div class="form-group">
            <label>Phụ Cấp:</label>
            <input type="number" name="phuCap" value="<?php echo htmlspecialchars($getNhanVienUpdate->phuCap); ?>" />
        </div>

        <div class="form-group">
            <label>Chức Vụ:</label>
            <input type="text" name="chucVu" value="<?php echo htmlspecialchars($getNhanVienUpdate->chucVu); ?>" />
        </div>

        <div class="form-actions">
            <input type="submit" id="btnsubmit" value="Cập nhật" class="btn-update" />
            <div id="noteForm" style="margin-top: 10px;"></div>
        </div>
    </form>
</div>

<style>
    .update-form {
        max-width: 100%;
        margin: 0;
        padding: 0;
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
    .form-group input[type="email"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-actions {
        margin-top: 20px;
        text-align: center;
    }

    .btn-update {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-update:hover {
        background-color: #0056b3;
    }

    #noteForm {
        display: block;
        margin-top: 10px;
        color: #666;
    }
</style>
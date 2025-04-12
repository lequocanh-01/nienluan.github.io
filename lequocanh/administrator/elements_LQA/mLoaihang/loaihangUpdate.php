<?php
require_once __DIR__ . '/../mod/loaihangCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idloaihang = isset($_POST['idloaihang']) ? $_POST['idloaihang'] : (isset($_GET['idloaihang']) ? $_GET['idloaihang'] : (isset($_REQUEST['idloaihang']) ? $_REQUEST['idloaihang'] : null));

$debug['ID detected'] = $idloaihang;

// Output debug if requested
if (isset($_GET['debug']) || isset($_POST['debug'])) {
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
}

if (!$idloaihang) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID loại hàng",
        'debug' => $debug
    ]);
    exit;
}

$lhobj = new loaihang();
$getLhUpdate = $lhobj->LoaihangGetbyId($idloaihang);

if (!$getLhUpdate) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy loại hàng với ID: " . htmlspecialchars($idloaihang),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form">
    <h3>Cập nhật loại hàng</h3>
    <form name="updateloaihang" id="formupdatelh" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idloaihang" value="<?php echo $getLhUpdate->idloaihang; ?>" />
        <input type="hidden" name="hinhanh" value="<?php echo $getLhUpdate->hinhanh; ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idloaihang); ?></div>
        </div>

        <div class="form-group">
            <label>Tên loại hàng:</label>
            <input type="text" name="tenloaihang" value="<?php echo htmlspecialchars($getLhUpdate->tenloaihang); ?>" required />
        </div>

        <div class="form-group">
            <label>Mô tả:</label>
            <input type="text" name="mota" value="<?php echo htmlspecialchars($getLhUpdate->mota); ?>" />
        </div>

        <div class="form-group">
            <label>Hình ảnh hiện tại:</label>
            <div class="current-image">
                <img width="150" src="data:image/png;base64,<?php echo $getLhUpdate->hinhanh ?>" alt="Current image">
            </div>
            <label>Chọn hình ảnh mới (nếu muốn thay đổi):</label>
            <input type="file" name="fileimage" accept="image/*">
        </div>

        <div class="form-actions">
            <input type="submit" id="btnsubmit" value="Cập nhật" class="btn-update" />
            <div id="noteForm" style="margin-top: 10px;"></div>
        </div>
    </form>
</div>

<script>
    // Force page reload after successful form submission
    document.getElementById('formupdatelh').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);

        // Send form data
        fetch('./elements_LQA/mLoaihang/loaihangAct.php?reqact=updateloaihang', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Form submitted, reloading page...');

                // Force reload the page
                window.top.location.href = 'index.php?req=loaihangview&t=' + new Date().getTime();
            })
            .catch(error => {
                console.error('Error:', error);

                // Still reload on error
                window.top.location.href = 'index.php?req=loaihangview';
            });
    });
</script>

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

    .form-group input[type="text"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .current-image {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #eee;
        border-radius: 4px;
        text-align: center;
    }

    .current-image img {
        max-width: 150px;
        height: auto;
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

    .alert {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
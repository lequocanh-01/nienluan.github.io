<?php
require_once __DIR__ . '/../mod/nhanvienCls.php';
require_once __DIR__ . '/../mod/userCls.php';

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

// Lấy danh sách người dùng cho dropdown
$userObj = new user();
$listUsers = $userObj->UserGetAll();
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
            <label>Người dùng:</label>
            <select name="iduser" id="iduser_update" class="form-control">
                <option value="">-- Chọn người dùng --</option>
                <?php foreach ($listUsers as $user): ?>
                    <option value="<?php echo $user->iduser; ?>" <?php echo (isset($getNhanVienUpdate->iduser) && $getNhanVienUpdate->iduser == $user->iduser) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user->hoten); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tên Nhân Viên:</label>
            <input type="text" name="tenNV" id="tenNV_update" value="<?php echo htmlspecialchars($getNhanVienUpdate->tenNV); ?>" required />
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" id="email_update" value="<?php echo htmlspecialchars($getNhanVienUpdate->email); ?>" />
        </div>

        <div class="form-group">
            <label>Số Điện Thoại:</label>
            <input type="text" name="SDT" id="SDT_update" value="<?php echo htmlspecialchars($getNhanVienUpdate->SDT); ?>" />
        </div>

        <div class="form-group">
            <label>Lương Cơ Bản:</label>
            <input type="number" name="luongCB" value="<?php echo htmlspecialchars($getNhanVienUpdate->luongCB); ?>" max="9999999999" />
        </div>

        <div class="form-group">
            <label>Phụ Cấp:</label>
            <input type="number" name="phuCap" value="<?php echo htmlspecialchars($getNhanVienUpdate->phuCap); ?>" max="9999999999" />
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
    .form-group input[type="number"],
    .form-group textarea,
    .form-group select {
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

<script>
    $(document).ready(function() {
        // Xử lý khi thay đổi user trong dropdown
        $('#iduser_update').change(function() {
            var userId = $(this).val();

            if (userId) {
                // Lấy thông tin user qua AJAX
                $.ajax({
                    url: './elements_LQA/mUser/getUserInfo.php',
                    type: 'GET',
                    data: {
                        iduser: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Điền thông tin vào form
                            var userData = response.data;
                            $('#tenNV_update').val(userData.hoten);
                            $('#SDT_update').val(userData.dienthoai);
                            if (userData.email) {
                                $('#email_update').val(userData.email);
                            }
                        } else {
                            alert('Không thể lấy thông tin người dùng: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Đã xảy ra lỗi khi kết nối đến máy chủ');
                    }
                });
            }
        });

        // Xử lý submit form cập nhật
        $('#updatenhanvien').submit(function(e) {
            e.preventDefault(); // Ngăn chặn form submit bình thường

            // Hiển thị trạng thái đang xử lý
            $('#noteForm').html('<span style="color:blue">Đang xử lý...</span>');

            // Thu thập dữ liệu từ form
            var formData = {
                reqact: 'updatenhanvien',
                idNhanVien: $('input[name="idNhanVien"]').val(),
                tenNV: $('#tenNV_update').val(),
                email: $('#email_update').val(),
                SDT: $('#SDT_update').val(),
                luongCB: $('input[name="luongCB"]').val(),
                phuCap: $('input[name="phuCap"]').val(),
                chucVu: $('input[name="chucVu"]').val(),
                iduser: $('#iduser_update').val() || null
            };

            // Hiển thị thông tin debug trong console
            console.log('Sending data:', formData);

            // Gửi AJAX request
            $.ajax({
                url: './elements_LQA/mnhanvien/nhanvienAct.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#noteForm').html('<span style="color:green">Cập nhật thành công!</span>');

                        // Đóng popup sau 1 giây và reload trang
                        setTimeout(function() {
                            $('#w_update_nv').hide();
                            location.reload();
                        }, 1000);
                    } else {
                        $('#noteForm').html('<span style="color:red">Lỗi: ' + (response.message || 'Không thể cập nhật') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    $('#noteForm').html('<span style="color:red">Lỗi kết nối đến máy chủ</span>');
                }
            });
        });
    });
</script>
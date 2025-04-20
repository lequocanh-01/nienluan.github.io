<div class="admin-title">Quản lý nhân viên</div>
<hr>

<?php
require_once './elements_LQA/mod/nhanvienCls.php';
require_once './elements_LQA/mod/userCls.php';

// Hiển thị thông báo nếu có
if (isset($_GET['notice']) && $_GET['notice'] == 'duplicate_user') {
    echo '<div class="alert alert-warning">Lưu ý: Người dùng này đã được gán cho một nhân viên khác.</div>';
}

$lhobj = new NhanVien();
$list_lh = $lhobj->nhanvienGetAll();
$l = count($list_lh);

// Lấy danh sách người dùng cho dropdown (trừ admin)
$userObj = new user();
$listUsers = $userObj->UserGetAllExceptAdmin();

// Lấy danh sách id của những người dùng đã là nhân viên để đánh dấu trong dropdown
$existingUserIds = [];
foreach ($list_lh as $employee) {
    if (isset($employee->iduser) && $employee->iduser) {
        $existingUserIds[] = $employee->iduser;
    }
}
?>

<div class="admin-form">
    <h3>Thêm nhân viên mới</h3>
    <form name="newnhanvien" id="formaddnhanvien" method="post"
        action='./elements_LQA/mnhanvien/nhanvienAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
            <tr>
                <td>Người dùng</td>
                <td>
                    <select name="iduser" id="iduser" class="form-control">
                        <option value="">-- Chọn người dùng --</option>
                        <?php foreach ($listUsers as $user): ?>
                            <option value="<?php echo $user->iduser; ?>"
                                <?php echo in_array($user->iduser, $existingUserIds) ? 'style="color: grey;"' : ''; ?>>
                                <?php echo htmlspecialchars($user->username) . ' (' . htmlspecialchars($user->hoten) . ') - ' . htmlspecialchars($user->dienthoai); ?>
                                <?php echo in_array($user->iduser, $existingUserIds) ? ' [Đã có nhân viên]' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tên nhân viên</td>
                <td><input type="text" name="tenNV" id="tenNV" required /></td>
            </tr>
            <tr>
                <td>Số điện thoại</td>
                <td><input type="tel" name="SDT" id="SDT" pattern="[0-9]{10}" required /></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" id="email" required /></td>
            </tr>
            <tr>
                <td>Lương cơ bản</td>
                <td><input type="number" name="luongCB" id="luongCB" max="9999999999" required /></td>
            </tr>
            <tr>
                <td>Phụ cấp</td>
                <td><input type="number" name="phuCap" id="phuCap" max="9999999999" required /></td>
            </tr>
            <tr>
                <td>Chức vụ</td>
                <td><input type="text" name="chucVu" id="chucVu" required /></td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<div class="content_nhanvien">
    <div class="admin-info">
        Tổng số nhân viên: <b><?php echo $l; ?></b>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên nhân viên</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Lương cơ bản</th>
                <th>Phụ cấp</th>
                <th>Chức vụ</th>
                <th>Người dùng</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($l > 0) {
                foreach ($list_lh as $u) {
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u->idNhanVien); ?></td>
                        <td><?php echo htmlspecialchars($u->tenNV); ?></td>
                        <td><?php echo htmlspecialchars($u->SDT); ?></td>
                        <td><?php echo htmlspecialchars($u->email); ?></td>
                        <td><?php echo number_format($u->luongCB, 0, ',', '.'); ?> đ</td>
                        <td><?php echo number_format($u->phuCap, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($u->chucVu); ?></td>
                        <td><?php echo isset($u->ten_user) ? htmlspecialchars($u->ten_user) : ''; ?></td>
                        <td align="center">
                            <?php if (isset($_SESSION['ADMIN'])) { ?>
                                <a href="./elements_LQA/mnhanvien/nhanvienAct.php?reqact=deletenhanvien&idNhanVien=<?php echo htmlspecialchars($u->idNhanVien); ?>"
                                    onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                    <img src="./img_LQA/Delete.png" class="iconimg">
                                </a>
                            <?php } else { ?>
                                <img src="./img_LQA/Delete.png" class="iconimg" />
                            <?php } ?>
                            <img src="./img_LQA/Update.png"
                                class="iconimg generic-update-btn"
                                data-module="mnhanvien"
                                data-update-url="./elements_LQA/mnhanvien/nhanvienUpdate.php"
                                data-id-param="idNhanVien"
                                data-title="Cập nhật Nhân viên"
                                data-id="<?php echo htmlspecialchars($u->idNhanVien); ?>"
                                alt="Update">
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<div id="w_update_nv">
    <div id="w_update_form_nv"></div>
    <button type="button" id="w_close_btn_nv">X</button>
</div>

<!-- Thêm JavaScript để xử lý chọn user -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Lưu danh sách user đã là nhân viên
        var existingUserIds = <?php echo json_encode($existingUserIds); ?>;

        // Xử lý khi thay đổi user trong dropdown
        $('#iduser').change(function() {
            var userId = $(this).val();
            $('#noteForm').text('');

            if (userId) {
                // Kiểm tra xem user đã là nhân viên chưa
                if (existingUserIds.includes(parseInt(userId))) {
                    $('#noteForm').html('<span style="color: orange;">Lưu ý: Người dùng này đã được gán cho một nhân viên khác.</span>');
                }

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
                            $('#tenNV').val(userData.hoten);
                            $('#SDT').val(userData.dienthoai);
                            if (userData.email) {
                                $('#email').val(userData.email);
                            }
                        } else {
                            alert('Không thể lấy thông tin người dùng: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Đã xảy ra lỗi khi kết nối đến máy chủ');
                    }
                });
            } else {
                // Xóa dữ liệu nếu không chọn user
                $('#tenNV').val('');
                $('#SDT').val('');
                $('#email').val('');
            }
        });
    });
</script>
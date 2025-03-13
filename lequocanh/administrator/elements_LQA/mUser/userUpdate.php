<head>
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
</head>

<?php
require './elements_LQA/mod/userCls.php';

$iduser = isset($_REQUEST['iduser']) ? $_REQUEST['iduser'] : 0;
$userObj = new user();
$getUserUpdate = $userObj->UserGetbyId($iduser);
?>

<div class="admin-content">
    <h3 class="admin-title">Cập nhật thông tin người dùng</h3>
    <form name="updateuser" id="formupdateuser" method="post" action="./elements_LQA/mUser/userAct.php?reqact=updateuser">
        <input type="hidden" name="iduser" value="<?php echo $getUserUpdate->iduser; ?>" />
        <table class="form-table">
            <tr>
                <td>Tên đăng nhập:</td>
                <td>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($getUserUpdate->username); ?>"
                        <?php echo ($getUserUpdate->username === 'admin') ? 'readonly' : 'required'; ?> />
                </td>
            </tr>
            <tr>
                <td>Mật khẩu:</td>
                <td>
                    <input type="password" name="password"
                        <?php echo ($getUserUpdate->username !== 'admin') ? 'required' : ''; ?>
                        placeholder="<?php echo ($getUserUpdate->username === 'admin') ? 'Để trống nếu không muốn đổi mật khẩu' : ''; ?>" />
                </td>
            </tr>
            <tr>
                <td>Họ tên:</td>
                <td><input type="text" name="hoten" value="<?php echo htmlspecialchars($getUserUpdate->hoten); ?>" required /></td>
            </tr>
            <tr>
                <td>Giới tính:</td>
                <td>
                    Nam<input type="radio" name="gioitinh" value="1" <?php echo $getUserUpdate->gioitinh == 1 ? 'checked' : ''; ?> />
                    Nữ<input type="radio" name="gioitinh" value="0" <?php echo $getUserUpdate->gioitinh == 0 ? 'checked' : ''; ?> />
                </td>
            </tr>
            <tr>
                <td>Ngày sinh:</td>
                <td><input type="date" name="ngaysinh" value="<?php echo $getUserUpdate->ngaysinh; ?>" required /></td>
            </tr>
            <tr>
                <td>Địa chỉ:</td>
                <td><input type="text" name="diachi" value="<?php echo htmlspecialchars($getUserUpdate->diachi); ?>" required /></td>
            </tr>
            <tr>
                <td>Điện thoại:</td>
                <td><input type="tel" name="dienthoai" value="<?php echo htmlspecialchars($getUserUpdate->dienthoai); ?>" pattern="[0-9]{10}" required /></td>
            </tr>

            <?php if ($getUserUpdate->username === 'admin'): ?>
                <tr>
                    <td>Mật khẩu xác thực:</td>
                    <td>
                        <input type="password" name="verify_password" required
                            placeholder="Nhập mật khẩu xác thực để hoàn tất cập nhật" />
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <td colspan="2" class="form-actions">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="index.php?req=userview" class="btn btn-secondary">Quay lại</a>
                </td>
            </tr>
        </table>
    </form>
</div>


<script src="../../js_LQA/jscript.js"></script>
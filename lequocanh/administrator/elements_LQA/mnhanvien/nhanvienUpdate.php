<div align="center">Cập nhật nhân viên</div>
<hr>
<?php
require '../../elements_LQA/mod/nhanvienCls.php';

$idNhanVien = isset($_REQUEST['idNhanVien']) ? $_REQUEST['idNhanVien'] : null;
if (!$idNhanVien) {
    echo "ID nhân viên không hợp lệ.";
    exit;
}

$lhobj = new NhanVien();
$getLhUpdate = $lhobj->nhanvienGetById($idNhanVien);

if (!$getLhUpdate) {
    echo "Không tìm thấy nhân viên.";
    exit;
}
?>

<div>
    <form name="updatenhanvien" id="formupdatelh" method="post" action='./elements_LQA/mnhanvien/nhanvienAct.php?reqact=updatenhanvien' enctype="multipart/form-data">
        <!-- Gửi idNhanVien để cập nhật chính xác -->
        <input type="hidden" name="idNhanVien" value="<?php echo $idNhanVien; ?>">
        
        <table>
            <tr>
                <td>Tên nhân viên</td>
                <td><input type="text" name="tenNV" value="<?php echo htmlspecialchars($getLhUpdate->tenNV); ?>" /></td>
            </tr>
            <tr>
                <td>SĐT</td>
                <td><input type="phone" name="SDT" value="<?php echo htmlspecialchars($getLhUpdate->SDT); ?>" /></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($getLhUpdate->email); ?>" /></td>
            </tr>
            <tr>
                <td>Lương CB</td>
                <td><input type="number" name="luongCB" value="<?php echo htmlspecialchars($getLhUpdate->luongCB); ?>" /></td>
            </tr>
            <tr>
                <td>Phụ Cấp</td>
                <td><input type="number" name="phuCap" value="<?php echo htmlspecialchars($getLhUpdate->phuCap); ?>" /></td>
            </tr>
            <tr>
                <td>Chức Vụ</td>
                <td><input type="text" name="chucVu" value="<?php echo htmlspecialchars($getLhUpdate->chucVu); ?>" /></td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Update" /></td>
                <td><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

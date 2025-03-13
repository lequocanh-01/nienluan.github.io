<div align="center">Cập nhật thương hiệu</div>
<hr>
<?php
require '../../elements_LQA/mod/thuonghieuCls.php';

// Kiểm tra tồn tại của idThuongHieu
$idThuongHieu = isset($_REQUEST['idThuongHieu']) ? $_REQUEST['idThuongHieu'] : null;
if (!$idThuongHieu) {
    echo "ID thương hiệu không hợp lệ.";
    exit;
}

$lhobj = new ThuongHieu();
$getLhUpdate = $lhobj->thuonghieuGetById($idThuongHieu);

if (!$getLhUpdate) {
    echo "Không tìm thấy thương hiệu.";
    exit;
}
?>

<div>
    <form name="updatethuonghieu" id="formupdatelh" method="post" action='./elements_LQA/mthuonghieu/thuonghieuAct.php?reqact=updatethuonghieu' enctype="multipart/form-data">
        <input type="hidden" name="idThuongHieu" value="<?php echo $idThuongHieu; ?>">
        <input type="hidden" name="hinhanh" value="<?php echo $getLhUpdate->hinhanh;  ?>" />
        <table>
            <tr>
                <td>Tên thương hiệu</td>
                <td><input type="text" size="50" name="tenTH" value="<?php echo htmlspecialchars($getLhUpdate->tenTH); ?>" /></td>
            </tr>
            <tr>
                <td>SĐT</td>
                <td><input type="text" size="50" name="SDT" value="<?php echo htmlspecialchars($getLhUpdate->SDT); ?>" /></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="text" size="50" name="email" value="<?php echo htmlspecialchars($getLhUpdate->email); ?>" /></td>
            </tr>
            <tr>
                <td>Địa Chỉ</td>
                <td><input type="text" size="50" name="diaChi" value="<?php echo htmlspecialchars($getLhUpdate->diaChi); ?>" /></td>
            </tr>
            <tr>
                <td>Hình ảnh</td>
                <td>
                    <img width="150px" src="data:image/png;base64,<?php echo $getLhUpdate->hinhanh ?>"><br>
                    <input type="file" name="fileimage">
                </td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Update" size="50" /></td>
                <td><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<?php
require '../../elements_LQA/mod/thuoctinhhhCls.php';

$idThuocTinhHH = $_POST['idThuocTinhHH'] ?? null;
$thuocTinhHHObj = new ThuocTinhHH();
$getLhUpdate = $thuocTinhHHObj->thuoctinhhhGetbyId($idThuocTinhHH);

if (!$getLhUpdate) {
    echo "Không tìm thấy thuộc tính với ID: " . htmlspecialchars($idThuocTinhHH);
    exit;
}
?>

<div align="center">Cập nhật thuộc tính hàng hóa</div>
<hr>

<form name="updatethuoctinhhh" method="post" action="./elements_LQA/mthuoctinhhh/thuoctinhhhAct.php?reqact=updatethuoctinhhh">
    <input type="hidden" name="idThuocTinhHH" value="<?php echo htmlspecialchars($getLhUpdate->idThuocTinhHH); ?>" />
    <table>
        <tr>
            <td>Tên Thuộc Tính HH</td>
            <td><input type="text" name="tenThuocTinhHH" value="<?php echo htmlspecialchars($getLhUpdate->tenThuocTinhHH); ?>" /></td>
        </tr>
        <tr>
            <td>Ghi Chú</td>
            <td><input type="text" name="ghiChu" value="<?php echo htmlspecialchars($getLhUpdate->ghiChu); ?>" /></td>
        </tr>
        <tr>
            <td><input type="submit" id="btnsubmit" value="Update" size="50" /></td>
            <td><b id="noteForm"></b></td>
        </tr>
    </table>
</form>
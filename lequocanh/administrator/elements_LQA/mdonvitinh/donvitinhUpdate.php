<div align="center">Cập nhật đơn vị tính</div>
<hr>
<?php
require '../../elements_LQA/mod/donvitinhCls.php';
$idDonViTinh = $_REQUEST['idDonViTinh'];
echo $idDonViTinh;

$lhobj = new donvitinh();
$getLhUpdate = $lhobj->donvitinhGetbyId($idDonViTinh);
// echo $getUserUpdate->hoten;

?>

<div>
    <form name="updatedonvitinh" id="formupdatelh" method="post" action='./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh' enctype="multipart/form-data">
    <input type="hidden" name="idDonViTinh" value="<?php echo $getLhUpdate->idDonViTinh;  ?>" />
    <input type="hidden" name="hinhanh" value="<?php echo $getLhUpdate->hinhanh;  ?>" />
        <table>
            <tr>
                <td>Tên đơn vị tính</td>
                <td><input type="text" name="tenDonViTinh" value="<?php echo $getLhUpdate->tenDonViTinh;
                                                                    ?>" /></td>
            </tr>
            <tr>
                <td>Mô tả</td>
                <td><input type="text" size="50" name="moTa" value="<?php echo $getLhUpdate->moTa;
                                                                    ?>" /></td>
            </tr>
            <tr>
                <td>Ghi Chú</td>
                <td>
                td><input type="text" size="50" name="ghiChu" value="<?php echo $getLhUpdate->ghiChu;
                                                                 ?>" /></td>
            </tr>

            <tr>
                <td><input type="submit" id="btnsubmit" value="Update" size="50" /></td>
                <td><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>
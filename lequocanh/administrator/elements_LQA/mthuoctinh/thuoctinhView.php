<div class="admin-title">Quản lý thuộc tính</div>
<hr>
<?php
require './elements_LQA/mod/thuoctinhCls.php';

$lhobj = new ThuocTinh();
$list_lh = $lhobj->thuoctinhGetAll();
$l = count($list_lh);
?>

<div class="admin-form">
    <h3>Thêm thuộc tính mới</h3>
    <form name="newthuoctinh" id="formaddthuoctinh" method="post" action='./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
            <tr>
                <td>Tên thuộc tính</td>
                <td><input type="text" name="tenThuocTinh" id="tenThuocTinh" required /></td>
            </tr>
            <tr>
                <td>Ghi Chú</td>
                <td><input type="text" name="ghiChu" /></td>
            </tr>
            <tr>
                <td>Hình ảnh</td>
                <td><input type="file" name="fileimage" required></td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<div class="content_thuoctinh">
    <div class="admin-info">
        Tổng số thuộc tính: <b><?php echo $l; ?></b>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên thuộc tính</th>
                <th>Ghi chú</th>
                <th>Hình ảnh</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($l > 0) {
            foreach ($list_lh as $u) {
        ?>
                <tr>
                    <td><?php echo htmlspecialchars($u->idThuocTinh); ?></td>
                    <td><?php echo htmlspecialchars($u->tenThuocTinh); ?></td>
                    <td><?php echo htmlspecialchars($u->ghiChu); ?></td>
                    <td align="center">
                        <img class="iconbutton" src="data:image/png;base64,<?php echo $u->hinhanh; ?>">
                    </td>
                    <td align="center">
                        <?php if (isset($_SESSION['ADMIN'])) { ?>
                            <a href="./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=deletethuoctinh&idThuocTinh=<?php echo $u->idThuocTinh; ?>">
                                <img src="./img_LQA/delete.png" class="iconimg">
                            </a>
                        <?php } else { ?>
                            <img src="./img_LQA/delete.png" class="iconimg">
                        <?php } ?>
                        <img src="./img_LQA/Update.png" class="w_update_btn_open_tt" value="<?php echo $u->idThuocTinh; ?>">
                    </td>
                </tr>
        <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>

<div id="w_update_tt">
    <div id="w_update_form_tt"></div>
    <input type="button" value="close" id="w_close_btn_tt">
</div>

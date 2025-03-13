<div class="admin-title">Quản lý đơn vị tính</div>
<hr>
<?php
require_once './elements_LQA/mod/donvitinhCls.php';

$lhobj = new donvitinh();
$list_lh = $lhobj->donvitinhGetAll();
$l = count($list_lh);
?>

<div class="admin-form">
    <h3>Thêm đơn vị tính mới</h3>
    <form name="newdonvitinh" id="formadddonvitinh" method="post" action='./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
            <tr>
                <td>Tên đơn vị tính</td>
                <td><input type="text" name="tenDonViTinh" id="tenDonViTinh" required /></td>
            </tr>
            <tr>
                <td>Mô tả</td>
                <td><input type="text" name="moTa" id="moTa" /></td>
            </tr>
            <tr>
                <td>Ghi chú</td>
                <td><input type="text" name="ghiChu" id="ghiChu" /></td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<div class="content_donvitinh">
    <div class="admin-info">
        Tổng số đơn vị tính: <b><?php echo $l; ?></b>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đơn vị tính</th>
                <th>Mô tả</th>
                <th>Ghi chú</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($l > 0) {
                foreach ($list_lh as $u) {
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u->idDonViTinh); ?></td>
                        <td><?php echo htmlspecialchars($u->tenDonViTinh); ?></td>
                        <td><?php echo htmlspecialchars($u->moTa); ?></td>
                        <td><?php echo htmlspecialchars($u->ghiChu); ?></td>
                        <td align="center">
                            <?php if (isset($_SESSION['ADMIN'])) { ?>
                                <a href="./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=deletedonvitinh&iddonvitinh=<?php echo htmlspecialchars($u->idDonViTinh); ?>" onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                    <img src="./img_LQA/delete.png" class="iconimg">
                                </a>
                            <?php } else { ?>
                                <img src="./img_LQA/delete.png" class="iconimg">
                            <?php } ?>
                            <img src="./img_LQA/Update.png" class="w_update_btn_open_dvt" value="<?php echo htmlspecialchars($u->idDonViTinh); ?>">
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<div id="w_update_dvt">
    <div id="w_update_form_dvt"></div>
    <input type="button" value="close" id="w_close_btn_dvt">
</div>
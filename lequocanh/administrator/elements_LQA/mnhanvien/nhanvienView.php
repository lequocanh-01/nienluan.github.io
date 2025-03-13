<div class="admin-title">Quản lý nhân viên</div>
<hr>

<?php
require_once './elements_LQA/mod/nhanvienCls.php';

$lhobj = new NhanVien();
$list_lh = $lhobj->nhanvienGetAll();
$l = count($list_lh);
?>

<div class="admin-form">
    <h3>Thêm nhân viên mới</h3>
    <form name="newnhanvien" id="formaddnhanvien" method="post" action='./elements_LQA/mnhanvien/nhanvienAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
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
                <td><input type="number" name="luongCB" id="luongCB" required /></td>
            </tr>
            <tr>
                <td>Phụ cấp</td>
                <td><input type="number" name="phuCap" id="phuCap" required /></td>
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
                        <td align="center">
                            <?php if (isset($_SESSION['ADMIN'])) { ?>
                                <a href="./elements_LQA/mnhanvien/nhanvienAct.php?reqact=deletenhanvien&idNhanVien=<?php echo htmlspecialchars($u->idNhanVien); ?>" onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                    <img src="./img_LQA/delete.png" class=""
                                </a>
                            <?php } else { ?>
                                <img src="./img_LQA/delete.png" class="">
                            <?php } ?>
                            <img src="./img_LQA/Update.png" class="w_update_btn_open_nv" value="<?php echo htmlspecialchars($u->idNhanVien); ?>">
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
    <input type="button" value="close" id="w_close_btn_nv">
</div>

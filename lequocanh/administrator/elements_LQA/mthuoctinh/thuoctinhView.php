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
    <form name="newthuoctinh" id="formaddthuoctinh" method="post"
        action='./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=addnew' enctype="multipart/form-data">
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
                                <a
                                    href="./elements_LQA/mthuoctinh/thuoctinhAct.php?reqact=deletethuoctinh&idThuocTinh=<?php echo $u->idThuocTinh; ?>">
                                    <img src="./img_LQA/Delete.png" class="iconimg">
                                </a>
                            <?php } else { ?>
                                <img src="./img_LQA/Delete.png" class="iconimg">
                            <?php } ?>
                            <img src="./img_LQA/Update.png"
                                class="iconimg w_update_btn_open_tt"
                                value="<?php echo htmlspecialchars($u->idThuocTinh); ?>"
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

<!-- Container cho popup cập nhật thuộc tính -->
<div id="w_update_tt" class="visible-modal" style="display: none;">
    <div id="w_close_btn_tt" class="close-btn">X</div>
    <div id="w_update_form_tt"></div>
</div>

<style>
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 9990;
    }

    .popup-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 500px;
        background: transparent;
        border: none;
        z-index: 9995;
        box-shadow: none;
    }

    .popup-content {
        width: 100%;
        height: 100%;
        z-index: 9996;
        overflow: visible;
        background: transparent;
    }

    .iconimg {
        cursor: pointer;
        width: 24px;
        height: 24px;
    }
</style>

<script>
    $(document).ready(function() {
        // Để đảm bảo popup hiển thị đúng
        $("#w_update_tt").css("display", "none");

        // Hiển thị popup
        $(".w_update_btn_open_tt").click(function(e) {
            e.preventDefault();
            console.log("Clicked update button for thuoctinh ID:", $(this).attr("value"));
            $("#w_update_tt").css("display", "block");
        });

        // Đóng popup khi nhấp vào nút đóng
        $("#w_close_btn_tt").click(function() {
            $("#w_update_tt").css("display", "none");
        });

        // Đóng popup khi nhấp bên ngoài
        $("#w_update_tt").click(function(e) {
            if ($(e.target).is("#w_update_tt")) {
                $("#w_update_tt").css("display", "none");
            }
        });
    });
</script>
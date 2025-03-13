<div class="admin-title">Quản lý đơn giá</div>
<hr>
<?php
require_once './elements_LQA/mod/dongiaCls.php';
require_once './elements_LQA/mod/hanghoaCls.php';

$lhobj = new Dongia();
$list_lh = $lhobj->DongiaGetAll();
$l = count($list_lh);

$hhobj = new Hanghoa();
$list_hh = $hhobj->HanghoaGetAll();

if (empty($list_hh)) {
    $list_hh = [];
}
?>

<div class="admin-form">
    <h3>Thêm đơn giá mới</h3>
    <form name="newdongia" id="formadddongia" method="post" action='./elements_LQA/mdongia/dongiaAct.php?reqact=addnew' enctype="multipart/form-data">
        <table>
            <tr>
                <td>Chọn hàng hóa:</td>
                <td>
                    <select name="idhanghoa" id="hanghoaSelect" onchange="updatePrice()" required>
                        <option value="">-- Chọn hàng hóa --</option>
                        <?php
                        if (!empty($list_hh)) {
                            foreach ($list_hh as $h) {
                        ?>
                                <option value="<?php echo htmlspecialchars($h->idhanghoa); ?>" 
                                        data-price="<?php echo htmlspecialchars($h->giathamkhao); ?>">
                                    <?php echo htmlspecialchars($h->tenhanghoa); ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Giá bán</td>
                <td><input type="text" name="giaban" id="giaban" required /></td>
            </tr>
            <tr>
                <td>Tên hàng hóa</td>
                <td><input type="text" name="tenHangHoa" id="tenHangHoa" readonly /></td>
            </tr>
            <tr>
                <td>Ngày áp dụng</td>
                <td><input type="date" name="ngayapdung" required /></td>
            </tr>
            <tr>
                <td>Ngày kết thúc</td>
                <td><input type="date" name="ngayketthuc" required /></td>
            </tr>
            <tr>
                <td>Điều kiện</td>
                <td><input type="text" name="dieukien" /></td>
            </tr>
            <tr>
                <td>Ghi chú</td>
                <td><input type="text" name="ghichu" /></td>
            </tr>
            <tr>
                <td><input type="submit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<div class="content_dongia">
    <div class="admin-info">
        Tổng số đơn giá: <b><?php echo $l; ?></b>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Hàng Hóa</th>
                <th>Tên Hàng Hóa</th>
                <th>Giá Bán</th>
                <th>Ngày áp dụng</th>
                <th>Ngày kết thúc</th>
                <th>Điều kiện</th>
                <th>Ghi chú</th>
                <th>Áp dụng</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($l > 0) {
                foreach ($list_lh as $u) {
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u->idDonGia); ?></td>
                        <td><?php echo htmlspecialchars($u->idHangHoa); ?></td>
                        <td><?php echo htmlspecialchars($u->tenhanghoa); ?></td>
                        <td><?php echo number_format($u->giaBan, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($u->ngayApDung); ?></td>
                        <td><?php echo htmlspecialchars($u->ngayKetThuc); ?></td>
                        <td><?php echo htmlspecialchars($u->dieuKien); ?></td>
                        <td><?php echo htmlspecialchars($u->ghiChu); ?></td>
                        <td>
                            <form method="post" action="./elements_LQA/mdongia/updateSetFalse.php">
                                <input type="hidden" name="idDonGia" value="<?php echo htmlspecialchars($u->idDonGia); ?>">
                                <input type="hidden" name="apDung" value="<?php echo $u->apDung ? 'false' : 'true'; ?>">
                                <button type="submit" class="btn-status <?php echo $u->apDung ? 'active' : ''; ?>">
                                    <?php echo $u->apDung ? 'Đang áp dụng' : 'Ngừng áp dụng'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    function updatePrice() {
        var select = document.getElementById("hanghoaSelect");
        var selectedOption = select.options[select.selectedIndex];
        var price = selectedOption.getAttribute("data-price");
        var name = selectedOption.text;
        document.getElementById("giaban").value = price;
        document.getElementById("tenHangHoa").value = name;
    }
</script>
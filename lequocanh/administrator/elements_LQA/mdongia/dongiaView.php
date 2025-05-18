<div class="admin-title">Quản lý đơn giá</div>
<hr>
<?php
// Hiển thị thông báo nếu có
if (isset($_SESSION['dongia_message'])) {
    $message = $_SESSION['dongia_message'];
    $success = isset($_SESSION['dongia_success']) ? $_SESSION['dongia_success'] : false;
    $alertClass = $success ? 'alert-success' : 'alert-danger';
    echo '<div class="alert ' . $alertClass . '" role="alert">' . htmlspecialchars($message) . '</div>';

    // Xóa thông báo sau khi hiển thị
    unset($_SESSION['dongia_message']);
    unset($_SESSION['dongia_success']);
}
?>
<style>
    /* CSS cho thông báo */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    /* CSS cho nút trạng thái áp dụng */
    .btn-status {
        display: inline-block;
        padding: 10px 15px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
        width: 160px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        letter-spacing: 0.5px;
    }

    /* Nút "Ngừng áp dụng" */
    .btn-status:not(.active) {
        background: linear-gradient(135deg, #ff7675, #d63031);
        color: white;
        border: 2px solid #ff7675;
    }

    /* Nút "Đang áp dụng" */
    .btn-status.active {
        background: linear-gradient(135deg, #00b894, #00cec9);
        color: white;
        border: 2px solid #00b894;
    }

    /* Hiệu ứng hover */
    .btn-status:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        filter: brightness(1.05);
    }

    /* Hiệu ứng khi nhấn */
    .btn-status:active {
        transform: translateY(1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Thêm biểu tượng */
    .btn-status::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        margin-right: 8px;
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .btn-status.active::before {
        content: "\f058"; /* Biểu tượng check-circle */
        color: #ffffff;
    }

    .btn-status:not(.active)::before {
        content: "\f057"; /* Biểu tượng times-circle */
        color: #ffffff;
    }

    /* Hiệu ứng hover cho biểu tượng */
    .btn-status:hover::before {
        transform: rotate(360deg);
    }

    /* Hiệu ứng ripple khi click */
    .btn-status::after {
        content: "";
        position: absolute;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        width: 100px;
        height: 100px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: transform 0.5s, opacity 0.5s;
        pointer-events: none;
    }

    .btn-status:active::after {
        transform: translate(-50%, -50%) scale(2);
        opacity: 0;
        transition: 0s;
    }

    /* Thêm hiệu ứng đổ bóng */
    .btn-status.active {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .btn-status:not(.active) {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Thêm hiệu ứng pulse cho nút đang áp dụng */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 184, 148, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 184, 148, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 184, 148, 0);
        }
    }

    .btn-status.active {
        animation: pulse 2s infinite;
    }
</style>
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
                                <option value="<?php echo htmlspecialchars($h->idhanghoa ?? ''); ?>"
                                    data-price="<?php echo htmlspecialchars($h->giathamkhao ?? ''); ?>">
                                    <?php echo htmlspecialchars($h->tenhanghoa ?? ''); ?>
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
                        <td><?php echo htmlspecialchars($u->idDonGia ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($u->idHangHoa ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($u->tenhanghoa ?? ''); ?></td>
                        <td><?php echo number_format($u->giaBan, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($u->ngayApDung ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($u->ngayKetThuc ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($u->dieuKien ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($u->ghiChu ?? ''); ?></td>
                        <td>
                            <form method="post" action="./elements_LQA/mdongia/updateSetFalse.php">
                                <input type="hidden" name="idDonGia" value="<?php echo htmlspecialchars($u->idDonGia ?? ''); ?>">
                                <input type="hidden" name="apDung" value="<?php echo $u->apDung ? 'false' : 'true'; ?>">
                                <button type="submit" class="btn-status <?php echo $u->apDung ? 'active' : ''; ?>" title="<?php echo $u->apDung ? 'Đây là giá đang được áp dụng. Nhấn để ngừng áp dụng.' : 'Đây là giá không được áp dụng. Nhấn để áp dụng giá này.'; ?>">
                                    <?php echo $u->apDung ? 'Đang áp dụng' : 'Chọn áp dụng'; ?>
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
    // Cập nhật giá và tên hàng hóa khi chọn sản phẩm
    function updatePrice() {
        var select = document.getElementById("hanghoaSelect");
        if (select.selectedIndex > 0) {
            var selectedOption = select.options[select.selectedIndex];
            var price = selectedOption.getAttribute("data-price");
            var name = selectedOption.text;
            document.getElementById("giaban").value = price;
            document.getElementById("tenHangHoa").value = name;

            // Tự động đặt ngày áp dụng là ngày hiện tại
            var today = new Date();
            var formattedDate = today.toISOString().substr(0, 10);

            // Chỉ đặt ngày áp dụng nếu chưa được đặt
            var ngayApDungInput = document.querySelector('input[name="ngayapdung"]');
            if (!ngayApDungInput.value) {
                ngayApDungInput.value = formattedDate;
            }

            // Đặt ngày kết thúc là 1 năm sau nếu chưa được đặt
            var nextYear = new Date();
            nextYear.setFullYear(today.getFullYear() + 1);
            var formattedNextYear = nextYear.toISOString().substr(0, 10);

            var ngayKetThucInput = document.querySelector('input[name="ngayketthuc"]');
            if (!ngayKetThucInput.value) {
                ngayKetThucInput.value = formattedNextYear;
            }
        }
    }

    // Kiểm tra form trước khi submit
    document.getElementById("formadddongia").addEventListener("submit", function(event) {
        var idHangHoa = document.getElementById("hanghoaSelect").value;
        var giaBan = document.getElementById("giaban").value;
        var ngayApDung = document.querySelector('input[name="ngayapdung"]').value;
        var ngayKetThuc = document.querySelector('input[name="ngayketthuc"]').value;

        if (!idHangHoa || !giaBan || !ngayApDung || !ngayKetThuc) {
            event.preventDefault();
            alert("Vui lòng điền đầy đủ thông tin bắt buộc!");
            return false;
        }

        // Kiểm tra ngày áp dụng phải trước ngày kết thúc
        var apDungDate = new Date(ngayApDung);
        var ketThucDate = new Date(ngayKetThuc);

        if (apDungDate >= ketThucDate) {
            event.preventDefault();
            alert("Ngày áp dụng phải trước ngày kết thúc!");
            return false;
        }

        // Kiểm tra giá bán phải là số dương
        if (isNaN(giaBan) || parseFloat(giaBan) <= 0) {
            event.preventDefault();
            alert("Giá bán phải là số dương!");
            return false;
        }

        return true;
    });

    // Đặt ngày mặc định khi trang được tải
    window.onload = function() {
        var today = new Date();
        var formattedDate = today.toISOString().substr(0, 10);

        // Đặt ngày áp dụng là ngày hiện tại
        var ngayApDungInput = document.querySelector('input[name="ngayapdung"]');
        if (!ngayApDungInput.value) {
            ngayApDungInput.value = formattedDate;
        }

        // Đặt ngày kết thúc là 1 năm sau
        var nextYear = new Date();
        nextYear.setFullYear(today.getFullYear() + 1);
        var formattedNextYear = nextYear.toISOString().substr(0, 10);

        var ngayKetThucInput = document.querySelector('input[name="ngayketthuc"]');
        if (!ngayKetThucInput.value) {
            ngayKetThucInput.value = formattedNextYear;
        }
    };
</script>
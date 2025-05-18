<div class="admin-title">Quản lý thuộc tính hàng hóa</div>
<hr>
<?php
require_once './elements_LQA/mod/hanghoaCls.php';
require_once './elements_LQA/mod/thuoctinhCls.php';
require_once './elements_LQA/mod/thuoctinhhhCls.php';

// Lấy danh sách hàng hóa
$hangHoaObj = new HangHoa();
$list_hh = $hangHoaObj->HanghoaGetAll();

// Lấy danh sách thuộc tính
$thuocTinhObj = new ThuocTinh();
$list_lh_thuoctinh = $thuocTinhObj->thuoctinhGetAll();

// Lấy danh sách thuộc tính hàng hóa
$thuocTinhHHObj = new ThuocTinhHH();
$list_lh_thuoctinhhh = $thuocTinhHHObj->thuoctinhhhGetAll();
?>

<div class="admin-form">
    <h3>Thêm thuộc tính hàng hóa mới</h3>
    <form name="newthuoctinhhh" id="formaddthuoctinhhh" method="post"
        action='./elements_LQA/mthuoctinhhh/thuoctinhhhAct.php?reqact=addnew'>
        <table>
            <tr>
                <td>Chọn hàng hóa:</td>
                <td>
                    <select name="idhanghoa" id="hanghoaSelect" required>
                        <option value="">-- Chọn hàng hóa --</option>
                        <?php if (!empty($list_hh)) {
                            foreach ($list_hh as $h) { ?>
                                <option value="<?php echo htmlspecialchars($h->idhanghoa); ?>">
                                    <?php echo htmlspecialchars($h->tenhanghoa); ?></option>
                        <?php }
                        } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Chọn thuộc tính:</td>
                <td>
                    <select name="idThuocTinh" id="idThuocTinh" required>
                        <?php if (!empty($list_lh_thuoctinh)) {
                            foreach ($list_lh_thuoctinh as $l) { ?>
                                <option value="<?php echo htmlspecialchars($l->idThuocTinh); ?>">
                                    <?php echo htmlspecialchars($l->tenThuocTinh); ?></option>
                        <?php }
                        } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tên Thuộc Tính HH</td>
                <td><input type="text" name="tenThuocTinhHH" required /></td>
            </tr>
            <tr>
                <td>Ghi Chú</td>
                <td><input type="text" name="ghiChu" /></td>
            </tr>
            <tr>
                <td><input type="submit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<div class="content_thuoctinhhh">
    <div class="admin-info">
        Tổng số thuộc tính hàng hóa: <b><?php echo count($list_lh_thuoctinhhh); ?></b>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Hàng Hóa</th>
                <th>ID Thuộc Tính</th>
                <th>Tên Thuộc Tính HH</th>
                <th>Ghi Chú</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list_lh_thuoctinhhh)) {
                foreach ($list_lh_thuoctinhhh as $u) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u->idThuocTinhHH); ?></td>
                        <td><?php echo htmlspecialchars($u->idhanghoa); ?></td>
                        <td><?php echo htmlspecialchars($u->idThuocTinh); ?></td>
                        <td class="tenthuoctinhhh"><?php echo htmlspecialchars($u->tenThuocTinhHH); ?></td>
                        <td><?php echo htmlspecialchars($u->ghiChu ?? ""); ?></td>
                        <td align="center">
                            <?php if (isset($_SESSION['ADMIN'])) { ?>
                                <a href="./elements_LQA/mthuoctinhhh/thuoctinhhhAct.php?reqact=deletethuoctinhhh&idThuocTinhHH=<?php echo htmlspecialchars($u->idThuocTinhHH); ?>"
                                    onclick="return confirm('Bạn có chắc muốn xóa không?');">
                                    <img src="./elements_LQA/img_LQA/Delete.png" class="iconimg">
                                </a>
                            <?php } else { ?>
                                <img src="./elements_LQA/img_LQA/Delete.png" class="iconimg">
                            <?php } ?>
                            <img src="./elements_LQA/img_LQA/Update.png"
                                class="iconimg generic-update-btn"
                                data-module="mthuoctinhhh"
                                data-update-url="./elements_LQA/mthuoctinhhh/thuoctinhhhUpdate.php"
                                data-id-param="idThuocTinhHH"
                                data-title="Cập nhật Thuộc tính hàng hóa"
                                data-id="<?php echo htmlspecialchars($u->idThuocTinhHH); ?>"
                                alt="Update">
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<!-- Nút quay lại đầu trang -->
<div id="back-to-top" class="back-to-top-button">
    <i class="fas fa-arrow-up"></i>
    <span class="tooltip">Lên đầu trang</span>
</div>

<style>
    .back-to-top-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        font-size: 20px;
    }

    .back-to-top-button:hover {
        background-color: #0056b3;
        transform: translateY(-3px);
    }

    .back-to-top-button.visible {
        opacity: 1;
        visibility: visible;
    }

    /* Tooltip */
    .back-to-top-button .tooltip {
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .back-to-top-button .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }

    .back-to-top-button:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.getElementById('back-to-top');

        // Kiểm tra vị trí cuộn khi trang tải
        checkScrollPosition();

        // Hiển thị nút khi người dùng cuộn xuống 300px
        window.addEventListener('scroll', checkScrollPosition);

        // Xử lý sự kiện khi nhấp vào nút
        backToTopButton.addEventListener('click', function() {
            // Kiểm tra hỗ trợ cuộn mượt
            if ('scrollBehavior' in document.documentElement.style) {
                // Trình duyệt hỗ trợ cuộn mượt
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                // Trình duyệt không hỗ trợ cuộn mượt, sử dụng JavaScript
                smoothScrollToTop();
            }
        });

        // Hàm kiểm tra vị trí cuộn
        function checkScrollPosition() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        }

        // Hàm cuộn mượt cho các trình duyệt không hỗ trợ scrollBehavior
        function smoothScrollToTop() {
            const currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
            if (currentScroll > 0) {
                window.requestAnimationFrame(smoothScrollToTop);
                window.scrollTo(0, currentScroll - currentScroll / 8);
            }
        }
    });
</script>
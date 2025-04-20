<div class="admin-title">Quản lý nhà cung cấp</div>
<hr>
<div class="admin-form">
    <h3>Thêm nhà cung cấp mới</h3>
    <form name="newnhacungcap" id="formaddncc" method="post"
        action='./elements_LQA/mnhacungcap/nhacungcapAct.php?reqact=addnew'>
        <table>
            <tr>
                <td>Tên nhà cung cấp</td>
                <td><input type="text" name="tenNCC" required /></td>
            </tr>
            <tr>
                <td>Người liên hệ</td>
                <td><input type="text" name="nguoiLienHe" /></td>
            </tr>
            <tr>
                <td>Số điện thoại</td>
                <td><input type="text" name="soDienThoai" /></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" /></td>
            </tr>
            <tr>
                <td>Địa chỉ</td>
                <td><textarea name="diaChi" rows="3"></textarea></td>
            </tr>
            <tr>
                <td>Mã số thuế</td>
                <td><input type="text" name="maSoThue" /></td>
            </tr>
            <tr>
                <td>Ghi chú</td>
                <td><textarea name="ghiChu" rows="3"></textarea></td>
            </tr>
            <tr>
                <td><input type="submit" id="btnsubmit" value="Tạo mới" /></td>
                <td><input type="reset" value="Làm lại" /><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<hr />
<?php
require_once './elements_LQA/mod/nhacungcapCls.php';
$nccObj = new nhacungcap();
$list_ncc = $nccObj->NhacungcapGetAll();
$l = count($list_ncc);
?>
<div class="admin-content">
    <div class="admin-info">
        Tổng số nhà cung cấp: <b><?php echo $l; ?></b>
    </div>

    <div class="search-box">
        <form id="search-form" class="search-form">
            <input type="text" id="search-input" placeholder="Tìm kiếm nhà cung cấp..." />
            <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
        </form>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên nhà cung cấp</th>
                <th>Người liên hệ</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Mã số thuế</th>
                <th>Ghi chú</th>
                <th>Trạng thái</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody id="supplier-list">
            <?php
            if ($l > 0) {
                foreach ($list_ncc as $ncc) {
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ncc->idNCC); ?></td>
                        <td><?php echo htmlspecialchars($ncc->tenNCC); ?></td>
                        <td><?php echo htmlspecialchars($ncc->nguoiLienHe ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ncc->soDienThoai ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ncc->email ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ncc->diaChi ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ncc->maSoThue ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($ncc->ghiChu ?? ''); ?></td>
                        <td align="center">
                            <?php if ($ncc->trangThai == 1): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Không hoạt động</span>
                            <?php endif; ?>
                        </td>
                        <td align="center">
                            <?php
                            if (isset($_SESSION['ADMIN'])) {
                            ?>
                                <a href="./elements_LQA/mnhacungcap/nhacungcapAct.php?reqact=deletenhacungcap&idNCC=<?php echo $ncc->idNCC; ?>"
                                    onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?');">
                                    <img src="./img_LQA/Delete.png" class="iconimg">
                                </a>
                            <?php
                            } else {
                            ?>
                                <img src="./img_LQA/Delete.png" class="iconimg">
                            <?php
                            }
                            ?>
                            <img src="./img_LQA/Update.png"
                                class="iconimg generic-update-btn"
                                data-module="mnhacungcap"
                                data-update-url="./elements_LQA/mnhacungcap/nhacungcapUpdate.php"
                                data-id-param="idNCC"
                                data-title="Cập nhật Nhà cung cấp"
                                data-id="<?php echo htmlspecialchars($ncc->idNCC); ?>"
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

<!-- Popup container for supplier update -->
<div id="w_update_ncc">
    <div class="update-popup-wrapper">
        <span id="w_close_btn_ncc">X</span>
        <div id="w_update_form_ncc"></div>
    </div>
</div>

<style>
    #w_update_ncc {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border: 2px solid #3498db;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border-radius: 5px;
        padding: 15px;
        z-index: 9999;
        display: none;
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .update-popup-wrapper {
        position: relative;
    }

    #w_close_btn_ncc {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #f44336;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        text-align: center;
        line-height: 25px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
    }

    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #28a745 !important;
        color: white;
    }

    .bg-secondary {
        background-color: #6c757d !important;
        color: white;
    }

    .search-box {
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 10px;
    }

    .search-form input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .search-form button {
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .search-form button:hover {
        background-color: #0056b3;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle search form
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = searchInput.value.trim().toLowerCase();

            // Simple client-side filtering
            const rows = document.querySelectorAll('#supplier-list tr');
            rows.forEach(row => {
                let matchFound = false;
                const cells = row.querySelectorAll('td');

                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        matchFound = true;
                    }
                });

                row.style.display = matchFound ? '' : 'none';
            });
        });

        // Reset search when input is cleared
        searchInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                const rows = document.querySelectorAll('#supplier-list tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
            }
        });
    });
</script>
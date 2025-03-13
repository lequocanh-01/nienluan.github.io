<script>
    function goBack() {
        window.history.back();
    }
</script>

<?php
require_once './administrator/elements_LQA/mod/hanghoaCls.php';
require_once './administrator/elements_LQA/mod/thuoctinhhhCls.php';
require_once './administrator/elements_LQA/mod/thuoctinhCls.php';
$hanghoa = new hanghoa();

if (isset($_GET['reqHanghoa'])) {
    $idhanghoa = $_GET['reqHanghoa'];
    $obj = $hanghoa->HanghoaGetbyId($idhanghoa);
    
    // Thêm truy vấn để lấy thông tin thuộc tính hàng hóa
    $thuocTinhHHObj = new ThuocTinhHH(); 
    $listThuocTinh = $thuocTinhHHObj->thuoctinhhhGetbyIdHanghoa($idhanghoa); 
}
?>
  <link rel="stylesheet" href="public_files/mycss.css">  
  <script src="administrator/elements_LQA/js_LQA/jscript.js"></script>

<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <?php
            // Get the image information from the database
            $image_info = null;
            if (isset($obj->hinhanh) && !empty($obj->hinhanh)) {
                $image_info = $hanghoa->GetHinhAnhById($obj->hinhanh);
            }
            
            if ($image_info && isset($image_info->duong_dan)) {
                $image_src = $image_info->duong_dan;
                
                if (strpos($image_src, 'data:image') === 0) {
                    // Keep base64 as is
                    echo '<img src="' . $image_src . '" class="img-fluid rounded-start" alt="' . htmlspecialchars($obj->tenhanghoa) . '">';
                } else {
                    // Try different path combinations
                    $possible_paths = [
                        "./administrator/uploads/" . basename($image_src),
                        "./uploads/" . basename($image_src),
                        "./administrator/" . $image_src,
                        "./" . $image_src
                    ];
                    
                    $display_src = "./administrator/img_LQA/no-image.png";
                    foreach ($possible_paths as $path) {
                        if (file_exists($path)) {
                            $display_src = $path;
                            break;
                        }
                    }
                    echo '<img src="' . $display_src . '" class="img-fluid rounded-start" alt="' . htmlspecialchars($obj->tenhanghoa) . '" onerror="this.src=\'./administrator/img_LQA/no-image.png\'">';
                }
            } else {
                // No image found
                echo '<img src="./administrator/img_LQA/no-image.png" class="img-fluid rounded-start" alt="No Image">';
            }
            ?>
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title"><?php echo $obj->tenhanghoa; ?></h5>
                <p class="card-text"><?php echo $obj->mota; ?></p>
                <p class="card-text">
                    <small class="text-muted">Giá bán: 
                        <span class="text-danger fw-bold">
                            <?php echo number_format($obj->giathamkhao, 0, ',', '.') . ' VNĐ'; ?>
                        </span>
                    </small>
                </p>
                <p class="card-text"><strong>Thương hiệu: </strong><?php echo $obj->idThuongHieu ? $hanghoa->GetThuongHieuById($obj->idThuongHieu)->tenTH : 'Chưa chọn'; ?></p>
                <!-- Hiển thị thông tin thuộc tính hàng hóa -->
                <?php if (!empty($listThuocTinh)): ?>
                    <h6>Thông số kỹ thuật:</h6>
                    <ul>
                        <?php foreach ($listThuocTinh as $tt): ?>
                            <?php
                            // Lấy tên thuộc tính từ bảng thuoctinh
                            $thuocTinhObj = new ThuocTinh();
                            $thuocTinh = $thuocTinhObj->thuoctinhGetbyId($tt->idThuocTinh);
                            ?>
                            <li>
                                <strong><?php echo htmlspecialchars($thuocTinh->tenThuocTinh); ?>:</strong> 
                                <?php echo htmlspecialchars($tt->tenThuocTinhHH); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <!-- Add the cart icon here -->
                <a href="administrator/elements_LQA/mgiohang/giohangAct.php?action=add&productId=<?php echo $obj->idhanghoa; ?>&quantity=1" class="btn btn-primary ms-2">
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart-fill" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                        </svg>
                        <!-- Giỏ hàng -->
                    </div>
                </a>
                <!-- Existing Buy button -->
                <a href="./purchase.php?productId=<?php echo $obj->idhanghoa; ?>" 
                   class="btn btn-success" 
                   onclick="return confirm('Bạn có chắc chắn muốn mua sản phẩm này?');">
                   Mua
                </a>
                <button onclick="goBack()" class="btn btn-secondary">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<style>

</style>

<?php if (isset($_SESSION['USER'])): ?>
    <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user me-2"></i>
            <?php echo $_SESSION['USER']; ?>
        </button>
        <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="./administrator/elements_LQA/mUser/userAct.php?reqact=userlogout">
                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
            </a></li>
        </ul>
    </div>
<?php elseif (isset($_SESSION['ADMIN'])): ?>
    <a href="./administrator/index.php" class="btn btn-light me-2">
        <i class="fas fa-user-shield me-2"></i>
        Quản trị viên
    </a>
<?php else: ?>
    <a href="./administrator/userLogin.php" class="btn btn-light me-2">
        <i class="fas fa-user me-2"></i>
        Đăng nhập
    </a>
<?php endif; ?>

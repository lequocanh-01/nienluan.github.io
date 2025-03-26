<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once './administrator/elements_LQA/mod/hanghoaCls.php';
require_once './administrator/elements_LQA/mod/thuoctinhhhCls.php';
require_once './administrator/elements_LQA/mod/thuoctinhCls.php';

$hanghoa = new hanghoa();
$thuocTinhHH = new ThuocTinhHH();

// Lấy danh sách sản phẩm để so sánh từ query string
$productIds = isset($_GET['products']) ? explode(',', $_GET['products']) : [];
$products = [];
$productFeatures = [];

// Lấy thông tin chi tiết của từng sản phẩm
foreach ($productIds as $id) {
    $product = $hanghoa->HanghoaGetbyId($id);
    if ($product) {
        $products[] = $product;
        // Lấy thuộc tính của sản phẩm
        $features = $thuocTinhHH->thuoctinhhhGetbyIdHanghoa($id);
        $productFeatures[$id] = $features;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>So sánh sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public_files/mycss.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="mb-4">So sánh sản phẩm</h2>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                Vui lòng chọn sản phẩm để so sánh
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered comparison-table">
                    <thead>
                        <tr>
                            <th>Thông tin</th>
                            <?php foreach ($products as $product):
                                // Get image from hinhanh table
                                $hinhanh = $hanghoa->GetHinhAnhById($product->hinhanh);
                            ?>
                                <th class="text-center">
                                    <?php if ($hinhanh && !empty($hinhanh->duong_dan)): ?>
                                        <img src="<?php echo $hinhanh->duong_dan; ?>"
                                            alt="<?php echo htmlspecialchars($product->tenhanghoa); ?>"
                                            class="img-fluid product-image mb-2"
                                            style="max-width: 150px;"
                                            onerror="this.src='img_LQA/updating-image.png'">
                                    <?php else: ?>
                                        <div class="updating-image-container text-center">
                                            <img src="img_LQA/updating-image.png"
                                                alt="Đang cập nhật ảnh"
                                                class="img-fluid product-image mb-2"
                                                style="max-width: 150px;">
                                            <p class="updating-text small text-muted">Đang cập nhật ảnh</p>
                                        </div>
                                    <?php endif; ?>
                                    <div><?php echo htmlspecialchars($product->tenhanghoa); ?></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Giá</td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <?php echo number_format($product->giathamkhao, 0, ',', '.'); ?> đ
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Mô tả</td>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo htmlspecialchars($product->mota); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <!-- Hiển thị các thuộc tính -->
                        <?php
                        // Lấy tất cả các loại thuộc tính có trong các sản phẩm
                        $allFeatureTypes = [];
                        foreach ($productFeatures as $features) {
                            foreach ($features as $feature) {
                                $thuocTinhObj = new ThuocTinh();
                                $thuocTinh = $thuocTinhObj->thuoctinhGetbyId($feature->idThuocTinh);
                                $allFeatureTypes[$feature->idThuocTinh] = $thuocTinh->tenThuocTinh;
                            }
                        }

                        // Hiển thị từng loại thuộc tính
                        foreach ($allFeatureTypes as $featureId => $featureName): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($featureName); ?></td>
                                <?php foreach ($products as $product): ?>
                                    <td>
                                        <?php
                                        $featureValue = '';
                                        foreach ($productFeatures[$product->idhanghoa] as $feature) {
                                            if ($feature->idThuocTinh == $featureId) {
                                                $featureValue = $feature->tenThuocTinhHH;
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($featureValue);
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Nút thao tác -->
                        <tr>
                            <td>Thao tác</td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <a href="administrator/elements_LQA/mgiohang/giohangAct.php?action=add&productId=<?php echo $product->idhanghoa; ?>&quantity=1"
                                        class="btn btn-primary btn-sm mb-2">
                                        Thêm vào giỏ
                                    </a>
                                    <br>
                                    <a href="./index.php?reqHanghoa=<?php echo $product->idhanghoa; ?>"
                                        class="btn btn-info btn-sm">
                                        Xem chi tiết
                                    </a>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
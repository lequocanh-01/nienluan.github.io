<?php
require_once './administrator/elements_LQA/mod/hanghoaCls.php';
$hanghoa = new hanghoa();

$query = isset($_GET['query']) ? $_GET['query'] : '';
$list_hanghoa = $hanghoa->searchHanghoa($query);

// Lấy domain của trang web
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public_files/mycss.css">
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <title>Kết quả tìm kiếm</title>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h2 class="text-center mb-4">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($query); ?>"</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (count($list_hanghoa) > 0): ?>
                <?php foreach ($list_hanghoa as $v): ?>
                    <?php
                    // Lấy thông tin hình ảnh từ bảng hinhanh
                    $hinhanh = $hanghoa->GetHinhAnhById($v->hinhanh);

                    // Khởi tạo đường dẫn hình ảnh mặc định
                    $imagePath = "img_LQA/updating-image.png";

                    // Nếu có hình ảnh và có đường dẫn, sử dụng đường dẫn đó
                    if ($hinhanh && !empty($hinhanh->duong_dan)) {
                        $originalPath = $hinhanh->duong_dan;

                        // Đảm bảo đường dẫn hình ảnh là tuyệt đối
                        if (strpos($originalPath, 'http') === 0) {
                            // Đường dẫn đã là URL đầy đủ
                            $imagePath = $originalPath;
                        } else {
                            // Chuẩn hóa đường dẫn tương đối
                            $relativePath = ltrim($originalPath, '/');

                            // Sử dụng đường dẫn đã chuẩn hóa
                            $imagePath = $relativePath;
                        }
                    }
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="updating-image-container">
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo $v->tenhanghoa; ?>" class="card-img-top" onerror="this.src='img_LQA/updating-image.png'">
                                <?php if ($imagePath == "img_LQA/updating-image.png"): ?>
                                    <p class="updating-text">Đang cập nhật ảnh</p>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo $v->tenhanghoa; ?></h5>
                                <p class="card-text text-muted">
                                    Giá bán:
                                    <span class="text-danger fw-bold">
                                        <?php echo number_format($v->giathamkhao, 0, ',', '.') . ' VNĐ'; ?>
                                    </span>
                                </p>
                                <a href="./index.php?reqHanghoa=<?php echo $v->idhanghoa; ?>" class="btn btn-outline-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center" role="alert">
                    Không tìm thấy sản phẩm nào.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <link rel="stylesheet" href="public_files/mycss.css">
    <script src="administrator/elements_LQA/js_LQA/jscript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
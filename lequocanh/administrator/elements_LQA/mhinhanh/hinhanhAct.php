<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("../mod/database.php");
require_once("../mod/hanghoaCls.php");

// Tắt báo lỗi để tránh output không mong muốn
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(dirname(dirname(__FILE__))) . '/upload_errors.log');

// Initialize database connection
$db = new Database();
$conn = $db->connect;

// Đảm bảo gửi header JSON cho các action không phải upload
if (!isset($_REQUEST["reqact"]) || $_REQUEST["reqact"] !== "addnew") {
    header('Content-Type: application/json; charset=utf-8');
}

function deleteImageFile($imagePath)
{
    if ($imagePath) {
        // Xây dựng đường dẫn đầy đủ đến file ảnh
        $fullPath = dirname(dirname(dirname(__FILE__))) . '/' . $imagePath;

        // Xóa file ảnh nếu tồn tại
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
    }
    return true; // Trả về true nếu không có file để xóa
}

try {
    if (isset($_REQUEST["reqact"])) {
        $requestAction = $_REQUEST["reqact"];
        $hanghoa = new hanghoa();

        switch ($requestAction) {
            case "addnew":
                if (isset($_FILES['files'])) {
                    $files = $_FILES['files'];
                    $totalFiles = count($files['tmp_name']);
                    $successCount = 0;
                    $failedCount = 0;
                    $appliedImages = [];
                    $errorMessages = [];

                    // Kiểm tra nếu không có file nào được chọn
                    if (empty($files['name'][0])) {
                        $_SESSION['upload_errors'] = ['Vui lòng chọn ít nhất một file hình ảnh để tải lên.'];
                        header('location: ../../index.php?req=hinhanhview&result=nofiles');
                        exit();
                    }

                    // Kiểm tra môi trường và cài đặt đường dẫn phù hợp
                    $isDocker = (getenv('DOCKER_ENV') !== false) || file_exists('/.dockerenv');
                    $uploadDirAbsolute = $isDocker ? '/var/www/html/administrator/uploads/' : 'D:/PHP_WS/lequocanh/administrator/uploads/';

                    error_log("Upload to directory: " . $uploadDirAbsolute . " in " . ($isDocker ? 'Docker' : 'Windows') . " environment");

                    // Đảm bảo thư mục upload tồn tại
                    if (!file_exists($uploadDirAbsolute)) {
                        if (!mkdir($uploadDirAbsolute, 0777, true)) {
                            error_log("Failed to create upload directory: " . $uploadDirAbsolute);
                            $_SESSION['upload_errors'] = ['Không thể tạo thư mục upload. Vui lòng kiểm tra quyền truy cập hoặc tạo thư mục thủ công.'];
                            header('location: ../../index.php?req=hinhanhview&result=notok');
                            exit();
                        } else {
                            chmod($uploadDirAbsolute, 0777); // Cấp quyền đủ cho thư mục vừa tạo
                        }
                    }

                    // Kiểm tra quyền ghi vào thư mục upload
                    if (!is_writable($uploadDirAbsolute)) {
                        error_log("Upload directory is not writable: " . $uploadDirAbsolute);
                        $_SESSION['upload_errors'] = ['Thư mục upload không có quyền ghi. Vui lòng kiểm tra quyền truy cập.'];
                        header('location: ../../index.php?req=hinhanhview&result=notok');
                        exit();
                    }

                    foreach ($files['tmp_name'] as $key => $tmp_name) {
                        if ($files['error'][$key] === 0) {
                            $fileName = $files['name'][$key];
                            $fileType = $files['type'][$key];
                            $fileTmpName = $files['tmp_name'][$key];
                            $fileSize = $files['size'][$key];

                            // Kiểm tra kích thước file (giới hạn 10MB)
                            $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
                            if ($fileSize > $maxFileSize) {
                                $failedCount++;
                                $errorMsg = "File '{$fileName}' vượt quá kích thước cho phép (10MB).";
                                $errorMessages[] = $errorMsg;
                                error_log($errorMsg);
                                continue;
                            }

                            // Tự động khớp hình ảnh với sản phẩm nếu tìm thấy sản phẩm có tên giống với tên file
                            // Hướng tiếp cận chính xác tuyệt đối: So khớp chính xác tên file với tên sản phẩm (phân biệt hoa thường)
                            $imageNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

                            // Tìm sản phẩm có tên khớp chính xác với tên file hình ảnh (chỉ loại bỏ khoảng trắng đầu/cuối)
                            // Vẫn giữ phân biệt chữ hoa/thường để so khớp chính xác tuyệt đối
                            $sqlMatchProduct = "SELECT idhanghoa, tenhanghoa FROM hanghoa WHERE TRIM(tenhanghoa) = TRIM(?)";
                            $stmtMatchProduct = $conn->prepare($sqlMatchProduct);
                            $stmtMatchProduct->execute([$imageNameWithoutExt]);
                            $matchedProduct = $stmtMatchProduct->fetch(PDO::FETCH_ASSOC);

                            // Kiểm tra thêm lần nữa để đảm bảo sự chính xác tuyệt đối
                            if ($matchedProduct && trim($matchedProduct['tenhanghoa']) === trim($imageNameWithoutExt)) {
                                // Kiểm tra xem ảnh có bị trùng lặp không (bằng hash MD5)
                                $fileHash = md5_file($fileTmpName);
                                $existingImageId = $hanghoa->CheckImageExistsByHash($fileHash);

                                if ($existingImageId) {
                                    // Nếu ảnh đã tồn tại, sử dụng ảnh có sẵn
                                    error_log("Ảnh trùng lặp đã được phát hiện, sử dụng ID: " . $existingImageId);

                                    // Cập nhật hình ảnh cho sản phẩm với ID ảnh đã tồn tại
                                    $sqlUpdateProduct = "UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?";
                                    $stmtUpdateProduct = $conn->prepare($sqlUpdateProduct);
                                    $stmtUpdateProduct->execute([(int)$existingImageId, $matchedProduct['idhanghoa']]);

                                    // Thêm quan hệ vào bảng hanghoa_hinhanh nếu chưa tồn tại
                                    $sqlCheckRelation = "SELECT COUNT(*) FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh = ?";
                                    $stmtCheckRelation = $conn->prepare($sqlCheckRelation);
                                    $stmtCheckRelation->execute([$matchedProduct['idhanghoa'], (int)$existingImageId]);

                                    if ($stmtCheckRelation->fetchColumn() == 0) {
                                        $sqlInsertRelation = "INSERT INTO hanghoa_hinhanh (idhanghoa, idhinhanh) VALUES (?, ?)";
                                        $stmtInsertRelation = $conn->prepare($sqlInsertRelation);
                                        $stmtInsertRelation->execute([$matchedProduct['idhanghoa'], (int)$existingImageId]);
                                    }

                                    // Lưu thông tin vào session
                                    if (!isset($_SESSION['matched_images'])) {
                                        $_SESSION['matched_images'] = [];
                                    }

                                    $_SESSION['matched_images'][] = [
                                        'product_id' => $matchedProduct['idhanghoa'],
                                        'product_name' => $matchedProduct['tenhanghoa'],
                                        'image_id' => (int)$existingImageId,
                                        'image_name' => $fileName,
                                        'duplicate' => true
                                    ];

                                    $successCount++;
                                    continue; // Bỏ qua việc upload file mới
                                }

                                // Tạo tên file mới để tránh trùng lặp
                                $newFileName = uniqid() . '_' . basename($fileName);
                                $targetPath = $uploadDirAbsolute . $newFileName;

                                // Debug information
                                error_log("Uploading file: " . $fileName);
                                error_log("Target path: " . $targetPath);
                                error_log("File hash: " . $fileHash);

                                if (move_uploaded_file($fileTmpName, $targetPath)) {
                                    // Lưu thông tin vào database với đường dẫn tương đối
                                    $relativePath = "administrator/uploads/" . $newFileName;

                                    if ($hanghoa->ThemHinhAnh($fileName, $fileType, $relativePath, $fileHash)) {
                                        $successCount++;
                                        $lastInsertId = $hanghoa->GetLastInsertId();

                                        // Cập nhật hình ảnh cho sản phẩm - đảm bảo gán ID ảnh làm giá trị integer
                                        $sqlUpdateProduct = "UPDATE hanghoa SET hinhanh = ? WHERE idhanghoa = ?";
                                        $stmtUpdateProduct = $conn->prepare($sqlUpdateProduct);
                                        $stmtUpdateProduct->execute([(int)$lastInsertId, $matchedProduct['idhanghoa']]);

                                        // Thêm quan hệ vào bảng hanghoa_hinhanh
                                        $sqlCheckRelation = "SELECT COUNT(*) FROM hanghoa_hinhanh WHERE idhanghoa = ? AND idhinhanh = ?";
                                        $stmtCheckRelation = $conn->prepare($sqlCheckRelation);
                                        $stmtCheckRelation->execute([$matchedProduct['idhanghoa'], (int)$lastInsertId]);

                                        if ($stmtCheckRelation->fetchColumn() == 0) {
                                            $sqlInsertRelation = "INSERT INTO hanghoa_hinhanh (idhanghoa, idhinhanh) VALUES (?, ?)";
                                            $stmtInsertRelation = $conn->prepare($sqlInsertRelation);
                                            $stmtInsertRelation->execute([$matchedProduct['idhanghoa'], (int)$lastInsertId]);
                                        }

                                        // Lưu thông tin vào session để hiển thị
                                        if (!isset($_SESSION['matched_images'])) {
                                            $_SESSION['matched_images'] = [];
                                        }

                                        $_SESSION['matched_images'][] = [
                                            'product_id' => $matchedProduct['idhanghoa'],
                                            'product_name' => $matchedProduct['tenhanghoa'],
                                            'image_id' => (int)$lastInsertId,
                                            'image_name' => $fileName
                                        ];

                                        // Luôn tự động áp dụng hình ảnh cho sản phẩm
                                        if ($hanghoa->ApplyImageToProduct($matchedProduct['idhanghoa'], (int)$lastInsertId)) {
                                            $_SESSION['matched_images'][] = [
                                                'image_name' => $fileName,
                                                'product_name' => $matchedProduct['tenhanghoa'],
                                                'product_id' => $matchedProduct['idhanghoa'],
                                                'image_id' => (int)$lastInsertId,
                                                'auto_applied' => true
                                            ];
                                        }
                                    } else {
                                        $failedCount++;
                                        $errorMsg = "Không thể lưu thông tin ảnh '{$fileName}' vào cơ sở dữ liệu.";
                                        $errorMessages[] = $errorMsg;
                                        error_log($errorMsg);

                                        if (file_exists($targetPath)) {
                                            unlink($targetPath);
                                        }
                                    }
                                } else {
                                    $failedCount++;
                                    $error = error_get_last();
                                    $errorMsg = "Không thể tải lên file '" . $fileName . "'. ";
                                    if ($error) {
                                        $errorMsg .= "Lỗi PHP: " . $error['message'];
                                    }
                                    $errorMessages[] = $errorMsg;
                                    error_log("Failed to move uploaded file: " . $fileName);
                                    error_log("PHP Upload error: " . ($error ? $error['message'] : 'Unknown error'));
                                }
                            } else {
                                $failedCount++;
                                $errorMsg = "Không tìm thấy sản phẩm nào có tên trùng khớp với tên file '{$imageNameWithoutExt}'.";
                                $errorMessages[] = $errorMsg;
                                error_log($errorMsg);
                                continue;
                            }
                        } else {
                            $failedCount++;
                            $errorCode = $files['error'][$key];
                            $errorMsg = "Lỗi tải lên file '" . $files['name'][$key] . "'. ";

                            // Giải thích các mã lỗi PHP upload
                            switch ($errorCode) {
                                case UPLOAD_ERR_INI_SIZE:
                                    $errorMsg .= "File vượt quá kích thước cho phép trong php.ini.";
                                    break;
                                case UPLOAD_ERR_FORM_SIZE:
                                    $errorMsg .= "File vượt quá kích thước cho phép trong form.";
                                    break;
                                case UPLOAD_ERR_PARTIAL:
                                    $errorMsg .= "File chỉ được tải lên một phần.";
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    $errorMsg .= "Không có file nào được tải lên.";
                                    break;
                                case UPLOAD_ERR_NO_TMP_DIR:
                                    $errorMsg .= "Thiếu thư mục tạm.";
                                    break;
                                case UPLOAD_ERR_CANT_WRITE:
                                    $errorMsg .= "Không thể ghi file vào đĩa.";
                                    break;
                                case UPLOAD_ERR_EXTENSION:
                                    $errorMsg .= "Tải lên bị chặn bởi extension.";
                                    break;
                                default:
                                    $errorMsg .= "Lỗi không xác định (code: " . $errorCode . ").";
                            }

                            $errorMessages[] = $errorMsg;
                            error_log("File upload error code: " . $errorCode . " for file: " . $files['name'][$key]);
                        }
                    }

                    // Lưu thông báo lỗi vào session
                    if (!empty($errorMessages)) {
                        $_SESSION['upload_errors'] = $errorMessages;
                    }

                    // Lưu thông báo về việc tự động áp dụng hình ảnh
                    if (!empty($appliedImages)) {
                        $_SESSION['auto_applied_images'] = $appliedImages;
                    }

                    if ($successCount > 0) {
                        if ($failedCount > 0) {
                            // Some succeeded, some failed
                            header('location: ../../index.php?req=hinhanhview&result=partial&success=' . $successCount . '&failed=' . $failedCount);
                        } else {
                            // All succeeded
                            header('location: ../../index.php?req=hinhanhview&result=ok&count=' . $successCount);
                        }
                    } else {
                        // All failed
                        header('location: ../../index.php?req=hinhanhview&result=notok');
                    }
                    exit();
                } else {
                    $_SESSION['upload_errors'] = ['Không có dữ liệu file được gửi. Vui lòng thử lại.'];
                    header('location: ../../index.php?req=hinhanhview&result=nofiles');
                    exit();
                }
                break;

            case "deleteimage":
                if (!isset($_POST["id"])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Thiếu ID hình ảnh'
                    ]);
                    exit;
                }

                $id = intval($_POST["id"]);

                // Kiểm tra xem hình ảnh có đang được sử dụng không
                $products = $hanghoa->GetProductsByImageId($id);

                if (!empty($products)) {
                    $productNames = array_map(function ($product) {
                        return $product['tenhanghoa'];
                    }, $products);

                    echo json_encode([
                        'success' => false,
                        'message' => 'Hình ảnh đang được sử dụng bởi các sản phẩm: ' . implode(", ", $productNames)
                    ]);
                    exit;
                }

                // Lấy đường dẫn ảnh trước khi xóa
                $imagePath = $hanghoa->GetImagePath($id);

                // Xóa file ảnh
                if (!deleteImageFile($imagePath)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa file ảnh'
                    ]);
                    exit;
                }

                // Xóa record trong database
                $result = $hanghoa->XoaHinhAnh($id);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Xóa hình ảnh thành công'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa hình ảnh khỏi database'
                    ]);
                }
                break;

            case "deletemultiple":
                $data = json_decode(file_get_contents('php://input'));

                if (!$data || !isset($data->ids) || !is_array($data->ids)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Dữ liệu không hợp lệ'
                    ]);
                    exit;
                }

                $success = true;
                $failedIds = [];
                $usedImages = [];

                foreach ($data->ids as $id) {
                    // Kiểm tra xem hình ảnh có đang được sử dụng không
                    $products = $hanghoa->GetProductsByImageId($id);
                    if (!empty($products)) {
                        $usedImages[] = [
                            'id' => $id,
                            'products' => $products
                        ];
                        continue;
                    }

                    // Lấy đường dẫn ảnh trước khi xóa
                    $imagePath = $hanghoa->GetImagePath($id);

                    // Xóa file ảnh
                    if (!deleteImageFile($imagePath)) {
                        $failedIds[] = $id;
                        continue;
                    }

                    // Xóa record trong database
                    if (!$hanghoa->XoaHinhAnh($id)) {
                        $failedIds[] = $id;
                    }
                }

                if (!empty($usedImages)) {
                    $message = "Một số hình ảnh không thể xóa vì đang được sử dụng:\n";
                    foreach ($usedImages as $image) {
                        $productNames = array_map(function ($product) {
                            return $product['tenhanghoa'];
                        }, $image['products']);
                        $message .= "- Hình ảnh ID " . $image['id'] . " đang được sử dụng bởi: " . implode(", ", $productNames) . "\n";
                    }
                    echo json_encode([
                        'success' => false,
                        'message' => $message
                    ]);
                    exit;
                }

                if (empty($failedIds)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Xóa tất cả hình ảnh thành công'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa một số hình ảnh: ' . implode(", ", $failedIds)
                    ]);
                }
                break;

            default:
                throw new Exception("Hành động không hợp lệ");
        }
    } else {
        throw new Exception("Thiếu tham số hành động");
    }
} catch (Exception $e) {
    error_log("Exception in hinhanhAct.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    if ($requestAction === "addnew") {
        $_SESSION['upload_errors'] = ['Lỗi hệ thống: ' . $e->getMessage()];
        header("location: ../../index.php?req=hinhanhview&result=notok");
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
}

// Đảm bảo kết thúc thực thi sau khi gửi JSON
if ($requestAction !== "addnew") {
    exit();
}

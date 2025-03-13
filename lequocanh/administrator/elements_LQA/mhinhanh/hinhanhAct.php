<?php
session_start();
require_once("../mod/database.php");
require_once("../mod/hanghoaCls.php");

// Tắt báo lỗi để tránh output không mong muốn
error_reporting(0);
ini_set('display_errors', 0);

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
                    $success = true;
                    $uploadDir = "../../uploads/"; // Đường dẫn thư mục upload

                    // Tạo thư mục nếu chưa tồn tại
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    foreach ($files['tmp_name'] as $key => $tmp_name) {
                        if ($files['error'][$key] === 0) {
                            $fileName = $files['name'][$key];
                            $fileType = $files['type'][$key];
                            $fileTmpName = $files['tmp_name'][$key];

                            // Tạo tên file mới để tránh trùng lặp
                            $newFileName = uniqid() . '_' . basename($fileName);
                            $targetPath = $uploadDir . $newFileName;

                            // Debug information
                            error_log("Uploading file: " . $fileName);
                            error_log("Target path: " . $targetPath);

                            if (move_uploaded_file($fileTmpName, $targetPath)) {
                                // Lưu thông tin vào database với đường dẫn tương đối
                                $relativePath = "uploads/" . $newFileName;
                                if (!$hanghoa->ThemHinhAnh($fileName, $fileType, $relativePath)) {
                                    error_log("Failed to save to database: " . $fileName);
                                    $success = false;
                                }
                            } else {
                                error_log("Failed to move uploaded file: " . $fileName);
                                error_log("PHP Upload error: " . error_get_last()['message']);
                                $success = false;
                            }
                        } else {
                            error_log("File upload error code: " . $files['error'][$key]);
                            $success = false;
                        }
                    }

                    if ($success) {
                        header('location: ../../index.php?req=hinhanhview&result=ok');
                    } else {
                        header('location: ../../index.php?req=hinhanhview&result=notok');
                    }
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
    if ($requestAction === "addnew") {
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

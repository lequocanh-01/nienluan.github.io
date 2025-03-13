<?php
require_once './administrator/elements_LQA/mod/hanghoaCls.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
error_log("Search term received: " . $term);

if (strlen($term) >= 2) {
    try {
        $hanghoa = new hanghoa();
        $results = $hanghoa->searchHanghoa($term);
        error_log("Search results count: " . count($results));
        
        $suggestions = array_map(function($item) {
            return [
                'id' => $item->idhanghoa,
                'name' => $item->tenhanghoa,
                'price' => number_format($item->giathamkhao, 0, ',', '.') . ' VNĐ',
                'image' => $item->hinhanh
            ];
        }, $results);
        
        header('Content-Type: application/json');
        echo json_encode($suggestions);
    } catch (Exception $e) {
        error_log("Error in search_suggestions.php: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Có lỗi xảy ra khi tìm kiếm']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}
exit();
?>
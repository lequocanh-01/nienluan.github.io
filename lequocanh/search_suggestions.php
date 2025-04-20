<?php
// Prevent PHP errors/warnings from breaking JSON output
error_reporting(0);
ob_start();

require_once 'core/init.php';
require_once './administrator/elements_LQA/mod/hanghoaCls.php';

// Check if we received a search query
if (!isset($_GET['query']) || empty($_GET['query'])) {
    // Return empty JSON array if no query provided
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

// Get and sanitize the search query
$searchQuery = trim($_GET['query']);
$searchQuery = filter_var($searchQuery, FILTER_SANITIZE_STRING);

// Ensure the query is at least 2 characters long
if (strlen($searchQuery) < 2) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

try {
    // First attempt with modern products table
    $results = searchProducts($searchQuery);

    // If no results, try with the hanghoaCls
    if (empty($results)) {
        $results = searchHanghoa($searchQuery);
    }

    // Clear any output buffers before sending JSON
    ob_end_clean();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($results);
} catch (Exception $e) {
    // Log error (don't expose details to frontend)
    error_log('Search suggestion error: ' . $e->getMessage());

    // Clear any output buffers
    ob_end_clean();

    // Return empty results on error
    header('Content-Type: application/json');
    echo json_encode([]);
}

// Function to search using the 'products' table
function searchProducts($searchQuery)
{
    global $pdo;
    $results = [];

    try {
        // Prepare search query with wildcards for partial matching
        $searchParam = '%' . $searchQuery . '%';

        // SQL query to search products by name, description, or brand
        $sql = "SELECT 
                    p.id, 
                    p.name, 
                    p.price, 
                    p.sale_price, 
                    (SELECT image FROM product_images WHERE product_id = p.id LIMIT 1) as image,
                    b.name as brand_name
                FROM 
                    products p
                LEFT JOIN 
                    brands b ON p.brand_id = b.id
                WHERE 
                    p.name LIKE ? OR 
                    p.description LIKE ? OR 
                    b.name LIKE ?
                LIMIT 8";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchParam, $searchParam, $searchParam]);

        // Format results for the frontend
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $displayPrice = isset($row['sale_price']) && $row['sale_price'] > 0 ? $row['sale_price'] : $row['price'];
            $image = !empty($row['image']) ? 'uploads/products/' . $row['image'] : 'uploads/products/default.jpg';

            $results[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => number_format($displayPrice, 0, ',', '.') . '₫',
                'image' => $image,
                'brand' => $row['brand_name'],
                'url' => 'product.php?id=' . $row['id']
            ];
        }
    } catch (PDOException $e) {
        error_log('Search products error: ' . $e->getMessage());
    }

    return $results;
}

// Function to search using hanghoa table
function searchHanghoa($searchQuery)
{
    $results = [];

    try {
        // Use the hanghoa class from the existing code
        $hanghoa = new hanghoa();
        $items = $hanghoa->searchHanghoa($searchQuery);

        if ($items && is_array($items)) {
            foreach ($items as $item) {
                // Get image information if available
                $imagePath = 'img_LQA/updating-image.png'; // Default fallback image
                if (isset($item->hinhanh)) {
                    $imageInfo = $hanghoa->GetHinhAnhById($item->hinhanh);
                    if ($imageInfo && !empty($imageInfo->duong_dan)) {
                        $imagePath = $imageInfo->duong_dan;
                    }
                }

                // Format price - assuming giathamkhao is the price
                $price = isset($item->giathamkhao) ?
                    number_format($item->giathamkhao, 0, ',', '.') . '₫' :
                    'Liên hệ';

                $results[] = [
                    'id' => $item->idhanghoa,
                    'name' => $item->tenhanghoa,
                    'price' => $price,
                    'image' => $imagePath,
                    'brand' => isset($item->thuonghieu) ? $item->thuonghieu : '',
                    'url' => 'index.php?reqHanghoa=' . $item->idhanghoa
                ];
            }
        }
    } catch (Exception $e) {
        error_log('Search hanghoa error: ' . $e->getMessage());
    }

    return $results;
}

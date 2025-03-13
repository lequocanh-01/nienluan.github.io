<?php
$s = '../../elements_LQA/mod/database.php';
if (file_exists($s)) {
    $f = $s;
} else {
    $f = './elements_LQA/mod/database.php';
    if (!file_exists($f)) {
        $f = './administrator/elements_LQA/mod/database.php';
    }
}
require_once $f;

class GioHang extends Database {
    private function getUserId() {
        if (isset($_SESSION['USER'])) {
            error_log("User logged in as: " . $_SESSION['USER']);
            return $_SESSION['USER'];
        } elseif (isset($_SESSION['ADMIN'])) {
            error_log("Admin logged in as: " . $_SESSION['ADMIN']);
            return $_SESSION['ADMIN'];
        }
        error_log("No user logged in");
        return null;
    }

    public function addToCart($productId, $quantity = 1) {
        $userId = $this->getUserId();
        error_log("Adding to cart - UserID: " . $userId . ", ProductID: " . $productId . ", Quantity: " . $quantity);
        
        if (!$userId) {
            error_log("Failed to add to cart: No user ID");
            return false;
        }

        try {
            // Kiểm tra sản phẩm có tồn tại trong bảng hanghoa không
            $checkProduct = "SELECT idhanghoa FROM hanghoa WHERE idhanghoa = ?";
            $stmtProduct = $this->connect->prepare($checkProduct);
            $stmtProduct->execute([$productId]);
            
            if (!$stmtProduct->fetch()) {
                error_log("Product does not exist: " . $productId);
                return false;
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $checkSql = "SELECT quantity FROM tbl_giohang WHERE user_id = ? AND product_id = ?";
            $checkStmt = $this->connect->prepare($checkSql);
            $checkStmt->execute([$userId, $productId]);
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Existing item check: " . print_r($existingItem, true));

            if ($existingItem) {
                // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                $sql = "UPDATE tbl_giohang SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
                $stmt = $this->connect->prepare($sql);
                $result = $stmt->execute([$quantity, $userId, $productId]);
            } else {
                // Nếu sản phẩm chưa tồn tại, thêm mới
                $sql = "INSERT INTO tbl_giohang (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $stmt = $this->connect->prepare($sql);
                $result = $stmt->execute([$userId, $productId, $quantity]);
            }
            
            error_log("Cart operation result: " . ($result ? "success" : "failed"));
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error in cart operation: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromCart($productId) {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            $sql = "DELETE FROM tbl_giohang WHERE user_id = ? AND product_id = ?";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$userId, $productId]);
        } catch (PDOException $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            return false;
        }
    }

    public function updateCart($productId, $quantity) {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            if ($quantity > 0) {
                $sql = "UPDATE tbl_giohang SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $this->connect->prepare($sql);
                return $stmt->execute([$quantity, $userId, $productId]);
            } else {
                return $this->removeFromCart($productId);
            }
        } catch (PDOException $e) {
            error_log("Error updating cart: " . $e->getMessage());
            return false;
        }
    }

    public function getCart() {
        $userId = $this->getUserId();
        error_log("Getting cart for user: " . $userId);
        
        if (!$userId) {
            error_log("Failed to get cart: No user ID");
            return [];
        }

        try {
            // Sửa câu SQL để lấy dữ liệu hình ảnh từ bảng hinhanh
            $sql = "SELECT g.product_id, g.quantity, h.tenhanghoa, h.giathamkhao, i.duong_dan as hinhanh 
                   FROM tbl_giohang g
                   INNER JOIN hanghoa h ON g.product_id = h.idhanghoa 
                   INNER JOIN hinhanh i ON h.hinhanh = i.id
                   WHERE g.user_id = ?";
            
            $stmt = $this->connect->prepare($sql);
            $stmt->execute([$userId]);
            
            $cart = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cart[] = [
                    'product_id' => $row['product_id'],
                    'tenhanghoa' => $row['tenhanghoa'],
                    'giathamkhao' => $row['giathamkhao'],
                    'quantity' => $row['quantity'],
                    'hinhanh' => $row['hinhanh']  // Đường dẫn hình ảnh từ bảng hinhanh
                ];
            }
            return $cart;
            
        } catch (PDOException $e) {
            error_log("Error getting cart: " . $e->getMessage());
            return [];
        }
    }

    public function clearCart() {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            $sql = "DELETE FROM tbl_giohang WHERE user_id = ?";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }

    public function getCartItemCount() {
        $userId = $this->getUserId();
        if (!$userId) return 0;

        try {
            $sql = "SELECT SUM(quantity) as total FROM tbl_giohang WHERE user_id = ?";
            $stmt = $this->connect->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting cart item count: " . $e->getMessage());
            return 0;
        }
    }

    // Phương thức mới để chuyển giỏ hàng từ session sang database khi đăng nhập
    public function migrateSessionCartToDatabase($username) {
        if (isset($_SESSION['cart']['guest_' . session_id()])) {
            $sessionCart = $_SESSION['cart']['guest_' . session_id()];
            foreach ($sessionCart as $productId => $quantity) {
                $this->addToCart($productId, $quantity);
            }
            unset($_SESSION['cart']['guest_' . session_id()]);
        }
    }

    public function updateQuantity($productId, $quantity) {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            $sql = "UPDATE tbl_giohang SET quantity = ? WHERE user_id = ? AND product_id = ?";
            $stmt = $this->connect->prepare($sql);
            return $stmt->execute([$quantity, $userId, $productId]);
        } catch (PDOException $e) {
            error_log("Error updating quantity: " . $e->getMessage());
            return false;
        }
    }

    public function getCartByUserId($userId) {
        try {
            $sql = "SELECT g.product_id, g.quantity, h.tenhanghoa, h.giathamkhao, i.duong_dan as hinhanh 
                   FROM tbl_giohang g
                   INNER JOIN hanghoa h ON g.product_id = h.idhanghoa 
                   INNER JOIN hinhanh i ON h.hinhanh = i.id
                   WHERE g.user_id = ?";
            
            $stmt = $this->connect->prepare($sql);
            $stmt->execute([$userId]);
            
            $cart = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cart[] = [
                    'product_id' => $row['product_id'],
                    'tenhanghoa' => $row['tenhanghoa'],
                    'giathamkhao' => $row['giathamkhao'],
                    'quantity' => $row['quantity'],
                    'hinhanh' => $row['hinhanh']  // Đường dẫn hình ảnh từ bảng hinhanh
                ];
            }
            return $cart;
            
        } catch (PDOException $e) {
            error_log("Error getting cart for user $userId: " . $e->getMessage());
            return [];
        }
    }
}
?>

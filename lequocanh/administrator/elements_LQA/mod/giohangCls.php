<?php
require_once 'database.php';

class GioHang
{
    private $db;
    private $cart_cache = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getUserId()
    {
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

    public function addToCart($productId, $quantity = 1)
    {
        $userId = $this->getUserId();
        error_log("Adding to cart - UserID: " . $userId . ", ProductID: " . $productId . ", Quantity: " . $quantity);

        if (!$userId) {
            error_log("Failed to add to cart: No user ID");
            return false;
        }

        try {
            // Kiểm tra sản phẩm có tồn tại trong bảng hanghoa không
            $checkProduct = "SELECT idhanghoa FROM hanghoa WHERE idhanghoa = ?";
            $stmtProduct = $this->db->prepare($checkProduct);
            $stmtProduct->execute([$productId]);

            if (!$stmtProduct->fetch()) {
                error_log("Product does not exist: " . $productId);
                return false;
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $checkSql = "SELECT quantity FROM tbl_giohang WHERE user_id = ? AND product_id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userId, $productId]);
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                $sql = "UPDATE tbl_giohang SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$quantity, $userId, $productId]);
            } else {
                // Nếu sản phẩm chưa tồn tại, thêm mới
                $sql = "INSERT INTO tbl_giohang (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$userId, $productId, $quantity]);
            }

            // Xóa cache khi thêm sản phẩm mới
            $this->clearCartCache();
            return $result;
        } catch (PDOException $e) {
            error_log("Error in cart operation: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromCart($productId)
    {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            $sql = "DELETE FROM tbl_giohang WHERE user_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId, $productId]);

            // Xóa cache khi xóa sản phẩm
            $this->clearCartCache();

            return $result;
        } catch (PDOException $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            return false;
        }
    }

    public function updateCart($productId, $quantity)
    {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            if ($quantity > 0) {
                $sql = "UPDATE tbl_giohang SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$quantity, $userId, $productId]);
            } else {
                return $this->removeFromCart($productId);
            }
        } catch (PDOException $e) {
            error_log("Error updating cart: " . $e->getMessage());
            return false;
        }
    }

    public function getCart()
    {
        // Nếu đã có cache, trả về từ cache
        if ($this->cart_cache !== null) {
            return $this->cart_cache;
        }

        $userId = $this->getUserId();
        error_log("Getting cart for user: " . $userId);

        if (!$userId) {
            error_log("Failed to get cart: No user ID");
            return [];
        }

        try {
            // Sửa câu SQL để lấy dữ liệu hình ảnh từ bảng hanghoa
            $sql = "SELECT g.product_id, g.quantity, h.tenhanghoa, h.giathamkhao, h.hinhanh
                   FROM tbl_giohang g
                   INNER JOIN hanghoa h ON g.product_id = h.idhanghoa
                   WHERE g.user_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $cart = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Check if hinhanh is a numeric ID (reference to hinhanh table)
                $hinhanh = $row['hinhanh'];

                $cart[] = [
                    'product_id' => $row['product_id'],
                    'tenhanghoa' => $row['tenhanghoa'],
                    'giathamkhao' => $row['giathamkhao'],
                    'quantity' => $row['quantity'],
                    'hinhanh' => $hinhanh  // Could be either an ID or base64 data
                ];
            }

            // Lưu vào cache
            $this->cart_cache = $cart;

            return $cart;
        } catch (PDOException $e) {
            error_log("Error getting cart: " . $e->getMessage());
            return [];
        }
    }

    public function clearCart()
    {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            $sql = "DELETE FROM tbl_giohang WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId]);

            // Xóa cache khi xóa giỏ hàng
            $this->clearCartCache();

            return $result;
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }

    public function getCartItemCount()
    {
        $userId = $this->getUserId();
        if (!$userId) return 0;

        try {
            $sql = "SELECT SUM(quantity) as total FROM tbl_giohang WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting cart count: " . $e->getMessage());
            return 0;
        }
    }

    // Phương thức mới để chuyển giỏ hàng từ session sang database khi đăng nhập
    public function migrateSessionCartToDatabase($username)
    {
        if (isset($_SESSION['cart']['guest_' . session_id()])) {
            $sessionCart = $_SESSION['cart']['guest_' . session_id()];
            foreach ($sessionCart as $productId => $quantity) {
                $this->addToCart($productId, $quantity);
            }
            unset($_SESSION['cart']['guest_' . session_id()]);
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        $userId = $this->getUserId();
        if (!$userId) return false;

        try {
            if ($quantity > 0) {
                $sql = "UPDATE tbl_giohang SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$quantity, $userId, $productId]);

                // Xóa cache khi cập nhật số lượng
                $this->clearCartCache();

                return $result;
            } else {
                return $this->removeFromCart($productId);
            }
        } catch (PDOException $e) {
            error_log("Error updating cart: " . $e->getMessage());
            return false;
        }
    }

    public function getCartByUserId($userId)
    {
        try {
            $sql = "SELECT g.product_id, g.quantity, h.tenhanghoa, h.giathamkhao, h.hinhanh 
                   FROM tbl_giohang g
                   INNER JOIN hanghoa h ON g.product_id = h.idhanghoa 
                   WHERE g.user_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            $cart = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cart[] = [
                    'product_id' => $row['product_id'],
                    'tenhanghoa' => $row['tenhanghoa'],
                    'giathamkhao' => $row['giathamkhao'],
                    'quantity' => $row['quantity'],
                    'hinhanh' => $row['hinhanh']
                ];
            }
            return $cart;
        } catch (PDOException $e) {
            error_log("Error getting cart for user $userId: " . $e->getMessage());
            return [];
        }
    }

    // Thêm phương thức để xóa cache khi giỏ hàng thay đổi
    private function clearCartCache()
    {
        $this->cart_cache = null;
    }
}

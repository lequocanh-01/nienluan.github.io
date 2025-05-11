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

    // Thêm phương thức để lấy session ID hiện tại
    private function getSessionId()
    {
        return session_id();
    }

    public function addToCart($productId, $quantity = 1)
    {
        $userId = $this->getUserId();
        $sessionId = $this->getSessionId();
        error_log("Adding to cart - UserID: " . $userId . ", SessionID: " . $sessionId . ", ProductID: " . $productId . ", Quantity: " . $quantity);

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
            $checkSql = "SELECT quantity FROM tbl_giohang WHERE (user_id = ? OR session_id = ?) AND product_id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userId, $sessionId, $productId]);
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                $sql = "UPDATE tbl_giohang SET quantity = quantity + ? WHERE (user_id = ? OR session_id = ?) AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$quantity, $userId, $sessionId, $productId]);
            } else {
                // Nếu sản phẩm chưa tồn tại, thêm mới
                $sql = "INSERT INTO tbl_giohang (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$userId, $sessionId, $productId, $quantity]);
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
        $sessionId = $this->getSessionId();

        try {
            $sql = "DELETE FROM tbl_giohang WHERE (user_id = ? OR session_id = ?) AND product_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId, $sessionId, $productId]);

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
        $sessionId = $this->getSessionId();

        try {
            if ($quantity > 0) {
                $sql = "UPDATE tbl_giohang SET quantity = ? WHERE (user_id = ? OR session_id = ?) AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$quantity, $userId, $sessionId, $productId]);
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
        $sessionId = $this->getSessionId();

        try {
            // Sử dụng LEFT JOIN thay vì INNER JOIN
            // LEFT JOIN sẽ lấy tất cả các mục trong giỏ hàng, ngay cả khi không tìm thấy sản phẩm
            $sql = "SELECT g.product_id, g.quantity, h.tenhanghoa, h.giathamkhao, h.hinhanh
                   FROM tbl_giohang g
                   LEFT JOIN hanghoa h ON g.product_id = h.idhanghoa
                   WHERE g.session_id = ? OR g.user_id = ?";

            error_log("Executing cart query with sessionId: " . $sessionId . ", userId: " . $userId);

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sessionId, $userId]);

            $cart = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Debug log để kiểm tra dữ liệu hình ảnh
                error_log("Cart item: Product ID=" . $row['product_id'] . ", Image ID=" . ($row['hinhanh'] ?? 'NULL') . ", Name=" . ($row['tenhanghoa'] ?? 'Unknown'));

                // Include cart items even if some fields are NULL
                $cart[] = [
                    'product_id' => $row['product_id'],
                    'tenhanghoa' => $row['tenhanghoa'] ?? 'Unknown Product',
                    'giathamkhao' => $row['giathamkhao'] ?? 0,
                    'quantity' => $row['quantity'],
                    'hinhanh' => $row['hinhanh'] ?? null,
                    'name' => $row['tenhanghoa'] ?? 'Unknown Product' // Thêm trường name để đảm bảo tương thích
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
        $sessionId = $this->getSessionId();

        try {
            $sql = "DELETE FROM tbl_giohang WHERE user_id = ? OR session_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId, $sessionId]);

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
        $sessionId = $this->getSessionId();

        try {
            $sql = "SELECT SUM(quantity) as total FROM tbl_giohang WHERE user_id = ? OR session_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $sessionId]);
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
        $sessionId = $this->getSessionId();

        try {
            if ($quantity > 0) {
                $sql = "UPDATE tbl_giohang SET quantity = ? WHERE (user_id = ? OR session_id = ?) AND product_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$quantity, $userId, $sessionId, $productId]);

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

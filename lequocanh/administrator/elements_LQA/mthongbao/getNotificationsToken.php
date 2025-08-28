<?php
/**
 * Get Customer Notifications API with Token Authentication
 * Alternative to session-based API for ngrok compatibility
 */

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include required files
    require_once '../mod/database.php';
    require_once '../mod/CustomerNotificationManager.php';
    require_once '../mod/TokenAuth.php';

    // Get action parameter
    $action = $_GET['action'] ?? 'list';

    // Get user from token
    $userId = TokenAuth::getUserFromRequest();

    // Debug log
    error_log("getNotificationsToken: User from token = " . ($userId ?: 'null'));
    error_log("getNotificationsToken: Action = " . $action);

    if (!$userId) {
        // Try to get user from session as fallback
        require_once '../mod/sessionManager.php';
        SessionManager::start();
        $userId = $_SESSION['USER'] ?? '';
        
        if ($userId) {
            error_log("getNotificationsToken: Fallback to session user = " . $userId);
        }
    }

    if (empty($userId)) {
        echo json_encode([
            'success' => false,
            'error' => 'Chưa đăng nhập hoặc token không hợp lệ',
            'notifications' => [],
            'unread_count' => 0
        ]);
        exit;
    }

    $notificationManager = new CustomerNotificationManager();

    switch ($action) {
        case 'list':
            // Lấy danh sách thông báo
            $notifications = $notificationManager->getUserNotifications($userId, 20);
            $unreadCount = $notificationManager->getUnreadCount($userId);

            // Format notifications for frontend
            $formattedNotifications = [];
            foreach ($notifications as $notification) {
                $formattedNotifications[] = [
                    'id' => $notification['id'],
                    'order_id' => $notification['order_id'],
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'is_read' => (bool)$notification['is_read'],
                    'created_at' => date('d/m/Y H:i', strtotime($notification['created_at'])),
                    'icon' => $notification['type'] === 'payment_confirmed' ? 'credit-card' : 'bell',
                    'color' => $notification['is_read'] ? 'secondary' : 'primary'
                ];
            }

            echo json_encode([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount,
                'total' => count($formattedNotifications),
                'user_id' => $userId,
                'auth_method' => 'token'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'mark_read':
            // Đánh dấu thông báo đã đọc
            $notificationId = $_POST['notification_id'] ?? $_GET['notification_id'] ?? '';
            
            if (empty($notificationId)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Thiếu ID thông báo'
                ]);
                exit;
            }

            $result = $notificationManager->markAsRead($notificationId, $userId);
            
            if ($result) {
                $unreadCount = $notificationManager->getUnreadCount($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã đánh dấu thông báo đã đọc',
                    'unread_count' => $unreadCount
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Không thể đánh dấu thông báo đã đọc'
                ]);
            }
            break;

        case 'mark_all_read':
            // Đánh dấu tất cả thông báo đã đọc
            $result = $notificationManager->markAllAsRead($userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã đánh dấu tất cả thông báo đã đọc',
                    'unread_count' => 0
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Không thể đánh dấu tất cả thông báo đã đọc'
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Action không hợp lệ'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("getNotificationsToken error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi server: ' . $e->getMessage(),
        'notifications' => [],
        'unread_count' => 0
    ]);
}
?>

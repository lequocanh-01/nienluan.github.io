<?php
/**
 * Add Payment Notification After Success
 * This file adds notification records after successful payment
 */

require_once 'administrator/elements_LQA/mod/database.php';
require_once 'administrator/elements_LQA/mod/CustomerNotificationManager.php';

// Get parameters
$orderId = $_GET['order_id'] ?? null;
$userId = $_GET['user_id'] ?? null;

if (!$orderId || !$userId) {
    die("Missing order_id or user_id parameters");
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if notification already exists
    $checkSql = "SELECT COUNT(*) as count FROM customer_notifications 
                 WHERE order_id = ? AND user_id = ? AND type IN ('payment_confirmed', 'order_approved')";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$orderId, $userId]);
    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if ($exists) {
        echo "Notifications already exist for this order.";
        exit;
    }
    
    // Get order info
    $orderSql = "SELECT * FROM don_hang WHERE id = ?";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("Order not found");
    }
    
    // Create notifications
    $notificationManager = new CustomerNotificationManager();
    
    // Add payment confirmed notification
    $notificationManager->notifyPaymentConfirmed($orderId, $userId);
    echo "✅ Added payment confirmation notification<br>";
    
    // Add order approved notification
    $notificationManager->notifyOrderApproved($orderId, $userId);
    echo "✅ Added order approval notification<br>";
    
    echo "<br><strong>Notifications added successfully!</strong><br>";
    echo "<a href='index.php'>Go to homepage</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

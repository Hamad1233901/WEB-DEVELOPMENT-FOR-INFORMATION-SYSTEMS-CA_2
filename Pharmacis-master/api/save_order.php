<?php
// Filename: api/save_order.php
session_start();
header("Content-Type: application/json");

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();

try {
    $db->beginTransaction();

    // 1. Save Order Header (Receipt Info)
    $stmt = $db->prepare("INSERT INTO orders (total_amount, cash_given, change_return, sold_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $input['totalAmount'],
        $input['cashGiven'],
        $input['changeAmount'],
        $_SESSION['user_id']
    ]);
    
    $order_id = $db->lastInsertId();

    // 2. Save Each Item & Deduct Stock
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_sale, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stockStmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

    foreach ($input['cart'] as $item) {
        // Insert Item
        $itemStmt->execute([
            $order_id,
            $item['id'],
            $item['qty'],
            $item['price'],
            $item['total']
        ]);

        // Deduct Stock
        $stockStmt->execute([$item['qty'], $item['id']]);
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
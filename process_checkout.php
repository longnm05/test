<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thanh toán!', 'redirect' => 'login.php']);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$shippingAddress = $data['shipping_address'] ?? 'Chưa cập nhật địa chỉ';

$totalAmount = 0;
foreach ($cart as $id => $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

try {
    $conn->beginTransaction();

    // 1. Chèn vào bảng orders
    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'pending', ?)");
    $stmtOrder->execute([$userId, $totalAmount, $shippingAddress]);
    $orderId = $conn->lastInsertId();

    // 2. Chèn vào bảng order_items
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    foreach ($cart as $id => $item) {
        $stmtItem->execute([$orderId, $id, $item['quantity'], $item['price']]);
    }

    $conn->commit();

    // Xóa giỏ hàng sau khi đặt thành công
    unset($_SESSION['cart']);

    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>

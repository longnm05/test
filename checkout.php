<?php
session_start();
require_once 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmtUser = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

$cart = $_SESSION['cart'] ?? [];
$total_price = 0;
foreach ($cart as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: var(--bg-light); padding-top: 100px; }
        .checkout-container { max-width: 1200px; margin: 0 auto; padding: 40px 5%; display: flex; gap: 40px; min-height: 70vh; }
        .checkout-form-section { flex: 1.5; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: fit-content; }
        .section-title { font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 30px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-muted); }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 20px; border: 1px solid var(--glass-border); border-radius: 10px; background: rgba(0,0,0,0.02); font-family: var(--font-body); color: var(--text-main); transition: 0.3s; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--accent-purple); background: white; box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1); }
        .checkout-summary-section { flex: 1; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: sticky; top: 100px; height: fit-content; }
        .summary-item { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .summary-item img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; }
        .summary-info { flex: 1; }
        .summary-info h4 { font-size: 0.95rem; margin-bottom: 5px; color: var(--text-main); }
        .summary-price { font-weight: 600; color: var(--accent-blue); }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--text-muted); font-size: 0.95rem; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--glass-border); font-size: 1.2rem; font-weight: 800; color: var(--text-main); }
        .btn-confirm { width: 100%; padding: 15px; margin-top: 30px; font-size: 1.1rem; }
        @media (max-width: 768px) { .checkout-container { flex-direction: column; } }
    </style>
</head>
<body>
    <nav class="glass-header" style="background: rgba(255,255,255,0.8);">
        <div class="logo"><a href="index.php" style="text-decoration: none; color: inherit;"><i class="fa-solid fa-microchip"></i> NovaStyle</a></div>
        <div class="nav-links">
            <a href="index.php" class="nav-item">Trang Chủ</a>
            <a href="products.php" class="nav-item">Sản Phẩm</a>
            <a href="cart.php" class="nav-item">Giỏ Hàng</a>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="checkout-form-section">
            <h2 class="section-title"><i class="fa-solid fa-map-location-dot" style="color: var(--accent-blue);"></i> Thông tin giao hàng</h2>
            <form id="checkoutForm">
                <div class="form-row">
                    <div class="form-group"><label>Họ và Tên</label><input type="text" id="shippingName" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
                    <div class="form-group"><label>Số điện thoại</label><input type="text" id="shippingPhone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required></div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;"><label>Email liên hệ</label><input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: rgba(0,0,0,0.05);"></div>
                <div class="form-group"><label>Địa chỉ nhận hàng</label><textarea id="shippingAddress" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
            </form>
        </div>

        <div class="checkout-summary-section">
            <h2 class="section-title" style="font-size: 1.5rem;"><i class="fa-solid fa-receipt" style="color: var(--accent-purple);"></i> Tóm tắt đơn hàng</h2>
            <div id="checkoutItems">
                <?php if (empty($cart)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 20px;">Giỏ hàng trống!</p>
                <?php else: ?>
                    <?php foreach ($cart as $id => $item): ?>
                        <div class="summary-item">
                            <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                            <div class="summary-info">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">Số lượng: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="summary-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="summary-row"><span>Tạm tính</span><span>$<?= number_format($total_price, 2) ?></span></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span>Miễn phí</span></div>
            <div class="summary-total"><span>Tổng thanh toán</span><span class="gradient-text">$<?= number_format($total_price, 2) ?></span></div>
            <button id="confirmOrderBtn" class="btn btn-primary btn-confirm btn-glow" <?= empty($cart) ? 'disabled' : '' ?>>Xác Nhận Đặt Hàng</button>
        </div>
    </div>

    <script>
        document.getElementById('confirmOrderBtn').addEventListener('click', () => {
            const name = document.getElementById('shippingName').value.trim();
            const phone = document.getElementById('shippingPhone').value.trim();
            const address = document.getElementById('shippingAddress').value.trim();

            if (!name || !phone || !address) {
                alert('Vui lòng điền đầy đủ thông tin giao hàng!');
                return;
            }

            const btn = document.getElementById('confirmOrderBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            btn.disabled = true;

            fetch('process_checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ shipping_address: `Người nhận: ${name} | SĐT: ${phone} | Địa chỉ: ${address}` })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'invoice.php?id=' + data.order_id;
                } else {
                    alert('Lỗi: ' + data.message);
                    btn.innerHTML = 'Xác Nhận Đặt Hàng';
                    btn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
</body>
</html>

<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Lấy danh sách tất cả đơn hàng của khách hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đơn Hàng - NovaStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .history-container {
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 0 5%;
        }
        .page-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            margin-bottom: 30px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .order-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition-smooth);
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .order-meta h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-delivered { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <nav class="glass-header">
        <div class="logo">
            <a href="index.php" style="text-decoration: none; color: inherit;">
                <i class="fa-solid fa-microchip"></i> NovaStyle
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-item">Trang Chủ</a>
            <a href="products.php" class="nav-item">Sản Phẩm</a>
            <a href="profile.php" class="nav-item">Hồ Sơ</a>
        </div>
    </nav>

    <div class="history-container">
        <h1 class="page-title">Lịch Sử Đơn Hàng</h1>

        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 50px;">
                <i class="fa-solid fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-meta">
                        <h3>Đơn hàng #<?= $order['id'] ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">
                            <i class="fa-solid fa-calendar"></i> <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                        </p>
                        <p style="font-weight: 800; color: var(--accent-blue); margin-top: 10px;">
                            Tổng: $<?= number_format($order['total_amount'], 2) ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        <div style="margin-top: 15px;">
                            <a href="invoice.php?id=<?= $order['id'] ?>" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.85rem;">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

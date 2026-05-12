<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['fullname']);
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;

    if (!$terms) {
        $error = "Bạn phải xác nhận đồng ý với các điều khoản!";
    } elseif ($password !== $confirmPassword) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email này đã được sử dụng!";
        } else {
            // Demo data insertion (for real apps use password_hash)
            // Using a simple hash prefix to match db data style or just plain text
            $hashed_password = 'hashed_' . $password; 
            
            $stmtInsert = $conn->prepare("INSERT INTO users (full_name, gender, email, phone, address, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
            if ($stmtInsert->execute([$fullName, $gender, $email, $phone, $address, $hashed_password])) {
                $success = "Đăng ký thành công! Đang chuyển hướng...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Có lỗi xảy ra. Vui lòng thử lại!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaStyle - Đăng Ký Tài Khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
            color: var(--text-main);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            font-family: var(--font-heading);
            font-size: 2rem;
            margin-bottom: 10px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--text-main);
            font-family: var(--font-body);
            transition: var(--transition-smooth);
        }

        .form-group input:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.1);
            outline: none;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            cursor: pointer;
            transition: var(--transition-smooth);
            margin-top: 10px;
            background: var(--primary-gradient);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.2);
        }

        .submit-btn:hover {
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.4);
            transform: translateY(-2px);
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 1.2rem;
            z-index: 10;
            background: rgba(255,255,255,0.8);
            padding: 10px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
            transition: var(--transition-smooth);
            border: 1px solid var(--glass-border);
        }

        .back-home:hover {
            background: rgba(255,255,255,1);
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <a href="login.php" class="back-home"><i class="fa-solid fa-arrow-left"></i></a>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2>Đăng Ký Tài Khoản</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Tạo tài khoản để trải nghiệm AI Shopping</p>
            </div>
            <form action="register.php" method="POST">
                <?php if($error): ?>
                    <p style="color: red; text-align: center; margin-bottom: 10px;"><?= $error ?></p>
                <?php endif; ?>
                <?php if($success): ?>
                    <p style="color: #00ff88; text-align: center; margin-bottom: 10px;"><?= $success ?></p>
                <?php endif; ?>
                <div class="form-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="fullname" placeholder="Họ và tên" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-venus-mars"></i>
                    <select name="gender" required style="width: 100%; padding: 12px 15px 12px 45px; background: rgba(0, 0, 0, 0.05); border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text-main); font-family: var(--font-body);">
                        <option value="" disabled selected>Chọn giới tính</option>
                        <option value="nam">Nam</option>
                        <option value="nu">Nữ</option>
                        <option value="khac">Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="phone" placeholder="Số điện thoại" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="address" placeholder="Địa chỉ" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>
                <div class="form-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <input type="checkbox" name="terms" id="terms" required style="width: auto; padding: 0;">
                    <label for="terms" style="font-size: 0.9rem; color: var(--text-main); cursor: pointer;">Tôi đã đọc và đồng ý với <a href="#" style="color: var(--accent-purple); text-decoration: none;">Điều khoản dịch vụ</a></label>
                </div>
                
                <button type="submit" class="submit-btn">Đăng Ký <i class="fa-solid fa-user-plus"></i></button>
                <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
                    Đã có tài khoản? <a href="login.php" style="color: var(--accent-purple); text-decoration: none; font-weight: 600;">Đăng nhập ngay</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>

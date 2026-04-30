<?php
/**
 * @var string|null $pageTitle
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <!-- UTF-8 Zorunluluğu: Türkçe Karakter Sorunlarını Önler -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Emlak Platformu' ?></title>
    <!-- Harici stil vs için ek alan -->
    <style>
        body {
            /* Şık arka plan ve ortalama (Center Align) */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            background: #fff;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo h3 {
            font-weight: 700;
            color: #0d6efd;
            letter-spacing: -0.5px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-logo">
        <h3>Emlak CRM</h3>
        <p class="text-muted">Danışman & Yönetim Paneli</p>
    </div>

    <!-- Hata Mesajı Gösterme Alanı (Session'dan) -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Giriş Formu -->
    <form action="<?= htmlspecialchars(\web_url('/emlak/public/auth/authenticate')) ?>" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label text-muted fw-bold">E-Posta Adresi</label>
            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="ornek@ofis.com" required autofocus>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label text-muted fw-bold">Şifre</label>
            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="••••••••" required>
        </div>
        
        <!-- Eğer Beni Hatırla mekanizması da koymak isterseniz (Şimdilik tasarım amaçlı) -->
        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label text-muted" for="rememberMe">Beni Hatırla</label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Giriş Yap</button>
    </form>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

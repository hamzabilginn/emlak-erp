<?php
/**
 * @var string|null $pageTitle
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Yeni Şube Kaydı' ?></title>
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            background: #fff;
            margin: 2rem;
        }
        .register-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-logo h3 {
            font-weight: 700;
            color: #198754;
            letter-spacing: -0.5px;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="register-logo">
        <h3><i class="bi bi-shop"></i> Yeni Şube Aç</h3>
        <p class="text-muted">Emlak ERP platformuna ofisinizi taşıyın</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars(\web_url('/emlak/public/register/store')) ?>" method="POST">
        
        <h5 class="mb-3 text-secondary border-bottom pb-2">Şube Bilgileri</h5>
        <div class="mb-3">
            <label for="tenant_name" class="form-label text-muted fw-bold">Ofis/Şube Adı</label>
            <input type="text" class="form-control" id="tenant_name" name="tenant_name" placeholder="Örn: Reyhan Gayrimenkul" required autofocus>
        </div>

        <h5 class="mb-3 mt-4 text-secondary border-bottom pb-2">Yönetici Bilgileri</h5>
        <div class="mb-3">
            <label for="admin_name" class="form-label text-muted fw-bold">Ad Soyad</label>
            <input type="text" class="form-control" id="admin_name" name="admin_name" placeholder="Yönetici Adı" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label text-muted fw-bold">E-Posta Adresi</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="ornek@ofis.com" required>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label text-muted fw-bold">Şifre</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
            <div class="form-text">En az 6 karakter olmalıdır.</div>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">Kaydı Tamamla ve Şubeyi Aç</button>

        <div class="mt-4 text-center">
            <a href="<?= htmlspecialchars(\web_url('/emlak/public/auth/login')) ?>" class="text-decoration-none">
                Zaten bir hesabınız var mı? Giriş Yapın.
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

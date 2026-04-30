<?php
/**
 * Ana şablon (BaseController::render ile gömülü içerik).
 *
 * @var string|null $content
 * @var string|null $pageTitle
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?? 'Emlak Platformu' ?></title>
    <!-- UTF-8 Kodlamasına tam destek -->
    
    <!-- Bootstrap 5 CSS ve İkonlar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Genel Tasarım (Custom Styles) -->
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background-color: #1e293b; /* Koyu, profesyonel bir lacivert/gri */
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            font-weight: bold;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            background-color: rgba(255,255,255,0.05); /* Logo arka planını ayırmak için hafif ton */
            margin-bottom: 1rem;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            font-size: 1.05rem;
            transition: all 0.2s ease-in-out;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #3b82f6; /* Modern Mavi Hover */
            color: white;
            border-left: 4px solid #fff;
        }
        .content-area {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
            width: calc(100% - 260px); /* Responsive kalması için tam width tanımlaması */
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <!-- Sol Sidebar (Menü) -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-buildings"></i> Emlak SaaS
        </div>
        
        <!-- Kullanıcının Yetkisi ve Ofisini (İstenirse) Gösterebiliriz -->
        <div class="px-4 pb-2 mb-3 text-center" style="font-size:0.85rem; color:#94a3b8;">
            Hoş Geldiniz, <br><strong class="text-white"><?= htmlspecialchars($_SESSION['name'] ?? 'Misafir') ?></strong>
        </div>

        <a href="<?= htmlspecialchars(\web_url('/emlak/public/ana-pano')) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/portfoyler')) ?>"><i class="bi bi-house"></i> Portföyler</a>
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/musteriler')) ?>"><i class="bi bi-people"></i> Müşteriler</a>
        <hr class="border-secondary mx-3 my-2" style="opacity:0.3;">
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/yer-gosterme')) ?>"><i class="bi bi-calendar-event"></i> Yer Gösterme</a>
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/ortak-havuz')) ?>"><i class="bi bi-globe"></i> Ortak Havuz</a>
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/esnaf-kasasi')) ?>" class="text-warning"><i class="bi bi-cash-stack"></i> Esnaf Kasası</a>
                <?php
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
            $hostUrl = $protocol . $_SERVER['HTTP_HOST'];
            $tenantRef = $_SESSION['tenant_id'] ?? 0;
            $showcaseLink = $hostUrl . \web_url('/emlak/public/showcase') . '?tenant=' . urlencode((string) $tenantRef);
            $waVitrinText = "Merhaba, ofisimizin tüm güncel ve detaylı ilan portföyüne şu linkten ulaşabilirsiniz: {$showcaseLink}";
        ?>
        <a href="https://wa.me/?text=<?= rawurlencode($waVitrinText) ?>" target="_blank" class="text-success fw-bold"><i class="bi bi-whatsapp"></i> Vitrini WhatsApp'ta Paylaş</a>

        <!-- Boşluk yaratmak için mt-auto kullanarak diğerlerini yukarı sıkıştırır -->
        <div class="mt-auto">
            <a href="<?= htmlspecialchars(\web_url('/emlak/public/cikis-yap')) ?>" class="text-danger border-top border-secondary py-3">
                <i class="bi bi-box-arrow-left"></i> Çıkış Yap
            </a>
        </div>
    </div>

    <!-- Sağ Ana İçerik (Content) Bölümü -->
    <div class="content-area">
        
        <!-- Session Başarı mesajlarını ekrana bastıran otomatik bir yapı (Layout'da olması akıllıcadır) -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tüm View Sayfaları (View Content) Buraya Gömülecek -->
        <?= $content ?? '' ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>

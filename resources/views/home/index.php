<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var array $tenants
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <!-- SEO Meta Etiketleri -->
    <meta name="robots" content="index, follow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 4rem 1rem;
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .hero-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .tenant-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            padding: 2rem 1rem;
        }
        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .tenant-icon {
            font-size: 4rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .tenant-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        .stats-badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
            border-radius: 50rem;
            margin: 0 0.25rem;
        }
        .main-content {
            flex: 1;
        }
        .footer-action {
            padding: 3rem 1rem;
            text-align: center;
            background-color: #e9ecef;
            margin-top: 4rem;
        }
    </style>
</head>
<body>

    <header class="hero-section">
        <div class="container">
            <h1 class="hero-title">Emlak Platformuna Hoş Geldiniz</h1>
            <p class="lead">Şehrinizdeki en seçkin emlak ofisleri ve binlerce ilan bir tık uzağınızda.</p>
        </div>
    </header>

    <main class="container main-content">
        <h2 class="text-center mb-4" style="font-weight: 700; color: #343a40;">Aktif Emlak Ofislerimiz</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($tenants as $tenant): ?>
            <div class="col">
                <a href="<?= htmlspecialchars(\web_url('/emlak/public/vitrin?tenant=' . $tenant['id'])) ?>" class="text-decoration-none">
                    <div class="card tenant-card h-100">
                        <div class="tenant-icon">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="card-body p-0">
                            <h3 class="tenant-name"><?= htmlspecialchars($tenant['name']) ?></h3>
                            <div class="mt-3">
                                <span class="badge bg-danger stats-badge"><i class="bi bi-house-door"></i> <?= (int)$tenant['for_sale_count'] ?> Satılık</span>
                                <span class="badge bg-primary stats-badge"><i class="bi bi-key"></i> <?= (int)$tenant['for_rent_count'] ?> Kiralık</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 mt-3">
                            <button class="btn btn-outline-primary w-100 fw-bold">Portföyü İncele</button>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($tenants)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted lead">Sistemde henüz aktif ofis bulunmuyor.</p>
            </div>
            <?php endif; ?>
        </div>

        <section class="mt-5">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-column flex-md-row gap-3">
                <div>
                    <h2 style="font-weight: 700; color: #343a40;">Öne Çıkan İlanlar</h2>
                    <p class="text-muted mb-0">Ana sayfadan direkt en yeni ilanlara ulaşın, Googlebot için güçlü iç linkleme sağlayın.</p>
                </div>
                <a href="<?= htmlspecialchars(\web_url('/emlak/public/vitrin')) ?>" class="btn btn-outline-primary fw-bold">Tüm Vitrini Gör</a>
            </div>

            <?php if (!empty($featuredProperties)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($featuredProperties as $prop): ?>
                        <?php
                            $titleText = !empty($prop['title']) ? htmlspecialchars($prop['title']) : htmlspecialchars($prop['city'] . ' / ' . $prop['district']);
                            $priceText = number_format((float)$prop['price'], 0, ',', '.') . ' TL';
                        ?>
                        <div class="col">
                            <div class="card tenant-card h-100">
                                <?php if (!empty($prop['cover_image'])): ?>
                                    <img src="<?= htmlspecialchars(\property_image_url((string)$prop['cover_image'])) ?>" class="card-img-top" alt="<?= $titleText ?>">
                                <?php else: ?>
                                    <div class="card-img-placeholder">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="tenant-name"><?= $titleText ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($prop['district'] . ' / ' . $prop['city']) ?></p>
                                    <p class="fw-bold text-success mb-3"><?= $priceText ?></p>
                                    <a href="<?= htmlspecialchars(property_show_url($prop)) ?>" class="btn btn-primary w-100 fw-bold">İlanı Görüntüle</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">Şu anda öne çıkan ilan bulunmuyor. Sistemimizdeki yeni ilanları yakından takip edin.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer-action">
        <div class="container">
            <h4 class="mb-3">Emlakçı mısınız?</h4>
            <p class="text-muted mb-4">Müşterilerinize daha iyi hizmet vermek için platformumuza katılın.</p>
            <a href="<?= htmlspecialchars(\web_url('/emlak/public/auth/login')) ?>" class="btn btn-dark btn-lg fw-bold px-5 py-3 rounded-pill shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Şube Girişi / Üye Ol
            </a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * @var int $tenantId
 * @var string $tenantName
 * @var list<array<string, mixed>> $properties
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">        
    <title><?= htmlspecialchars((string)$tenantName) ?> - Dijital Portföy</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Showcase Özel Tasarım -->
    <style>
        body {
            background-color: #fafbfc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .showcase-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            padding: 3rem 1rem;
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-bottom: 4px solid #3b82f6;
        }
        .showcase-title {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        .property-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s;
            background-color: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .card-img-placeholder {
            background-color: #e2e8f0;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 3rem;
        }
        .price-tag {
            font-size: 1.4rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 0.5rem;
        }
        .badge-status {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.85rem;
            padding: 0.5em 1em;
            border-radius: 50rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .property-details {
            font-size: 0.9rem;
            color: #64748b;
        }
        .property-details i {
            margin-right: 5px;
            color: #94a3b8;
        }
        .footer {
            margin-top: 4rem;
            padding: 2rem 0;
            background-color: #1e293b;
            color: #94a3b8;
            text-align: center;
            font-size: 0.9rem;
        }
        /* Mobile tweaks */
        @media (max-width: 768px) {
            .showcase-header { padding: 2rem 1rem; }
            .showcase-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <header class="showcase-header">
        <div class="container">
            <div class="mb-3">
                <i class="bi bi-buildings" style="font-size: 3.5rem; color: #3b82f6;"></i>
            </div>
            <h1 class="showcase-title"><?= htmlspecialchars((string)$tenantName) ?></h1>
            <p class="lead mb-0 text-white-50"><i class="bi bi-shield-check text-success"></i> Güvenilir Portföy Vitrini</p>
        </div>
    </header>

    <main class="container">
        <?php if (empty($properties)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-3 text-secondary">Şu anaktif bir ilan bulunmuyor</h3>
                <p class="text-muted">Lütfen daha sonra tekrar ziyaret ediniz.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($properties as $p): ?>
                    <?php 
                        $statusBadge = ($p['status'] === 'for_sale') 
                            ? '<span class="badge bg-danger badge-status">Satılık</span>' 
                            : '<span class="badge bg-primary badge-status">Kiralık</span>'; 
                    ?>
                    <div class="col">
                        <div class="card property-card h-100 position-relative">
                            
                            <?= $statusBadge ?>

                                                        <!-- Kapak Resmi -->
                            <?php if (!empty($p['cover_image'])): ?>
                                <div style="height: 200px; overflow: hidden;">
                                    <img src="<?= htmlspecialchars(\web_url('/emlak/public' . $p['cover_image'])) ?>" class="card-img-top" alt="İlan Kapağı" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <div class="card-img-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            $details = json_decode($p['details'] ?? '[]', true);
                            $catStr = $p['category'] === 'residential' ? 'Konut' : ($p['category'] === 'commercial' ? 'İş Yeri' : 'Arsa');
                            $statStr = $p['status'] === 'for_sale' ? 'Satılık' : 'Kiralık';
                            $titleText = !empty($p['title']) ? htmlspecialchars($p['title']) : (htmlspecialchars($p['city']) . ' / ' . htmlspecialchars($p['district']) . ' - ' . $statStr . ' ' . $catStr);
                            ?>
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark text-truncate" title="<?= $titleText ?>">
                                    <?= $titleText ?>
                                </h5>

                                <div class="price-tag">
                                    <?= number_format((float)$p['price'], 0, ',', '.') ?> TL
                                </div>
                                <a href="<?= htmlspecialchars(\web_url('/emlak/public/showcase/show/' . $p['id']) . '?tenant=' . (int) $tenantId) ?>" class="btn btn-outline-primary btn-sm mt-3 fw-bold w-100"><i class="bi bi-eye"></i> İlan Detayını İncele</a>

                                <div class="property-details mt-3 mb-2">
                                    <div class="row g-2">
                                        <div class="col-6 text-truncate" title="<?= htmlspecialchars((string)$p['district']) ?> / <?= htmlspecialchars((string)$p['city']) ?>">
                                            <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars((string)$p['district']) ?>
                                        </div>
                                        
                                        <div class="col-6">
                                            <i class="bi bi-arrows-fullscreen"></i> <?= !empty($details['net_m2']) ? htmlspecialchars((string)$details['net_m2']) : '?' ?> m²
                                        </div>
                                        
                                        <?php if(!empty($details['rooms'])): ?>
                                        <div class="col-6 text-truncate">
                                            <i class="bi bi-door-open-fill"></i> <?= htmlspecialchars((string)$details['rooms']) ?> Oda
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($details['floor'])): ?>
                                        <div class="col-6 text-truncate">
                                            <i class="bi bi-check2-square"></i> Kat: <?= htmlspecialchars((string)$details['floor']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                <?php
                                    $waText = urlencode("Merhaba, '" . $titleText . "' (İlan No: " . $p['id'] . ") başlıklı ilanınızla ilgileniyorum.");
                                ?>
                                <a href="https://wa.me/?text=<?= $waText ?>" target="_blank" class="btn btn-outline-success w-100 fw-bold">
                                    <i class="bi bi-whatsapp"></i> Bilgi Al
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> <strong><?= htmlspecialchars((string)$tenantName) ?></strong>. Tüm hakları saklıdır.</p>
            <p class="mb-0 text-secondary" style="font-size:0.8rem;">Bu sayfa Emlak ERP Dijital Vitrin sistemidir.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
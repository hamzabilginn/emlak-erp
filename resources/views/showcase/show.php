<?php
/**
 * @var array<string, mixed> $property
 * @var list<array<string, mixed>> $images
 * @var int $tenantId
 * @var string $tenantName
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="KTUe-TDE8-rODsoU-xXqDBW4PxoXV2aP_S0HSv9xzb8" />
    <title><?= htmlspecialchars(!empty($property['title']) ? $property['title'] : ($property['city'] . ' / ' . $property['district'] . ' - ' . ($property['status'] === 'for_sale' ? 'Satılık' : 'Kiralık') . ' ' . ($property['category'] === 'residential' ? 'Konut' : ($property['category'] === 'commercial' ? 'İş Yeri' : 'Arsa')))) ?> - <?= htmlspecialchars($tenantName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .showcase-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 1.5rem 0; color: #fff; }
        .carousel-item img { height: 500px; object-fit: cover; border-radius: 12px; }
        .property-details { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .price-badge { font-size: 2rem; font-weight: bold; color: #10b981; }
        .agent-card { background: #e2e8f0; padding: 1.5rem; border-radius: 12px; text-align: center; }
        .match-form { background: #eff6ff; padding: 1.5rem; border-radius: 12px; margin-top: 2rem; }
    </style>
</head>
<body>

<header class="showcase-header mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="bi bi-buildings text-primary"></i> <?= htmlspecialchars($tenantName) ?></h4>
        </div>
        <a href="<?= htmlspecialchars(\web_url('/emlak/public/showcase') . '?tenant=' . (int) $tenantId) ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Vitrine Dön</a>
    </div>
</header>

<main class="container">
    <div class="row">
        <!-- Fotoğraf Galerisi (Carousel) -->
        <div class="col-lg-8 mb-4">
            <?php if (!empty($images)): ?>
            <div id="propertyCarousel" class="carousel slide shadow-sm" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $img): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars(\property_image_url((string) $img['image_path'])) ?>" class="d-block w-100" alt="İlan Fotoğrafı">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Önceki</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sonraki</span>
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-secondary text-center p-5" role="alert">
                <i class="bi bi-image" style="font-size: 4rem; color: #94a3b8;"></i>
                <h5 class="mt-3 text-muted">Bu ilana ait fotoğraf bulunmuyor.</h5>
            </div>
            <?php endif; ?>

            <!-- Alt Detaylar (JSONB vs) -->
            <div class="property-details mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0 fw-bold"><?= htmlspecialchars(!empty($property['title']) ? $property['title'] : ($property['city'] . ' / ' . $property['district'] . ' - ' . ($property['status'] === 'for_sale' ? 'Satılık' : 'Kiralık') . ' ' . ($property['category'] === 'residential' ? 'Konut' : ($property['category'] === 'commercial' ? 'İş Yeri' : 'Arsa')))) ?></h2>
                    <div class="price-badge"><?= number_format((float)$property['price'], 0, ',', '.') ?> TL</div>
                </div>
                <hr>
                <div class="row mb-3 text-muted">
                    <div class="col-sm-4"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($property['district']) ?> / <?= htmlspecialchars($property['city']) ?></div>
                    <div class="col-sm-4"><i class="bi bi-tag-fill text-primary"></i> Kategori: <?= $property['category'] === 'residential' ? 'Konut' : ($property['category'] === 'commercial' ? 'İş Yeri' : 'Arsa') ?></div>
                    <div class="col-sm-4"><i class="bi bi-house-door-fill text-warning"></i> Durum: <?= $property['status'] === 'for_sale' ? 'Satılık' : 'Kiralık' ?></div>
                </div>
                
                
                
                <?php 
                $details = json_decode($property['details'], true) ?? []; 
                if (!empty($details)): 
                ?>
                <h5 class="mt-4"><i class="bi bi-list-check"></i> Ek Özellikler</h5>
                <ul class="list-group list-group-flush">
                    <?php foreach ($details as $key => $val): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php
                            $labels = [
                                'rooms' => 'Oda Sayısı',
                                'net_m2' => 'Net Metrekare (m²)',
                                'heating' => 'Isıtma',
                                'building_age' => 'Bina Yaşı',
                                'floor' => 'Bulunduğu Kat',
                                'furniture' => 'Eşya Durumu',
                                'elevator' => 'Asansör Durumu'
                            ];
                            $label = $labels[$key] ?? htmlspecialchars(str_replace('_', ' ', $key));
                        ?>
                        <strong class="text-capitalize"><?= $label ?></strong>
                        <span><?= htmlspecialchars($val) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sağ Taraf (Ajan Bilgisi ve Müşteri Eşleştirme) -->
        <div class="col-lg-4 mb-4">
            <div class="agent-card">
                <i class="bi bi-person-circle text-secondary" style="font-size: 3rem;"></i>
                <h5 class="mt-2 fw-bold text-dark"><?= htmlspecialchars($tenantName) ?></h5>
                <p class="text-muted"><i class="bi bi-telephone-fill"></i> <?= !empty($property['tenant_phone']) ? htmlspecialchars($property['tenant_phone']) : 'Emlak Ofisi' ?></p>
                <a href="tel:<?= htmlspecialchars($property['tenant_phone'] ?? '') ?>" class="btn btn-success w-100"><i class="bi bi-telephone"></i> Hemen Ara</a>
            </div>

            <!-- Talep Formu -->
            <div class="match-form">
                <h5 class="fw-bold"><i class="bi bi-chat-dots"></i> İlanla İlgileniyorum</h5>
                <p class="text-sm text-muted">Bilgilerinizi bırakın, emlak ofisimiz size ulaşsın.</p>
                <form action="<?= htmlspecialchars(\web_url('/emlak/public/showcase/lead/' . $property['id'])) ?>" method="POST">
                    <input type="hidden" name="tenant_id" value="<?= $tenantId ?>">
                    <div class="mb-3">
                        <label class="form-label">Adınız Soyadınız</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ali Yılmaz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon Numaranız</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="05XX XXX XX XX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mesajınız</label>
                        <textarea name="message" class="form-control" rows="2" placeholder="İlan hakkında detaylı bilgi almak istiyorum..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Gönder</button>
                    <div class="form-text mt-2"><i class="bi bi-shield-lock"></i> Bilgileriniz güvenle iletilecektir.</div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
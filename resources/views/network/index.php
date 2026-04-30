<?php
/**
 * @var string $pageTitle
 * @var list<array<string, mixed>> $sharedProperties
 * @var int $currentTenantId
 */
?>
<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 text-gray-800"><i class="bi bi-globe fs-2 me-2 text-primary"></i> Ortak Havuz (Paslaşma Ağı)</h1>
        <p class="text-muted">Ağımızdaki diğer mahalle ofislerinin paylaşıma açtığı ve komisyon paylaşımı (Paslaşma) yapabileceği aktif ilanlar.</p>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    <?php if (empty($sharedProperties)): ?>
        <div class="col-12 text-center w-100 py-5">
            <div class="text-muted">
                <i class="bi bi-inbox-fill fs-1 d-block mb-3"></i>
                <h4 class="fw-bold">Şu anda ortak havuzda paylaşılmış bir ilan bulunmuyor.</h4>
                <p>İlk paslaşma ilanını ofisinizden kendi ilanınızı ekleyerek yapmak ister misiniz?</p>
                <a href="<?= htmlspecialchars(\web_url('/emlak/public/property/create')) ?>" class="btn btn-primary mt-2">Yeni İlan Ekle</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($sharedProperties as $prop): ?>
            <?php
                // JSONB Formatını Çöz
                $details = json_decode($prop['details'], true) ?: [];
                $rooms = $details['rooms'] ?? '- Odalı';
                $net_m2 = $details['net_m2'] ?? '- m²';
                
                // Tipleri ve Statusu Tr yap
                $catLabel = $prop['category'] === 'residential' ? 'Konut' : ($prop['category'] === 'land' ? 'Arsa' : 'Ticari');
                $statusType = $prop['status'] === 'for_sale' ? 'Satılık' : 'Kiralık';
                $badgeColor = $prop['status'] === 'for_sale' ? 'bg-success' : 'bg-info text-dark';
                
                // Telefon Check
                $officePhone = !empty($prop['office_phone']) ? $prop['office_phone'] : '';

                // Whatsapp Link Generation (Otomatik wa.me URL)
                // Mesaj: "Ortak havuzdaki [İlan Başlığı] ilanınız için yazıyorum, paslaşabilir miyiz?"
                $message = sprintf(
                    "Merhaba %s,\nOrtak havuzdaki [%s - %s / %s | %s TL] ilanınız için yazıyorum, müşteri için paslaşabilir miyiz?",
                    $prop['office_name'],
                    $catLabel,
                    $prop['city'],
                    $prop['district'],
                    number_format($prop['price'], 0, ',', '.')
                );
                
                // Telefon varsa direkt o telefona link gider, yoksa whatsapp sadece mesajı sorarak pencere açar.
                $whatsAppUrl = 'https://wa.me/';
                if ($officePhone) {
                    // + ve bosluklari yok sayarak sadece numaraya dönustur (Örn: +90 530 123 45 67 -> 905301234567)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $officePhone);
                    $whatsAppUrl .= $cleanPhone;
                }
                $whatsAppUrl .= '?text=' . rawurlencode($message);
            ?>
            <div class="col">
                <!-- Bootstrap Şık Kart Yapısı -->
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                    
                    <div class="card-img-top bg-light text-center position-relative overflow-hidden" style="height: 160px; border-bottom: 2px dashed #f0f0f0;">
                        <?php if (!empty($prop['cover_image'])): ?>
                            <img src="<?= htmlspecialchars(\web_url('/emlak/public' . $prop['cover_image'])) ?>"
                                 alt=""
                                 class="w-100 h-100"
                                 style="object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <i class="bi bi-house-door text-secondary opacity-50" style="font-size: 5rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge <?= $badgeColor ?> rounded-pill fs-6 px-3 shadow-sm"><?= $statusType ?></span>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 pb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-primary fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">
                                <?= $catLabel ?>
                            </span>
                            <h4 class="text-success fw-bolder mb-0">
                                ₺<?= number_format($prop['price'], 0, ',', '.') ?>
                            </h4>
                        </div>
                        
                        <h5 class="card-title fw-bold text-dark mt-3">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                            <?= htmlspecialchars($prop['district']) ?>, <?= htmlspecialchars($prop['city']) ?>
                        </h5>
                        
                        <!-- JSONB Detaylar Bölümü -->
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php if(isset($details['rooms'])): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-door-open"></i> <?= htmlspecialchars($details['rooms']) ?></span>
                            <?php endif; ?>
                            <?php if(isset($details['net_m2'])): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-rulers"></i> <?= htmlspecialchars((string)$details['net_m2']) ?> m²</span>
                            <?php endif; ?>
                            <?php if(isset($details['heating'])): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-fire"></i> <?= htmlspecialchars($details['heating']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Ortak Ofis Bilgisi (Footer) -->
                    <div class="card-footer bg-white border-top-0 p-4 pt-1">
                        <hr class="mt-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shop fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">İLAN SAHİBİ (OFİS)</small>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($prop['office_name']) ?></span>
                                    <?php if ((int) ($prop['tenant_id'] ?? 0) === (int) ($currentTenantId ?? 0)): ?>
                                        <span class="badge bg-secondary ms-1">Kendi ilanınız</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                <a href="<?= htmlspecialchars(\web_url('/emlak/public/showcase/show/' . (int) $prop['id']) . '?tenant=' . (int) $prop['tenant_id']) ?>"
                                   target="_blank" rel="noopener"
                                   class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center justify-content-center"
                                   title="İlanın müşteri vitrinindeki detay ve fotoğrafları">
                                    <i class="bi bi-images me-1"></i> Fotoğraflar
                                </a>
                                <a href="<?= htmlspecialchars($whatsAppUrl) ?>" target="_blank" rel="noopener" class="btn btn-success d-flex align-items-center justify-content-center rounded-pill shadow-sm" title="Ofise WhatsApp'tan yazarak paslaşma talep et">
                                    <i class="bi bi-whatsapp me-2"></i> Paslaş
                                </a>
                            </div>
                        </div>
                        <?php if ($officePhone): ?>
                             <div class="text-muted text-end mt-2"><small><i class="bi bi-telephone"></i> <?= htmlspecialchars($officePhone) ?></small></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
/* Kartların üzerine gelince zarifçe yukarı kalkma efekti */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

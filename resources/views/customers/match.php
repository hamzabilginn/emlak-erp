<div class="content-header bg-white rounded-3 shadow-sm border border-info mb-4" style="padding: 1.5rem; border-left: 5px solid #17a2b8 !important;">
    <div>
        <h2 class="text-dark fw-bold mb-1">🔍 Müşteri Eşleştirme (Akıllı CRM Bulucusu)</h2>
        <h5 class="text-secondary fw-normal">
            Bütçeyi Aşmayan, Eşleşen Mülkler Aranıyor...
        </h5>
        <div class="d-flex align-items-center mt-3 flex-wrap">
            <span class="badge bg-primary fs-6 me-3 p-2 rounded-3" style="box-shadow: 0 4px 6px rgba(13,110,253,.2);">
                <i class="bi bi-person-circle"></i> Müşteri: <?= htmlspecialchars($customer['name']) ?>
            </span>
            <span class="badge bg-secondary fs-6 me-3 p-2 rounded-3 text-uppercase">
                <i class="bi bi-tag-fill"></i> 
                <?= $customer['type'] === 'buyer' ? 'ALICI (Satılık İlanlar Aranıyor)' : 'KİRACı (Kiralık İlanlar Aranıyor)' ?>
            </span>

            <!-- Akıllı Filtrenin Dinamik Bilgi Kartları (Eğer Varsa Yeşil ile Yoksa Griyle Null Yazarız JSONB) -->
            <?php if (!empty($demands['min_price']) || !empty($demands['max_price'])): ?>
                <span class="badge bg-success fs-6 me-2 p-2 rounded-3">
                    <i class="bi bi-cash"></i> 
                    <?= number_format($demands['min_price'] ?? 0) ?>₺ - <?= number_format($demands['max_price'] ?? 999999999) ?>₺ Arası
                </span>
            <?php endif; ?>

            <?php if (!empty($demands['city'])): ?>
                <span class="badge bg-info text-dark fs-6 me-2 p-2 rounded-3">
                    <i class="bi bi-pin-map"></i> <?= mb_strtoupper($demands['city'] ?? '') ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($demands['rooms'])): ?>
                <span class="badge bg-warning text-dark fs-6 me-2 p-2 rounded-3 border-danger">
                    <i class="bi bi-house-door-fill"></i> Aranan Oda: <?= htmlspecialchars($demands['rooms']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="/emlak/public/customer/index" class="btn btn-outline-dark d-flex align-items-center mt-3 ms-auto shadow-sm" style="max-height: 48px;">
        <i class="bi bi-arrow-left me-2 fs-5"></i> Müşteri Rehberine Dön
    </a>
</div>

<!-- EŞLEŞEN İLAN TABLOLARI VEYA KARTLARI -->
<div class="row g-4">
    <?php if (empty($properties)): ?>
        <div class="col-12">
            <div class="alert border border-danger bg-white p-5 text-center shadow-sm rounded-3">
                <i class="bi bi-x-octagon text-danger fw-bolder mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-dark fw-bold">Seçilen Kriterlere Göre Herhangi Bir "Eşleşen Mülk" Bulunamadı.</h4>
                <p class="text-secondary fs-5">Lütfen müşterinizin arama/fiyat kriterlerini veya elinizdeki ilanın özelliklerini yeniden kontrol edip tekrar deneyin.</p>
                <a href="/emlak/public/property/create" class="btn btn-danger mt-3 text-white px-5 rounded-pill shadow">
                    Yeni Bir İlan/Mülk Daha Ekle
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($properties as $p): ?>
            <!-- Dinamik Kart Render'i -->
            <div class="col-md-6 col-lg-4">
                 <div class="card h-100 shadow-sm border-0 border-top border-4 border-success position-relative" style="transition: transform 0.3s; border-radius: 12px;">
                    <!-- Kart Gövdesi Tasarımı -->
                     <div class="card-body p-4 position-relative">
                         <div class="d-flex justify-content-between align-items-start mb-3">
                             <div class="badge bg-success px-3 py-2 rounded-pill fs-6 shadow">
                                %100 UYUM ❤️
                             </div>
                             <h4 class="text-success fw-bolder mb-0"><?= number_format($p['price'], 0, ',', '.') ?> ₺</h4>
                         </div>
                         <h5 class="card-title fw-bold text-dark mt-2 mb-1">
                             <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                             <?= htmlspecialchars($p['district']) . ' / ' . htmlspecialchars($p['city']) ?>
                         </h5>
                         
                         <?php 
                            // Kartın İçinde PropertyModel gibi sadece 'JSONB details' olan JSON sütunu tekrar çözümleyip
                            // Kullanıcıya küçük özetleri (Isıtma, Metrekare vs var mı diye basıcağız) ekleyebiliriz
                            $pDetails = json_decode($p['details'] ?? '{}', true);
                            $catStr = $p['category'] === 'commercial' ? 'Ticari Mülk (Dükkan)' : ($p['category'] === 'land' ? 'Arsa / Tarla' : 'Konut Tipi Ev');
                         ?>
                         <p class="text-secondary fw-semibold mt-2 mb-3 border-bottom pb-2">Kategori: <?= $catStr ?></p>
                         
                         <div class="row text-muted fs-6 g-2 mt-1">
                             <div class="col-6">
                                <i class="bi bi-aspect-ratio text-primary me-1"></i> Net: <span class="text-dark fw-bold"><?= htmlspecialchars($pDetails['net_m2'] ?? 'Belirtilmedi') ?> m²</span>
                             </div>
                             <div class="col-6">
                                <i class="bi bi-door-open text-primary me-1"></i> Oda: <span class="text-dark fw-bold"><?= htmlspecialchars($pDetails['rooms'] ?? 'Belirtilmedi') ?></span>
                             </div>
                             <div class="col-12 text-truncate mt-1" title="<?= htmlspecialchars($pDetails['heating'] ?? 'Kombi vs') ?>">
                                 <i class="bi bi-fire text-danger me-1"></i> Isıtma: <strong class="text-dark"><?= htmlspecialchars($pDetails['heating'] ?? 'Belirtilmedi') ?></strong>
                             </div>
                         </div>
                     </div>
                     <div class="card-footer border-0 bg-transparent px-4 pb-4 mt-2">
                         <button class="btn btn-outline-success w-100 rounded text-success fw-bold d-flex shadow align-items-center justify-content-center" onclick="alert('İletişim Kuruluyor...')">
                             <i class="bi bi-whatsapp me-2 fs-5"></i> Müşteriye Sun / İlet
                         </button>
                     </div>
                 </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .card:hover {  transform: translateY(-8px);  box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;  }
</style>

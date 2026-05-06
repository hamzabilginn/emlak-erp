<?php
/**
 * @var string $pageTitle
 * @var list<array<string, mixed>> $customers
 * @var list<array<string, mixed>> $properties
 */
?>
<div class="content-header">
    <div>
        <h2 class="mb-0">Yeni Yer Gösterme Ajandası</h2>
        <span class="text-muted">Ofisinizin İlanları ile Alıcı/Kiracı Müşterinizi anında eşleştirin. Randevu oluşturun.</span>
    </div>
    <a href="<?= htmlspecialchars(\web_url('/emlak/public/yer-gosterme')) ?>" class="btn btn-outline-secondary d-flex align-items-center mt-3 mt-md-0">
        <i class="bi bi-arrow-left me-2"></i> Ajandaya Geri Dön
    </a>
</div>

<div class="card shadow-sm border-0 rounded-3 mb-5">
    <div class="card-body p-4">
        <!-- Ekleme Yolu -->
        <form action="<?= htmlspecialchars(\web_url('/emlak/public/viewing/store')) ?>" method="POST">

            <!-- BÖLÜM: Kişi ve İlan Seçme -->
            <h5 class="text-primary border-bottom pb-2 mb-4">
                <i class="bi bi-person-rolodex me-2"></i> Sözleşme ve Evi Gösterme Bilgisi
            </h5>
            <div class="row g-3 mb-4">
                
                <!-- 1. Müşteri (Select Dropdown) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Rehberden Müşteriyi Seçiniz *(Zorunlu)</label>
                    <select name="customer_id" class="form-select border-primary bg-light shadow-sm" required>
                        <option value="" disabled selected>--- CRM'den Bir Müşteri Seçin ---</option>
                        <?php if(!empty($customers)): ?>
                            <?php foreach($customers as $c): ?>
                                <!-- Sadece alıcı / kiralayan tipleri mi göstermeliyiz ? Yoksa hepsini mi? Hepsini gösterebiliriz -->
                                <option value="<?= htmlspecialchars($c['id']) ?>">
                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                                    - <?= $c['type']==='buyer'?'(Alıcı) ':($c['type']==='tenant'?'(Kiracı) ':'(Satıcı) ') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 2. İlan / Portföyümüz (Select Dropdown) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Gösterilecek İlan (Property) *(Zorunlu)</label>
                    <select name="property_id" class="form-select border-success bg-light shadow-sm" required>
                        <option value="" disabled selected>--- Mülklerinizden Birini Seçin ---</option>
                        <?php if(!empty($properties)): ?>
                            <?php foreach($properties as $p): ?>
                                <?php $lbl = ($p['status']==='for_sale'?'Satılık':'Kiralık'); ?>
                                <option value="<?= htmlspecialchars($p['id']) ?>">
                                    <?= "İlan #{$p['id']} - [{$lbl}] - {$p['district']}/{$p['city']}" ?> (<?= number_format($p['price'], 0, '', '.') ?> ₺)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- BÖLÜM: Ajanda Tarihi ve Notları -->
            <h5 class="text-secondary border-bottom pb-2 mb-4 mt-5">
                <i class="bi bi-calendar-event me-2"></i> Ajanda (Tarih ve Not)
            </h5>
            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Randevu Günü ve Saati *(Zorunlu)</label>
                    <!-- 'datetime-local' ile takvimi anında çıkartır -->
                    <input type="datetime-local" name="viewing_date" class="form-control" required>
                </div>
                
                <div class="col-md-8 mb-3">
                    <label class="form-label text-muted fw-bold">Görüşme Öncesi Not (İsteğe Bağlı)</label>
                    <textarea name="notes" rows="2" class="form-control border-light shadow-sm" placeholder="Anahtarı danışmandan al, evi kapıcı açacak vs. gibi notlar"></textarea>
                </div>
            </div>
            
            <hr class="mt-4 mb-4">
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary text-white btn-lg px-5 mx-2 fw-bold shadow-sm">
                    <i class="bi bi-save2 me-2"></i> Planla ve Ajandaya Ekle
                </button>
            </div>
        </form>
    </div>
</div>
<?php
/**
 * @var array<string, mixed> $property
 * @var string $pageTitle
 */
$details = json_decode($property['details'] ?? '[]', true);
$getDet = function($key) use ($details) {
    return isset($details[$key]) ? htmlspecialchars($details[$key]) : '';
};
?>
<div class="content-header">
    <div>
        <h2 class="mb-0">İlanı Düzenle / Güncelle</h2>
        <span class="text-muted">Kayıtlı ilanın detaylarını aşağıdan güncelleyebilirsiniz.</span>
    </div>
    <!-- Geri dönmek İçin Küçük Bir Ok Butonu -->
    <a href="<?= htmlspecialchars(\web_url('/emlak/public/portfoyler')) ?>" class="btn btn-outline-secondary d-flex align-items-center">
        <i class="bi bi-arrow-left me-2"></i> Listeye Dön
    </a>
</div>

<div class="card shadow-sm border-0 rounded-3 mb-5">
    <div class="card-body p-4">
        <!-- İlan Formu. Method zorunlu POST ve route "/property/store" -->
        <form action="<?= htmlspecialchars(\web_url('/emlak/public/portfoy-guncelle/' . $property['id'])) ?>"  method="POST" enctype="multipart/form-data">

            <!-- 1. Bölüm: Temel Mülk Bilgileri -->
            <h5 class="text-primary border-bottom pb-2 mb-4">
                <i class="bi bi-journal-text me-2"></i> Temel Mülk Bilgileri
            </h5>
            <!-- 0. İlan Başlığı -->
            <div class="row g-3 mb-4">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">İlan Başlığı *(Zorunlu)</label>
                    <input type="text" name="title" class="form-control form-control-lg text-primary fw-bold" placeholder="Örn: Sahibinden Ankara Manzaralı 3+1 Lüks Daire" value="<?= htmlspecialchars($property['title'] ?? '') ?>" required>
                    <div class="form-text">Müşterilerinizin vitrinde (WhatsApp veya listede) göreceği ana başlıktır.</div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Taşınmaz Kategorisi *(Zorunlu)</label>
                    <select name="category" class="form-select form-select-lg" required>
    <option value="residential" <?= $property['category'] === 'residential' ? 'selected' : '' ?>>Konut</option>
    <option value="commercial" <?= $property['category'] === 'commercial' ? 'selected' : '' ?>>Ticari İşyeri</option>
    <option value="land" <?= $property['category'] === 'land' ? 'selected' : '' ?>>Arsa / Tarla</option>
</select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">İlan Durumu *(Zorunlu)</label>
                    <select name="status" class="form-select form-select-lg" required>
    <option value="for_sale" <?= $property['status'] === 'for_sale' ? 'selected' : '' ?>>Satılık Mülk</option>
    <option value="for_rent" <?= $property['status'] === 'for_rent' ? 'selected' : '' ?>>Kiralık Mülk</option>
</select>
                </div>
            </div>

            <!-- 2. Bölüm: Konum ve Fiyat -->
            <h5 class="text-primary border-bottom pb-2 mb-4">
                <i class="bi bi-geo-alt me-2"></i> Konum &amp; Fiyat
            </h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">İl / Şehir *(Zorunlu)</label>
                    <!-- UTF-8 Destekli Input (Türkçe Karakterler sorunsuz okunur) -->
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($property['city'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">İlçe / Semt *(Zorunlu)</label>
                    <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($property['district'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-success">Fiyat (Türk Lirası) *(Zorunlu)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="price" class="form-control fw-bold" value="<?= htmlspecialchars($property['price'] ?? '') ?>"  min="0" required>
                        <span class="input-group-text bg-success text-white px-3 fw-bold">₺</span>
                    </div>
                    <small class="text-muted">Virgülsüz tam sayı veya kuruş (2,750,000.50)</small>
                </div>
            </div>

            <!-- 3. Bölüm: Teknİk JSONB Detaylar, PostgreSQL'in en çok sevdiği yer :) -->
            <h5 class="text-primary border-bottom pb-2 mb-4 mt-5">
                <i class="bi bi-gear-wide-connected me-2"></i> Teknik Detaylar (İsteğe Bağlı Ekstralar)
            </h5>
            <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-center" role="alert">
                <!-- Data Model->details field -->
                <i class="bi bi-info-circle-fill me-3 fs-3"></i>
                <div class="text-sm">
                    Bu veriler PostgreSQL üzerinde <strong>JSONB objesi</strong> olarak indekslenebilir şekilde tutulacaktır. Arsa kaydediyorsanız da arsanın tipini, Konut kaydediyorsanız Oda sayısını çekinmeden yazabilirsiniz. Dinamiktir.
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Oda Sayısı</label>
                    <!-- name="details[oda]" json array map -->
                    <input type="text" name="details[rooms]" class="form-control" value="<?= $getDet('rooms') ?>" >
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kesin Metrekare (Net m²)</label>
                    <input type="number" step="1" name="details[net_m2]" class="form-control" value="<?= $getDet('net_m2') ?>" >
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Isıtma Seçeneği</label>
                    <input type="text" name="details[heating]" class="form-control" value="<?= $getDet('heating') ?>" >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Bina Yaşı</label>
                    <input type="number" name="details[building_age]" class="form-control" value="<?= $getDet('building_age') ?>" >
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bulunduğu Kat</label>
                    <input type="text" name="details[floor]" class="form-control" value="<?= $getDet('floor') ?>" >
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Eşya Durumu</label>
                    <select name="details[furniture]" class="form-select">
    <option value="">Seçiniz / Boş Bırak</option>
    <option value="Yok" <?= $getDet('furniture') === 'Yok' ? 'selected' : '' ?>>Hayır, Eşyasız</option>
    <option value="Var" <?= $getDet('furniture') === 'Var' ? 'selected' : '' ?>>Evet, Eşyalı</option>
</select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Asansör Durumu</label>
                    <select name="details[elevator]" class="form-select">
    <option value="">Seçiniz / Boş Bırak</option>
    <option value="Asansörlü" <?= $getDet('elevator') === 'Asansörlü' ? 'selected' : '' ?>>Asansörlü</option>
    <option value="Asansörsüz" <?= $getDet('elevator') === 'Asansörsüz' ? 'selected' : '' ?>>Asansörsüz</option>
</select>
                </div>
            </div>
            
            <hr class="mt-4 mb-4">

            <!-- 4. Bölüm: Anahtar Takip Sistemi -->
            <h5 class="text-primary border-bottom pb-2 mb-4 mt-5">
                <i class="bi bi-key-fill me-2"></i> Anahtar Takip Sistemi
            </h5>
             <div class="row g-3 mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Anahtar Kimde?</label>
                    <select name="key_status" class="form-select">
    <option value="Bizde" <?= ($property['key_status'] ?? '') === 'Bizde' ? 'selected' : '' ?>>Bizde (Ofiste)</option>
    <option value="Mülk Sahibinde" <?= ($property['key_status'] ?? '') === 'Mülk Sahibinde' ? 'selected' : '' ?>>Mülk Sahibinde</option>
    <option value="Diğer Emlakçıda" <?= ($property['key_status'] ?? '') === 'Diğer Emlakçıda' ? 'selected' : '' ?>>Diğer Emlakçıda (Paslaşma)</option>
</select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Anahtar Pano/Kutu Numarası</label>
                    <input type="text" name="key_number" class="form-control" value="<?= htmlspecialchars($property['key_number'] ?? '') ?>" >
                    <small class="text-muted">Anahtar 'Bizde' ise ofisteki yerini hatırlamak için yazabilirsiniz.</small>
                </div>
            </div>

            <!-- 5. Bölüm: Ortak Havuz (Paslaşma) -->
            <div class="row w-100 mb-4 mx-0 mt-5">
                <div class="col-12 p-4 rounded-3 d-flex align-items-center" style="background-color: #e0f2fe; border-left: 5px solid #0d6efd;">
                    <i class="bi bi-globe fs-2 text-primary me-3"></i>
                    <div class="form-check form-switch pt-1">
                        <input class="form-check-input" type="checkbox" id="isSharedPool" name="is_shared_pool" <?= !empty($property['is_shared_pool']) ? 'checked' : '' ?> style="cursor: pointer; transform: scale(1.5); margin-right: 15px;">
                        <label class="form-check-label fw-bold text-dark pt-1" for="isSharedPool" style="cursor: pointer; font-size: 1.15rem;">
                            Bu İlanı "Ortak Havuz"da Paylaş (Diğer emlak ofisleri görebilir ve komisyon paylaşımı teklif edebilir)
                        </label>
                    </div>
                </div>
            </div>

            <hr class="mt-4 mb-4">

            <!-- 6. Bölüm: Fotoğraf Galerisi -->
            <h5 class="text-primary border-bottom pb-2 mb-4 mt-5">
                <i class="bi bi-images me-2"></i> Fotoğraflar (Çoklu Seçim)
            </h5>
            <div class="row g-3 mb-4">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">İlan Fotoğrafları</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <div class="form-text">Aynı anda birden fazla fotoğraf seçebilirsiniz. İlk yüklediğiniz ilan kapağı olacaktır.</div>
                </div>
            </div>

            <hr class="mt-4 mb-4">
            
            <!-- Submit / Gönder -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success btn-lg px-5 mx-2 fw-bold shadow-sm">
                    <i class="bi bi-save me-2"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

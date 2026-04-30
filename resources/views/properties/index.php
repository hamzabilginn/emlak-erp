<?php
/**
 * @var string $pageTitle
 * @var list<array<string, mixed>> $properties
 * @var list<array<string, mixed>> $customers
 */
?>
<!-- Üst Araç Çubuğu ve Buton -->
<div class="content-header">
    <div>
        <h2 class="mb-0">Portföy (İlan) Yönetimi</h2>
        <span class="text-muted">Bütün ofis mülklerinizi tek noktadan görün.</span>
    </div>
    <a href="<?= htmlspecialchars(\web_url('/emlak/public/property/create')) ?>" class="btn btn-primary d-flex align-items-center">
        <i class="bi bi-plus-lg me-2"></i> Yeni İlan Ekle
    </a>
</div>

<!-- Tablo Alanı (Card Style) -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <!-- DataTables mantalitesinde Hover'lı standart Tablo (Aynı zamanda Responsive olur) -->
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Kategori</th>
                        <th>Durum</th>
                        <th>Konum (İl/İlçe)</th>
                        <th>Fiyat</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($properties)): ?>
                        <?php foreach ($properties as $prop): ?>
                            <?php
                                // Dil çevirileri: Konut, Arsa vb. (Enum'den Turkish Label'a)
                                $catLabel = $prop['category'] === 'residential' ? 'Konut' : ($prop['category'] === 'land' ? 'Arsa' : 'Ticari');
                                
                                // Duruma Göre Bootstrap Badge Rengi
                                $statusBadge = 'bg-secondary';
                                $statusText = 'Bilinmiyor';
                                switch($prop['status']) {
                                    case 'for_sale': $statusBadge = 'bg-success'; $statusText = 'Satılık'; break;
                                    case 'for_rent': $statusBadge = 'bg-info text-dark'; $statusText = 'Kiralık'; break;
                                    case 'sold':     $statusBadge = 'bg-danger'; $statusText = 'Satıldı'; break;
                                    case 'rented':   $statusBadge = 'bg-warning text-dark'; $statusText = 'Kiralandı'; break;
                                }
                            ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?= htmlspecialchars($prop['id']) ?></td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= $catLabel ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $statusBadge ?> px-3 py-2 rounded-pill"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?= htmlspecialchars($prop['district']) ?></span>
                                        <span class="text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars($prop['city']) ?></span>
                                        
                                        <?php if(isset($prop['key_status']) && !empty($prop['key_status'])): ?>
                                            <div class="mt-1">
                                                <!-- Anahtar Rozeti -->
                                                <span class="badge bg-secondary opacity-75 shadow-sm" style="font-size: 0.70rem;" title="Anahtar Takibi">
                                                    <i class="bi bi-key-fill text-warning"></i> 
                                                    <?= htmlspecialchars($prop['key_status']) ?> 
                                                    <?= !empty($prop['key_number']) && $prop['key_status'] === 'Bizde' ? '('.htmlspecialchars($prop['key_number']).')' : '' ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- Veritabanındaki Numeric Field'ı Türk Lirası cinsine formatlama -->
                                    <span class="text-success fw-bold"><?= number_format($prop['price'], 2, ',', '.') ?> ₺</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= htmlspecialchars(\web_url('/emlak/public/portfoy-duzenle/' . $prop['id'])) ?>" class="btn btn-sm btn-outline-secondary rounded-circle" title="Detay">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <!-- Düzenle ve Sil İkon Alanları -->
                                    <a href="<?= htmlspecialchars(\web_url('/emlak/public/portfoy-duzenle/' . $prop['id'])) ?>" class="btn btn-sm btn-outline-primary rounded-circle ms-1" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- WhatsApp Hızlı İlan Paylaş Butonu -->
                                    <?php
                                        $detailsWA = json_decode($prop['details'] ?? '[]', true);
                                        $waRooms = !empty($detailsWA['rooms']) ? $detailsWA['rooms'] . ' ' : '';
                                        $waDistrict = !empty($prop['district']) ? $prop['district'] : '';
                                        $waTitle = !empty($prop['title']) ? ' ' . $prop['title'] : '';
                                        
                                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
                                        $hostUrl = $protocol . $_SERVER['HTTP_HOST'];
                                        $tenantRef = $_SESSION['tenant_id'] ?? 0;
                                        $propLink = $hostUrl . \web_url('/emlak/public/showcase/show/' . $prop['id']) . '?tenant=' . urlencode((string) $tenantRef);

                                        $whatsappText = rawurlencode("Merhaba, ilgilendiğiniz {$waDistrict} {$waRooms}{$waTitle} ilanımızın fotoğraflı detaylarına buradan bakabilirsiniz: {$propLink}");
                                    ?>
                                    <a href="https://wa.me/?text=<?= $whatsappText ?>" target="_blank" class="btn btn-sm btn-success rounded-pill ms-1 fw-bold shadow-sm" title="Vitrindeki İlan Linkini WhatsApp ile Gönder">
                                        <i class="bi bi-whatsapp"></i> Paylaş
                                    </a>

                                    <!-- İşi Bağla (Satıldı/Kiralandı) Butonu -->
                                    <?php if(in_array($prop['status'], ['for_sale', 'for_rent'])): ?>
                                        <button type="button" class="btn btn-sm btn-warning rounded-pill ms-1 fw-bold shadow-sm" style="color: #664d03; border-color: #ffc107;" data-bs-toggle="modal" data-bs-target="#dealModal" data-property-id="<?= htmlspecialchars($prop['id']) ?>" data-deal-type="<?= $prop['status'] === 'for_sale' ? 'Satış' : 'Kiralama' ?>" data-price="<?= number_format($prop['price'], 0, '', '') ?>" title="İşi Bağla (Satıldı/Kiralandı) Yap">
                                            <i class="bi bi-heptagon-half"></i> Bağla
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted"> 
                                <i class="bi bi-folder-x fs-1 d-block mb-3"></i>
                                Ofisinize ait henüz hiçbir portföy/ilan kaydedilmemiş. Hemen bir İlan Ekleyin!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- "İşi Bağla" (Deal Closing) Sihirbazı Modal -->
<div class="modal fade" id="dealModal" tabindex="-1" aria-labelledby="dealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars(\web_url('/emlak/public/deal/store')) ?>" method="POST" class="modal-content border-top-warning" style="border-top: .35rem solid #ffc107;">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="dealModalLabel">
                    <i class="bi bi-heptagon-half text-warning me-2"></i> İşi Bağla (Satış / Kiralama)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="property_id" id="dealPropertyId" value="">
                <input type="hidden" name="deal_type" id="dealType" value="">

                <div class="mb-3">
                    <label class="form-label fw-bold">1. Müşteri (Alıcı / Kiracı) Seçin</label>
                    <select name="customer_id" class="form-select bg-light" required>
                        <option value="">-- Müşteri Seçiniz --</option>
                        <?php if (isset($customers) && is_array($customers)): ?>
                            <?php foreach($customers as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">2. Anlaşılan Fiyat (TL)</label>
                    <input type="number" step="0.01" name="price" id="dealPrice" class="form-control fw-bold fs-5 text-success" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Önden Alınan Kapora</label>
                        <input type="number" step="0.01" name="deposit_taken" class="form-control" value="0.00" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-success border-bottom border-success border-2 pb-1">Bizim Komisyonumuz</label>
                        <input type="number" step="0.01" name="commission_earned" class="form-control bg-success text-white bg-opacity-10 fw-bold border-success" placeholder="Kazanılan Komisyon" required>
                        <small class="text-muted" style="font-size: 0.70rem;">(Varsa; Doğrudan Kasaya İşlenir)</small>
                    </div>
                </div>

                <div class="alert alert-warning py-2 mb-0 mt-3 d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle me-2 fs-5"></i>
                    <small>Onayladığınızda ilan otomatik olarak "Satıldı / Kiralandı" statüsüne geçecek ve yayından kalkacaktır.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark border-dark shadow-sm">
                    <i class="bi bi-check2-circle"></i> Onayla ve İşi Kapat
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var dealModal = document.getElementById('dealModal')
    if (dealModal) {
        dealModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var propertyId = button.getAttribute('data-property-id')
            var dealType = button.getAttribute('data-deal-type')
            var price = button.getAttribute('data-price')

            var modalPropertyIdInput = dealModal.querySelector('#dealPropertyId')
            var modalDealTypeInput = dealModal.querySelector('#dealType')
            var modalPriceInput = dealModal.querySelector('#dealPrice')

            modalPropertyIdInput.value = propertyId;
            modalDealTypeInput.value = dealType;
            modalPriceInput.value = price;
        })
    }
});
</script>

<div class="content-header">
    <div>
        <h2 class="mb-0">Müşteri (CRM) Rehberi</h2>
        <span class="text-muted">Portföyünüzle ilgilenen Tüm Alıcı, Satıcı ve Kiracıları tek merkezden yönetip takip edin.</span>
    </div>
    <a href="/emlak/public/customer/create" class="btn btn-warning d-flex align-items-center text-dark">
        <i class="bi bi-person-plus-fill me-2 fs-5"></i> Yeni Müşteri Rehbere Ekle
    </a>
</div>

<!-- Tablo Alanı -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <!-- DataTables Uyumlu Tasarım -->
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-4">Kimlik (ID)</th>
                        <th>Ad Soyad</th>
                        <th>İletişim / Tel</th>
                        <th>Müşteri Tipi Grubu</th>
                        <th class="text-end pe-4">Sihirli CRM İşlemleri</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $c): ?>
                            <?php
                                // Type (Enum) Translate Mantığı (Tamamen Türkçe Karakter Uyumlu)
                                $typeLabel = 'Kiracı';
                                $typeBadge = 'bg-info text-dark';
                                
                                if ($c['type'] === 'buyer') {
                                    $typeLabel = 'Alıcı Seçeneği';
                                    $typeBadge = 'bg-success';
                                } elseif ($c['type'] === 'seller') {
                                    $typeLabel = 'Mülk Satıcısı';
                                    $typeBadge = 'bg-danger';
                                }
                            ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">CRM-<?= htmlspecialchars($c['id']) ?></td>
                                <td>
                                    <span class="fw-bold text-primary fs-6"><?= htmlspecialchars($c['name']) ?></span>
                                </td>
                                <td>
                                    <i class="bi bi-telephone-outbound text-secondary me-1"></i>
                                    <?= htmlspecialchars($c['phone']) ?>
                                </td>
                                <td>
                                    <!-- Tip Gruplarına Göre Renkli Etiketler -->
                                    <span class="badge <?= $typeBadge ?> px-3 py-2 rounded-pill"><?= $typeLabel ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <!-- Sadece "Alıcı(buyer)" ve "Kiracı(tenant)" ise 'Akıllı Eşleştirme' Butonunu Gösteririz -->
                                    <?php if ($c['type'] === 'buyer' || $c['type'] === 'tenant'): ?>
                                        <a href="/emlak/public/customer/match/<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning border-warning shadow-sm rounded text-dark fw-semibold" title="Bütçesine Uygun Olan Evleri Bul!">
                                            🔍 Uygun İlanları Bul
                                        </a>
                                    <?php else: ?>
                                    <!-- Satıcı Müşteriler Zaten Kendi Mülkünü Satar. O Yüzden Eşleşmediğini pasif Gri yapalım -->
                                        <button disabled class="btn btn-sm btn-light border" title="Satıcılarda eşleştirme yapılmaz. Sadece kayıtları vardır.">Satıcı Profili</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-people-fill fs-1 d-block mb-3"></i>
                                Ofisinize ait henüz hiçbir CRM rehber kaydı / Müşteri bulunamadı. Hemen Bir Müşteriyi Ekleyin!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
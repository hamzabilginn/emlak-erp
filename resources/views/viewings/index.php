<div class="content-header">
    <div>
        <h2 class="mb-0">Ajanda & Yer Gösterme Takibi</h2>
        <span class="text-muted">Göstermek için sözleştiğiniz müşterilerin kayıtları ve resmi imzalı belgeleri buradan yönetilir.</span>
    </div>
    <a href="/emlak/public/viewing/create" class="btn btn-primary d-flex align-items-center">
        <i class="bi bi-calendar-plus me-2 fs-5"></i> Yeni Randevu Planla
    </a>
</div>

<!-- Tablo -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-4">Tarih (Zaman)</th>
                        <th>Kimliği (Müşteri)</th>
                        <th>Gösterilecek Ev/İlan</th>
                        <th>Durum</th>
                        <th class="text-end pe-4">Kurumsal İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($viewings)): ?>
                        <?php foreach ($viewings as $v): ?>
                            <?php
                                // Türkçe okunabilir format (Zaman dönüşümü)
                                $dt = new DateTime($v['viewing_date']);
                                $formattedDate = $dt->format('d.m.Y - H:i');

                                $statBadge = 'bg-secondary';
                                if ($v['status'] === 'Bekliyor') $statBadge = 'bg-warning text-dark';
                                if ($v['status'] === 'Gösterildi') $statBadge = 'bg-success';
                                if ($v['status'] === 'İptal') $statBadge = 'bg-danger';

                                // WhatsApp mesajı oluşturma
                                $waPhone = str_replace(' ', '', $v['customer_phone']); // Boşlukları sil 
                                $waText = rawurlencode("Merhaba {$v['customer_name']},\n\nSizinle {$formattedDate} tarihinde {$v['district']}/{$v['city']} konumundaki ilanımız için yer gösterme randevumuz bulunmaktadır. Lütfen teyit ediniz.\n\nEmlak Yönetimi");
                                $waUrl = "https://wa.me/90" . ltrim($waPhone, '0') . "?text=" . $waText;
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <i class="bi bi-calendar-check text-primary me-2"></i> <?= $formattedDate ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary"><?= htmlspecialchars($v['customer_name']) ?></div>
                                    <div class="text-muted" style="font-size:0.85rem;"><i class="bi bi-telephone"></i> <?= htmlspecialchars($v['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <span class="text-dark"> İlan #<?= $v['property_id'] ?> - <?= mb_strtoupper(htmlspecialchars($v['district'])) ?></span><br>
                                    <small class="text-muted"><i class="bi bi-tag"></i> <?= number_format($v['price'], 0, '', '.') ?> ₺</small>
                                </td>
                                <td>
                                    <span class="badge <?= $statBadge ?> px-3 py-2 rounded-pill"><?= htmlspecialchars($v['status']) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    
                                    <!-- Resmi Evrak Çıktısı -->
                                    <a href="/emlak/public/viewing/printDocument/<?= $v['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark me-2" title="Resmi A4 Yer Gösterme Matbu Evrağı">
                                        <i class="bi bi-printer-fill fs-6 text-dark pe-1"></i> Belge Yazdır
                                    </a>

                                    <!-- Müşteriye Randevu Hatırlatma (WhatsApp) -->
                                    <a href="<?= $waUrl ?>" target="_blank" class="btn btn-sm btn-success rounded shadow-sm text-white" title="Müşteriye Doğrudan WP'dan At">
                                        <i class="bi bi-whatsapp"></i> WP'dan At
                                    </a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                Henüz hiçbir randevu veya yer gösterme kaydınız bulunmuyor. Hadi yeni bir randevu yapın!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
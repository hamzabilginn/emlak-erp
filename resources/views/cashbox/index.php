<?php
/**
 * @var string $pageTitle
 * @var float|int $monthlyIncome
 * @var float|int $monthlyExpense
 * @var float|int $netProfit
 * @var list<array<string, mixed>> $transactions
 */
?>
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">💼 Esnaf Kasası (Gelir & Gider)</h1>
            <p class="text-muted">Bu ayki ofis harcamalarınız ve komisyon gelirlerinizi en pratik şekilde takip edin.</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#incomeModal">
                <i class="bi bi-plus-circle me-1"></i> Gelir Ekle
            </button>
            <button class="btn btn-danger shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#expenseModal">
                <i class="bi bi-dash-circle me-1"></i> Gider Ekle
            </button>
        </div>
    </div>

    <!-- Hızlı Özet Kartları -->
    <div class="row">
        <!-- 1. Toplam Gelir Kutusu -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">BU AYKİ KAZANÇ (KOMİSYON)</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">₺<?= number_format($monthlyIncome, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-wallet2 fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Toplam Gider Kutusu -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">BU AYKİ GİDERLER (OFİS & REKLAM)</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">₺<?= number_format($monthlyExpense, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-credit-card fs-1 text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Kalan Kasa Kutusu -->
        <div class="col-xl-4 col-md-6 mb-4">
            <?php $bg_color = $netProfit >= 0 ? 'text-primary' : 'text-warning'; ?>
            <?php $border_color = $netProfit >= 0 ? 'border-left-primary' : 'border-left-warning'; ?>
            
            <div class="card <?= $border_color ?> shadow h-100 py-2 bg-light">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold <?= $bg_color ?> text-uppercase mb-1">NET PİYASA (BU AY)</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">₺<?= number_format($netProfit, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Row End -->

    <!-- Pratik İşlem Geçmişi Tablosu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark"><i class="bi bi-list-task"></i> Yakın Zamanlı Kasa İşlemleri</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tarih</th>
                            <th>Tür</th>
                            <th>Kategori</th>
                            <th>Açıklama</th>
                            <th class="text-end">Tutar (TL)</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Kasanızda henüz işlem bulunmuyor.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($transactions as $txn): ?>
                                <tr>
                                    <td><?= date('d.m.Y H:i', strtotime($txn['transaction_date'])) ?></td>
                                    <td>
                                        <?php if($txn['type'] === 'Gelir'): ?>
                                            <span class="badge bg-success rounded-pill px-3">Gelir</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill px-3">Gider</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($txn['category']) ?></strong></td>
                                    <td class="text-muted"><small><?= htmlspecialchars($txn['description']) ?></small></td>
                                    <td class="text-end fw-bold <?= $txn['type'] === 'Gelir' ? 'text-success' : 'text-danger' ?>">
                                        <?= $txn['type'] === 'Gelir' ? '+' : '-' ?> ₺<?= number_format($txn['amount'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="<?= htmlspecialchars(\web_url('/emlak/public/cashbox/delete')) ?>" class="d-inline" onsubmit="return confirm('Bu işlemi silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$txn['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sil (İptal Et)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- GELİR EKLE MODEL -->
<div class="modal fade" id="incomeModal" tabindex="-1" aria-labelledby="incomeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= htmlspecialchars(\web_url('/emlak/public/cashbox/store')) ?>" class="modal-content border-top-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="incomeModalLabel"><i class="bi bi-plus-circle"></i> Yeni Gelir Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="type" value="Gelir">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Gelir Kategorisi</label>
                    <select name="category" class="form-select" required>
                        <option value="Komisyon (Satılık)">Komisyon (Satılık)</option>
                        <option value="Komisyon (Kiralık)">Komisyon (Kiralık)</option>
                        <option value="Ekspertiz Ücreti">Ekspertiz Ücreti</option>
                        <option value="Danışmanlık/Sözleşme">Danışmanlık/Sözleşme Bedeli</option>
                        <option value="Diğer Gelir">Diğer Kalemler</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tutar (TL)</label>
                    <input type="number" name="amount" class="form-control form-control-lg text-success fw-bold" step="0.01" min="1" placeholder="0.00" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Açıklama / Müşteri Adı <small>(İsteğe Bağlı)</small></label>
                    <input type="text" name="description" class="form-control" placeholder="Örn: Ahmet Bey'den Alınan Kira Komisyonu">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Kasaya Ekle</button>
            </div>
        </form>
    </div>
</div>

<!-- GİDER EKLE MODEL -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= htmlspecialchars(\web_url('/emlak/public/cashbox/store')) ?>" class="modal-content border-top-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="expenseModalLabel"><i class="bi bi-dash-circle"></i> Yeni Gider Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="type" value="Gider">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Gider Kategorisi</label>
                    <select name="category" class="form-select" required>
                        <option value="İlan Sitesi (Sahibinden/Hepsiemlak)">İlan Web Siteleri / Portallar</option>
                        <option value="Afiş (Branda) & Kartvizit">Afiş, Branda, Matbaa</option>
                        <option value="Ofis Kirası">Ofis Kirası</option>
                        <option value="Elektrik/Su/İnternet">Faturalar (Elektrik, Su, İnternet)</option>
                        <option value="Ofis Giderleri (Çay, Kahve)">Mutfak, Çay, İkram</option>
                        <option value="Personel / SGK">Maaş & SGK</option>
                        <option value="Araç, Yakıt & Ulaşım">Yakıt & Ulaşım</option>
                        <option value="Diğer Giderler">Diğer Kalemler</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tutar (TL)</label>
                    <input type="number" name="amount" class="form-control form-control-lg text-danger fw-bold" step="0.01" min="1" placeholder="0.00" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Ekstra Not <small>(İsteğe Bağlı)</small></label>
                    <input type="text" name="description" class="form-control" placeholder="Örn: Bu haftaki zincir market mutfak alışverişi">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg"></i> Gideri Yaz</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Kasa Özel CSS */
.border-left-success { border-left: .35rem solid #198754 !important; }
.border-left-danger { border-left: .35rem solid #dc3545 !important; }
.border-left-primary { border-left: .35rem solid #0d6efd !important; }
.border-left-warning { border-left: .35rem solid #ffc107 !important; }
.border-top-success { border-top: .3rem solid #198754 !important; }
.border-top-danger { border-top: .3rem solid #dc3545 !important; }
</style>

<?php
/**
 * @var string $pageTitle
 * @var array<string, mixed> $doc
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Standart BootStrap ile tabloyu da ekleyelim -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /*
         *  KRİTİK EKLENTİ: RESMİ BELGE TASARIMI (Matbu Kağıdı)
         *  Yalnızca Siyah-Beyaz Baskı (Print), A4 Sayfası Standardı
         */
        body { background: #fff; font-family: 'Times New Roman', serif; color: #000; font-size: 14pt; }
        
        .document-container { max-width: 800px; margin: 0 auto; padding: 40px; border: 1px solid #ccc; min-height: 100vh; }
        .doc-header h2 { font-weight: 900; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; text-align: center; }
        
        .doc-meta { font-size: 12pt; display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: bold; }
        
        .doc-body { text-align: justify; line-height: 1.8; margin-bottom: 50px; }
        .doc-details table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 11pt; }
        .doc-details th, .doc-details td { border: 1px solid #000; padding: 10px; }
        .doc-details th { background: #f8f9fa; width: 40%; }
        
        .signatures { display: flex; justify-content: space-between; margin-top: 80px; text-align: center; }
        .sign-box { width: 45%; border-top: 1px dotted #000; padding-top: 10px; font-weight: bold; }
        
        /* Print komutunda UI butonları gizlensin */
        @media print {
            .no-print { display: none !important; }
            .document-container { border: none; padding: 0; min-height: auto; }
            body { font-size: 12pt; }
        }
    </style>
</head>

<?php 
    $viewDate = new DateTime($doc['viewing_date']); 
    $cat = $doc['category'] === 'for_sale' ? 'SATILIK' : 'KİRALIK';
?>

<body>
    
    <!-- Üstteki Yazdır Butonu (Ekrandan gider) -->
    <div class="text-center mt-3 mb-4 no-print">
        <button onclick="window.print();" class="btn btn-outline-dark fw-bold btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-printer-fill me-2" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            </svg> 
            A4 Kağıdına Resmi Olarak Yazdır
        </button>
        <button onclick="window.close();" class="btn btn-outline-danger btn-lg ms-2 border">Pencereyi Kapat</button>
    </div>

    <div class="document-container shadow">
        
        <div class="doc-header">
            <h2>Yer Gösterme ve Tellaliye Sözleşmesi</h2>
        </div>
        
        <div class="doc-meta">
            <div>Firma / Dükkan: <span><?= mb_strtoupper(htmlspecialchars($doc['tenant_name'])) ?></span></div>
            <div>Tarih & Saat: <span><?= $viewDate->format('d.m.Y - H:i') ?></span></div>
            <div>Belge No: <span>YG-<?= htmlspecialchars($doc['id']) ?></span></div>
        </div>

        <div class="doc-body">
            <p>
                İşbu sözleşme ile, aşağıda adı, soyadı ve iletişim bilgileri yer alan muhatap (Müşteri); aşağıda özellikleri ve adresi açıkça yazılı bulunan gayrimenkulü, <strong><?= mb_strtoupper(htmlspecialchars($doc['tenant_name'])) ?></strong> yetkilileri eşliğinde bizzat yerinde gördüğünü, incelediğini ve gayrimenkul hakkında detaylı bilgilendirildiğini beyan ve kabul eder. 
            </p>
            <p>
                Müşteri, işbu taşınmazı satın alması veya kiralaması durumunda aracı firmaya/emlak komisyoncusuna yasa ve yönetmeliklerde belirlenen (satış bedeli üzerinden %2 + KDV veya sözleşmede belirlenen) hizmet bedelini (tellaliye) ödemeyi şimdiden ve gayrikabili rücu kabul, beyan ve taahhüt eder.
            </p>
        </div>

        <div class="doc-details">
            <h5 class="fw-bold mb-3 border-bottom pb-1">Mülk & Gösterilen Gayrimenkul Detayları</h5>
            <table>
                <tr>
                    <th>Mülkiyet Referans No</th>
                    <td class="fw-bold fs-5">#<?= htmlspecialchars($doc['property_no']) ?></td>
                </tr>
                <tr>
                    <th>Portföy Tipi / Durumu</th>
                    <td class="fw-bold"><?= $cat ?></td>
                </tr>
                <tr>
                    <th>Gayrimenkulün İl / İlçesi</th>
                    <td><?= mb_strtoupper(htmlspecialchars($doc['city'])) ?> / <?= mb_strtoupper(htmlspecialchars($doc['district'])) ?></td>
                </tr>
                <tr>
                    <th>Ekspertiz (Beyan) Fiyatı</th>
                    <td class="fw-bold fs-5"><?= number_format($doc['price'], 0, '', '.') ?>,00 ₺</td>
                </tr>
            </table>

            <h5 class="fw-bold mb-3 mt-5 border-bottom pb-1">Hizmet Alan / Gayrimenkulü Gören (Müşteri)</h5>
            <table>
                <tr>
                    <th>Aday / Müşteri Adı - Soyadı</th>
                    <td class="fw-bold text-uppercase"><?= htmlspecialchars($doc['customer_name']) ?></td>
                </tr>
                <tr>
                    <th>Müşteri İletişim / Tel</th>
                    <td><?= htmlspecialchars($doc['customer_phone']) ?></td>
                </tr>
            </table>
        </div>

        <div class="signatures">
            <div class="sign-box">
                <p>Emlak Firması Kaşe / İmza</p>
                <div class="mt-5"><?= mb_strtoupper(htmlspecialchars($doc['tenant_name'])) ?> Yönetimi</div>
            </div>
            <div class="sign-box">
                <p>Yer Gösterilen Müşteri İmzası</p>
                <div class="text-uppercase mt-5"><?= htmlspecialchars($doc['customer_name']) ?></div>
            </div>
        </div>
        
    </div>
</body>
</html>

<div class="content-header">
    <div>
        <h2 class="mb-0">Yeni CRM Müşterisi Oluştur (Adayı Arayın)</h2>
        <span class="text-muted">Aşağıdaki formu doldurup akıllı eşleşme verilerine göre bütçesine sadık müşteriyi kaydedebilirsiniz.</span>
    </div>
    <a href="/emlak/public/customer/index" class="btn btn-outline-secondary d-flex align-items-center">
        <i class="bi bi-arrow-left " style="margin-right:0.5rem;"></i> Tüm Müşteriler Rehberine Dön
    </a>
</div>

<div class="card shadow-sm border-0 rounded-3 mb-5">
    <div class="card-body p-4">
        <!-- CRM (Müşteri) Ekleme Yolu (POST) -->
        <form action="/emlak/public/customer/store" method="POST">

            <!-- BÖLÜM 1: TEMEL KİMLİK (Sabit Kısımlar) -->
            <h5 class="text-warning text-dark border-bottom pb-2 mb-4">
                <i class="bi bi-person-badge-fill me-2 fs-5"></i> Müşteri Kimlik Kartı
            </h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Müşterinin Rolü / Tipi *(Zorunlu)</label>
                    <!-- Type select ile JS Toggle için id verdik -->
                    <select name="type" id="customerTypeSelector" class="form-select form-select-lg shadow-sm" required>
                         <!-- Value'ler database de enum-check tiplere denktir. --> 
                        <option value="buyer" selected>Mülk Alıcısı (Emanetçi/Yatırımcı)</option>
                        <option value="tenant">Kiralayacak Olan (Kiracı Adayı)</option>
                        <option value="seller">Mülk Sahibi (Ev Olan / Satıcı Adayı)</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Adayın Adı - Soyadı *(Zorunlu)</label>
                    <input type="text" name="name" class="form-control form-control-lg border-primary shadow-sm" placeholder="Ahmet Yılmaz" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold"><i class="bi bi-telephone-fill text-success"></i> Telefon Bilgisi *(Zorunlu)</label>
                    <input type="tel" name="phone" class="form-control form-control-lg border-success fw-bold shadow-sm" placeholder="05XX XXX XX XX" required>
                </div>
            </div>

            <!-- BÖLÜM 2: JSONB TALEPLERI. (JavaScrip İle Toggle Edilecek Div ID: demandDetailsSection) -->
            <!-- Emlak CRM'sinde Eğer Aday SATICI('seller') ise bütçe vb aramaz bu yüzden bu kısım formdan saklanır. -->
            <div id="demandDetailsSection" class="mt-5">
                <h5 class="text-primary border-bottom pb-2 mb-4">
                    <i class="bi bi-sliders me-2 fs-5"></i> Bütçe ve Aradığı Evin Eşleştirme Kriterleri (JSONB İstatistikleri)
                </h5>
                <div class="alert alert-primary border-0 rounded-3 mb-4 d-flex align-items-center" role="alert">
                    <div class="text-sm">
                        Akıllı CRM Eşleştiricisi (Matchmaking) kullanıcının aşağıdaki istekleri üzerinden algoritmaya girer. Girilmezse sistem boş olanları yok sayarak eşletirmeye tüm mülk kategorilerini sunar!
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Asgari (Minimum) Bütçesi</label>
                        <input type="number" step="0.01" name="min_price" class="form-control" placeholder="Örn: 500,000 ₺">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Azami (Maksimum) Bütçesi</label>
                        <input type="number" step="0.01" name="max_price" class="form-control" placeholder="Örn: 3,500,000 ₺">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-dark">Aradığı İl (Şehir)</label>
                        <input type="text" name="city" class="form-control" placeholder="Örn: İstanbul">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-dark">Aradığı İlçe (Semt)</label>
                        <input type="text" name="district" class="form-control" placeholder="Örn: Kadıköy">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Ailesi Kaç Kişi? (Oda Sayısı Tipi)</label>
                        <input type="text" name="rooms" class="form-control border-warning" placeholder="Örn: 3+1 (veya null bırak)">
                    </div>
                </div>
            </div>
            
            <hr class="mt-4 mb-4">
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-warning text-dark btn-lg px-5 mx-2 fw-bold shadow-sm">
                    <i class="bi bi-person-check-fill me-2 fs-5"></i> Seçili Müşteriyi Sistemi / Rehbere Al
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Vanilla JS ile Satıcı Tipi Seçildiğinde Detaylı Kriter Formunu Saklayan Harika Kod  -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelector = document.getElementById('customerTypeSelector');
        const demandSection = document.getElementById('demandDetailsSection');
        
        function toggleDemandSection() {
            if (typeSelector.value === 'seller') {
                demandSection.style.display = 'none'; // DOM'da Satıcı iken gizlenir veriler gitmez.
            } else {
                demandSection.style.display = 'block'; // Kiracı veya Alıcı Emlakçıya kriter sorar
            }
        }
        
        typeSelector.addEventListener('change', toggleDemandSection);
        toggleDemandSection(); // Form Sayfası açıldığında kontrol eder (Edit'e/Yenilelemeye karşı fix)
    });
</script>
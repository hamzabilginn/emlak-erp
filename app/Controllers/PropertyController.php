<?php
namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Models\CustomerModel;
use App\Services\SupabaseStorageClient;

/**
 * Portföy / İlan Yönetimi Controller
 * CRUD işlemlerinin yanı sıra Data Render'ları buradan yapılır.
 */
class PropertyController extends BaseController {

    public function __construct() {
        // SaaS Güvenliği: Eğer login olmamışsa (tenant_id oturumu yoksa) anında sayfadan at.
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {    
            $this->redirect('/emlak/public/auth/login');
            // Constructor'da exit yapmamak için redirect zaten exit; kullanıyor ama emin olmalıyız.
        }
    }

    /**
     * Tüm İlanların (Model sayesinde sadece ilgili Ofise ait olanların) Listesi
     */
    public function index(): void {
        $model = new PropertyModel();
        $properties = $model->getAll();
        
        $customerModel = new CustomerModel();
        $customers = $customerModel->getAll();

        $this->render('properties/index', [
            'pageTitle' => 'Emlak Portföyüm',
            'properties' => $properties,
            'customers' => $customers
        ]);
    }

    /**
     * Yeni İlan Ekleme Formunu açan fonksiyon
     */
    public function create(): void {
        $this->render('properties/create', [
            'pageTitle' => 'Yeni İlan Ekle'
        ]);
    }

    /**
     * Post metoduyla gelen yeni İlan verisini yakalayıp Veritabanına (Insert) döken yapı
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/property/index');
            return;
        }

        $model = new PropertyModel();

        // 1. Standart Verileri Yakala (PHP 8.1+ FILTER_SANITIZE_STRING kaldırıldı; PDO ile güvenli kayıt)
        $data = [
            'title'    => trim((string) ($_POST['title'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? 'residential')),
            'status'   => trim((string) ($_POST['status'] ?? 'for_sale')),
            'city'     => trim((string) ($_POST['city'] ?? '')),
            'district' => trim((string) ($_POST['district'] ?? '')),
            'price'    => (float) ($_POST['price'] ?? 0),
            'is_shared_pool' => isset($_POST['is_shared_pool']) ? 1 : 0,
            'key_status' => htmlspecialchars(trim($_POST['key_status'] ?? 'Bizde')),
            'key_number' => htmlspecialchars(trim($_POST['key_number'] ?? ''))
        ];

        // 2. Teknik Detayları (Oda Sayısı, m2 vs) Dinamik Olarak JSONB (JSON String) yap.
        $detailsArray = $_POST['details'] ?? [];
        
        // Boş olan array leri (kullanıcının girmediklerini) filtrele temiz tut
        $detailsClean = array_filter($detailsArray, function($value) {
            return $value !== null && $value !== '';
        });

        // PostgreSQL JSONB alanı tam uyumlu formata (JSON Encode) geçir (Türkçe karakter dostu)
        $data['details'] = json_encode($detailsClean, JSON_UNESCAPED_UNICODE);

        // 3. Base Model'in tenant_id güvenli insert'üne yolla
        $propertyId = $model->insert($data);
        if ($propertyId) {
            $this->handleImages((int) $propertyId);
            $imgErr = $_SESSION['error'] ?? '';
            if ($imgErr !== '') {
                unset($_SESSION['error']);
                $_SESSION['success'] = 'Kayıt eklendi. Fotoğraf yükleme: ' . $imgErr;
            } else {
                $_SESSION['success'] = 'Yeni emlak kaydı portföye başarıyla eklendi.';
            }
        } else {
            $_SESSION['error'] = "Ekleme sırasında veritabanı hatası oluştu.";
        }

        $this->redirect('/emlak/public/property/index');
    }

    /**
     * Portföy Düzenleme / Detay Görünümü
     */
    public function edit(int $id): void {
        $model = new PropertyModel();
        $property = $model->getById($id);

        if (!$property) {
            $_SESSION['error'] = 'Kayıt bulunamadı veya bu kaydı görme yetkiniz yok.';
            $this->redirect('/emlak/public/portfoyler');
            return;
        }

        $this->render('properties/edit', [
            'pageTitle' => 'İlan Düzenle & Detay',
            'property' => $property
        ]);
    }

    /**
     * Düzenleme POST
     */
    public function update(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             $this->redirect('/emlak/public/portfoyler');
             return;
        }

        $model = new PropertyModel();
        
        // Form Verileri (Gelişmiş filter eklenebilir)
        $data = [
            'title'    => trim((string) ($_POST['title'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? 'residential')),
            'status'   => trim((string) ($_POST['status'] ?? 'for_sale')),
            'city'     => trim((string) ($_POST['city'] ?? '')),
            'district' => trim((string) ($_POST['district'] ?? '')),
            'price'    => (float) ($_POST['price'] ?? 0),
            'is_shared_pool' => isset($_POST['is_shared_pool']) ? 1 : 0,
            'key_status' => trim((string) ($_POST['key_status'] ?? 'Bizde')),
            'key_number' => trim((string) ($_POST['key_number'] ?? ''))
        ];

        $detailsArray = $_POST['details'] ?? [];
        $detailsClean = array_filter($detailsArray, function($value) {
            return $value !== null && $value !== ''; 
        });
        $data['details'] = json_encode($detailsClean, JSON_UNESCAPED_UNICODE);

        if ($model->update($id, $data)) {
            $this->handleImages($id);
            $imgErr = $_SESSION['error'] ?? '';
            if ($imgErr !== '') {
                unset($_SESSION['error']);
                $_SESSION['success'] = 'Kayıt güncellendi. Fotoğraf yükleme: ' . $imgErr;
            } else {
                $_SESSION['success'] = 'Emlak kaydı güncellendi.';
            }
        } else {
             $_SESSION['error'] = 'Güncelleme hatası.';
        }
        $this->redirect('/emlak/public/portfoyler');
    }


    /**
     * İlan Silme: Veritabanı Transaction'ı içinde:
     * 1. İlana ait tüm görselleri bulur
     * 2. Supabase Storage'tan siler
     * 3. Veritabanından property ve images silir
     * Eğer herhangi bir hata: Rollback ve hata mesajı göster.
     */
    public function delete(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/portfoyler');
            return;
        }

        $propertyModel = new PropertyModel();
        $imageModel = new PropertyImageModel();
        $storage = new SupabaseStorageClient();

        // Silme izni: İlan mevcut mu ve bu tenant'a ait mi kontrol et
        $property = $propertyModel->getById($id);
        if (!$property) {
            $_SESSION['error'] = 'İlan bulunamadı veya bu kaydı silme yetkiniz yok.';
            $this->redirect('/emlak/public/portfoyler');
            return;
        }

        try {
            // Transaction başlat (silme işleminin atomikliği)
            // Model'ın db bağlantısını kullan (Base Model'dan erişilebilir)
            $dbConnection = (new PropertyModel())->getDb();
            if (!$dbConnection) {
                throw new \Exception('Veritabanı bağlantısı kurulamadı.');
            }
            $dbConnection->beginTransaction();

            // 1. İlana ait tüm görsellerin URL'lerini al
            $imagePaths = $imageModel->getImagePathsByPropertyId($id);

            // 2. Supabase Storage'tan sil (başarısız olanları log et, fakat devam et)
            if (!empty($imagePaths) && $storage->isConfigured()) {
                $deleteResult = $storage->deleteObjectsByPublicUrls($imagePaths);
                if (!empty($deleteResult['failedUrls'])) {
                    $failedCount = count($deleteResult['failedUrls']);
                    error_log("[PropertyController::delete] Supabase silme başarısız ({$failedCount}/{$deleteResult['totalCount']}): " . json_encode($deleteResult['failedUrls'], JSON_UNESCAPED_UNICODE));
                    // UYARI: Çoğu başarılıysa, DB'den de sil (yetim dosya kabul edilebilir)
                }
            }

            // 3. property_images tablosundan tüm görselleri sil
            if (!$imageModel->deleteAllByPropertyId($id)) {
                throw new \Exception('Veritabanından görseller silinemedi.');
            }

            // 4. properties tablosundan ilanı sil
            if (!$propertyModel->delete($id)) {
                throw new \Exception('İlan veritabanından silinemedi.');
            }

            // Transaction'ı commit et
            $dbConnection->commit();

            $_SESSION['success'] = 'İlan ve tüm görselleri başarıyla silindi.';
        } catch (\Exception $e) {
            // Hata oluşursa tüm işlemi geri al
            $dbConnection = (new PropertyModel())->getDb();
            if ($dbConnection && $dbConnection->inTransaction()) {
                $dbConnection->rollBack();
            }
            error_log('[PropertyController::delete] Transaction hatası (ID=' . $id . '): ' . $e->getMessage());
            $_SESSION['error'] = 'İlan silme işlemi başarısız oldu: ' . mb_substr($e->getMessage(), 0, 200, 'UTF-8');
        }

        $this->redirect('/emlak/public/portfoyler');
    }

    /**
     * Çoklu resim: geçici dosyayı Supabase Storage'a yükler, dönen herkese açık URL'i DB'ye yazar.
     */
    protected function handleImages(int $propertyId): void {
        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            return;
        }

        $storage = new SupabaseStorageClient();
        if (!$storage->isConfigured()) {
            $msg = 'Supabase Storage kullanılamıyor: SUPABASE_URL ve SUPABASE_SERVICE_ROLE_KEY '
                . 'ortam değişkenlerini ayarlayın. İsteğe bağlı: SUPABASE_STORAGE_BUCKET (varsayılan: ilan-fotograflari).';
            error_log('[PropertyController::handleImages] ' . $msg);
            $_SESSION['error'] = $msg;
            return;
        }

        $tenantId = (int) ($_SESSION['tenant_id'] ?? 0);
        if ($tenantId <= 0) {
            $msg = 'Oturumdaki ofis bilgisi bulunamadı; fotoğraf yüklenemedi.';
            error_log('[PropertyController::handleImages] ' . $msg);
            $_SESSION['error'] = $msg;
            return;
        }

        $mimeByExt = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'webp' => 'image/webp',
        ];

        $imageModel = new PropertyImageModel();
        $errors = [];

        $fileCount = count($_FILES['images']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Dosya ' . ($i + 1) . ': yükleme hatası kodu ' . (int) $_FILES['images']['error'][$i];
                }
                continue;
            }

            $tmpName = $_FILES['images']['tmp_name'][$i];
            $name = basename((string) $_FILES['images']['name'][$i]);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!isset($mimeByExt[$ext])) {
                $errors[] = 'Dosya ' . ($i + 1) . ' (“' . $name . '”): izin verilen uzantılar jpg, jpeg, png, webp.';
                continue;
            }

            $objectKey = 'tenant_' . $tenantId . '/prop_' . $propertyId . '_' . uniqid('', true) . '.' . $ext;
            $result = $storage->uploadObject($tmpName, $objectKey, $mimeByExt[$ext]);

            if (!empty($result['ok']) && !empty($result['publicUrl'])) {
                $publicUrl = (string) $result['publicUrl'];
                if (preg_match('#^https?://#i', $publicUrl) !== 1) {
                    $errors[] = 'Storage yanıtı geçersiz (tam public URL bekleniyordu).';
                    error_log('[PropertyController::handleImages] Geçersiz publicUrl: ' . substr($publicUrl, 0, 200));
                    continue;
                }
                if (!$imageModel->addImage($propertyId, $publicUrl)) {
                    $errors[] = 'Veritabanına kayıt eklenemedi (' . $objectKey . ').';
                }
            } else {
                $errors[] = $result['error'] ?? 'Bilinmeyen Storage hatası';
            }
        }

        if (!empty($errors)) {
            $joined = implode(' ', $errors);
            error_log('[PropertyController::handleImages] ' . $joined);
            $_SESSION['error'] = $joined;
        }
    }
}
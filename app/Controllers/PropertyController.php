<?php
namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\PropertyImageModel;
use App\Models\CustomerModel;

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
            $this->handleImages($propertyId);
            $_SESSION['success'] = "Yeni emlak kaydı portföye başarıyla eklendi.";
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
            $_SESSION['success'] = 'Emlak kaydı güncellendi.';
        } else {
             $_SESSION['error'] = 'Güncelleme hatası.';
        }
        $this->redirect('/emlak/public/portfoyler');
    }


    /**
     * Çoklu resim yükleme işlemini yönetir ve veritabanına PropertyImageModel ile yazar
     */
    protected function handleImages(int $propertyId): void {
        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/properties/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageModel = new PropertyImageModel();
        
        $fileCount = count($_FILES['images']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$i];
                $name    = basename($_FILES['images']['name'][$i]);
                
                // Güvenli dosya adı oluştur
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

                $newName = uniqid("prop_{$propertyId}_") . '.' . $ext;
                $targetFile = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    // public altındaki path'i veritabanına kaydet (/uploads/properties/abc.jpg)
                    $imageModel->addImage($propertyId, '/uploads/properties/' . $newName);
                }
            }
        }
    }
}
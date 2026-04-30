<?php
namespace App\Controllers;

use App\Models\ViewingModel;
use App\Models\CustomerModel;
use App\Models\PropertyModel;

/**
 * Randevu, Yer Gösterme ve Belge Çıktıları Yönetimi
 */
class ViewingController extends BaseController {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {
            $this->redirect('/emlak/public/auth/login');
        }
    }

    /**
     * Yer Gösterme Ajandasını Listele
     */
    public function index(): void {
        $model = new ViewingModel();
        $viewings = $model->getViewingsWithDetails();

        $this->render('viewings/index', [
            'pageTitle' => 'Ajanda & Yer Gösterme Takibi',
            'viewings'  => $viewings
        ]);
    }

    /**
     * Müşteri ve İlan Seçme Formunu Yükle
     */
    public function create(): void {
        $cModel = new CustomerModel();
        $pModel = new PropertyModel();
        
        $this->render('viewings/create', [
            'pageTitle'  => 'Yeni Yer Gösterme Kaydı / Randevusu',
            'customers'  => $cModel->getAll(),    // Formda Dropdown ile seçilmesi için
            'properties' => $pModel->getAll()     // Formda Dropdown ile seçilmesi için
        ]);
    }

    /**
     * Randevuyu Veritabanına Yazar
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/viewing/index');
            return;
        }

        // Güvenli PostgreSQL formatında veri yakalama
        $data = [
            'customer_id'  => (int) $_POST['customer_id'],
            'property_id'  => (int) $_POST['property_id'],
            'viewing_date' => $_POST['viewing_date'],  // Date string yollanacak örn: 2026-04-01 14:00
            'status'       => 'Bekliyor',
            'notes'        => trim((string) ($_POST['notes'] ?? ''))
        ];

        $model = new ViewingModel();
        if ($model->insert($data)) {
            $_SESSION['success'] = "Ajanda Kaydı Başarıyla Oluşturuldu!";
        } else {
            $_SESSION['error'] = "Randevu kaydedilirken bir veritabanı hatası oluştu.";
        }

        $this->redirect('/emlak/public/viewing/index');
    }

    /**
     * Resmi Matbu "Yer Gösterme Belgesini" Full A4 Formatında Çıkarır (Yazdırmaya Hazır)
     */
    public function printDocument($id = null): void {
        $viewingId = (int) $id;

        if ($viewingId <= 0) {
            $this->redirect('/emlak/public/viewing/index');
            return;
        }

        $model = new ViewingModel();
        $viewing = $model->getViewingDocumentData($viewingId);

        if (!$viewing) {
            $_SESSION['error'] = "Sözleşme verisine erişilemedi (Yetki eksik veya silinmiş).";
            $this->redirect('/emlak/public/viewing/index');
            return;
        }

        // Ana Main Layout olmaksızın, kendi Print CSS'leriyle çalışacak.
        $this->render('viewings/document', [
            'pageTitle' => 'Yer Gösterme Sözleşmesi No: ' . $viewing['id'],
            'doc'       => $viewing
        ]);
    }
}

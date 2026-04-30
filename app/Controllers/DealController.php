<?php
namespace App\Controllers;

use App\Models\PropertyModel;
use App\Models\CashboxModel;
use App\Models\DealModel;
use Config\Database;

class DealController extends BaseController {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {
            $this->redirect('/emlak/public/auth/login');
            exit;
        }
    }

    /**
     * "İşi Bağla" Sihirbazı kaydetme fonksiyonu
     * Form POST ile submit edilir, property bağlanır, komisyon kasaya işlenir.
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/property/index');
            return;
        }

        $propertyId = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
        $dealType = htmlspecialchars(trim($_POST['deal_type'] ?? 'Satış')); // 'Satış' veya 'Kiralama'
        $price = (float) filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $depositTaken = (float) filter_input(INPUT_POST, 'deposit_taken', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $commissionEarned = (float) filter_input(INPUT_POST, 'commission_earned', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (!$propertyId || !$customerId || $price <= 0) {
            $_SESSION['error'] = "Geçersiz değerler girdiniz.";
            $this->redirect('/emlak/public/property/index');
            return;
        }

        $db = Database::getInstance()->getConnection();
        
        try {
            // "Bir taşla üç kuş vuruyoruz" => Transaction Başlat
            $db->beginTransaction();

            $dealModel = new DealModel();
            $propertyModel = new PropertyModel();
            $cashboxModel = new CashboxModel();

            // 1. İşlemi deals (Satışlar) tablosuna kaydet
            $dealModel->insert([
                'property_id' => $propertyId,
                'customer_id' => $customerId,
                'deal_type' => $dealType,
                'price' => $price,
                'commission_earned' => $commissionEarned,
                'deposit_taken' => $depositTaken
            ]);

            // 2. İlanın durumunu 'sold' (Satıldı) veya 'rented' (Kiralandı) yap
            $newStatus = $dealType === 'Satış' ? 'sold' : 'rented';
            $propertyModel->update($propertyId, ['status' => $newStatus]);

            // 3. Alınan KOMİSYON 0'dan büyükse doğrudan 'Esnaf Kasasına' (cashbox) GEli̇R işle
            if ($commissionEarned > 0) {
                $categoryName = $dealType === 'Satış' ? 'Komisyon (Satılık)' : 'Komisyon (Kiralık)';
                $cashboxModel->insert([
                    'type' => 'Gelir',
                    'category' => $categoryName,
                    'amount' => $commissionEarned,
                    'description' => "İlan İşlemi (İlan #{$propertyId}, Müşteri #{$customerId})"
                ]);
            }

            // Transaction Onayla
            $db->commit();
            
            $_SESSION['success'] = "Tebrikler! İş bağlandı ve kasa başarıyla işlendi.";

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
        }

        $this->redirect('/emlak/public/property/index');
    }
}

<?php
namespace App\Controllers;

use App\Models\CashboxModel;

class CashboxController extends BaseController {

    private CashboxModel $cashboxModel;

    public function __construct() {
        // Zaten BaseController içinden multitenancy korunuyor
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/emlak/public/auth/login');
            exit;
        }
        $this->cashboxModel = new CashboxModel();
    }

    /**
     * Kasa Arayüzü: Gelir, Gider ve Tabloyu Gösterir (Pratik ve Anlaşılır).
     */
    public function index(): void {
        // Ay başından bugüne kadar olan toplamları çek.
        $monthlyIncome = $this->cashboxModel->getMonthlyIncome();
        $monthlyExpense = $this->cashboxModel->getMonthlyExpense();
        
        // Net Kâr (Komisyon)
        $netProfit = $monthlyIncome - $monthlyExpense;

        // İşlem geçmişi (Son 100 kaydı hızlı tablo için).
        $transactions = $this->cashboxModel->getAllTransactions(100);

        $this->render('cashbox/index', [
            'pageTitle' => 'Esnaf Kasası (Gelir & Gider)',
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'netProfit' => $netProfit,
            'transactions' => $transactions
        ]);
    }

    /**
     * Sadece kayıt işlemine ( POST request ) cevap verir.
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/cashbox/index');
            return;
        }

        // Form parametrelerini al (PHP 8 PHP Uyumlu)
        $type = htmlspecialchars(trim($_POST['type'] ?? '')); // 'Gelir' veya 'Gider'
        $category = htmlspecialchars(trim($_POST['category'] ?? ''));
        $amount = (float) filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));

        // Validasyon ve Kayıt
        if (in_array($type, ['Gelir', 'Gider']) && $amount > 0 && !empty($category)) {
            $this->cashboxModel->insert([
                'type' => $type,
                'category' => $category,
                'amount' => $amount,
                'description' => $description
                // transaction_date DB tarafından CURRENT_TIMESTAMP atanacak
            ]);

            $_SESSION['flash_message'] = "Kasa işlemi başarıyla kaydedildi.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Geçersiz değerler girdiniz!";
            $_SESSION['flash_type'] = "danger";
        }

        $this->redirect('/emlak/public/cashbox/index');
    }

    /**
     * Seçilen kaydı (Yanlış girildiyse) anında Sil (POST ile yapılır ki URL ile tetiklenmesin).
     */
    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $this->cashboxModel->delete($id);
                $_SESSION['flash_message'] = "Kasa kaydı başarıyla silindi.";
                $_SESSION['flash_type'] = "success";
            }
        }
        $this->redirect('/emlak/public/cashbox/index');
    }
}
